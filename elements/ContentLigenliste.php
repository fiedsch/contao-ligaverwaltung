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

class ContentLigenliste extends \ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_ligenliste';

    /**
     * Generate the content element
     *
     * @return string
     */
    public function compile()
    {
        if ($this->verband == '') {
            return;
        }
        $saisonIds = deserialize($this->saison);

        $saisonFilter = sprintf('saison IN (%s)', implode(',', $saisonIds));
        $ligen = \LigaModel::findAll([
            'column' => ['pid=?', 'aktiv=?', $saisonFilter],
            'value'  => [$this->verband, '1'],
            'order'  => 'spielstaerke ASC',
        ]);
        if ($ligen === null) {
            return;
        }

        $listitems = [];
        foreach ($ligen as $liga) {
            $listitems[] = sprintf("%s %s",
                $liga->name,
                $liga->getRelated("saison")->name
            );
            $mannschaften = \MannschaftModel::findByLiga($liga->id);
            $temp = [];
            foreach ($mannschaften as $mannschaft) {
                if ($mannschaft->teampage) {
                    $teampage = \PageModel::findById($mannschaft->teampage);
                    $temp[] = sprintf("<a href='%s'>%s</a>",
                        \Controller::generateFrontendUrl($teampage->row()),
                        $mannschaft->name
                    );
                } else {
                    $temp[] = $mannschaft->name;
                }
            }
            $listitems[] = $temp;
        }

        $this->Template->listitems = $listitems;

    }

}