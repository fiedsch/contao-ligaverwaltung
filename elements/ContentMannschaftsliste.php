<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Content element "Liste aller Mannschaften einer Liga".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */
class ContentMannschaftsliste extends \ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_mannschaftsliste';

    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->title = $this->headline;


            $liga = \LigaModel::findById($this->liga);
            $suffix = 'Mannschaften';
            $subject = sprintf('%s %s %s',
                $liga->getRelated('pid')->name,
                $liga->name,
                $liga->getRelated('saison')->name
            );
            $objTemplate->wildcard = "### " . $GLOBALS['TL_LANG']['CTE']['mannschaftsliste'][0] . " $subject ###";
            return $objTemplate->parse();
        }
        return parent::generate();
    }

    /**
     * Generate the content element
     */
    public function compile()
    {
        if ($this->liga == '') {
            return;
        }
        $mannschaften = \MannschaftModel::findByLiga($this->liga, ['order' => 'name ASC']);
        if ($mannschaften === null) {
            return;
        }

        $listitems = [];
        foreach ($mannschaften as $mannschaft) {
            /*
            if ($mannschaft->teampage) {
                $teampage = \PageModel::findById($mannschaft->teampage);
                $listitem = sprintf("<a href='%s'>%s</a>",
                    \Controller::generateFrontendUrl($teampage->row()),
                    $mannschaft->name
                );
            } else {
                $listitem = $mannschaft->name;
            }
            */
            if ($mannschaft->active==='1') {
                $listitem = $mannschaft->getLinkedName();
                $listitems[] = $listitem;
            }
        }

        $this->Template->listitems = $listitems;

    }

}