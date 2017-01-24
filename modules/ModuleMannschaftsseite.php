<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Module "Mannschaftsseite".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */
class ModuleMannschaftsseite extends \Module
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'mod_mannschaftsseite';

    /**
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['mannschaftsseite'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    public function compile()
    {
        $this->mannschaft = 16; // Mockup

        $mannschaftModel = \MannschaftModel::findById($this->mannschaft);

        $contentModel = new \ContentModel();
        $contentModel->type = 'spielerliste';
        $contentModel->mannschaft = $this->mannschaft;
        $contentModel->showdetails = '1';
        $contentModel->headline = [
            'value' => 'Modul Mannschaftsseite: ' . $mannschaftModel->name,
            'unit'  => 'h2',
        ];

        $contentElement = new \ContentSpielerliste($contentModel);

        $this->Template->spielerliste = $contentElement->generate();

        // TODO: dito für Spielort, etc.
    }

}