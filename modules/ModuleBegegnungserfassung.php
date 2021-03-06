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
     * Vue.js App erwartet wird (vgl. :slots="8").
     */
    const NUM_PLAYERS = 8;

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
            \Controller::redirect(sprintf('contao/main.php?do=liga.begegnung&rt=%s', REQUEST_TOKEN));
        }

        if ('begegnungserfassung' === \Input::post('FORM_SUBMIT')) {
            // Daten verarbeiten:
            $this->saveFormData();
        } else {
            // Daten erfassen
            $this->generateForm();
        }
    }

    /**
     * TODO: Situation erkennen (und behandeln) wenn beim erneuten Bearbeiten
     * durch geänderte Spielerreihenfolge andere (neue!) Spiele erzeugt werden,
     * die alten aber nicht gelöscht werden.
     * Immer erst alle Spiele löschen und dann alles neu anlegen (meist identisch)
     * ist auch nicht schön und erhöht unnötig die tl_spiel.id
     */
    protected function saveFormData()
    {
        $data = $this->scanPostData();

        $this->saveSpiele($data);

        \Controller::redirect(sprintf('contao/main.php?do=liga.begegnung&table=tl_spiel&id=%s&rt=%s',
            \Input::post('id'),
            REQUEST_TOKEN
        ));
    }

    /**
     * Die (rohen) POST Daten scannen und einen "Datenbaum" aufbauen, der beim
     *  speichern der Spiele in der Datenbank abgearbeit werden kann.
     */
    protected function scanPostData()
    {
        foreach ($_POST as $k => $v) {
            switch ($k) {
                case 'id':
                    $data['begegnung'] = $v;
                    $begegnung = \BegegnungModel::findById($v);

                    if ($begegnung) {
                        $data['home'] = [
                            'name' => $begegnung->getRelated('home')->name,
                            'id'   => $begegnung->getRelated('home')->id,
                        ];
                        $data['away'] = [
                            'name' => $begegnung->getRelated('away')->name,
                            'id'   => $begegnung->getRelated('away')->id,
                        ];
                    }
                    break;
                case 'homelineup':
                    $data['lineup']['home'] = explode(',', $v);
                    break;
                case 'awaylineup':
                    $data['lineup']['away'] =  explode(',', $v);
                    break;
                case 'REQUEST_TOKEN':
                case 'FORM_SUBMIT':
                    // ignorieren
                    break;

                default:
                    if (preg_match("/^spieler_(home|away)_(\d+)$/", $k, $matches)) {
                        // Einzel (Spieler)
                        $mapped_id = $data['lineup'][$matches[1]][$v];
                        $data['spiele'][$matches[2]][$matches[1]]['spieler'] = [
                            'name' => $this->getSpielerName($mapped_id),
                            'id'   => $mapped_id,
                        ];
                    } else if (preg_match("/^spieler_(home|away)_(\d+)_(\d+)$/", $k, $matches)) {
                        // Doppel (Spieler)
                        $mapped_id = $data['lineup'][$matches[1]][$v];
                        $data['spiele'][$matches[2]][$matches[1]]['spieler'][$matches[3]] = [
                            'name' => $this->getSpielerName($mapped_id),
                            'id'   => $mapped_id,
                        ];
                    } else if (preg_match("/^score_(home|away)_(\d+)$/", $k, $matches)) {
                        // Score (Einzel und Doppel)
                        $data['spiele'][$matches[2]][$matches[1]]['score'] = $v;
                    } else {
                        $data['TODO'][] = sprintf("%s = %s\n", $k, $v);
                    }
            }
        }
        return $data;
    }

    /**
     * @param int $id
     * @return string
     */
    protected function getSpielerName($id)
    {
        $spieler = \SpielerModel::findById($id);
        if (!$spieler) {
            return "Spieler mit der ID $id nicht gefunden";
        }
        $member = $spieler->getRelated('member_id');
        if (!$member) {
            return "Mitglied zum Spieler mit der ID $id nicht gefunden";
        }
        return \Fiedsch\Liga\DCAHelper::makeSpielerName($member->firstname, $member->lastname);
    }

    /**
     * @param array $data
     */
    protected function saveSpiele($data)
    {
        // über $data['spiele'] iterieren und je Eintrag
        // * checken, ob es bereits einen zugehörigen Eintrag in tl_spiel gibt
        //   - falls nein, anlegen1
        //   - falls ja, ändern
        //
        // Dabei mit den "nicht vorhandenen Spielern" (Fülleinträge mit einer ID < 0)
        // umgehen. Hier gäbe es mehrere Möglichkeiten:
        // * nichts speichern, da "nicht sein kann, was nicht sein darf"
        // * die negative ID abspeichern (sollte gehen, denn tl_spiel.home und tl_spiel.away
        //   sind "int(10) NOT NULL default '0'".

        if (!$data['spiele'] || !is_array($data['spiele'])) {
            return;
        }

        if (!$data['begegnung']) {
            return;
        }

        foreach ($data['spiele'] as $i => $spielData) {
            if (isset($spielData['home']['spieler'][2])) {
                $this->checkAndSaveDoppel($data['begegnung'], $i + 1, $spielData);
            } else {
                $this->checkAndSaveEinzel($data['begegnung'], $i + 1, $spielData);
            }
        }
    }

    /**
     * @param int $begegnung
     * @param int $slot
     * @param array $spielData
     */
    protected function checkAndSaveDoppel($begegnung, $slot, $spielData)
    {
        $spiel = \SpielModel::findBy(
            ['pid=?', 'slot=?'],
            [
                $begegnung,
                $slot,
            ]
        );
        if (null === $spiel) {
            $spiel = new \SpielModel();
            $spiel->pid = $begegnung;
            $spiel->slot = $slot;
        }

        $spiel->spieltype = \SpielModel::TYPE_DOPPEL;

        $spiel->home  = $spielData['home']['spieler'][1]['id'];
        $spiel->away  = $spielData['away']['spieler'][1]['id'];
        $spiel->home2 = $spielData['home']['spieler'][2]['id'];
        $spiel->away2 = $spielData['away']['spieler'][2]['id'];

        $spiel->score_home = $spielData['home']['score'] ?: 0;
        $spiel->score_away = $spielData['away']['score'] ?: 0;

        $spiel->tstamp = time();

        $spiel->save();
    }

    /**
     * @param int $begegnung
     * @param int $slot
     * @param array $spielData
     */
    protected function checkAndSaveEinzel($begegnung, $slot, $spielData)
    {
        $spiel = \SpielModel::findBy(
            ['pid=?', 'slot=?'],
            [
                $begegnung,
                $slot,
            ]
        );
        if (null === $spiel) {
            $spiel = new \SpielModel();
            $spiel->pid = $begegnung;
            $spiel->slot = $slot;
        }

        $spiel->spieltype = \SpielModel::TYPE_EINZEL;
        $spiel->home = $spielData['home']['spieler']['id'];
        $spiel->away = $spielData['away']['spieler']['id'];

        $spiel->score_home = $spielData['home']['score'] ?: 0;
        $spiel->score_away = $spielData['away']['score'] ?: 0;

        $spiel->tstamp = time();

        $spiel->save();
    }

    /**
     * Den Code für das Eingabeformular erstellen. Die Liste der Spieler etc.
     * dynamisch befüllen und am Ende $this->generatePatchSpielplanCode(); aufrufen,
     * damit bereits erfasste Ergebnisse wieder anggezeigt werden.
     *
     * TODO: die Situation erkennen und behandeln, daß sich inzwischen die Voraussetzungen
     * geändert haben (z.B. verfügbare Spieler gelöscht oder weitere hinzugekommen oder einzelne
     * Spiele einer bereits erfassten Begegnung manuell gelöscht wurden)!
     */
    protected function generateForm()
    {
        $GLOBALS['TL_CSS'][] = 'system/modules/ligaverwaltung/assets/begegnungserfassung.css|static';
        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/ligaverwaltung/assets/vue.2.2.0.js|static';
        // Wird am Ende des Templates included:
        //$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/ligaverwaltung/assets/main.js|static';

        $this->Template->NUM_PLAYERS = self::NUM_PLAYERS;

        $spielplan = \LigaModel::SPIELPLAN_16E2D; // default

        // Teams belegen
        $begegnung = \BegegnungModel::findById(\Input::get('id'));
        if (null !== $begegnung) {

            $spielplan = $begegnung->getRelated('pid')->spielplan;

            $this->Template->begegnung = $begegnung->id;
            $team_name['home'] = $begegnung->getRelated('home')->name;
            $team_name['away'] = $begegnung->getRelated('away')->name;
            $this->Template->team_home_name = $team_name['home'];
            $this->Template->team_away_name = $team_name['away'];

            $this->Template->team_home_lineup = ''; // TODO
            $this->Template->team_away_lineup = ''; // TODO

            $team_home = [];
            $team_away = [];
            foreach (['home', 'away'] as $homeaway) {
                $spieler = \SpielerModel::findBy(
                    ['pid=?', 'tl_spieler.active=?'],
                    [$begegnung->$homeaway,'1'],
                    ['order' => 'id ASC']
                );
                if ($spieler) {
                    foreach ($spieler as $s) {
                        // addslashes() wegen Namen wie O'Reilly
                        $player_name = addslashes(\Fiedsch\Liga\DCAHelper::makeSpielerName(
                            $s->getRelated('member_id')->firstname,
                            $s->getRelated('member_id')->lastname
                        ));
                        if ($homeaway === 'home') {
                            $team_home[] = sprintf("{name: '%s', id: %d}", $player_name, $s->id);
                        } else {
                            $team_away[] = sprintf("{name: '%s', id: %d}", $player_name, $s->id);
                        }
                    }
                }
            }
            // auf mindestens self::NUM_PLAYERS Spieler "auffüllen"
            /*
            for ($i = count($team_home); $i < self::NUM_PLAYERS; $i++) {
                $team_home[] = sprintf("{name: '%s',id: -%d}", 'no player' . $i, $i);
            }
            for ($i = count($team_away); $i < self::NUM_PLAYERS; $i++) {
                $team_away[] = sprintf("{name: '%s',id: -%d}", 'no player' . $i, $i);
            }
            */

            // Die zu sortierenden JSON-Daten sind strings, bei denen der Name
            // vorne steht. Daher passt eine String-Sortierung ;-)
            usort($team_home, function($a, $b) { return $a <=> $b; });
            usort($team_away, function($a, $b) { return $a <=> $b; });

            // Immer noch zusätzlich einen "Spieler", der ausgewählt werden kann,
            // wenn z.B. zwa6 6 Spieler gemeldet sind, aber am konkreten Spieltag nur
            // 4 Spieler erschienen sind. Es wäre dann wenig sinnvoll, die nicht
            // anwensenden Spieler in der liste der verfügbaren Spieler auszuwählen.
            $team_home[] = sprintf("{name: '%s',id: 0}", 'kein Spieler');
            $team_away[] = sprintf("{name: '%s',id: 0}", 'kein Spieler');

            $this->Template->team_home_players = join(',', $team_home);
            $this->Template->team_away_players = join(',', $team_away);
        }

        $this->Template->spielplan = $spielplan;
        $this->generatePatchSpielplanCode();

    }

    /**
     * TODO: neu implementieren, da sich das zugrundelegende Datenmodell geändert hat
     * Siehe linup mit den tl_spieler IDs und played mit den Index-Positionen aus lineup
     *
     * Frage: wie können wir das lineup aus den Ergebnissen aus played rekonstruieren?
     * Oder: müssen wir den zugehörigen spielplan auch speichern? Dieser ist keine
     * Konstante (z.B. in der Bezirksliga anders). Dies ist aber ohnehin ein weiteres
     * TODO, da im Javascript Code die "konstante" spielplan.16E2D.js eingebunden wird.
     */
    protected function generatePatchSpielplanCode()
    {
        $jsCodeLines = [];

        if (!\Input::get('id')) {
            $jsCodeLines[] = '// ID der Begegnung nicht angegeben';
        } else {
            $spiele = \SpielModel::findByPid(\Input::get('id'));
            if ($spiele) {
                //$message  = '\nFür diese Begegnung sind bereits Spiele erfasst worden!';
                //$message .= '\nFalls noch etwas geändert werden muss, bitte das zugehörige einzelne Spiel bearbeiten.';
                //$jsCodeLines[] = "alert(\"$message\")";
                // FIXME: je nach Route, die der User gewählt hat könnte "back"
                // entweder
                // * contao/main.php?do=liga.begegnung  (Dirkte über Menüpunkt "Begegnungen)
                // oder
                // * hcontao/main.php?do=liga.verband&table=tl_begegnung (über Menüpunkt "Verbände/Ligen" -> liga -> Begegnung)
                // sein!
                $jsCodeLines[] = 'window.location = "contao/main.php?do=liga.begegnung";';
            }
        }

        $this->Template->patchSpielplanCode = join("\n", $jsCodeLines) . "\n";
    }

}
