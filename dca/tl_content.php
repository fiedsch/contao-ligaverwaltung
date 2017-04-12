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
    'label'      => &$GLOBALS['TL_LANG']['tl_content']['saison'],
    'exclude'    => true,
    'foreignKey' => 'tl_saison.name',
    'inputType'  => 'checkboxWizard',
    'eval'       => ['mandatory' => true, 'multiple' => true, 'tl_class' => 'w50 clr'],
    //'options_callback' => ['\Fiedsch\Liga\DCAHelper', 'getAlleVerbaendeForSelect'],
    'sql'        => "blob NULL",
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
$GLOBALS['TL_DCA']['tl_content']['palettes']['spielerliste'] = '{type_legend},type,headline;{mannschaft_legend},mannschaft,showdetails;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['fields']['mannschaft'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['mannschaft'],
    'exclude'          => true,
    'foreignKey'       => '',
    'inputType'        => 'select',
    'eval'             => ['mandatory' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
    'options_callback' => ['\Fiedsch\Liga\DCAHelper', 'getAlleMannschaftenForSelect'],
    'sql'              => "int(10) unsigned NOT NULL default '0'",
];
$GLOBALS['TL_DCA']['tl_content']['fields']['showdetails'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['showdetails'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''",
];
/* Spielplan */
$GLOBALS['TL_DCA']['tl_content']['palettes']['spielplan'] = '{type_legend},type,headline;{filter_legend},liga,mannschaft;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
// liga und mannschaft bereits bei Mannschaftsliste bzw. Spielerliste definiert


/* Spielortinfo */
$GLOBALS['TL_DCA']['tl_content']['palettes']['spielortinfo'] = '{type_legend},type,headline,spielort;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['fields']['spielort'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_content']['spielort'],
    'exclude'    => true,
    'foreignKey' => '',
    'inputType'  => 'select',
    'eval'       => ['mandatory' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
    'foreignKey' => 'tl_spielort.name',
    'sql'        => "int(10) unsigned NOT NULL default '0'",
    //'sql'              => "blob NULL",
];

/* Ranking/Tabelle */
$GLOBALS['TL_DCA']['tl_content']['palettes']['ranking'] = '{type_legend},type,headline,liga,rankingtype;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'rankingtype';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['rankingtype_2'] = 'mannschaft';
// liga und mannschaft bereits bei Mannschaftsliste bzw. Spielerliste definiert

$GLOBALS['TL_DCA']['tl_content']['fields']['rankingtype'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_content']['rankingtype'],
    'exclude'    => true,
    'options'    => [1 => 'Mannschaften', 2 => 'Spieler'],
    'inputType'  => 'select',
    'eval'       => ['mandatory' => true, 'tl_class' => 'w50', 'submitOnChange'=>true],
    'sql'        => "int(10) unsigned NOT NULL default '0'",
];

/* Mannschaftsseite */
$GLOBALS['TL_DCA']  ['tl_content']['palettes']['mannschaftsseite'] = '{title_legend},type,headline,name,mannschaft';
// mannschaft bereits bei Mannschaftsliste bzw. Spielerliste definiert