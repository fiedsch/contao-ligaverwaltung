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
        'liga.aufsteller'   => [
            'tables' => ['tl_aufsteller'],
            //'icon' => 'system/modules/ligaverwaltung/assets/img/aufsteller.gif'
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
        'liga.highlight'  => [
            'tables' => ['tl_highlight'],
            'javascript' => 'system/modules/ligaverwaltung/assets/tl_highlight.js',
            'stylesheet' => 'system/modules/ligaverwaltung/assets/tl_highlight.css',
            //'icon' => 'system/modules/ligaverwaltung/assets/img/highlights.gif'
        ],
        // In ModuleBegegnungserfassung als ::redirect() auf 'liga.begegnung' falls
        // keine id angegeben ist.
        'liga.begegnungserfassung'  => [
            'callback' => 'ModuleBegegnungserfassung',
            //'icon' => 'system/modules/ligaverwaltung/assets/img/begegnung.gif'
        ],
        'liga.spieler_history'  => [
            'callback' => 'ModuleSpielerHistory',
            //'icon' => 'system/modules/ligaverwaltung/assets/img/begegnung.gif'
        ],
    ],
]);

/**
 * Contentelemente
 */
$GLOBALS['TL_CTE']['texts']['ligenliste'] = 'ContentLigenliste';
$GLOBALS['TL_CTE']['texts']['mannschaftsliste'] = 'ContentMannschaftsliste';
$GLOBALS['TL_CTE']['texts']['spielbericht'] = 'ContentSpielbericht';
$GLOBALS['TL_CTE']['texts']['spielerliste'] = 'ContentSpielerliste';
$GLOBALS['TL_CTE']['texts']['spielplan'] = 'ContentSpielplan';
$GLOBALS['TL_CTE']['texts']['spielortinfo'] = 'ContentSpielortinfo';
$GLOBALS['TL_CTE']['texts']['ranking'] = 'ContentRanking';
$GLOBALS['TL_CTE']['texts']['highlightranking'] = 'ContentHighlightRanking';
$GLOBALS['TL_CTE']['texts']['mannschaftsseite'] = 'ContentMannschaftsseite';
$GLOBALS['TL_CTE']['texts']['spielortseite'] = 'ContentSpielortseite';
$GLOBALS['TL_CTE']['texts']['mannschaftenuebersicht'] = 'ContentMannschaftenuebersicht';

/**
 * Module
 */
$GLOBALS['FE_MOD']['ligaverwaltung']['mannschaftsseitenreader'] = 'ModuleMannschaftsseitenReader';
$GLOBALS['FE_MOD']['ligaverwaltung']['spielortseitenreader'] = 'ModuleSpielortseitenReader';
$GLOBALS['FE_MOD']['ligaverwaltung']['spielberichtreader'] = 'ModuleSpielberichtReader';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['addCustomRegexp'][] = array('\Fiedsch\Liga\DCAHelper', 'addCustomRegexp');

/* Add to Backend CSS */
if (TL_MODE === 'BE') {
    $GLOBALS['TL_CSS'][] = 'system/modules/ligaverwaltung/assets/ligaverwaltung_be.css';
}