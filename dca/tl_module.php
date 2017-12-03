<?php

$GLOBALS['TL_DCA']['tl_module']['palettes']['spielortseitenreader'] = '{title_legend},name,headline,type;{config_legend},liga';

$GLOBALS['TL_DCA']['tl_module']['fields']['liga'] = [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['liga'],
        'inputType'        => 'checkboxWizard',
        'filter'           => false,
        'sorting'          => false,
        // 'flag'        => 1, // Sort by initial letter ascending
        'relation'         => ['type' => 'belongsTo', 'load' => 'lazy'],
        'foreignKey'       => 'tl_liga.name',
        'eval'             => ['multiple'=>true,'tl_class' => 'w50 clr'],
        'options_callback' => ['\Fiedsch\Liga\DCAHelper', 'getLigaForSelect'],
        'sql'              => "blob NULL",
];