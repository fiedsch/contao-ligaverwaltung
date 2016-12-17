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
class ContentSpielerliste extends \ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_spielerliste';

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
        $mannschaftsspieler = \SpielerModel::findByPid($this->mannschaft);
        if ($mannschaftsspieler === null) {
            return '';
        }

        $listitems = [];
        foreach ($mannschaftsspieler as $spieler) {
            $member = $spieler->getRelated('member_id');
            $listitems[] = sprintf("%s, %s", $member->lastname, $member->firstname);
        }

        $this->Template->listitems = $listitems;

    }

}