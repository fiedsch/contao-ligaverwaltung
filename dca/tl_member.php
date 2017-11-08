<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

// Nicht jeder Spieler hat eine E-Mail-Adresse: den Contao-Standard "ist Pflichtfeld" ändern

$GLOBALS['TL_DCA']['tl_member']['fields']['email']['eval']['mandatory'] = false;

$GLOBALS['TL_DCA']['tl_member']['palettes']['default']
    = preg_replace("/;{address_legend/", ";{liga_legend},passnummer,haspaidcurrentseason;{address_legend", $GLOBALS['TL_DCA']['tl_member']['palettes']['default']);

$GLOBALS['TL_DCA']['tl_member']['fields']['passnummer'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_member']['passnummer'],
    'inputType' => 'text',
    'search'    => true,
    'sorting'   => true,
    'eval'      => ['rgxp'=>'alnum','tl_class'=>'w50','maxlength'=>32, 'unique'=>true],
    'sql'       => "varchar(32) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_member']['fields']['haspaidcurrentseason'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_member']['haspaidcurrentseason'],
    'inputType' => 'checkbox',
    'filter'    => true,
    'eval'      => ['tl_class'=>'w50'],
    'sql'       => "char(1) NOT NULL default ''",
];

// remove fields we don't need/want

foreach (['company','country','state','fax','website','lang'] as $field) {
  $GLOBALS['TL_DCA']['tl_member']['palettes']['default']
      = preg_replace("/$field,*;*/", "", $GLOBALS['TL_DCA']['tl_member']['palettes']['default']);
}

// change tl_style so fields align nicely again
$GLOBALS['TL_DCA']['tl_member']['fields']['postal']['eval']['tl_class'] .= ' clr';

// make username case insensitive
// original definition
$GLOBALS['TL_DCA']['tl_member']['fields']['username']['sql'] = 'varchar(64) COLLATE utf8_bin NULL';
// redefinition
$GLOBALS['TL_DCA']['tl_member']['fields']['username']['sql'] = 'varchar(64) COLLATE utf8_general_ci NULL';
