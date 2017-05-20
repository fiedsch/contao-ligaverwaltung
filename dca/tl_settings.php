<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{ligaverwaltung_legend},ligaverwaltung_exclusive_model,teampage';

$GLOBALS['TL_DCA']['tl_settings']['fields']['ligaverwaltung_exclusive_model'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['ligaverwaltung_exclusive_model'],
    'inputType' => 'select',
    'options'   => [ 1 => '(in einer Mannschaft) je Saison', 2 => '(in einer Mannschaft) je Liga' ],
    'eval'      => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['teampage'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_settings']['teampage'],
    'inputType'  => 'pageTree',
    'exclude'    => true,
    'search'     => false,
    'filter'     => false,
    'sorting'    => false,
    'eval'       => ['mandatory' => false, 'multiple'=>false, 'fieldType'=>'radio', 'tl_class'=>'clr'],
    //'sql'        => "blob NULL",
];
