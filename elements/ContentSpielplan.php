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
    public function compile()
    {
        if ($this->liga == '') {
            return;
        }
        $begegnungen = \BegegnungModel::findByPid($this->liga);

        if ($begegnungen === null) {
            return;
        }

        $listitems = [];
        foreach ($begegnungen as $begegnung) {
            $home = \MannschaftModel::findById($begegnung->home);
            $away = \MannschaftModel::findById($begegnung->away);
            $spielort = \SpielortModel::findById($home->spielort);
            $listitems[] = sprintf("%s : %s (%s)",
                $home->name,
                $away->name,
                $spielort->name
            );
        }

        $this->Template->listitems = $listitems;

    }

}