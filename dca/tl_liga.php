<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

$GLOBALS['TL_DCA']  ['tl_liga'] = [
    'config' => [
        'dataContainer'    => 'Table',
        'ptable'           => 'tl_verband',
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id'          => 'primary',
                'pid,name,saison' => 'unique',
            ],
        ],
    ],

    'list' => [
        'sorting'           => [
            'mode'        => 1,
            'flag'        => 11,
            'fields'      => ['name'],
            'panelLayout' => 'sort,filter;search,limit',
        ],
        'label'             => [
            'fields'         => ['name'],
            'format'         => '%s',
            'label_callback' => ['\Fiedsch\Liga\DCAHelper', 'ligaLabelCallback'],
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
                'label' => &$GLOBALS['TL_LANG']['tl_liga']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_liga']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete'     => [
                'label'      => &$GLOBALS['TL_LANG']['tl_liga']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_liga']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
            'toggle'     => [
                'label'                => &$GLOBALS['TL_LANG']['tl_liga']['toggle'],
                'attributes'           => 'onclick="Backend.getScrollOffset();"',
                'haste_ajax_operation' => [
                    'field'   => 'aktiv',
                    'options' => [
                        [
                            'value' => '',
                            'icon'  => 'invisible.gif',
                        ],
                        [
                            'value' => '1',
                            'icon'  => 'visible.gif',
                        ],
                    ],
                ],
            ],
        ],
    ],

    'palettes' => [
        'default' => '{title_legend},name,saison,aktiv',
    ],

    'fields' => [
        'id'     => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pid'         => [
            'foreignKey' => 'tl_verband.name',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'name'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_liga']['name'],
            'sorting'   => true,
            'flag'      => 11, // sort ascending
            'inputType' => 'text',
            'exclude'   => true,
            'eval'      => ['maxlength' => 128, 'tl_class' => 'w50'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'saison' => [
            'label'      => &$GLOBALS['TL_LANG']['tl_liga']['saison'],
            'inputType'  => 'select',
            'filter'     => true,
            'exclude'    => true,
            'foreignKey' => 'tl_saison.name',
            'eval'       => ['chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
            'sql'        => "int(10) unsigned NOT NULL default '0'",
        ],
        'aktiv'  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_liga']['aktiv'],
            'inputType' => 'checkbox',
            'filter'    => true,
            'exclude'   => true,
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
    ],
];