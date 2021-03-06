<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Front end module "Spielortseiten reader".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */
class ModuleSpielortseitenReader extends Module
{

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'mod_spielortseitenreader';


    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['spielortseitenreader'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        /** @var \PageModel $objPage */
        global $objPage;

        // Falls wir einen Back-Link einbauen wollen:
        // $this->Template->referer = 'javascript:history.go(-1)';
        // $this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];

        $id = \Input::get('id');
        if (empty($id)) {
            $this->Template->spielort = null;
            return;
        }
        $spielort = \SpielortModel::findById(\Input::get('id'));
        if (!$spielort) {
            $this->Template->spielort = null;
            return;
        }

        $this->Template->spielort = $spielort;

        $contentModel = new \ContentModel();
        $contentModel->type = 'spielortseite';
        $contentModel->spielort = $spielort->id;
        $contentModel->ligen = $this->ligen;
        $contentElement = new \ContentSpielortseite($contentModel);
        $this->Template->spielortseite = $contentElement->generate();

    }
}
