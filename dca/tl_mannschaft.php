<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

$GLOBALS['TL_DCA']['tl_mannschaft'] = [
    'config' => [
        'dataContainer'    => 'Table',
        'ptable'           => 'tl_liga',
        'ctable'           => ['tl_spieler'],
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id'       => 'primary',
                'pid'      => 'index',
                'pid,name' => 'unique',
            ],
        ],
    ],

    'list' => [
        'sorting'           => [
            'mode'        => 1,
            'fields'      => ['name'],
            'flag'        => 1,
            'panelLayout' => 'sort,filter;search,limit',
        ],
        'label'             => [
            'fields' => ['name'],
            'format' => '%s',
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
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_mannschaft']['edit'],
                'href'  => 'table=tl_spieler',
                'icon'  => 'edit.gif',
            ],

            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_mannschaft']['editheader'],
                'href'  => 'act=edit',
                'icon'  => 'header.gif',
            ],
            'copy'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_mannschaft']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'cut'        => [
                'label' => &$GLOBALS['TL_LANG']['tl_mannschaft']['cut'],
                'href'  => 'act=paste&amp;mode=cut',
                'icon'  => 'cut.gif',
            ],
            'delete'     => [
                'label'      => &$GLOBALS['TL_LANG']['tl_mannschaft']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_mannschaft']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],

    'palettes' => [
        '__selector__' => ['protected', 'allowComments'],
        'default'      => '{title_legend},name,spielort',
    ],

    'fields' => [

        'id' => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],

        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],

        'pid' => [
            'foreignKey' => 'tl_liga.name',
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
            'sql'        => "int(10) unsigned NOT NULL default '0'",
        ],

        'name'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_mannschaft']['name'],
            'inputType' => 'text',
            'exclude'   => true,
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'spielort' => [
            'label'      => &$GLOBALS['TL_LANG']['tl_mannschaft']['spielort'],
            'inputType'  => 'select',
            'exclude'    => true,
            'search'     => false,
            'filter'     => true,
            'sorting'    => false,
            'eval'       => ['mandatory' => true, 'chosen' => true, 'includeBlankOption' => true],
            'foreignKey' => 'tl_spielort.name',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
        ],
    ],
];

