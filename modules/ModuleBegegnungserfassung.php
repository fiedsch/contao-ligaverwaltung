<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Module "Begegnungserfassung". Vue.js Formular anzeigen und die gePOSATeten Daten
 * in tl_spiel (child records von tl_begegnug) abspeichern
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */
class ModuleBegegnungserfassung extends \BackendModule
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'be_begegnungserfassung';

    public function compile()
    {
            $this->Template->foo = ['bar', 'baz'];
    }

}