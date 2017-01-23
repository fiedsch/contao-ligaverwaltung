<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

/* Ligenliste */
$GLOBALS['TL_DCA']['tl_content']['palettes']['ligenliste'] = '{type_legend},type,headline;{auswahl_legend},verband,saison;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['fields']['verband'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['verband'],
    'exclude'          => true,
    'foreignKey'       => '',
    'inputType'        => 'select',
    'eval'             => ['mandatory' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
    //'eval'             => ['mandatory' => true, 'multiple'=>true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
    //'inputType'        => 'checkboxWizard',
    //'eval'             => ['mandatory' => true, 'multiple'=>true, 'tl_class' => ''],
    'options_callback' => ['\Fiedsch\Liga\DCAHelper', 'getAlleVerbaendeForSelect'],
    'sql'              => "int(10) unsigned NOT NULL default '0'",
    //'sql'              => "blob NULL",
];
$GLOBALS['TL_DCA']['tl_content']['fields']['saison'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['saison'],
    'exclude'          => true,
    'foreignKey'       => 'tl_saison.name',
    'inputType'        => 'checkboxWizard',
    'eval'             => ['mandatory' => true, 'multiple'=>true, 'tl_class' => 'w50 clr'],
    //'options_callback' => ['\Fiedsch\Liga\DCAHelper', 'getAlleVerbaendeForSelect'],
    'sql'              => "blob NULL",
];
/* Mannschaftsliste */
$GLOBALS['TL_DCA']['tl_content']['palettes']['mannschaftsliste'] = '{type_legend},type,headline;{liga_legend},liga;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['fields']['liga'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['liga'],
    'exclude'          => true,
    'foreignKey'       => '',
    'inputType'        => 'select',
    'eval'             => ['mandatory' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
    //'eval'             => ['mandatory' => true, 'multiple'=>true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
    //'inputType'        => 'checkboxWizard',
    //'eval'             => ['mandatory' => true, 'multiple'=>true, 'tl_class' => ''],
    'options_callback' => ['\Fiedsch\Liga\DCAHelper', 'getAlleLigenForSelect'],
    'sql'              => "int(10) unsigned NOT NULL default '0'",
    //'sql'              => "blob NULL",
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

/* Spielortinfos */
$GLOBALS['TL_DCA']['tl_content']['palettes']['spielortinfo'] = '{type_legend},type,headline,spielort;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['fields']['spielort'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['spielort'],
    'exclude'          => true,
    'foreignKey'       => '',
    'inputType'        => 'select',
    'eval'             => ['mandatory' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
    'foreignKey'       => 'tl_spielort.name',
    'sql'              => "int(10) unsigned NOT NULL default '0'",
    //'sql'              => "blob NULL",
];
