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
    public static function spielLabelCallback($row, $label)
    {
        $liga = \LigaModel::findById($row['liga']);
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
     * ('options_callback' in tl_spiel)
     *
     * @param \DataContainer $dc
     * @return array
     */
    public function getAktiveLigenForSelect(\DataContainer $dc)
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
    public function getAlleLigenForSelect(\DataContainer $dc)
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
     * ('options_callback' in tl_spiel)
     *
     * @param \DataContainer $dc
     * @return array
     */
    public function getMannschaftenForSelect(\DataContainer $dc)
    {
        $result = [];
        if (!$dc->activeRecord->liga) {
            $mannschaften = \MannschaftModel::findAllActive();
        } else {
            $mannschaften = \MannschaftModel::findByLiga($dc->activeRecord->liga);
        }
        if (null === $mannschaften) {
            return ['0' => 'keine Mannschaften gefunden. Liga wählen und speichern!'];
        }
        foreach ($mannschaften as $mannschaft) {
            $result[$mannschaft->id] = $mannschaft->name;
        }
        return $result;
    }

    /**
     * Einträge für ein Mannschaftsauswahl Dropdown -- auch inaktive Mannschaften,
     * daher um Namen der Liga erweitert.
     * ('options_callback' in tl_content)
     *
     * @param \DataContainer $dc
     * @return array
     */
    public function getAlleMannschaftenForSelect(\DataContainer $dc)
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
     * Return HTML Code to display one team member
     * ('child_record_callback' in tl_spieler)
     *
     * @param $arrRow
     * @return string
     */
    public
    function listMemberCallback($arrRow)
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
     * Das zum Spieler gehörige Mitglied (tl_member) in einem Modal-Window bearbeiten
     * ('wizard' in tl_spieler)
     *
     * @param \DataContainer $dc
     * @return string
     */
    public
    function editMemberWizard(\DataContainer $dc)
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