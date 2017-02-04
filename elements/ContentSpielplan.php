<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Content element "Spielplan einer Liga".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */
class ContentSpielplan extends \ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_spielplan';

    /**
     * Generate the content element
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            return $this->generateBackendView();
        }
        return parent::generate();
    }

    /**
     * generate the view for the back end
     */
    protected function generateBackendView()
    {
        /** @var \BackendTemplate|object $objTemplate */
        $objTemplate = new \BackendTemplate('be_wildcard');

        $liga = \LigaModel::findById($this->liga);
        $filter = '';
        if ($this->filtermannschaft) {
            $mannschaft = \MannschaftModel::findById($this->filtermannschaft);
            $filter = ' (nur Begegnungen von "' . $mannschaft->name . '"")';
        }
        $saison = \SaisonModel::findById($liga->saison);
        $ligalabel = sprintf("%s %s %s",
            $liga->getRelated('pid')->name,
            $liga->name,
            $saison->name
        );
        $suffix =sprintf("%s %s", $ligalabel, $filter);
        $objTemplate->title = $this->headline;
        $objTemplate->wildcard = "### ".$GLOBALS['TL_LANG']['CTE']['spielplan'][0]." $suffix ###";
        // $objTemplate->id = $this->id;
        // $objTemplate->link = 'the text that will be linked with href';
        // $objTemplate->href = 'contao/main.php?do=article&amp;table=tl_content&amp;act=edit&amp;id=' . $this->id;

        return $objTemplate->parse();
    }

    /**
     * Generate the content element
     */
    public function compile()
    {
        if ($this->liga == '') {
            return;
        }
        $columns = ['pid=?'];
        $conditions = [$this->liga];
        if ($this->filtermannschaft) {
            $columns[] = 'home=? OR away=?';
            $conditions[] = $this->filtermannschaft;
            $conditions[] = $this->filtermannschaft;
        }
        $begegnungen = \BegegnungModel::findBy(
            $columns,
            $conditions,
            ['order' => 'spiel_tag ASC']
        );

        if ($begegnungen === null) {
            return;
        }

        $listitems = [];
        foreach ($begegnungen as $begegnung) {

            $home = $begegnung->getRelated('home');
            $away = $begegnung->getRelated('away');
            $spielort = $home->getRelated('spielort');
            $listitem = sprintf("%s : %s (%d. Spieltag %s; %s)",
                $home->name,
                $away->name,
                $begegnung->spiel_tag,
                \Date::parse(\Config::get('dateFormat'), $begegnung->spiel_am),
                $spielort->name
            );
            if ($this->filtermannschaft) {
                $listitem = sprintf("<span class='%s'>%s</span>",
                    $home->id === $this->filtermannschaft
                            ? 'home'
                            : $away->id === $this->filtermannschaft ? 'away' : '',
                    $listitem
                );
            }

            $listitems[] = $listitem;
        }

        $this->Template->listitems = $listitems;

    }

}