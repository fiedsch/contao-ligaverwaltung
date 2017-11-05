<?php

/**
 * Spielerliste erstellen
  */

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Konfiguration:
// ID der Saison, die berücksichtigt werden sollen

$saison_id = 1; // "2017/2018"

// Ende Konfiguration

// - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// Installationsverzeichnis von Contao (dieses Skript
// muss nicht zwingend dort liegen)
define('CONTAO_DIR', '/Users/andreas/Sites/edart-bayern.de');

// Set the script name
define('TL_SCRIPT', 'spielerliste_erstellen.php');

// Contao "booten"
define('TL_MODE', 'FE');
require CONTAO_DIR . '/system/initialize.php';



$saison = \SaisonModel::findById($saison_id);
if (!$saison) { die("Die Saison ist nicht definiert\n"); }

$liga_ids = [];
$liga = \LigaModel::findBySaison($saison->id);
if (!$liga) { die("Für die Saison ".$saison->name." sind keien Ligen hinterlegt\n"); }
foreach($liga as $l) {
    $liga_ids[] = $l->id;
}

$printMask = "\"%s\";\"%s\";\"%s\";\"%s\";\"%s\"\n";

// Header
printf($printMask,
    'Nachname',
    'Vorname',
    'Mannschaft',
    'Liga',
    'Saison'
);

// Data
foreach ($liga_ids as $liga_id) {

  $liga = \LigaModel::findById($liga_id);
  if (!$liga) { die("Liga mit der ID $liga_id existiert nicht\n"); }

  $mannschaften = \MannschaftModel::findByLiga($liga->id);
  if (!$mannschaften) { die("In der Liga sind keine Mannschaften angelegt\n"); }

  foreach ($mannschaften as $mannschaft) {

    $spieler = \SpielerModel::findByPid($mannschaft->id);

    foreach ($spieler as $s) {
        $member = $s->getRelated('member_id');
        printf($printMask,
            $member->lastname,
            $member->firstname,
            $mannschaft->name,
            $liga->name,
            $saison->name
            );
    }

  }

}

