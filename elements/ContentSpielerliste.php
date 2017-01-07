<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Content element "Liste aller Spieler einer Mannaschft".
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
            return;
        }
        //$mannschaftsspieler = \SpielerModel::findByPid($this->mannschaft);
        $mannschaftsspieler = \SpielerModel::findAll([
            'column' => ['pid=?'],
            'value'  => [$this->mannschaft],
            'order'  => 'teamcaptain DESC, co_teamcaptain DESC, lastname ASC, firstname ASC',
        ]);
        if ($mannschaftsspieler === null) {
            return;
        }

        $listitems = [];
        foreach ($mannschaftsspieler as $spieler) {
            $member = $spieler->getRelated('member_id');
            $listitems[] = ['member' => $member, 'spieler' => $spieler];
        }

        $this->Template->listitems = $listitems;
    }

}