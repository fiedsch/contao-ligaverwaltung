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
            'mode'        => 1,
            'flag'        => 11, // sort ascending
            'fields'      => ['pid','home','away'],
            'panelLayout' => 'sort,filter;search,limit',
        ],
        'label'             => [
            'fields'         => ['home', 'away'],
            'format'         => '%s : %s',
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
        'default' => '{title_legend},pid,home,away',
    ],

    'fields' => [
        'id'     => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pid'    => [
            'label'            => &$GLOBALS['TL_LANG']['tl_spiel']['pid'],
            'filter'           => true,
            'exclude'          => true,
            'inputType'        => 'select',
            'foreignKey'       => 'tl_liga.name',
            'eval'             => ['submitOnChange' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
            'options_callback' => ['\Fiedsch\Liga\DCAHelper', 'getAktiveLigenForSelect'],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'home'   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_spiel']['home'],
            'filter'           => true,
            'exclude'          => true,
            'sorting'          => true,
            //'flag'             => 11, // sort ascending
            'inputType'        => 'select',
            'foreignKey'       => 'tl_mannschaft.name',
            'eval'             => ['tl_class' => 'w50 clr', 'chosen' => true, 'includeBlankOption' => true],
            //'relation'         => ['type' => 'hasOne', 'load' => 'eager'],
            'options_callback' => ['\Fiedsch\Liga\DCAHelper', 'getMannschaftenForSelect'],
            'sql'              => "int(10) NOT NULL default '0'",
        ],
        'away'   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_spiel']['away'],
            'filter'           => true,
            'exclude'          => true,
            'sorting'          => true,
            'flag'             => 11, // sort ascending
            'foreignKey'       => 'tl_mannschaft.name',
            'inputType'        => 'select',
            'eval'             => ['tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
            'relation'         => ['type' => 'hasOne', 'load' => 'eager'],
            'options_callback' => ['\Fiedsch\Liga\DCAHelper', 'getMannschaftenForSelect'],
            'sql'              => "int(10) NOT NULL default '0'",
        ],
    ],
];