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
class ContentRanking extends \ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_ranking';

    /**
     * Generate the content element
     *
     * @return string
     */
    public function compile()
    {
        switch ($this->rankingtype) {
            case 1:
                $this->compileMannschaftsranking();
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
     */
    protected function compileMannschaftsranking()
    {
        $liga = \LigaModel::findById($this->liga);
        $this->Template->subject = sprintf('Ranking aller Mannschaften der %s %s %s',
            $liga->getRelated('pid')->name,
            $liga->name,
            $liga->getRelated('saison')->name
        );

        $scores = [];

        $spiele = \Database::getInstance()
            ->prepare("SELECT 
                          s.punkte_home AS punkte_home,
                          s.punkte_away as punkte_away,
                          b.home AS team_home,
                          b.away AS team_away
                          FROM tl_spiel s
                          LEFT JOIN tl_begegnung b
                          ON (s.pid=b.id)
                          LEFT JOIN tl_liga l
                          ON (b.pid=l.id)
                          WHERE l.id=?")
            ->execute($this->liga);

        while ($spiele->next()) {
            $scores[$spiele->team_home]['begegnungen'][] = sprintf("%s:%s", $spiele->team_home, $spiele->team_away);
            $scores[$spiele->team_home]['spiele'] += 1;
            $scores[$spiele->team_home]['punkte'] += $spiele->punkte_home;

            $scores[$spiele->team_away]['begegnungen'][] = sprintf("%s:%s", $spiele->team_home, $spiele->team_away);
            $scores[$spiele->team_away]['spiele'] += 1;
            $scores[$spiele->team_away]['punkte'] += $spiele->punkte_away;
        }

        foreach ($scores as $id => $data) {
            $scores[$id]['name'] = \MannschaftModel::findById($id)->name;
            $scores[$id]['begegnungen'] = count(array_values(array_unique($scores[$id]['begegnungen'])));
        }

        usort($scores, function($a, $b) {
            if ($a['punkte'] == $b['punkte']) {
                if ($a['spiele'] == $b['spiele']) { return 0; }
                return $a['spiele'] < $b['spiele'] ? -1 : +1;
            }
            return $a['punkte'] < $b['punkte'] ? +1 : -1;
        });

        $this->Template->listitems = $scores;
    }

    /**
     * Ranking aller Spieler einer Mannschaft (in einer liga)
     * TODO: ohne ausgewählte Mannschaft => Ranking aller Spieler der Liga
     */
    protected function compileSpielerranking()
    {
        $mannschaft = \MannschaftModel::findById($this->mannschaft);
        $this->Template->subject = 'Ranking aller Spieler der Mannschaft ' . $mannschaft->name;
    }

}