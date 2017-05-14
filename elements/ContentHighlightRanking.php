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
     * alles bis inkl. 20 Darts ist ein Shortleg
     */
    const MAX_SHORTLEG_DARTS = 20;
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

        $sql = "SELECT 
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
                          WHERE b.pid=?";

        $sql .= " AND " . $this->getRankingTypeFilter('h');

        $highlights = \Database::getInstance()
            ->prepare($sql)
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
                'hl_punkte'     => [],
                'hl_rang'       => 0,
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
            $sql .= " AND s.pid=?";
            $sql .= " AND " . $this->getRankingTypeFilter('h');
            $sql .= " ORDER BY spiel_am DESC";
            $highlights = \Database::getInstance()
                ->prepare($sql)->execute($this->liga, $this->mannschaft);
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
                    'hl_171'        => 0,  // Anzahl
                    'hl_180'        => 0,  // dito
                    'hl_highfinish' => [], // Liste der Highfinishes
                    'hl_shortleg'   => [], // dito
                    'hl_punkte'     => [], // List der einzelnen Punkte
                    'hl_rang'       => 0,
                ];
            }
            switch ($highlights->type) {
                case \HighlightModel::TYPE_171:
                    $results[$highlights->spieler_id]['hl_171'] += $highlights->value;
                    $results[$highlights->spieler_id]['hl_punkte'][] = $highlights->value;
                    break;
                case \HighlightModel::TYPE_180:
                    $results[$highlights->spieler_id]['hl_180'] += $highlights->value;
                    $results[$highlights->spieler_id]['hl_punkte'][] = $highlights->value;
                    break;
                case \HighlightModel::TYPE_HIGHFINISH:
                    $results[$highlights->spieler_id]['hl_highfinish'][] = $highlights->value;
                    $results[$highlights->spieler_id]['hl_punkte'][] = explode(',', $highlights->value);
                    break;
                case \HighlightModel::TYPE_SHORTLEG:
                    $results[$highlights->spieler_id]['hl_shortleg'][] = $highlights->value;
                    $results[$highlights->spieler_id]['hl_punkte'][] = explode(',', $highlights->value);
                    break;
            }
        }

        // Daten "normieren" und Punkte berechnen

        foreach ($results as $id => $data) {
            switch ($this->rankingfield) {
                case \HighlightModel::TYPE_171:
                case \HighlightModel::TYPE_180:
                    $results[$id]['hl_punkte'] = [ array_sum($results[$id]['hl_punkte']) ];
                    break;
                case \HighlightModel::TYPE_HIGHFINISH:
                    $results[$id]['hl_punkte'] = static::flattenToIntArray($results[$id]['hl_punkte']);
                    $results[$id]['hl_highfinish'] = static::prettyPrintSorted($results[$id]['hl_highfinish'], 'DESC');
                    // höchstes Finish zuerst
                    rsort($results[$id]['hl_punkte']);
                    break;
                case \HighlightModel::TYPE_SHORTLEG:
                    $results[$id]['hl_punkte'] = static::flattenToIntArray($results[$id]['hl_punkte']);
                    $results[$id]['hl_shortleg'] = static::prettyPrintSorted($results[$id]['hl_shortleg'], 'ASC');
                    // Mapping
                    $results[$id]['hl_punkte'] = array_map(function($val) {
                        // Wert > self::MAX_SHORTLEG_DARTS via 0 Punkte nicht berücksichtigen
                        if (self::MAX_SHORTLEG_DARTS < $val) { return 0; }
                        // mapping: kürzeres es Leg === besser
                        return self::MAX_SHORTLEG_DARTS - $val + 1;
                    }, $results[$id]['hl_punkte']);

                    // kürzester Shortleg zuerst (nach Mapping => höchster Wert zuerst!)
                    rsort($results[$id]['hl_punkte']);
                    break;
                case \HighlightModel::TYPE_ALL:
                    $results[$id]['hl_punkte'] = [ ]; // wir sortieren hier nach Namen, brauchen also die Punkte nicht
                    $results[$id]['hl_shortleg'] = static::prettyPrintSorted($results[$id]['hl_shortleg'], 'ASC');
                    $results[$id]['hl_highfinish'] = static::prettyPrintSorted($results[$id]['hl_highfinish'], 'DESC');
            }
        }

        // Sortieren

        // print '<pre>'.print_r(['rankingtype'=>$this->rankingtype, $results], true) .'</pre>';

        if ($this->rankingfield == \HighlightModel::TYPE_ALL) {
            uasort($results, function($a, $b) {
                // ohne spezielle Punkteregel: nach Namen sortieren
                return $a['name'] <=> $b['name'];
            });
        } else {
            uasort($results, function($a, $b) {
                $i = 0;
                while (isset($a['hl_punkte'][$i]) && isset($b['hl_punkte'][$i])) {
                    if ($a['hl_punkte'][$i] != $b['hl_punkte'][$i]) {
                        return $b['hl_punkte'][$i] <=> $a['hl_punkte'][$i]; // $b <=> $a sort DESC
                    }
                    $i++;
                }
                return 0;
            });
        }

        // TODO: Berechnung Rang (Tabellenplatz) und Label
        $lastpunkte = PHP_INT_MAX;
        $rang = 0;
        $rang_skip = 1;

        foreach ($results as $i => $data) {
            // die Konkatenierten Punktwerte als "Prüfstring" für die Feststellung,
            // ob ein Tie vorliegt. Denn: nur, wenn zwei aufeinanderfolgende Prüfstrings
            // identisch sind haben wir bei der Rangvergabe einen "Tie"!
            $punkte = implode('', $results[$i]['hl_punkte']);
            if ($punkte == $lastpunkte) {
                // we have a "tie"
                $rang_skip++;
            } else {
                $rang += $rang_skip;
                $rang_skip = 1;
            }
            $results[$i]['hl_rang'] = $rang;
            $lastpunkte = $punkte;
        }

        $this->Template->rankingtype = 'spieler';
        if ($this->mannschaft > 0) {
            $this->Template->rankingsubtype = 'mannschaft';
        } else {
            $this->Template->rankingsubtype = 'alle';
        }

        $this->Template->listitems = $results;
    }

    /**
     * @param string $tablealias
     * @return string
     */
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

    /**
     * flatten an array. E.g. [1,2,[3,4],5] becomes [1,2,3,4,5].
     * additionally the array elements will be casted to integers.
     *
     * @param array $a
     * @return array (of integers)
     */
    protected static function flattenToIntArray(array $a)
    {
        $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($a));
        $result = [];
        foreach ($it as $v) {
            $result[] = (int) $v;
        }
        return $result;
    }

    /**
     * @param string|array $value
     * @param string $order
     * @return string
     */
    protected static function prettyPrintSorted($value, $order)
    {
        if (is_array($value)) {
            $data = $value;
        } else {
            $data = explode(',', $value);
        }
        // prepare ['1','2','3,4',5'] for sort,
        // i.e. make it ['1','2','3','4',5']
        // i.e. split '3,4' into '3','4'
        $data = explode(',', implode(',', $data));
        if ($order === 'ASC') {
            asort($data);
        } else {
            arsort($data);
        }
        return implode(',', $data);
    }

}