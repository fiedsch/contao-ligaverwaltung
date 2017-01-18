<?php
/**
 * Backendmodule
 */
array_insert($GLOBALS['BE_MOD'], 2, [
    'liga' => [
        'liga.spielort'   => [
            'tables' => ['tl_spielort'],
            //'icon' => 'system/modules/ligaverwaltung/assets/img/spielort.gif'
        ],
        'liga.saison'     => [
            'tables' => ['tl_saison'],
            //'icon' => 'system/modules/ligaverwaltung/assets/img/saison.gif'
        ],
        'liga.verband'    => [
            'tables' => ['tl_verband', 'tl_liga', 'tl_begegnung', 'tl_spiel'],
            //'icon' => 'system/modules/ligaverwaltung/assets/img/saison.gif'
        ],
        //'liga.liga'       => [
        //    'tables' => ['tl_liga', 'tl_begegnung', 'tl_spiel'],
        //    //'icon' => 'system/modules/ligaverwaltung/assets/img/liga.gif'
        //],
        'liga.mannschaft' => [
            'tables' => ['tl_mannschaft', 'tl_spieler'],
            //'icon' => 'system/modules/ligaverwaltung/assets/img/mannschaft.gif'
        ],
        'liga.begegnung'  => [
            'tables' => ['tl_begegnung', 'tl_spiel'],
            //'icon' => 'system/modules/ligaverwaltung/assets/img/begegnung.gif'
        ],

    ],
]);

/**
 * Contentelemente
 */
$GLOBALS['TL_CTE']['texts']['ligenliste'] = 'ContentLigenliste';
$GLOBALS['TL_CTE']['texts']['mannschaftsliste'] = 'ContentMannschaftsliste';
$GLOBALS['TL_CTE']['texts']['spielerliste'] = 'ContentSpielerliste';
$GLOBALS['TL_CTE']['texts']['spielplan'] = 'ContentSpielplan';
