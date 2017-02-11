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
     * Anzahl der Spieler pro Mannschaft (inkl. Austauschspieler) wie sie von der
     * Vue.js App erwartet wird (siehe :slots="6").
     */
    const NUM_PLAYERS = 6;
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'be_begegnungserfassung';

    public function compile()
    {
        // Aufruf über den Menüpunkte
        if (\Input::get('id') <= 0) {
            $this->Template->message = sprintf('Aufruf bitte über den Menüpunkt <a href="%s">%s</a>!',
                'contao/main.php?do=liga.begegnung',
                'Begegnungen'
            );
            return;
        }

        if ('begegnungserfassung' === \Input::post('FORM_SUBMIT')) {
            // Daten verarbeiten:
            $this->saveFormData();
            return;
        } else {
            // Daten erfassen
            $this->generateForm();
        }
        // Nach erfolgreicher Erfassung zu
        // contao/main.php?do=liga.verband&table=tl_spiel&id=67
        //                                                   ^^Begegnung
        // weiterleiten

    }

    /**
     *
     */
    protected function saveFormData()
    {
        $this->Template->form = $_POST;
        $this->Template->message = sprintf('Daten wurden erfasst! (<a href="%s">%s</a>)<pre>%s</pre>)',
            'contao/main.php?do=liga.begegnung',
            'Zu den Begegnungen',
            print_r($_POST, true)
        );
    }

    /**
     *
     */
    protected function generateForm()
    {
        $GLOBALS['TL_CSS'][] = 'system/modules/ligaverwaltung/assets/begegnungserfassung.css|static';
        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/ligaverwaltung/assets/vue.2.1.6.js|static';
        // Wird am Ende des Templates included:
        //$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/ligaverwaltung/assets/main.js|static';

        // Teams belegen
        $begegnung = \BegegnungModel::findById(\Input::get('id'));
        if (null !== $begegnung) {
            $this->Template->begegnung = $begegnung->id;
            $team_name['home'] = $begegnung->getRelated('home')->name;
            $team_name['away'] = $begegnung->getRelated('away')->name;
            $this->Template->team_home_name = $team_name['home'];
            $this->Template->team_away_name = $team_name['away'];
            $team_home = [];
            $team_away = [];
            foreach (['home', 'away'] as $homeaway) {
                $spieler = \SpielerModel::findBy(
                    ['pid=?'],
                    [$begegnung->$homeaway]
                );
                if ($spieler) {
                    foreach ($spieler as $s) {
                        $player_name = sprintf("%s, %s",
                            $s->getRelated('member_id')->lastname,
                            $s->getRelated('member_id')->firstname
                        );
                        if ($homeaway === 'home') {
                            $team_home[] = sprintf("{name: '%s', id: %d}", $player_name, $s->id);
                        } else {
                            $team_away[] = sprintf("{name: '%s', id: %d}", $player_name, $s->id);
                        }
                    }
                }
            }
            // auf mindestens self::NUM_PLAYERS Spieler "auffüllen"
            for ($i = count($team_home); $i < self::NUM_PLAYERS; $i++) {
                $team_home[] = sprintf("{name: '%s',id: -%d}", 'no player' . $i, $i);
            }
            for ($i = count($team_away); $i < self::NUM_PLAYERS; $i++) {
                $team_away[] = sprintf("{name: '%s',id: -%d}", 'no player' . $i, $i);
            }
            $this->Template->team_home_players = join(',', $team_home);
            $this->Template->team_away_players = join(',', $team_away);
        }
    }

}