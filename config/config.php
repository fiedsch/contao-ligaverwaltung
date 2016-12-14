<?php
/**
 * Backendmodule
 */
array_insert($GLOBALS['BE_MOD'], 2, [
    'liga' => [
        'liga.spielort' => [
            'tables' => ['tl_spielort'],
            //'icon' => 'system/modules/ligaverwaltung/assets/img/spielort.gif'
        ],
        'liga.saison'   => [
            'tables' => ['tl_saison'],
            //'icon' => 'system/modules/ligaverwaltung/assets/img/saison.gif'
        ],
        'liga.liga'     => [
            'tables' => ['tl_liga', 'tl_mannschaft', 'tl_spieler'],
            //'icon' => 'system/modules/ligaverwaltung/assets/img/liga.gif'
        ],
        'liga.spiel'    => [
            'tables' => ['tl_spiel'],
            //'icon' => 'system/modules/ligaverwaltung/assets/img/spiel.gif'
        ],
    ],
]);

/**
 * Contentelemente
 */
$GLOBALS['TL_CTE']['texts']['mannschaftsliste'] = 'ContentMannschaftsliste';
$GLOBALS['TL_CTE']['texts']['spielerliste'] = 'ContentSpielerliste';