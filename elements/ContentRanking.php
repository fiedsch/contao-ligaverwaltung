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
            if ($this->rankingtype == 1) {
                $suffix = 'Mannschaften';
                $liga = \LigaModel::findById($this->liga);
                $subject = sprintf('%s %s %s',
                    $liga->getRelated('pid')->name,
                    $liga->name,
                    $liga->getRelated('saison')->name
                );
            } else {
                $suffix = 'Spieler';
                $mannschaft = \MannschaftModel::findById($this->mannschaft);
                $subject = 'Mannschaft ' . $mannschaft->name;
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
     *
     * @return string
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
                          s.score_home AS score_home,
                          s.score_away AS score_away,
                          b.home AS team_home,
                          b.away AS team_away
                          FROM tl_spiel s
                          LEFT JOIN tl_begegnung b
                          ON (s.pid=b.id)
                          LEFT JOIN tl_liga l
                          ON (b.pid=l.id)
                          WHERE l.id=?")
            ->execute($this->liga);

        $begegnungen = [];

        while ($spiele->next()) {
            $key = sprintf("%d:%d", $spiele->team_home, $spiele->team_away);
            if (!isset($begegnungen[$key])) {
                $begegnungen[$key] = new Begegnung();
            }
            $begegnungen[$key]->addSpiel(new Spiel($spiele->row()));
        }

        $results = [];

        /** @var \Fiedsch\Liga\Begegnung $begegnung */
        foreach ($begegnungen as $key => $begegnung) {
            list($home, $away) = explode(':', $key);
            $results[$home]['begegnungen'] += 1;
            $results[$away]['begegnungen'] += 1;
            $results[$home]['spiele'] += $begegnung->getNumSpiele();
            $results[$away]['spiele'] += $begegnung->getNumSpiele();

            $results[$home]['score_self'] += $begegnung->getScoreHome();
            $results[$away]['score_self'] += $begegnung->getScoreAway();
            $results[$home]['score_other'] += $begegnung->getScoreAway();
            $results[$away]['score_other'] += $begegnung->getScoreHome();

            $results[$home]['punkte_self'] += $begegnung->getPunkteHome();
            $results[$away]['punkte_self'] += $begegnung->getPunkteAway();
            $results[$home]['punkte_other'] += $begegnung->getPunkteAway();
            $results[$away]['punkte_other'] += $begegnung->getPunkteHome();
        }

        foreach ($results as $id => $data) {
            $results[$id]['name'] = \MannschaftModel::findById($id)->name;
        }

        usort($results, function($a, $b) {
            return \Fiedsch\Liga\Begegnung::compareMannschaftResults($a, $b);
        });

        $this->Template->listitems = $results;
    }

    /**
     * Ranking aller Spieler einer Mannschaft (in einer liga)
     *
     * Achtung: Spiele vom spieltype "Doppel" gehen *nicht* mit in die Berechnung
     * ein -- gezählt werden nur die "Einzel".
     *
     * TODO: ohne ausgewählte Mannschaft => Ranking aller Spieler der Liga
     */
    protected function compileSpielerranking()
    {
        $mannschaft = \MannschaftModel::findById($this->mannschaft);

        $this->Template->subject = 'Ranking aller Spieler der Mannschaft ' . $mannschaft->name;

        $spiele = \Database::getInstance()
            ->prepare("SELECT 
                          s.score_home AS score_home,
                          s.score_away AS score_away,
                          s.home AS player_home,
                          s.away AS player_away,
                          b.home AS team_home,
                          b.away AS team_away
                          FROM tl_spiel s
                          LEFT JOIN tl_begegnung b
                          ON (s.pid=b.id)
                          WHERE 
                            b.home=? OR b.away=? 
                              AND s.spieltype=1" // nur "Einzel"
            )->execute($this->mannschaft, $this->mannschaft);

        $results = [];

        while ($spiele->next()) {
            $heimspiel = $spiele->team_home == $this->mannschaft;
            $spiel = new Spiel($spiele->row());
            $player = $heimspiel ? $spiele->player_home : $spiele->player_away;

            $results[$player]['begegnungen'][] = sprintf("%s:%s", $spiele->team_home, $spiele->team_away);
            $results[$player]['spiele'] += 1;
            $results[$player]['score_self'] += $heimspiel ? $spiel->getScoreHome() : $spiel->getScoreAway();
            $results[$player]['score_other'] += $heimspiel ? $spiel->getScoreAway() : $spiel->getScoreHome();
            $results[$player]['punkte_self'] += $heimspiel ? $spiel->getPunkteHome() : $spiel->getPunkteAway();
            $results[$player]['punkte_other'] += $heimspiel ? $spiel->getPunkteAway() : $spiel->getPunkteHome();
        }
        foreach ($results as $id => $data) {
            $member = \MemberModel::findById($id);
            $results[$id]['name'] = sprintf("%s, %s", $member->lastname, $member->firstname);
            // reduce; ein Spieler kann während einer Begegnung mehrere Spiele machen
            $results[$id]['begegnungen'] = count(array_values(array_unique($results[$id]['begegnungen'])));
        }
        usort($results, function($a, $b) {
            return Spiel::compareSpielerResults($a, $b);
        });
        $this->Template->listitems = $results;
    }
}