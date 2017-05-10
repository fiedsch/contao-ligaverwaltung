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
class ContentHighlightRanking extends \ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_highlightranking';

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
                $subject = 'Mannschaft ' . ($mannschaft->name ?: 'alle');
            }
            $objTemplate->title = $this->headline;
            $objTemplate->wildcard = "### " . $GLOBALS['TL_LANG']['CTE']['highlightranking'][0] . " $suffix $subject ###";
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
        $this->Template->rankingfield = $this->rankingfield;
    }

    /**
     * Highlight-"Ranking" aller Mannschaften einer Liga
     */
    protected function compileMannschaftenranking()
    {
        $liga = \LigaModel::findById($this->liga);

        $this->Template->subject = sprintf('Highlight-Ranking aller Mannschaften der %s %s %s',
            $liga->getRelated('pid')->name,
            $liga->name,
            $liga->getRelated('saison')->name
        );

        $highlights = \Database::getInstance()
            ->prepare("SELECT 
                          h.*, b.spiel_am, ma.name as mannschaft
                          FROM tl_highlight h
                          LEFT JOIN tl_begegnung b
                          ON (h.begegnung_id = b.id)
                          LEFT JOIN tl_spieler s
                          ON (h.spieler_id=s.id)
                          LEFT JOIN tl_member me
                          ON (s.member_id=me.id)
                          LEFT JOIN tl_mannschaft ma
                          ON (s.pid=ma.id)
                          WHERE b.pid=?")
            ->execute($this->liga);

        // TODO $this->rankingfield berücksichtigen!

        $results = [];

        while ($highlights->next()) {
            //print "<pre>".print_r($highlights->row(), true)."</pre>";
            $results[] = [
                'datum'         => \Date::parse(\Config::get('dateFormat'), $highlights->spiel_am),
                'mannschaft'    => $highlights->mannschaft,
                'hl_171'        => $highlights->type == \HighlightModel::TYPE_171 ? $highlights->value : '',
                'hl_180'        => $highlights->type == \HighlightModel::TYPE_180 ? $highlights->value : '',
                'hl_highfinish' => $highlights->type == \HighlightModel::TYPE_HIGHFINISH ? $highlights->value : '',
                'hl_shortleg'   => $highlights->type == \HighlightModel::TYPE_SHORTLEG ? $highlights->value : '',
            ];
        }

        // TODO analog compileSpielerranking() aufbereiten

        $this->Template->rankingtype = 'mannschaften';
        $this->Template->listitems = $results;
    }

    /**
     * Highlight-"Ranking" aller Spieler einer Mannschaft (in einer liga)
     *
     * ohne ausgewählte Mannschaft => Ranking aller Spieler der Liga
     */
    protected function compileSpielerranking()
    {
        $sql = "SELECT 
                          h.*, s.id as spieler_id, s.pid, me.firstname, me.lastname, b.spiel_am, ma.name as mannschaft 
                          FROM tl_highlight h
                          LEFT JOIN tl_begegnung b
                          ON (h.begegnung_id = b.id)
                          LEFT JOIN tl_spieler s
                          ON (h.spieler_id=s.id)
                          LEFT JOIN tl_member me
                          ON (s.member_id=me.id)
                          LEFT JOIN tl_mannschaft ma
                          ON (s.pid=ma.id)
                          WHERE b.pid=?";

        if ($this->mannschaft > 0) {
            // eine bestimmte Mannschaft
            $mannschaft = \MannschaftModel::findById($this->mannschaft);
            $this->Template->subject = 'Highlight-Ranking aller Spieler der Mannschaft ' . $mannschaft->name;
            $sql .= " AND b.home=? OR b.away=?";
            $sql .= " AND " . $this->getRankingTypeFilter('h');
            $sql .= " ORDER BY spiel_am DESC";
            $highlights = \Database::getInstance()
                ->prepare($sql)->execute($this->liga, $this->mannschaft, $this->mannschaft);
        } else {
            // alle Mannschaften
            $sql .= " AND " . $this->getRankingTypeFilter('h');
            $sql .= " ORDER BY spiel_am DESC";
            $this->Template->subject = 'Highlight-Ranking aller Spieler';
            $highlights = \Database::getInstance()
                ->prepare($sql)->execute($this->liga);
        }

        $results = [];

        while ($highlights->next()) {
            if (!isset($results[$highlights->spieler_id])) {
                $results[$highlights->spieler_id] = [
                    'name'          => sprintf('%s, %s', $highlights->lastname, $highlights->firstname),
                    'mannschaft'    => $highlights->mannschaft,
                    'hl_171'        => 0, // Anzahl
                    'hl_180'        => 0, // Anzahl
                    'hl_highfinish' => [], // Liste der einzelnen Finisches
                    'hl_shortleg'   => [], // Liste der einzelnen Shortlegs
                    'hl_punkte'        => 0,
                ];
            }

            switch ($highlights->type) {
                case \HighlightModel::TYPE_171:
                    $results[$highlights->spieler_id]['hl_171'] += $highlights->value;
                    break;
                case \HighlightModel::TYPE_180:
                    $results[$highlights->spieler_id]['hl_180'] += $highlights->value;
                    break;
                case \HighlightModel::TYPE_HIGHFINISH:
                    $results[$highlights->spieler_id]['hl_highfinish'][] = $highlights->value;
                    break;
                case \HighlightModel::TYPE_SHORTLEG:
                    $results[$highlights->spieler_id]['hl_shortleg'][] =$highlights->value;
                    break;
            }

        }

        uasort($results, function($a, $b) {
            //return $a['hl_punkte'] <=> $b['hl_punkte'];
            // solange wir keine Punkte vergeben haben: alphabetisch nach Spielernamen sortieren
            return $a['name'] <=> $b['name'];
        });

        // TODO: Berechnung Rang (Tabellenplatz) und Label
        //$lastpunkte = PHP_INT_MAX;
        //$lastlegs = PHP_INT_MAX;
        //$rang = 0;

        $this->Template->rankingtype = 'spieler';
        if ($this->mannschaft > 0) {
            $this->Template->rankingsubtype = 'mannschaft';
        } else {
            $this->Template->rankingsubtype = 'alle';
        }

        $this->Template->listitems = $results;
    }


    protected function getRankingTypeFilter($tablealias)
    {
        $result = '';
        switch ($this->rankingfield) {
            case \HighlightModel::TYPE_171:
            case \HighlightModel::TYPE_180:
                $result = sprintf('%s.type IN (%d,%d)',
                    $tablealias,
                    \HighlightModel::TYPE_171, \HighlightModel::TYPE_180
                );
                break;
            case \HighlightModel::TYPE_HIGHFINISH:
                $result = sprintf('%s.type=%d',
                    $tablealias,
                    \HighlightModel::TYPE_HIGHFINISH
                );
                break;
            case \HighlightModel::TYPE_SHORTLEG:
                $result = sprintf('%s.type=%d',
                    $tablealias,
                    \HighlightModel::TYPE_SHORTLEG
                );
                break;
            default:
                $result = '1=1'; // alle Records, aber zusammen mit AND ... sinnvolles SQL
        }
        return $result;
    }
}