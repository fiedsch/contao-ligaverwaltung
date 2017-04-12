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
        if ($this->mannschaft) {
            $mannschaft = \MannschaftModel::findById($this->mannschaft);
            $filter = ' (nur Begegnungen von "' . $mannschaft->name . '"")';
        }
        $saison = \SaisonModel::findById($liga->saison);
        $ligalabel = sprintf("%s %s %s",
            $liga->getRelated('pid')->name,
            $liga->name,
            $saison->name
        );
        $suffix = sprintf("%s %s", $ligalabel, $filter);
        $objTemplate->title = $this->headline;
        $objTemplate->wildcard = "### " . $GLOBALS['TL_LANG']['CTE']['spielplan'][0] . " $suffix ###";
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
        if ($this->mannschaft) {
            $columns[] = 'home=? OR away=?';
            $conditions[] = $this->mannschaft;
            $conditions[] = $this->mannschaft;
        }
        $begegnungen = \BegegnungModel::findBy(
            $columns,
            $conditions,
            ['order' => 'spiel_tag ASC, spiel_am ASC']
        );

        if ($begegnungen === null) {
            return;
        }

        $listitems = [];
        foreach ($begegnungen as $begegnung) {

            $home = $begegnung->getRelated('home');
            $away = $begegnung->getRelated('away');
            $spielort = $home->getRelated('spielort');

            $homelabel = $home->name;
            if ($home->teampage) {
                $teampage = \PageModel::findById($home->teampage);
                $homelabel = sprintf("<a href='%s'>%s</a>",
                    \Controller::generateFrontendUrl($teampage->row()),
                    $home->name
                );
            }
            $awaylabel = $away->name;
            if ($away->teampage) {
                $teampage = \PageModel::findById($away->teampage);
                $awaylabel = sprintf("<a href='%s'>%s</a>",
                    \Controller::generateFrontendUrl($teampage->row()),
                    $away->name
                );
            }
            $spielortlabel = $spielort->name;
            if ($spielort->spielortpage) {
                $spielortpage = \PageModel::findById($spielort->spielortpage);
                $spielortlabel = sprintf("<a href='%s'>%s</a>",
                    \Controller::generateFrontendUrl($spielortpage->row()),
                    $spielort->name
                );
            }

            $spiel = [
                'home' => $homelabel,
                'away' => $awaylabel,
                'am'   => sprintf("%s. %s",
                        \Date::parse('D', $begegnung->spiel_am),
                        \Date::parse(\Config::get('dateFormat'), $begegnung->spiel_am)
                    ),
                'um'   => \Date::parse(\Config::get('timeFormat'), $begegnung->spiel_am),
                'im'   => $spielortlabel,
            ];
            if ($this->mannschaft) {
                $spiel['heimspiel'] = $home->id == $this->mannschaft;
            }

            $spiele[$begegnung->spiel_tag][] = $spiel;
        }

        $this->Template->mannschaft = $this->mannschaft;

        $this->Template->spiele = $spiele;

    }

}