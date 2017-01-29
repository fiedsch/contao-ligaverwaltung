<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\Liga;

/**
 * Class Spiel
 * Spiel zweier Spieler gegeneinander (Teil einer Begegnung zweier Mannschaften)
 *
 * @package Fiedsch\Liga
 */
class Spiel
{

    /**
     * @var array
     */
    protected $data;

    /**
     * Spiel constructor.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getScoreHome()
    {
        return $this->data['score_home'];
    }

    /**
     * @return int
     */
    public function getScoreAway()
    {
        return $this->data['score_away'];
    }

    /**
     * Achtung: $this->data['spieltype'] (Einzel oder Doppel) wird nicht berücksichtigt,
     * d.h. beide Spielarten werden gleich "bepunktet".
     *
     * @return int
     */
    public function getPunkteHome()
    {
        if ($this->data['score_home'] == $this->data['score_away']) {
            return 0;
        }
        return $this->data['score_home'] > $this->data['score_away'] ? 1 : 0;
    }

    /**
     * Achtung: $this->data['spieltype'] (Einzel oder Doppel) wird nicht berücksichtigt,
     * d.h. beide Spielarten werden gleich "bepunktet".
     *
     * @return int
     */
    public
    function getPunkteAway()
    {
        if ($this->data['score_home'] == $this->data['score_away']) {
            return 0;
        }
        return $this->data['score_home'] > $this->data['score_away'] ? 0 : 1;
    }


    /**
     * TODO: in eine eigene Klasse?
     *
     * Compare results $a and $b for sorting, i.e. return -1, 0 or +1
     *
     * @param array $a
     * @param array $b
     */
    public static function compareSpielerResults($a, $b)
    {
        if ($a['punkte_self'] == $b['punkte_self']) {
            if ($a['score_self'] == $b['score_self']) {
                return 0;
            }
            return $a['score_self'] < $b['score_self'] ? +1 : -1;
        }
        return $a['punkte_self'] < $b['punkte_self'] ? +1 : -1;
    }
}