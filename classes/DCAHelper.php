<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\Liga;

class DCAHelper
{

    /**
     * @param array $row
     * @param string $label
     * @return string
     */
    public static function begegnungLabelCallback($row, $label)
    {
        $liga = \LigaModel::findById($row['pid']);
        $home = \MannschaftModel::findById($row['home']);
        $away = \MannschaftModel::findById($row['away']);
        return sprintf("%s %s <span class='tl_blue'>%s vs %s</span>",
            $liga->name,
            $liga->getRelated('saison')->name,
            $home->name,
            $away->name
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
            $result[$liga->id] = sprintf("%s %s", $liga->name, $liga->getRelated('saison')->name);
        }
        return $result;
    }

    /**
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
            $result[$liga->id] = sprintf("%s %s", $liga->name, $liga->getRelated('saison')->name);
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
        if (!$dc->activeRecord->liga) {
            $mannschaften = \MannschaftModel::findAll();
        } else {
            $mannschaften = \MannschaftModel::findByLiga($dc->activeRecord->liga);
        }
        if (null === $mannschaften) {
            return ['0' => 'keine Mannschaften gefunden. Liga wählen und speichern!'];
        }
        foreach ($mannschaften as $mannschaft) {
            $result[$mannschaft->id] = sprintf("%s (%s %s)",
                $mannschaft->name,
                $mannschaft->getRelated('pid')->name,
                $mannschaft->getRelated('pid')->getRelated('saison')->name
            );
        }
        return $result;
    }

    /**
     * @param \DataContainer $dc
     */
    public static function getLigaForSelect(\DataContainer $dc)
    {
        $result = [];
        $ligen = \LigaModel::findAll();

        if (null === $ligen) {
            return ['0' => 'keine Ligen gefunden. Btte erst anlegen!'];
        }
        foreach ($ligen as $liga) {
            $result[$liga->id] = sprintf("%s %s",
                $liga->name,
                $liga->getRelated('saison')->name
            );
        }
        return $result;
    }

    /**
     * Einträge für ein Spielerauswahl Dropdown.
     * ('options_callback' in tl_mannschaft)
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

        // Alle Spieler, die nicht bereits in einer (anderen) Mannschaft in einer
        // Liga spielen, die "in der gleichen Saison ist" (unabhängig von der Liga)
        // wie die aktuell betrachtete.
        // Annahme: ein Spieler darf in einer Saison nur in einer Mannschaft spielen!

        $saison = \MannschaftModel::findById($dc->activeRecord->pid)->getRelated('pid')->saison;

        $query =
            'SELECT * FROM tl_member WHERE id NOT IN ('
            . ' SELECT s.member_id FROM tl_spieler s'
            . ' LEFT JOIN tl_mannschaft m ON (s.pid=m.id)'
            . ' LEFT JOIN tl_liga l ON (m.pid=l.id)'
            . ' WHERE l.saison=?'
            . ')';
        $member = \Database::getInstance()->prepare($query)->execute($saison);
        while ($member->next()) {
            $result[$member->id] = sprintf("%s, %s", $member->lastname, $member->firstname);
        }
        // }
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
        //return json_encode($arrRow);
        $member = \MemberModel::findById($arrRow['member_id']);

        $teamcaptain_label = $arrRow['teamcaptain'] ? ('(Teamcaptain: ' . $member->email . ')') : '';

        return sprintf('<div class="tl_content_left">%s, %s %s</div>',
            $member->lastname,
            $member->firstname,
            $teamcaptain_label
        );
    }

    /**
     * Return HTML Code to display one team
     * ('child_record_callback' in tl_mannschaft_link)
     *
     * @param $arrRow
     * @return string
     */
    public static function listMannschaftLinkCallback($arrRow)
    {
        $mannschaft = \MannschaftModel::findById($arrRow['mannschaft_id']);
        if (!$mannschaft) {
            return sprintf("Mannschaft mit der ID %d nicht gefunden", $arrRow['mannschaft_id']);
        }
        return sprintf("%s",
            $mannschaft->name
        );
    }

    /**
     * Return HTML Code to display one team
     * ('child_record_callback' in tl_mannschaft)
     *
     * @param $arrRow
     * @return string
     */
    public static function mannschaftLabelCallback($arrRow)
    {
        $liga = \LigaModel::findById($arrRow['liga']);
        $spielort = \SpielortModel::findById($arrRow['spielort']);

        return sprintf('<div class="tl_content_left">%s, %s %s (%s)</div>',
            $arrRow['name'],
            $liga->name,
            $liga->getRelated('saison')->name,
            $spielort->name
        );
    }

    /**
     * Das zum Spieler gehörige Mitglied (tl_member) in einem Modal-Window bearbeiten
     * ('wizard' in tl_spieler)
     *
     * @param \DataContainer $dc
     * @return string
     */
    public static function editMemberWizard(\DataContainer $dc)
    {
        if ($dc->value < 1) {
            return '';
        }
        return '<a href="contao/main.php?do=member&amp;&amp;act=edit&amp;id=' . $dc->value
            . '&amp;popup=1&amp;&amp;rt=' . REQUEST_TOKEN
            . '" title="' . specialchars($GLOBALS['TL_LANG']['tl_spieler']['edit'][1]) . '"'
            . ' style="padding-left:3px" onclick="Backend.openModalIframe({\'width\':768,\'title\':\''
            . specialchars(str_replace("'", "\\'", specialchars($GLOBALS['TL_LANG']['tl_spieler']['edit'][1])))
            . '\',\'url\':this.href});return false">'
            . Image::getHtml('alias.gif', $GLOBALS['TL_LANG']['tl_spieler']['edit'][1], 'style="vertical-align:top"')
            . '</a>';
    }

}