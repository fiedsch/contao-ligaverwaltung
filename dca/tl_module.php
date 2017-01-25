<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

$GLOBALS['TL_DCA']  ['tl_module']['palettes']['mannschaftsseite'] = '{title_legend},name,headline,type,mannschaft';

$GLOBALS['TL_DCA']  ['tl_module']['fields']['mannschaft'] = [
            'label'            => &$GLOBALS['TL_LANG']['tl_module']['mannschaft'],
            'filter'           => true,
            'exclude'          => true,
            'sorting'          => true,
            'flag'             => 11, // sort ascending
            'foreignKey'       => 'tl_mannschaft.name',
            // TODO options_callback => Mannschaftsname inkl Liga/Saison
            'inputType'        => 'select',
            'eval'             => ['tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
            'sql'              => "int(10) NOT NULL default '0'",
        ];
