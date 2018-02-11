<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Historie eines Spielers == In welchen Mannschaften (und damit Ligen) hat er/sie
 * im Verlauf der Zeit gespielt?
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */
class ModuleSpielerHistory extends \BackendModule
{

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'be_spielerhistory';

    public function compile()
    {
        $history = [];
        // $this->Template->member = \MemberModel::findById(\Input::get('id'));
        $spieler = \SpielerModel::findBy(['member_id=?'], [\Input::get('id')], ['pid ASC']);
        if ($spieler) {
            foreach ($spieler as $sp) {
                $liga = $sp->getRelated('pid')->getRelated('liga');
                $history[] = [
                  'mannschaft' => $sp->getRelated('pid')->name,
                  'saison' => $liga->name . ' '. $liga->getRelated('saison')->name,

                ];
            }
        }
        $this->Template->history = $history;
        // $this->Template->spieler = $spieler;


    }

}
