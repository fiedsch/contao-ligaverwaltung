<?php

/**
 * Begegnungen für eine Liga erstellen.
 * - Die Liga muss bereits angelegt sein
 * - die Mansnchaften die in dieser Liga spielen werden müssen bereits angelegt sein
 */

// Konfiguration:
// ID der Liga im Contao Backend "raussuchen"
//$liga_id = 8; // B-Liga 2017
$liga_id = 3; // A-Liga 2017

// Installationsverzeichnis von Contao (dieses Skript
// muss nicht zwingend dort liegen)
define('CONTAO_DIR', '/Users/andreas/Sites/edart-bayern.de');

// Ende Konfiguration

// - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// Set the script name
define('TL_SCRIPT', 'spielplan_erstellen.php');

// Contao "booten"
define('TL_MODE', 'FE');
require CONTAO_DIR . '/system/initialize.php';

// Liga
$liga = \LigaModel::findById($liga_id);
if (!$liga) { die("Liga mit der ID $liga_id existiert nicht\n"); }

$saison = \SaisonModel::findById($liga->saison);
if (!$saison) { die("Liga ist keiner Saison zugeordnet\n"); }

printf("Erstelle Spielplan für %s %s\n", $liga->name, $saison->name);

$mannschaften = \MannschaftModel::findByLiga($liga->id);
if (!$mannschaften) { die("In der Liga sind keine Mannschaften angelegt\n"); }

$mannschafts_ids = [];
foreach ($mannschaften as $mannschaft) {
    $mannschafts_ids[] = $mannschaft->id;
    printf("-> berücksichtige 'Mannschaft %s'\n", $mannschaft->name);
}

print "Erstelle Spielplan\n";

foreach ($mannschafts_ids as $idHome) {
    foreach ($mannschafts_ids as $idAway) {
        if ($idHome == $idAway) { continue; }
        $begegnung = \BegegnungModel::findBy(['pid=?', 'home=?', 'away=?'], [$liga->id, $idHome, $idAway]);
        if ($begegnung) {
            printf("-> Begegnung '%s:%s' existiert bereits\n", $idHome, $idAway);
        } else {
            printf("->lege Begegnung '%s:%s' an\n", $idHome, $idAway);
            $begegnung = new \BegegnungModel();
            $begegnung->tstamp = time();
            $begegnung->pid = $liga->id;
            $begegnung->home = $idHome;
            $begegnung->away = $idAway;
            $begegnung->spiel_tag = 1; // nur ein Marker! Bei der Spielplanerstellung manuell ändern.
            $begegnung->save();
        }
    }
}

print "fertig\n";
