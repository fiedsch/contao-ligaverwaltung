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
                          *
                          FROM tl_highlight
                          WHERE l.id=?")
            ->execute($this->liga);

        while ($highlights->next()) {
            print "<pre>".print_r($highlights, true)."</pre>";  // TODO
        }

        $results = [];
        // ...

        //uasort($results, function($a, $b) {
        //    return \Fiedsch\Liga\Begegnung::compareMannschaftResults($a, $b);
        //});

        // Berechnung Rang (Tabellenplatz) und Label
        $lastpunkte = PHP_INT_MAX;
        $lastlegs = PHP_INT_MAX;
        $rang = 0;

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
                          h.*, s.pid, me.firstname, me.lastname, b.spiel_am, ma.name as mannschaft 
                          FROM tl_highlight h
                          LEFT JOIN tl_begegnung b
                          ON (h.begegnung_id = b.id)
                          LEFT JOIN tl_spieler s
                          ON (h.spieler_id=s.id)
                          LEFT JOIN tl_member me
                          ON (s.member_id=me.id)
                          LEFT JOIN tl_mannschaft ma
                          ON (s.pid=ma.id)
                          WHERE b.pid=?
                          ORDER BY spiel_am DESC";

        if ($this->mannschaft > 0) {
            // eine bestimmte Mannschaft
            $mannschaft = \MannschaftModel::findById($this->mannschaft);
            $this->Template->subject = 'Highlight-Ranking aller Spieler der Mannschaft ' . $mannschaft->name;
            $sql .= " AND b.home=? OR b.away=?";
            $highlights = \Database::getInstance()
                ->prepare($sql)->execute($this->liga, $this->mannschaft, $this->mannschaft);
        } else {
            // alle Mannschaften
            $this->Template->subject = 'Highlight-Ranking aller Spieler';
            $highlights = \Database::getInstance()
                ->prepare($sql)->execute($this->liga);
        }

        $results = [];

        while ($highlights->next()) {
            print "<pre>".print_r($highlights->row(), true)."</pre>";
            $results[] = [
                'datum'         => \Date::parse(\Config::get('dateFormat'), $highlights->spiel_am),
                'name'          => sprintf('%s, %s', $highlights->lastname, $highlights->firstname),
                'mannschaft'    => $highlights->mannschaft,
                'hl_171'        => $highlights->type == \HighlightModel::TYPE_171 ? $highlights->value : '',
                'hl_180'        => $highlights->type == \HighlightModel::TYPE_180 ? $highlights->value : '',
                'hl_highfinish' => $highlights->type == \HighlightModel::TYPE_HIGHFINISH ? $highlights->value : '',
                'hl_shortleg'   => $highlights->type == \HighlightModel::TYPE_SHORTLEG ? $highlights->value : '',
            ];
        }

        uasort($results, function($a, $b) {
            return $a['hl_punkte'] <=> $b['hl_punkte'];
        });

        // Berechnung Rang (Tabellenplatz) und Label
        $lastpunkte = PHP_INT_MAX;
        $lastlegs = PHP_INT_MAX;
        $rang = 0;

        //foreach ($results as $id => $data) {

        //}


        $this->Template->rankingtype = 'spieler';
        if ($this->mannschaft > 0) {
            $this->Template->rankingsubtype = 'mannschaft';
        } else {
            $this->Template->rankingsubtype = 'alle';
        }

        $this->Template->listitems = $results;
    }
}