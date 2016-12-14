<?php

/**
 * Register the namespaces
 */
ClassLoader::addNamespaces([
    'Fiedsch\Liga',
]);

ClassLoader::addClasses([
    // Classes
    'Fiedsch\Liga\DCAHelper'  => 'system/modules/ligaverwaltung/classes/DCAHelper.php',

    // Elements
    'ContentMannschaftsliste' => 'system/modules/ligaverwaltung/elements/ContentMannschaftsliste.php',
    'ContentSpielerliste'     => 'system/modules/ligaverwaltung/elements/ContentSpielerliste.php',

    // Models
    'SpielerModel'            => 'system/modules/ligaverwaltung/models/SpielerModel.php',
    'MannschaftModel'         => 'system/modules/ligaverwaltung/models/MannschaftModel.php',
    'LigaModel'               => 'system/modules/ligaverwaltung/models/LigaModel.php',
    'SaisonModel'             => 'system/modules/ligaverwaltung/models/SaisonModel.php',
    // Modules

]);

TemplateLoader::addFiles([
    'ce_mannschaftsliste' => 'system/modules/ligaverwaltung/templates',
    'ce_spielerliste'     => 'system/modules/ligaverwaltung/templates',
]);
