<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Content element "Informationen zu einem Spielort".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */

class ContentSpielortinfo extends \ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_spielortinfo';

    /**
     * Generate the content element
     */
    public function compile()
    {
        $this->Template->spielort = \SpielortModel::findById($this->spielort);
    }

}