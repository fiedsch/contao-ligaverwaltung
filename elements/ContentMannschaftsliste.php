<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Content element "Liste aller Mannschaften einer Liga".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */
class ContentMannschaftsliste extends \ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_mannschaftsliste';

    /**
     * Generate the content element
     *
     * @return string
     */
    public function compile()
    {
        if ($this->liga == '') {
            return;
        }
        $mannschaften = \MannschaftModel::findByLiga($this->liga);
        if ($mannschaften === null) {
            return;
        }

        $listitems = [];
        foreach ($mannschaften as $mannschaft) {
            if ($mannschaft->teampage) {
                $teampage = \PageModel::findById($mannschaft->teampage);
                $listitem = sprintf("<a href='%s'>%s</a>",
                    \Controller::generateFrontendUrl($teampage->row()),
                    $mannschaft->name
                );
            } else {
                $listitem = $mannschaft->name;
            }
            $listitems[] = $listitem;
        }

        $this->Template->listitems = $listitems;

    }

}