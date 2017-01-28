<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\Liga;

class DCAHelper
{

    /* Helper für tl_verband */

    /**
     * @param $row
     * @param $label
     * @return string
     */
    public static function verbandLabelCallback($row, $label)
    {
        $ligen = \Database::getInstance()
            ->prepare("SELECT COUNT(*) n FROM tl_liga WHERE pid=?")
            ->execute($row['id']);
        return sprintf("%s (%d Ligen)", $label, $ligen->n);
    }

    /* Helper für tl_liga */

    /**
     *
     * * ('child_record_callback' in tl_liga)
     *
     * @param $arrRow
     * @return string
     */
    public static function ligaListCallback($arrRow)
    {
        $begegnungen = \Database::getInstance()
            ->prepare("SELECT COUNT(*) n FROM tl_begegnung WHERE pid=?")
            ->execute($arrRow['id']);
        return self::ligaLabelCallback($arrRow, $arrRow['name'])
            . sprintf(" (%d Begegnungen)", $begegnungen->n)//. ' <span class="tl_gray">'. json_encode($arrRow).'</span>'
            ;
    }

    /**
     * Label für eine Liga
     * * ('label_callback' in tl_mannschaft)
     *
     * @param $row
     * @param $label
     * @return string
     */
    public static function ligaLabelCallback($row, $label)
    {
        $saison = \SaisonModel::findById($row['saison']);
        $class = $row['aktiv'] ? 'tl_green' : 'tl_gray';
        return sprintf("<span class='%s'>%s %s</span>", $class, $label, $saison->name);
    }

    /* Helper für tl_mannschaft */

    /**
     * Label für eine Mannschaft
     * ('child_record_callback' in tl_mannschaft)
     *
     * @param $arrRow
     * @return string
     */
    public static function mannschaftLabelCallback($arrRow)
    {
        $liga = \LigaModel::findById($arrRow['liga']);
        if (!$liga) {
            return sprintf("%s <span class='tl_red'>Liga '%d' existiert nicht mehr!</span>",
                $arrRow['name'],
                $arrRow['liga']);
        }
        $spielort = \SpielortModel::findById($arrRow['spielort']);
        $spieler = \Database::getInstance()
            ->prepare("SELECT COUNT(*) AS n FROM tl_spieler WHERE pid=?")
            ->execute($arrRow['id']);
        $anzahlSpieler = '<span class="tl_red">keine Spieler eingetragen</span>';
        if ($spieler->n > 0) {
            $anzahlSpieler = sprintf("%d Spieler", $spieler->n);
        }

        return sprintf('<div class="tl_content_left">%s, %s %s %s (%s, %s)</div>',
            $arrRow['name'],
            $liga->getRelated('pid')->name,
            $liga->name,
            $liga->getRelated('saison')->name,
            $spielort->name,
            $anzahlSpieler
        );
    }

    /**
     * Alle zur Vefügung stehenden Ligen
     * ('options_callback' in tl_mannschaft)
     *
     * @param \DataContainer $dc
     */
    public
    static function getLigaForSelect(\DataContainer $dc)
    {
        $result = [];
        $ligen = \LigaModel::findAll();

        if (null === $ligen) {
            return ['0' => 'keine Ligen gefunden. Btte erst anlegen!'];
        }
        foreach ($ligen as $liga) {
            $result[$liga->id] = sprintf("%s %s %s",
                $liga->getRelated('pid')->name,
                $liga->name,
                $liga->getRelated('saison')->name
            );
        }
        return $result;
    }



    /* Helper für tl_begegnung */

    /**
     *
     * * ('child_record_callback' in tl_begegnung)
     *
     * @param $arrRow
     * @return string
     */
    public static function listBegegnungCallback($arrRow)
    {
        $home = \MannschaftModel::findById($arrRow['home']);
        $away = \MannschaftModel::findById($arrRow['away']);

        return sprintf("%s vs %s",
            $home->name,
            $away->name
        );
    }

