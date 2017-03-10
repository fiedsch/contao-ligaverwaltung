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
     * TODO: Situation erkennen (und behandeln) wenn beim erneuten Bearbeite
     * durch geänderte Spielerreihenfolge andere (neue!) Spiele erzeugt werden,
     * die alten aber nicht gelöscht werden.
     * Immer erst alle Spiele löschen und dann alles neu anlegen (meist identisch)
     * ist auch nicht schön und erhöht unnötig die tl_spiel.id
     */
    protected function saveFormData()
    {
        $this->Template->form = $_POST;
        $message = sprintf('Daten wurden erfasst! (<a href="%s">%s</a>)',
            'contao/main.php?do=liga.begegnung',
            'Zu den Begegnungen'
        );

        $data = $this->scanPostData();

        $message .= "<pre>" . print_r($data, true) . "</pre>";

        $this->saveSpiele($data);

        $this->Template->message = $message;

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
                case 'REQUEST_TOKEN':
                case 'FORM_SUBMIT':
                    // ignorieren
                    break;

                default:
                    if (preg_match("/^spieler_(home|away)_(\d+)$/", $k, $matches)) {
                        // Einzel (Spieler)
                        $data['spiele'][$matches[2]][$matches[1]]['spieler'] = [
                            'name' => $this->getSpielerName($v),
                            'id'   => $v,
                        ];
                    } else if (preg_match("/^spieler_(home|away)_(\d+)_(\d+)$/", $k, $matches)) {
                        // Doppel (Spieler)
                        $data['spiele'][$matches[2]][$matches[1]]['spieler'][$matches[3]] = [
                            'name' => $this->getSpielerName($v),
                            'id'   => $v,
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
        return sprintf("%s, %s",
            $member->lastname,
            $member->firstname
        );
    }

    /**
     * @param array $data
     */
    protected function saveSpiele($data)
    {
        // über $data['spiele'] iterieren und je Eintrag
        // * checken, ob es bereits einen zugehörigen Eintrag in tl_spiel gibt
        //   - falls nein, anlegen
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
        $spiel->home = $spielData['home']['spieler'][1]['id'];
        $spiel->away = $spielData['away']['spieler'][1]['id'];
        $spiel->home2 = $spielData['home']['spieler'][2]['id'];
        $spiel->away3 = $spielData['away']['spieler'][2]['id'];

        $spiel->score_home = $spielData['home']['score'] ?: 0;
        $spiel->score_away = $spielData['away']['score'] ?: 0;

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
                    [$begegnung->$homeaway],
                    ['order' => 'id ASC']
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

        $this->generatePatchSpielplanCode();

    }


    /**
     * this.$data == (Beispiel)
     * {
     * "home":
     * {
     * "key":"home",
     * "name":"Die Superchecker",
     * "available":
     * [
     * {"name":"no player0","id":0}, // Name des Members, id aus tl_spieler
     * {"name":"no player1","id":-1},
     * ...
     * {"name":"no player5","id":-5} // immer bis Index 5 bei (konstamt) 6 Spielern? Nein, nur bei ...players!
     * ],
     * "players":
     * [
     * {"id":-5}, // id aus tl_spieler wie oben in "available"; hier "vom User gewählte Reihenfolge")
     * {"id":-4},
     * {"id":-3},
     * {"id":-2},
     * {"id":-1},
     * {"id":0} // immer genau 6 Einträge!
     * ]
     * }, // ende "home"
     * "away":{ ... genau wie "home" ... }
     * "spielplan":
     * [
     * {"home":[0],"away":[0],"scores":{"home":1,"away":2},"result":"0:1"},
     * {"home":[1],"away":[1],"scores":{"home":2,"away":3},"result":"0:1"},
     * ...
     * {"home":[0,2],"away":[1,3],"scores":{"home":3,"away":2},"result":"1:0"},
     * {"home":[1,3],"away":[0,2],"scores":{"home":2,"away":3},"result":"0:1"}
     * ]
     * }
     *
     * Schritte des $data patchens, wenn bereits Daten vorliegen:
     * (1a) spielplan[i].scores.{home,away} =  ... setzen (integer )
     * (1b) spielplan[i].result = ... setzen (z.B. "1:0")
     * (2a) home.available.{name,id} setzen {String,int id aus tl_spieler}
     * (2b) home.players setzen (id int gemäß home.available.id; d.h. deren Reihenfolge;
     * .available bleibt immer gleich, in .players wird die gewählte Reihenfolge abgebildet)
     */

    protected function generatePatchSpielplanCode()
    {

        $jsCodeLines = [];

        if (!\Input::get('id')) {
            $jsCodeLines[] = '// ID der Begegnung nicht angegeben';
        } else {

            $spiele = \SpielModel::findBy(
                ['pid=?'],
                [\Input::get('id')],
                ['order' => 'slot ASC']
            );
            if (!$spiele) {
                $jsCodeLines[] = '// Keine Daten für Begegnung ' . \Input::get('id') . ' gefunden ';
            } else {
                foreach ($spiele as $spiel) {
                    $jsCodeLines[] = sprintf('data.home.players.push({id:%d});', $spiel->home);
                    $jsCodeLines[] = sprintf('data.away.players.push({id:%d});', $spiel->away);
                    $jsCodeLines[] = sprintf('data.spielplan[%d].scores={home:%d,away:%d};',
                        $spiel->slot - 1,
                        $spiel->score_home,
                        $spiel->score_away
                    );
                    //$jsCodeLines[] = 'data.spielplan[0].result = "0:1";'; // können wir uns sparen, das macht die app selbst
                }
            }

        }

        // TODO: fix problem when \SpielModel entries have been created manually in the backend
        // (only some, but not all that would be created by the form) befor using the form.
        // Issue: the form will not be usable then, as data.{home,away}.players is incomplete.

        $this->Template->patchSpielplanCode = join("\n", $jsCodeLines) . "\n";
    }

}
