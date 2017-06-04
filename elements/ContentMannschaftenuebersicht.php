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
class ContentMannschaftenuebersicht extends \ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_mannschaftenuebersicht';

    public function generate()
    {
        if (TL_MODE == 'BE') {
            /** @var \BackendTemplate $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->title = $this->headline;
            $begegnunglabel = \BegegnungModel::findById($this->begegnung) ? \BegegnungModel::findById($this->begegnung)->getLabel('full') : 'Begegnung nicht gefunden!';
            $objTemplate->wildcard = "### " . $GLOBALS['TL_LANG']['CTE']['mannschaftenuebersicht'][0] . " ###";
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
        if (!$this->saison) {
            return;
        }

        $ligen = \LigaModel::findBy(
            ['saison IN (' . join(",", deserialize($this->saison)) . ')'],
            [],
            ['order' => 'tl_liga.name ASC']);

        $arrLigen = [];
        $arrDetails = [];

        foreach ($ligen as $liga) {

            $arrLigen[$liga->id] = $liga->name;
            $arrDetails[$liga->id] = [];
            $mannschaften = \MannschaftModel::findByLiga($liga->id, ['order' => 'name ASC']);
            if ($mannschaften === null) {
                continue;
            }
            foreach ($mannschaften as $mannschaft) {
                $arrTc = [];
                $spieler = \SpielerModel::findBy(
                    ['pid=?', '(teamcaptain=1 OR co_teamcaptain=1)'],
                    [$mannschaft->id],
                    ['order' => 'tl_spieler.teamcaptain DESC, tl_spieler.co_teamcaptain DESC']
                );
                foreach ($spieler as $sp) {
                    $arrTc[] = $sp->getTcDetails();
                }
                $arrDetails[$liga->id][] = [
                    'mannschaft' => $mannschaft->getLinkedName(),
                    'tc'         => $arrTc,
                ];
            }
        }

        $this->Template->ligen = $arrLigen;
        $this->Template->details = $arrDetails;
    }

}