    /**
     * Label für eine Begegnung (Spiel zweier Mansnchaften gegeneinander)
     * ('label_callback' in tl_begegnung)
     *
     * @param array $row
     * @param string $label
     * @return string
     */
    public static function labelBegegnungCallback($row, $label)
    {
        $liga = \LigaModel::findById($row['pid']);
        $verband = \VerbandModel::findById($liga->pid);
        $home = \MannschaftModel::findById($row['home']);
        $away = \MannschaftModel::findById($row['away']);
        $spiele = \SpielModel::findByPid($row['id']);
        $spieleHinterlegt = count($spiele) > 0 ? sprintf('(%d Spiele)', count($spiele)) : '';
        $score_home = $score_away = 0;
        if ($spiele) {
            foreach ($spiele as $spiel) {
                $punkte_home += $spiel->score_home > $spiel->score_away ? 1 : 0;
                $punkte_away += $spiel->score_home < $spiel->score_away ? 1 : 0;
            }
        }
        $final_score = $punkte_home + $punkte_away > 0 ? sprintf('%d:%d', $punkte_home, $punkte_away) : '';
        return sprintf("<span class='tl_gray'>%s %s %s %d. Spieltag:</span> 
                        <span class='tl_blue'>%s vs %s</span> 
                        <span class='tl_green'>%s</span> 
                        <span class='tl_gray'>%s</span>",
            $verband->name,
            $liga->name,
            $liga->getRelated('saison')->name,
            $row['spiel_tag'],
            $home->name,
            $away->name,
            $final_score,
            $spieleHinterlegt
        );
    }

    /**
     * Einträge für ein Ligaauswahl Dropdown
     * ('options_callback' in tl_begegnung)
     *
     * @param \DataContainer $dc
     * @return array
     */
    public static function getAktiveLigenForSelect(\DataContainer $dc)
    {
        $result = [];
        $ligen = \LigaModel::findBy(['aktiv=?'], ['1']);
        if (null === $ligen) {
            return ['0' => 'keine Ligen gefunden!'];
        }
        foreach ($ligen as $liga) {
            $result[$liga->id] = sprintf("%s %s %s", $liga->getRelated('pid')->name, $liga->name, $liga->getRelated('saison')->name);
        }
        return $result;
    }

    /**
     * Einträge für ein Mannschaftsauswahl Dropdown -- nur aktive Mannschaften
     * ('options_callback' in tl_begegnung)
     *
     * @param \DataContainer $dc
     * @return array
     */
    public static function getMannschaftenForSelect(\DataContainer $dc)
    {
        $result = [];
        if ($dc->activeRecord->pid) {
            // Callback beim bearbeiten einer Begegnung
            $mannschaften = \MannschaftModel::findByLiga($dc->activeRecord->pid);
        } else {
            // Callback im Listview (Filter:)
            $mannschaften = \MannschaftModel::findAllActive();
        }

        if (null === $mannschaften) {
            return ['0' => 'keine Mannschaften gefunden. Bitte erst anlegen un dieser Liga zuordnen!'];
        }
        foreach ($mannschaften as $mannschaft) {
            $result[$mannschaft->id] = $mannschaft->name;
        }
        return $result;
    }


    /* Helper für tl_spieler */

