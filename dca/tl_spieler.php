<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

$GLOBALS['TL_DCA']['tl_spieler'] = [

    'config' => [
        'dataContainer'    => 'Table',
        'ptable'           => 'tl_mannschaft',
        'switchToEdit'     => true,
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id'            => 'primary',
                'pid'           => 'index',
                'pid,member_id' => 'unique',
            ],
        ],
    ],

    'list' => [
        'sorting'           => [
            'mode'                  => 4, // Displays the child records of a parent record
            'headerFields'          => ['name', 'spielort', 'liga'],
            // TODO(?): wird flag bei mode 4 nicht berücksichtigt?
            // Workarround: DESC als Teil des Feldnamens angeben
            'flag'                  => 1,
            'fields'                => ['teamcaptain DESC,co_teamcaptain DESC'],
            'panelLayout'           => '', // sort, search,filter etc. nicht anzeigen
            'child_record_callback' => ['\Fiedsch\Liga\DCAHelper', 'listMemberCallback'],
            'child_record_class'    => 'no_padding',
            'disableGrouping'       => true,
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
                'label' => &$GLOBALS['TL_LANG']['tl_spieler']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_spieler']['copy'],
                'href'  => 'act=paste&amp;mode=copy',
                'icon'  => 'copy.gif',
            ],
            'cut'    => [
                'label' => &$GLOBALS['TL_LANG']['tl_spieler']['cut'],
                'href'  => 'act=paste&amp;mode=cut',
                'icon'  => 'cut.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_spieler']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_spieler']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],

    'palettes' => [
        'default' => '{member_legend},member_id;{details_legend},teamcaptain,co_teamcaptain,active',
    ],

    'fields' => [
        'id'          => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'         => [
            'foreignKey' => 'tl_mannschaft.name',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'tstamp'      => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'member_id'   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_spieler']['member_id'],
            'exclude'          => true,
            'search'           => true,
            'sorting'          => true,
            'inputType'        => 'select',
            'options_callback' => ['\Fiedsch\Liga\DCAHelper', 'getSpielerForSelect'],
            'eval'             => ['chosen' => true, 'includeBlankOption' => true, 'mandatory' => true, 'tl_class' => 'w50 wizard'],
            'wizard'           => [
                    ['\Fiedsch\Liga\DCAHelper', 'editMemberWizard']
            ],
            //'foreignKey'       => 'tl_member.CONCAT(lastname, ", ", firstname)',
            'foreignKey'       => 'tl_member.CONCAT(firstname, " ", lastname)',
            'relation'         => ['type' => 'hasOne', 'table' => 'tl_member', 'load' => 'eager'],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'teamcaptain' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_spieler']['teamcaptain'],
            'inputType' => 'checkbox',
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'co_teamcaptain' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_spieler']['co_teamcaptain'],
            'inputType' => 'checkbox',
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'active' => [
            'label'      => &$GLOBALS['TL_LANG']['tl_spieler']['active'],
            'save_callback' => [['\Fiedsch\Liga\DCAHelper', 'spielerSaveCallback']],
            'inputType'  => 'checkbox',
            'exclude'    => true,
            'search'     => false,
            'filter'     => true,
            'sorting'    => false,
            //'eval'       => ['tl_style'=>'w50'],
            'sql'        => "char(1) NOT NULL default '1'",
        ]
    ],
];


