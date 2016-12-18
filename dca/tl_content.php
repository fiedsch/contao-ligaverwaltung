<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

/* Mannschaftsliste */
$GLOBALS['TL_DCA']['tl_content']['palettes']['mannschaftsliste'] = '{type_legend},type,headline;{liga_legend},liga;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['fields']['liga'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['liga'],
    'exclude'          => true,
    'foreignKey'       => '',
    'inputType'        => 'select',
    'eval'             => ['mandatory' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
    'options_callback' => ['\Fiedsch\Liga\DCAHelper', 'getAlleLigenForSelect'],
    'sql'              => "int(10) unsigned NOT NULL default '0'",
];

/* Spielerliste */
$GLOBALS['TL_DCA']['tl_content']['palettes']['spielerliste'] = '{type_legend},type,headline;{mannschaft_legend},mannschaft;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['fields']['mannschaft'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['mannschaft'],
    'exclude'          => true,
    'foreignKey'       => '',
    'inputType'        => 'select',
    'eval'             => ['mandatory' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
    'options_callback' => ['\Fiedsch\Liga\DCAHelper', 'getAlleMannschaftenForSelect'],
    'sql'              => "int(10) unsigned NOT NULL default '0'",
];

/* Spielplan */
$GLOBALS['TL_DCA']['tl_content']['palettes']['spielplan'] = '{type_legend},type,headline;{liga_legend},liga;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
// $GLOBALS['TL_DCA']['tl_content']['fields']['liga'] = [ ... ]; // bereits bei der Mannschaftsliste definiert!