    /**
     * Einträge für ein Spielerauswahl Dropdown.
     * ('options_callback' in tl_spieler)
     *
     * @param \DataContainer $dc
     * @return array
     */
    public static function getSpielerForSelect(\DataContainer $dc)
    {
        $result = [];
        // Wird ein bestehender Record editiert, dann das zugehörige Member in
        // das $result aufnehmen, da der folgende $query es ja nicht finden würde
        // weil es bereits in der Datenbank eingetragen und somit "im Einsatz" ist.
        if ($dc->activeRecord->member_id) {
            $member = \MemberModel::findById($dc->activeRecord->member_id);
            $result[$member->id] = sprintf("%s, %s", $member->lastname, $member->firstname);
        }


        if (\Config::get('ligaverwaltung_exclusive_model') == 1) {
            // Modell I (edart-bayern.de-Modell);
            // Alle Spieler, die nicht bereits in einer (anderen) Mannschaft in einer
            // Liga spielen, die "in der gleichen Saison ist" (unabhängig von der Liga)
            // wie die aktuell betrachtete.
            // Annahme: ein Spieler darf in einer Saison nur in einer Mannschaft spielen!

            $saison = \MannschaftModel::findById($dc->activeRecord->pid)->getRelated('liga')->saison;

            $query =
                'SELECT * FROM tl_member WHERE id NOT IN ('
                . ' SELECT s.member_id FROM tl_spieler s'
                . ' LEFT JOIN tl_mannschaft m ON (s.pid=m.id)'
                . ' LEFT JOIN tl_liga l ON (m.liga=l.id)'
                . ' WHERE l.saison=?'
                . ')';
            $member = \Database::getInstance()->prepare($query)->execute($saison);
        } else {
            // Modell II harlekin Modell (weniger restriktiv):
            // Alle Spieler, die nicht bereits in einer (anderen) Mannschaft in der gleichen
            // Liga spielen.
            // Annahme: ein Spieler darf in einer Liga nur in einer Mannschaft spielen!

            $liga = \MannschaftModel::findById($dc->activeRecord->pid)->getRelated('liga')->id;

            $query =
                'SELECT * FROM tl_member WHERE id NOT IN ('
                . ' SELECT s.member_id FROM tl_spieler s'
                . ' LEFT JOIN tl_mannschaft m ON (s.pid=m.id)'
                . ' WHERE m.liga=?'
                . ')';
            $member = \Database::getInstance()->prepare($query)->execute($liga);
        }


        while ($member->next()) {
            $result[$member->id] = sprintf("%s, %s", $member->lastname, $member->firstname);
        }
        return $result;
    }

    /**
     * Return HTML Code to display one team member
     * ('child_record_callback' in tl_spieler)
     *
     * @param $arrRow
     * @return string
     */
    public static function listMemberCallback($arrRow)
    {
        $member = \MemberModel::findById($arrRow['member_id']);

        $teamcaptain_label = $arrRow['teamcaptain'] ? ('(Teamcaptain: ' . $member->email . ')') : '';
        $co_teamcaptain_label = $arrRow['co_teamcaptain'] ? ('(Co-Teamcaptain: ' . $member->email . ')') : '';

        return sprintf('<div class="tl_content_left">%s, %s %s%s</div>',
            $member->lastname,
            $member->firstname,
            $teamcaptain_label,
            $co_teamcaptain_label
        );
    }

    /**
     * Button um das zum Spieler gehörige Mitglied (tl_member) in einem Modal-Window bearbeiten zu können
     * ('wizard' in tl_spieler)
     *
     * @param \DataContainer $dc
     * @return string
     */
    public
    static function editMemberWizard(\DataContainer $dc)
    {
        if ($dc->value < 1) {
            return '';
        }
        return '<a href="contao/main.php?do=member&amp;&amp;act=edit&amp;id=' . $dc->value
            . '&amp;popup=1&amp;&amp;rt=' . REQUEST_TOKEN
            . '" title="' . specialchars($GLOBALS['TL_LANG']['tl_spieler']['editmember'][1]) . '"'
            . ' style="padding-left:3px" onclick="Backend.openModalIframe({\'width\':768,\'title\':\''
            . specialchars(str_replace("'", "\\'", specialchars($GLOBALS['TL_LANG']['tl_spieler']['editmember'][1])))
            . '\',\'url\':this.href});return false">'
            . \Image::getHtml('alias.gif', $GLOBALS['TL_LANG']['tl_spieler']['editmember'][1], 'style="vertical-align:top"')
            . '</a>';
    }


    /* Helper für tl_spiel */

    /**
     * Spieler der Heimmannschaft
     * ('options_callback' in tl_spiel)
     *
     * @param DataContaner|DC_Table $dc
     */
    public
    static function getHomeSpielerForSelect($dc)
    {
        if (!$dc->activeRecord->pid) {
            return [];
        }
        $begegnung = \BegegnungModel::findById($dc->activeRecord->pid);
        if (!$begegnung) {
            return [];
        }
        $result = [];
        $query = 'SELECT * FROM tl_member WHERE id IN (SELECT member_id FROM tl_spieler s WHERE pid=?)';
        $member = \Database::getInstance()->prepare($query)->execute($begegnung->home);

        if ($member) {
            while ($member->next()) {
                $result[$member->id] = sprintf("%s, %s", $member->lastname, $member->firstname);
            }
        }
        return $result;
    }

