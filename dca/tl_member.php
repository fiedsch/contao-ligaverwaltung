<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

$GLOBALS['TL_DCA']['tl_member']['list']['operations']['history'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_member']['history'],
        //'href'  => 'do=liga.begegnungserfassung',
        //'icon'  => 'editheader.gif',
        'button_callback' => function($arrRow,
                                      $href,
                                      $label,
                                      $title,
                                      $icon,
                                      $attributes,
                                      $strTable,
                                      $arrRootIds,
                                      $arrChildRecordIds,
                                      $blnCircularReference,
                                      $strPrevious,
                                      $strNext) {
            return sprintf(
                '<a href="contao/main.php?do=liga.spieler_history&amp;id=%d&amp;popup=1&amp;rt=%s"'
                .' title="" style="padding-left:3px"'
                .' onclick="Backend.openModalIframe({\'width\':768,\'title\':\'Spielerhistorie des Mitglieds ID %d anzeigen\',\'url\':this.href});return false"'
                .'>'
                .'%s</a>'
                ,
                $arrRow['id'],
                REQUEST_TOKEN,
                $arrRow['id'],
                // getHtml(a, foo, c) setzt mit foo das alt-Attribut, wir benötigen aber das title-Attribut
                // das wir im dritten Parameter "manuell" setzen.
                Image::getHtml('diff.gif', $GLOBALS['TL_LANG']['tl_member']['spielerhistorie'][0], 'style="vertical-align:top" title="'.$GLOBALS['TL_LANG']['tl_member']['spielerhistorie'][0].'"')
            );
        }
];

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

// do not use 'filter' for these
foreach (['country','language','disable','login','city'] as $field) {
    $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['filter'] = false;
}

// do not use 'search' for these
foreach (['company','website','street'] as $field) {
    $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['search'] = false;
}

// do not use 'sorting' for these
foreach (['company','country','state'] as $field) {
    $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['sorting'] = false;
}