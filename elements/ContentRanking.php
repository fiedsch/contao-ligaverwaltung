<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

use Fiedsch\Liga\Spiel;

/**
 * Content element "Liste aller Spieler einer Mannaschft".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */
class ContentRanking extends \ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_ranking';

    public function generate()
    {
        if (TL_MODE == 'BE') {
            /** @var \BackendTemplate $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');
            $liga = \LigaModel::findById($this->liga);
            if ($this->rankingtype == 1) {
                $suffix = 'Mannschaften';
                $subject = sprintf('%s %s %s',
                    $liga->getRelated('pid')->name,
                    $liga->name,
                    $liga->getRelated('saison')->name
                );
            } else {
                $suffix = 'Spieler';
                $mannschaft = \MannschaftModel::findById($this->mannschaft);
                $subject = sprintf('%s %s %s',
                    'Mannschaft ' . ($mannschaft->name ?: 'alle'),
                    $liga->name,
                    $liga->getRelated('saison')->name
                );
            }
            $objTemplate->title = $this->headline;
            $objTemplate->wildcard = "### " . $GLOBALS['TL_LANG']['CTE']['ranking'][0] . " $suffix $subject ###";
            // $objTemplate->id = $this->id;
            // $objTemplate->link = 'the text that will be linked with href';
            // $objTemplate->href = 'contao/main.php?do=article&amp;table=tl_content&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }
        return parent::generate();
    }

    /**
     * Generate the content element
     */
    public function compile()
    {
        switch ($this->rankingtype) {
            case 1:
                $this->compileMannschaftenranking();
                break;
            case 2:
                $this->compileSpielerranking();
                break;
            default:
                $this->Template->subject = 'Undefined ' . $this->rankingtype;
        }
    }

    /**
     * Ranking aller Mannschaften einer Liga
     *
     * Achtung: Spiele vom spieltype "Doppel" gehen wie "Einzel" mit in die Berechnung
     * ein. (d.h. hier ohne Fallunterscheidung).
     */
    protected function compileMannschaftenranking()
    {
        $liga = \LigaModel::findById($this->liga);

        $this->Template->subject = sprintf('Ranking aller Mannschaften der %s %s %s',
            $liga->getRelated('pid')->name,
            $liga->name,
            $liga->getRelated('saison')->name
        );

        $spiele = \Database::getInstance()
            ->prepare("SELECT 
                          s.score_home AS legs_home,
                          s.score_away AS legs_away,
                          b.home AS team_home,
                          b.away AS team_away,
                          b.spiel_tag AS spieltag
                          FROM tl_spiel s
                          LEFT JOIN tl_begegnung b
                          ON (s.pid=b.id)
                          LEFT JOIN tl_liga l
                          ON (b.pid=l.id)
                          LEFT JOIN tl_mannschaft m1
                          ON (b.home=m1.id)
                          LEFT JOIN tl_mannschaft m2
                          ON (b.away=m2.id)
                          WHERE l.id=?
                          AND m1.active='1'
                          AND m2.active='1'
                          ")
            ->execute($this->liga);

        $begegnungen = [];

        while ($spiele->next()) {
            $key = sprintf("%d:%d:%d", $spiele->spieltag, $spiele->team_home, $spiele->team_away);
            if (!isset($begegnungen[$key])) {
                $begegnungen[$key] = new Begegnung();
            }
            $begegnungen[$key]->addSpiel(new Spiel($spiele->row()));
        }

        $results = [];

        /** @var \Fiedsch\Liga\Begegnung $begegnung */
        foreach ($begegnungen as $key => $begegnung) {
            list($spieltag, $home, $away) = explode(':', $key);

            // Begegnungen: Mannschaft gegen Mannschaft

            $results[$home]['begegnungen'] += 1;
            $results[$away]['begegnungen'] += 1;

            // Legs (Ergebnis von Spieler gegen Spieler)

            $results[$home]['legs_self'] += $begegnung->getLegsHome();
            $results[$away]['legs_self'] += $begegnung->getLegsAway();
            $results[$home]['legs_other'] += $begegnung->getLegsAway();
            $results[$away]['legs_other'] += $begegnung->getLegsHome();

            // Spiele (Ergebnis von Spieler gegen Spieler; entweder 1:0 oder 0:1)

            $results[$home]['spiele_self'] += $begegnung->getSpieleHome();
            $results[$away]['spiele_self'] += $begegnung->getSpieleAway();
            $results[$home]['spiele_other'] += $begegnung->getSpieleAway();
            $results[$away]['spiele_other'] += $begegnung->getSpieleHome();

            // Punkte für die Begegnung

            $results[$home]['punkte_self'] += $begegnung->getPunkteHome();
            $results[$away]['punkte_self'] += $begegnung->getPunkteAway();
            $results[$home]['punkte_other'] += $begegnung->getPunkteAway();
            $results[$away]['punkte_other'] += $begegnung->getPunkteHome();

            $results[$home]['gewonnen'] += $begegnung->isGewonnenHome() ? 1 : 0;
            $results[$home]['unentschieden'] += $begegnung->isUnentschieden() ? 1 : 0;
            $results[$home]['verloren'] += $begegnung->isVerlorenHome() ? 1 : 0;

            $results[$away]['gewonnen'] += $begegnung->isGewonnenAway() ? 1 : 0;
            $results[$away]['unentschieden'] += $begegnung->isUnentschieden() ? 1 : 0;
            $results[$away]['verloren'] += $begegnung->isVerlorenAway() ? 1 : 0;
        }

        uasort($results, function($a, $b) {
            return \Fiedsch\Liga\Begegnung::compareMannschaftResults($a, $b);
        });

        // Berechnung Rang (Tabellenplatz) und Label
        $lastpunkte = PHP_INT_MAX;
        $lastlegs_self = PHP_INT_MAX;
        $lastlegs_other = PHP_INT_MAX;
        $rang = 0;
        $rang_skip = 1;
        foreach ($results as $id => $data) {
            $mannschaft = \MannschaftModel::findById($id);

            if (!$mannschaft || $mannschaft->active !=='1') {
                unset($results[$id]);
                continue;
            }

            $mannschaftlabel = $mannschaft->getLinkedName();

            $results[$id]['name'] = $mannschaftlabel;
            if ($results[$id]['punkte_self'] == $lastpunkte
                && $results[$id]['legs_self'] == $lastlegs_self
                && $results[$id]['legs_other'] == $lastlegs_other
            ) {
                // we have a "tie"
                $rang_skip++;
            } else {
                $rang += $rang_skip;
                $rang_skip = 1;
            }
            $results[$id]['rang'] = $rang;
            $lastpunkte = $results[$id]['punkte_self'];
            $lastlegs_self = $results[$id]['legs_self'];
            $lastlegs_other = $results[$id]['legs_other'];
        }

        $this->Template->rankingtype = 'mannschaften';
        $this->Template->listitems = $results;
    }

    /**
     * Ranking aller Spieler einer Mannschaft (in einer liga)
     *
     * Achtung: Spiele vom spieltype "Doppel" gehen *nicht* mit in die Berechnung
     * ein -- gezählt werden nur die "Einzel".
     *
     * ohne ausgewählte Mannschaft => Ranking aller Spieler der Liga
     */
    protected function compileSpielerranking()
    {
        $sql = "SELECT 
                          s.score_home AS legs_home,
                          s.score_away AS legs_away,
                          s.home AS player_home,
                          s.away AS player_away,
                          b.home AS team_home,
                          b.away AS team_away,
                          b.id AS begegnung_id
                          FROM tl_spiel s
                          LEFT JOIN tl_begegnung b
                          ON (s.pid=b.id)
                          LEFT JOIN tl_liga l
                          ON (b.pid=l.id)
                          LEFT JOIN tl_mannschaft m1
                          ON (b.home=m1.id)
                          LEFT JOIN tl_mannschaft m2
                          ON (b.away=m2.id)                          
                          WHERE s.spieltype=1
                          AND l.id=?
                          AND m1.active='1'
                          AND m2.active='1'                          
                          ";


        if ($this->mannschaft > 0) {
            // eine bestimmte Mannschaft
            $mannschaft = \MannschaftModel::findById($this->mannschaft);
            $this->Template->subject = 'Ranking aller Spieler der Mannschaft ' . $mannschaft->name;
            $sql .= " AND (b.home=? OR b.away=?)";
            $spiele = \Database::getInstance()
                ->prepare($sql)->execute($this->liga, $this->mannschaft, $this->mannschaft);
        } else {
            // alle Mannschaften
            $this->Template->subject = 'Ranking aller Spieler';
            $spiele = \Database::getInstance()
                ->prepare($sql)->execute($this->liga);
        }

        $results = [];

        while ($spiele->next()) {
            $spiel = new Spiel($spiele->row());

            // TODO wird nicht benötigt (auch weiter unten auskommentiert)
            // $begegnung = sprintf("%d:%d:%s", $spiele->spieltag, $spiele->team_home, $spiele->team_away);

            $results[$spiele->player_home]['mannschaft_id'] = $spiele->team_home;
            $results[$spiele->player_away]['mannschaft_id'] = $spiele->team_away;

            // $results[$spiele->player_home]['begegnungen'][$begegnung]++;
            $results[$spiele->player_home]['spiele'] += 1;
            $results[$spiele->player_home]['spiele_self'] += $spiel->getScoreHome();
            $results[$spiele->player_home]['spiele_other'] += $spiel->getScoreAway();
            $results[$spiele->player_home]['legs_self'] += $spiel->getLegsHome();
            $results[$spiele->player_home]['legs_other'] += $spiel->getLegsAway();
            $results[$spiele->player_home]['punkte_self'] += $spiel->getPunkteHome();
            $results[$spiele->player_home]['punkte_other'] += $spiel->getPunkteAway();

            // $results[$spiele->player_away]['begegnungen'][$begegnung]++;
            $results[$spiele->player_away]['spiele'] += 1;
            $results[$spiele->player_away]['spiele_self'] += $spiel->getScoreAway();
            $results[$spiele->player_away]['spiele_other'] += $spiel->getScoreHome();
            $results[$spiele->player_away]['legs_self'] += $spiel->getLegsAway();
            $results[$spiele->player_away]['legs_other'] += $spiel->getLegsHome();
            $results[$spiele->player_away]['punkte_self'] += $spiel->getPunkteAway();
            $results[$spiele->player_away]['punkte_other'] += $spiel->getPunkteHome();
        }

        // ID 0 ist der Platzhalter für "kein Spieler" (z.B. bei "nicht angetreten"),
        // was uns im Ranking nicht interessiert
        unset($results[0]);

        // Bei mannschaftsinternen Rankings alle Spieler löschen, die nicht
        // zur betrachteten Mannschaft gehören.
        if ($this->mannschaft > 0) {
            foreach ($results as $id => $data) {
                if ($data['mannschaft_id'] != $this->mannschaft)
                    unset($results[$id]);
            }
        }

        uasort($results, function($a, $b) {
            return Spiel::compareSpielerResults($a, $b);
        });

        // Berechnung Rang (Tabellenplatz) und Label
        $lastpunkte = PHP_INT_MAX;
        $lastlegs_self = PHP_INT_MAX;
        $lastlegs_other = PHP_INT_MAX;
        $rang = 0;
        $rang_skip = 1;

        foreach ($results as $id => $data) {
            // $results[$id]['anzahl_spiele'] = array_sum($results[$id]['begegnungen']);
            // $results[$id]['anzahl_begegnungen'] = count($results[$id]['begegnungen']);

            $spieler = \SpielerModel::findById($id);
            $mannschaft = \MannschaftModel::findById($results[$id]['mannschaft_id']);

            if (!$spieler || $spieler->active !== '1' || !$mannschaft || $mannschaft->active !== '1') {
                unset($results[$id]);
                continue;
            }
            //$results[$id]['name'] = \SpielerModel::getNameById($id);
            $results[$id]['name'] = $spieler->getName();

            $results[$id]['mannschaft'] = $mannschaft->getLinkedName();

            if ($results[$id]['punkte_self'] == $lastpunkte
                && $results[$id]['legs_self'] == $lastlegs_self
                && $results[$id]['legs_other'] == $lastlegs_other
            ) {
                // we have a "tie", gleicher Rang und beim nächsten einen Rang mehr auslassen
                $rang_skip++;

            } else {
                // ein Rang weiter und keinen folgenden auslassen,
                // aber die ggf. vorherige Auslassung berücksichtigen)
                $rang += $rang_skip;
                $rang_skip = 1;
            }
            $results[$id]['rang'] = $rang;
            $lastpunkte = $results[$id]['punkte_self'];
            $lastlegs_self = $results[$id]['legs_self'];
            $lastlegs_other = $results[$id]['legs_other'];
        }

        $this->Template->rankingtype = 'spieler';
        if ($this->mannschaft > 0) {
            $this->Template->rankingsubtype = 'mannschaft';
        } else {
            $this->Template->rankingsubtype = 'alle';
        }

        $this->Template->listitems = $results;
    }
}