    /**
     * Spieler der Gastmannschaft
     * ('options_callback' in tl_spiel)
     *
     * @param DataContaner|DC_Table $dc
     */
    public static function getAwaySpielerForSelect($dc)
    {
        if (!$dc->activeRecord->pid) {
            return [];
        }
        $begegnung = \BegegnungModel::findById($dc->activeRecord->pid);
        $result = [];
        $query = 'SELECT * FROM tl_member WHERE id IN (SELECT member_id FROM tl_spieler s WHERE pid=?)';
        $member = \Database::getInstance()->prepare($query)->execute($begegnung->away);
        if ($member) {
            while ($member->next()) {
                $result[$member->id] = sprintf("%s, %s", $member->lastname, $member->firstname);
            }
        }
        return $result;
    }

    /**
     * Label für ein Spiel
     * ('child_record_callback' in tl_spiel)
     *
     * @param array $row
     */
    public static function listSpielCallback($row)
    {
        $memberHome = \MemberModel::findById($row['home']);
        $memberAway = \MemberModel::findById($row['away']);
        return sprintf("%s : %s <span class='tl_gray'>%d:%d</span>",
            sprintf("%s, %s", $memberHome->lastname, $memberHome->firstname),
            sprintf("%s, %s", $memberAway->lastname, $memberAway->firstname),
            $row['score_home'],
            $row['score_away']
        );
    }


    /* Helper für tl_content */


    /**
     * Liste aller definierten Verbände
     * ('options_callback' in tl_content)
     *
     * @param \DataContainer $dc
     * @return array
     */
    public static function getAlleVerbaendeForSelect(\DataContainer $dc)
    {
        $result = [];
        $verbaende = \VerbandModel::findAll();
        if (null === $verbaende) {
            return ['0' => 'keine Verbände gefunden!'];
        }
        foreach ($verbaende as $verband) {
            $result[$verband->id] = $verband->name;
        }
        return $result;
    }

    /**
     * Liste aller definierte Ligen
     * ('options_callback' in tl_content)
     *
     * @param \DataContainer $dc
     * @return array
     */
    public static function getAlleLigenForSelect(\DataContainer $dc)
    {
        $result = [];
        $ligen = \LigaModel::findAll();
        if (null === $ligen) {
            return ['0' => 'keine Ligen gefunden!'];
        }
        foreach ($ligen as $liga) {
            $result[$liga->id] = sprintf("%s %s %s",
                $liga->name,
                $liga->getRelated('pid')->name,
                $liga->getRelated('saison')->name
            );
        }
        return $result;
    }

    /**
     * Einträge für ein Mannschaftsauswahl Dropdown. Da hier alle Ligen aller Saisons in
     * Betracht kommen und eine Mannschaft gleichen Namens daher mehrfach auftaucht,
     * hängen wir Liga und Saison an, um die Auswahl eindeutig zu machen.
     * ('options_callback' in tl_content)
     *
     * @param \DataContainer $dc
     * @return array
     */
    public static function getAlleMannschaftenForSelect(\DataContainer $dc)
    {
        $result = [];
        if ($dc->activeRecord->liga) {
            $mannschaften = \MannschaftModel::findByLiga($dc->activeRecord->liga, ['order' => 'name ASC']);
        } else {
            $mannschaften = \MannschaftModel::findAll(['order' => 'name ASC']);
        }
        if (null === $mannschaften) {
            return ['0' => 'keine Mannschaften gefunden. Liga wählen und speichern!'];
        }
        foreach ($mannschaften as $mannschaft) {
            $result[$mannschaft->id] = sprintf("%s (%s %s)",
                $mannschaft->name,
                $mannschaft->getRelated('liga')->name,
                $mannschaft->getRelated('liga')->getRelated('saison')->name
            );
        }
        return $result;
    }

}