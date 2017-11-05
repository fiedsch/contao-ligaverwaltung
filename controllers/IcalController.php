<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 *
 *
 * Voraussetzungen:
 *
 * - eluceo/ical ist per Composer installiert
 *   (https://packagist.org/packages/eluceo/ical)
 *
 * - Es existiert eine Date ical.php im document root
 *   (Links auf ical.php stehen im Template ce_spielplan.html):
 *   mit folgendem Inhalt:
 *
 *   <?php
 *
 *   // Set the script name
 *   define('TL_SCRIPT', 'ical.php');
 *
 *   // Initialize the system
 *   define('TL_MODE', 'FE');
 *   require __DIR__ . '/system/initialize.php';
 *
 *   // Run the controller
 *   $controller = new \Fiedsch\Liga\IcalController;
 *   $controller->run();
 *
 */

namespace Fiedsch\Liga;

class IcalController extends \Frontend
{
    /**
     * @var array
     */
    protected $parameters;

    protected $requiredParametersOk;

    /**
     *
     */
    public function _construct()
    {
        parent::__construct();

        define('BE_USER_LOGGED_IN', false);
        define('FE_USER_LOGGED_IN', false);
    }

    /**
     *
     */
    protected function initialize()
    {
        $tz = 'Europe/Berlin';
        $dtz = new \DateTimeZone($tz);
        date_default_timezone_set($tz);

        $this->parameters = [];

        $this->parameters['liga']       = \Input::get('liga');;
        $this->parameters['mannschaft'] = \Input::get('mannschaft');

        // Test-Beispiele:
        //  8 === A-Liga 2017/2018
        // 38 === 180ger Wölfe

        $checkLiga = preg_match("/^\d+$/", $this->parameters['liga']);
        $checkMannschaft = $this->parameters['mannschaft'] === null || preg_match("/^\d+$/", $this->parameters['mannschaft']);

        $this->requiredParametersOk = $checkLiga && $checkMannschaft;
    }


    /**
     *
     */
    public function run()
    {
        $this->initialize();

        if ($this->requiredParametersOk) {
            $this->generateAndSendIcal();
        } else {
            $this->generateAndSendError();
        }
    }

    /**
     *
     */
    protected function generateAndSendError()
    {
        header('Content-Type: text/html; charset=utf-8');
        print "Sorry, beim Export des Spielplans ist ein Fehler aufgetreten!";
        print_r($this->parameters);
    }

    /**
     *
     */
    protected function generateAndSendIcal()
    {
        // Spiele auslesen

        $columns = ['pid=?'];
        $conditions[] = $this->parameters['liga'];

        if ($this->parameters['mannschaft']) {
            $columns[] = '(home=? OR away=?)';
            $conditions[] = $this->parameters['mannschaft'];
            $conditions[] = $this->parameters['mannschaft'];
        }

        $begegnungen = \BegegnungModel::findBy(
            $columns,
            $conditions,
            ['order' => 'spiel_tag ASC, spiel_am ASC']
        );

        if (null === $begegnungen) {
            $this->generateAndSendError();
            return;
        }

        // Kalender anlegen
        $vCalendar = new \Eluceo\iCal\Component\Calendar('www.edart-bayern.de'); // URL parametrisieren!

        // Events hinzufügen
        foreach ($begegnungen as $begegnung) {
            if (!$begegnung->spiel_am || !$begegnung->away) {
                // Mannschaft hat Spielfrei
                continue;
            }
            $this->generateIcalEvent($vCalendar, $begegnung);
        }

        // visual DEBUG
        // header('Content-Type: text/plain; charset=utf-8');
        // echo $vCalendar->render();

        $calendarName = sprintf("edart-bayern-de-%d-%d.ics",
            $this->parameters['liga'],
            $this->parameters['mannschaft'] ?: 'alle'
        );

        header('Content-Type: text/calendar; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$calendarName\"");
        echo $vCalendar->render();
    }

    /**
     * @param \Eluceo\iCal\Component\Calendar $vCalendar
     * @param \BegegnungModel $begegnung
     * @return mixed
     */
    protected function generateIcalEvent(\Eluceo\iCal\Component\Calendar &$vCalendar, \BegegnungModel $begegnung)
    {
        $vEvent = new \Eluceo\iCal\Component\Event();

        $liga = \LigaModel::findById($begegnung->pid);

        $home = \MannschaftModel::findById($begegnung->home);
        $away = \MannschaftModel::findById($begegnung->away);
        $spielort = \SpielortModel::findById($home->spielort);

        $summary = sprintf("%s: %s vs. %s (%s)",
            $liga->name,
            $home->name,
            $away->name,
            $spielort->name
        );

        $location = sprintf("%s, %s %s",
                $spielort->street,
                $spielort->postal,
                $spielort->city
        );

        $vEvent
            ->setDtStart(new \DateTime(date("Y-m-d H:i:s", $begegnung->spiel_am)))
            ->setSummary($summary)
            ->setLocation($location);

        $vCalendar->addComponent($vEvent);
    }

}