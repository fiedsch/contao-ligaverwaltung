<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Module "Begegnungserfassung". Vue.js Formular anzeigen und die gePOSTeten Daten
 * in tl_spiel (child records von tl_begegnug) abspeichern
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */
class ModuleBegegnungserfassung extends \BackendModule
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'be_begegnungserfassung';

    public function compile()
    {
        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/ligaverwaltung/assets/vue.2.1.6.js|static';
        // Wird am Ende des Templates included:
        //$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/ligaverwaltung/assets/main.js|static';
        $GLOBALS['TL_CSS'][] = 'system/modules/ligaverwaltung/assets/begegnungserfassung.css|static';

        if ('begegnungserfassung' === \Input::post('FORM_SUBMIT')) {
            $this->Template->form = $_POST;
        } else {
            // TODO Teams belegen und an das Template weiterreichen, das sie dann in
            // data.home bzw. data.away einbaut
            $begegnung = \BegegnungModel::findById(\Input::get('id'));
            if (null !== $begegnung) {
                $team_home = [];
                $team_away = [];
                foreach (['home', 'away'] as $homeaway) {
                    $spieler = \SpielerModel::findBy(
                        ['pid=?'],
                        [$begegnung->$homeaway]
                    );
                    if ($spieler) {
                        foreach ($spieler as $s) {
                            $player_name = sprintf("Team %s, %d, %s, %s",
                                $homeaway,
                                $begegnung->$homeaway,
                                $s->getRelated('member_id')->lastname,
                                $s->getRelated('member_id')->firstname
                            );
                            if ($homeaway === 'home') {
                                $team_home[] = sprintf("{name: '%s'}", $player_name);
                            } else {
                                $team_away[] = sprintf("{name: '%s'}", $player_name);
                            }
                        }
                    }
                }
                // auf 6 Spieler "auffüllen"
                for ($i=count($team_home); $i<6; $i++) { $team_home[] = sprintf("{name: '%s'}", 'no player'.$i); }
                for ($i=count($team_away); $i<6; $i++) { $team_away[] = sprintf("{name: '%s'}", 'no player'.$i); }
                $this->Template->team_home_players = join(',', $team_home);
                $this->Template->team_away_players = join(',', $team_away);
            }

        }

        // Nach erfolgreicher Erfassung zu
        // contao/main.php?do=liga.verband&table=tl_spiel&id=67
        //                                                   ^^Begegnung
        // weiterleiten

    }

}