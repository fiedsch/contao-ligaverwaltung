<?php

/**
 * Spielerliste erstellen
  */

// Konfiguration:
// ID der Ligen, die berücksichtigt werden sollen

$liga_ids = [8,18,17,19,21,15]; // Winter 2017/2018

// Installationsverzeichnis von Contao (dieses Skript
// muss nicht zwingend dort liegen)
define('CONTAO_DIR', '/Users/andreas/Sites/edart-bayern.de');

// Ende Konfiguration

// - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// Set the script name
define('TL_SCRIPT', 'spielerliste_erstellen.php');

// Contao "booten"
define('TL_MODE', 'FE');
require CONTAO_DIR . '/system/initialize.php';


printf("%s;%s;%s;%s;%s\n",
    'Nachname',
    'Vorname',
    'Mannschaft',
    'Liga',
    'Saison'
);

foreach ($liga_ids as $liga_id) {

  $liga = \LigaModel::findById($liga_id);
  if (!$liga) { die("Liga mit der ID $liga_id existiert nicht\n"); }

  $saison = \SaisonModel::findById($liga->saison);
  if (!$saison) { die("Liga ist keiner Saison zugeordnet\n"); }

  $mannschaften = \MannschaftModel::findByLiga($liga->id);
  if (!$mannschaften) { die("In der Liga sind keine Mannschaften angelegt\n"); }

  foreach ($mannschaften as $mannschaft) {
    $spieler = \SpielerModel::findByPid($mannschaft->id);
    foreach ($spieler as $s) {
        $member = $s->getRelated('member_id');
        printf("%s;%s;%s;%s;%s\n",
            $member->lastname,
            $member->firstname,
            $mannschaft->name,
            $liga->name,
            $saison->name
            );
    }

  }

}

