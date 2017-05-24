#!/usr/local/bin/php
<?php
/**
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

$data = [];

print "# Abrechnung\n";

foreach ($ligen as $liga) {

    $data[$liga->name] = [];
    printf("## %s (%s)\n", $liga->name, $saison->name);
    // Mannschaften in dieser Liga
    $mannschaften = \MannschaftModel::findBy(
        ['liga=?'],
        [$liga->id]
    );
    if ($mannschaften) {
        foreach ($mannschaften as $mannschaft) {
            $data[$liga->name][$mannschaft->name] = [
                'spielort'   => $mannschaft->getRelated('spielort')->name,
                'aufsteller' => $mannschaft->getRelated('spielort')->getRelated('aufsteller')->name,
            ];
            printf("* %s (%s, %s)\n",
                $mannschaft->name,
                $mannschaft->getRelated('spielort')->name,
                $mannschaft->getRelated('spielort')->getRelated('aufsteller')->name
            );
        }
    } else {
        print "keine Mannschaften in der Liga '" . $liga->name . "''\n";
    }
}

print_r($data);


