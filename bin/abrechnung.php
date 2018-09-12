#!/usr/local/bin/php
<?php
/**
 * Kommandozeilenprogramm, das die Daten für die Abrechnung ermittelt.
 * Könnte bei Bedarf in ein Backendmodul überführt werden.
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

// Set the script name
define('TL_SCRIPT', __DIR__ . '/abrechnung.php');

// Initialize the system
define('TL_MODE', 'FE');
require '../../../../system/initialize.php';

// Zu bearbeitende Saison (anpassen)

$saison = \SaisonModel::findByName('2017');

// Ligen dieser Saison

$ligen = \LigaModel::findBy(['saison=?'], [$saison->id]);

// Ergebnisdaten

$data = [
    'wirte'      => [],
    'aufsteller' => [],
];

print "# Daten für die Abrechnung\n";

print "## Ligen und Mannschaften\n";

foreach ($ligen as $liga) {

    printf("\n\n### %s (%s)\n", $liga->name, $saison->name);

    // Mannschaften in dieser Liga
    $mannschaften = \MannschaftModel::findBy(
        ['liga=?'],
        [$liga->id]
    );

    if ($mannschaften) {
        foreach ($mannschaften as $mannschaft) {
            $spielort = $mannschaft->getRelated('spielort')->name ?: 'kein Spielort';
            $aufsteller = $mannschaft->getRelated('spielort')->getRelated('aufsteller')->name ?: 'kein Aufsteller';

            if (!isset($data['wirte'][$spielort])) {
                $data['wirte'][$spielort] = [];
            }
            if (!isset($data['aufsteller'][$aufsteller])) {
                $data['aufsteller'][$aufsteller] = [];
            }
            $mannschaftsbezeichnung = sprintf("%s, %s, %s",
                $mannschaft->name,
                $liga->name,
                $saison->name
                );

            $data['wirte'][$spielort][] = $mannschaftsbezeichnung;
            $data['aufsteller'][$aufsteller][] = $mannschaftsbezeichnung.", $spielort";

            printf("* %s (%s, %s)",
                $mannschaft->name,
                $spielort,
                $aufsteller
            );
        }
    } else {
        print "keine Mannschaften in der Liga '" . $liga->name . "'";
    }
}


print "\n## Wirte";
foreach($data['wirte'] as $wirt => $d) {
    print "### $wirt";
    foreach($d as $who) {
        print "* $who\n";
    }
}

print "\n\\newpage\n";
print "\n\n## Aufsteller\n";
foreach($data['aufsteller'] as $aufsteller => $d) {
    print "\n### $aufsteller";
    foreach($d as $who) {
        print "* $who\n";
    }
}

/*
print "\n\\newpage\n";
print "```\n";
print_r($data);
print "```\n";
*/
