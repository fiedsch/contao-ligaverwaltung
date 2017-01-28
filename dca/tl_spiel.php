<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

$GLOBALS['TL_DCA']  ['tl_spiel'] = [
    'config' => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'ptable'           => 'tl_begegnung',
        'sql'              => [
            'keys' => [
                'id'   => 'primary',
                'pid'  => 'index',
                'home' => 'index',
                'away' => 'index',
                //'pid,home,away' => 'unique',
            ],
        ],
    ],

    'list' => [
        'sorting'           => [
            'mode'                  => 4, // Displays the child records of a parent record
            'flag'                  => 11, // sort ascending
            'fields'                => ['id'],
            'panelLayout'           => 'sort,filter;search,limit',
            'headerFields'          => ['home', 'away', 'pid'],
            'child_record_callback' => ['\Fiedsch\Liga\DCAHelper', 'listSpielCallback'],
            'child_record_class'    => 'no_padding',
            'disableGrouping'       => true,
        ],
        'label'             => [
            'fields' => ['home', 'away'],
            'format' => '%s : %s',
            //'label_callback' => ['\Fiedsch\Liga\DCAHelper', 'begegnungLabelCallback'],
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_spiel']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_spiel']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_spiel']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_spiel']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],

    'palettes' => [
        'default' => '{title_legend},pid,home,away,punkte_home,punkte_away',
    ],

    'fields' => [
        'id'     => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pid'    => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'home'   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_spiel']['home'],
            'filter'           => true,
            'exclude'          => true,
            'sorting'          => true,
            'flag'             => 11, // sort ascending
            'inputType'        => 'select',
            //'foreignKey'       => 'tl_member.CONCAT(lastname,", ",firstname)',
            'eval'             => ['tl_class' => 'w50 clr', 'chosen' => true, 'mandatory' => true, 'includeBlankOption' => true],
            'relation'         => ['type' => 'hasOne', 'table' => 'tl_member', 'load' => 'eager'],
            'options_callback' => ['\Fiedsch\Liga\DCAHelper', 'getHomeSpielerForSelect'],
            'sql'              => "int(10) NOT NULL default '0'",
        ],
        'away'   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_spiel']['away'],
            'filter'           => true,
            'exclude'          => true,
            'sorting'          => true,
            'flag'             => 11, // sort ascending
            //'foreignKey'       => 'tl_member.CONCAT(lastname,", ",firstname)',
            'inputType'        => 'select',
            'eval'             => ['tl_class' => 'w50', 'chosen' => true, 'mandatory' => true, 'includeBlankOption' => true],
            'relation'         => ['type' => 'hasOne', 'table' => 'tl_member', 'load' => 'eager'],
            'options_callback' => ['\Fiedsch\Liga\DCAHelper', 'getAwaySpielerForSelect'],
            'sql'              => "int(10) NOT NULL default '0'",
        ],
        'punkte_home'   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_spiel']['punkte_home'],
            'exclude'          => true,
            'inputType'        => 'text',
            'eval'             => ['tl_class' => 'w50', 'mandatory' => true, 'rgxp'=> 'digit'],
            'sql'              => "int(10) NOT NULL default '0'",
        ],
        'punkte_away'   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_spiel']['punkte_away'],
            'exclude'          => true,
            'inputType'        => 'text',
            'eval'             => ['tl_class' => 'w50', 'mandatory' => true, 'rgxp'=> 'digit'],
            'sql'              => "int(10) NOT NULL default '0'",
        ],
    ],
];