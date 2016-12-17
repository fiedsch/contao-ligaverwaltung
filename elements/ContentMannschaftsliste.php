<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Content element "csvtable".
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
            return '';
        }
        $mannschaften = \MannschaftModel::findByLiga($this->liga);
        if ($mannschaften === null) {
            return '';
        }

        $listitems = [];
        foreach ($mannschaften as $mannschaft) {
            $listitems[] = sprintf("%s", $mannschaft->name);
        }

        $this->Template->listitems = $listitems;

    }

}