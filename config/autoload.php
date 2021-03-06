<?php

/**
 * Register the namespaces
 */
ClassLoader::addNamespaces([
    'Fiedsch\Liga',
]);

ClassLoader::addClasses([
    // Classes
    'Fiedsch\Liga\DCAHelper'        => 'system/modules/ligaverwaltung/classes/DCAHelper.php',
    'Fiedsch\Liga\TemplateHelper'   => 'system/modules/ligaverwaltung/classes/TemplateHelper.php',
    'Fiedsch\Liga\Spiel'            => 'system/modules/ligaverwaltung/classes/Spiel.php',
    'Fiedsch\Liga\Begegnung'        => 'system/modules/ligaverwaltung/classes/Begegnung.php',

    // Elements
    'ContentLigenliste'             => 'system/modules/ligaverwaltung/elements/ContentLigenliste.php',
    'ContentMannschaftsliste'       => 'system/modules/ligaverwaltung/elements/ContentMannschaftsliste.php',
    'ContentSpielbericht'           => 'system/modules/ligaverwaltung/elements/ContentSpielbericht.php',
    'ContentSpielerliste'           => 'system/modules/ligaverwaltung/elements/ContentSpielerliste.php',
    'ContentSpielplan'              => 'system/modules/ligaverwaltung/elements/ContentSpielplan.php',
    'ContentSpielortinfo'           => 'system/modules/ligaverwaltung/elements/ContentSpielortinfo.php',
    'ContentRanking'                => 'system/modules/ligaverwaltung/elements/ContentRanking.php',
    'ContentMannschaftsseite'       => 'system/modules/ligaverwaltung/elements/ContentMannschaftsseite.php',
    'ContentSpielortseite'          => 'system/modules/ligaverwaltung/elements/ContentSpielortseite.php',
    'ContentHighlightRanking'       => 'system/modules/ligaverwaltung/elements/ContentHighlightRanking.php',
    'ContentMannschaftenuebersicht' => 'system/modules/ligaverwaltung/elements/ContentMannschaftenuebersicht.php',

    // Models
    'SpielerModel'                  => 'system/modules/ligaverwaltung/models/SpielerModel.php',
    'SpielortModel'                 => 'system/modules/ligaverwaltung/models/SpielortModel.php',
    'MannschaftModel'               => 'system/modules/ligaverwaltung/models/MannschaftModel.php',
    'LigaModel'                     => 'system/modules/ligaverwaltung/models/LigaModel.php',
    'SaisonModel'                   => 'system/modules/ligaverwaltung/models/SaisonModel.php',
    'BegegnungModel'                => 'system/modules/ligaverwaltung/models/BegegnungModel.php',
    'SpielModel'                    => 'system/modules/ligaverwaltung/models/SpielModel.php',
    'VerbandModel'                  => 'system/modules/ligaverwaltung/models/VerbandModel.php',
    'HighlightModel'                => 'system/modules/ligaverwaltung/models/HighlightModel.php',
    'AufstellerModel'               => 'system/modules/ligaverwaltung/models/AufstellerModel.php',

    // Modules
    'ModuleBegegnungserfassung'     => 'system/modules/ligaverwaltung/modules/ModuleBegegnungserfassung.php',
    'ModuleMannschaftsseitenReader' => 'system/modules/ligaverwaltung/modules/ModuleMannschaftsseitenReader.php',
    'ModuleSpielortseitenReader'    => 'system/modules/ligaverwaltung/modules/ModuleSpielortseitenReader.php',
    'ModuleSpielberichtReader'      => 'system/modules/ligaverwaltung/modules/ModuleSpielberichtReader.php',
    'ModuleSpielerHistory'          => 'system/modules/ligaverwaltung/modules/ModuleSpielerHistory.php',

    // Controllers
    'Fiedsch\Liga\IcalController'  => 'system/modules/ligaverwaltung/controllers/IcalController.php'

]);

TemplateLoader::addFiles([
    'ce_ligenliste'               => 'system/modules/ligaverwaltung/templates',
    'ce_mannschaftsliste'         => 'system/modules/ligaverwaltung/templates',
    'ce_spielbericht'             => 'system/modules/ligaverwaltung/templates',
    'ce_spielerliste'             => 'system/modules/ligaverwaltung/templates',
    'ce_spielplan'                => 'system/modules/ligaverwaltung/templates',
    'ce_spielortinfo'             => 'system/modules/ligaverwaltung/templates',
    'ce_ranking'                  => 'system/modules/ligaverwaltung/templates',
    'ce_mannschaftsseite'         => 'system/modules/ligaverwaltung/templates',
    'ce_spielortseite'            => 'system/modules/ligaverwaltung/templates',
    'be_begegnungserfassung'      => 'system/modules/ligaverwaltung/templates',
    'ce_highlightranking'         => 'system/modules/ligaverwaltung/templates',
    'mod_mannschaftsseitenreader' => 'system/modules/ligaverwaltung/templates',
    'mod_spielortseitenreader'    => 'system/modules/ligaverwaltung/templates',
    'mod_spielberichtreader'      => 'system/modules/ligaverwaltung/templates',
    'ce_mannschaftenuebersicht'   => 'system/modules/ligaverwaltung/templates',
    'be_spielerhistory'           => 'system/modules/ligaverwaltung/templates',
]);
