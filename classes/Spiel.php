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
     * @var int
     */
    protected $score_home;

    /**
     * @var int
     */
    protected $score_away;

    /**
     * Spiel constructor.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->score_home = $data['score_home'];
        $this->score_away = $data['score_away'];
    }

    /**
     * @return int
     */
    public function getScoreHome()
    {
        return $this->score_home;
    }

    /**
     * @return int
     */
    public function getScoreAway()
    {
        return $this->score_away;
    }

    /**
     * @return int
     */
    public function getPunkteHome()
    {
        if ($this->score_home == $this->score_away) {
            return 0;
        }
        return $this->score_home > $this->score_away ? 1 : 0;
    }

    /**
     * @return int
     */
    public
    function getPunkteAway()
    {
        if ($this->score_home == $this->score_away) {
            return 0;
        }
        return $this->score_home > $this->score_away ? 0 : 1;
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