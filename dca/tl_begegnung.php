<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

$GLOBALS['TL_DCA']  ['tl_begegnung'] = [
    'config' => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'ptable'           => 'tl_liga',
        'ctable'           => ['tl_spiel'],
        'sql'              => [
            'keys' => [
                'id'   => 'primary',
                'pid'  => 'index',
                'home' => 'index',
                'away' => 'index',
                // Jede Mannschaft spielt (in einer Liga) maximal einmal gegen eineandere:
                //'pid,home,away' => 'unique',
            ],
        ],
    ],

    'list' => [
        'sorting'           => [
            'mode'        => 2,
            'flag'        => 11, // sort ascending
            'fields'      => ['pid','home','away'],
            'panelLayout' => 'sort,filter;search,limit',
        ],
        'label'             => [
            'fields'         => ['home','away'],
            'format'         => '%s : %s',
            'label_callback' => ['\Fiedsch\Liga\DCAHelper', 'begegnungLabelCallback'],
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
                'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['edit'],
                'href'  => 'table=tl_spiel',
                'icon'  => 'edit.gif',
            ],
            'editheader'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['editheader'],
                'href'  => 'act=edit',
                'icon'  => 'header.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_begegnung']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['show'],
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
            'label'            => &$GLOBALS['TL_LANG']['tl_begegnung']['pid'],
            'filter'           => true,
            'exclude'          => true,
            'sorting'          => true,
            //'flag'             => 11, // sort ascending
            'inputType'        => 'select',
            'foreignKey'       => 'tl_liga.name',
            'eval'             => ['submitOnChange' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
            'options_callback' => ['\Fiedsch\Liga\DCAHelper', 'getAktiveLigenForSelect'],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'home'   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_begegnung']['home'],
            'filter'           => true,
            'exclude'          => true,
            'sorting'          => true,
            //'flag'             => 11, // sort ascending
            'inputType'        => 'select',
            'foreignKey'       => 'tl_mannschaft.name',
            'eval'             => ['tl_class' => 'w50 clr', 'chosen' => true, 'includeBlankOption' => true],
            'relation'         => ['type' => 'hasOne', 'load' => 'eager'],
            'options_callback' => ['\Fiedsch\Liga\DCAHelper', 'getMannschaftenForSelect'],
            'sql'              => "int(10) NOT NULL default '0'",
        ],
        'away'   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_begegnung']['away'],
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