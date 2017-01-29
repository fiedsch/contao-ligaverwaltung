<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\Liga;

/**
 * Class Begegnung
 *
 * Begegnungen zweier Mannschaften bestehen aus Spielen einzelner Spieler gegeneinander.
 *
 * @package Fiedsch\Liga
 */
class Begegnung
{

    /**
     * @var array
     */
    protected $spiele;

    /**
     * Begegnung constructor.
     *
     * @param int $score_home
     * @param int $score_away
     */
    public function __construct()
    {
    }

    /**
     * @param Spiel $spiel
     */
    public function addSpiel(Spiel $spiel)
    {
        $this->spiele[] = $spiel;
    }

    /**
     * @return int
     */
    public function getScoreHome()
    {
        $result = 0;
        /** @var Spiel $spiel */
        foreach ($this->spiele as $spiel) {
            $result += $spiel->getScoreHome();
        }
        return $result;
    }

    /**
     * @return int
     */
    public function getScoreAway()
    {
        $result = 0;
        /** @var Spiel $spiel */
        foreach ($this->spiele as $spiel) {
            $result += $spiel->getScoreAway();
        }
        return $result;
    }

    /**
     * @return int
     */
    public function getPunkteHome()
    {
        $score_home = $this->getScoreHome();
        $score_away = $this->getScoreAway();
        if ($score_home == $score_away) {
            return 1;
        }
        return $score_home > $score_away ? 3 : 0;
    }

    /**
     * @return int
     */
    public function getPunkteAway()
    {
        $score_home = $this->getScoreHome();
        $score_away = $this->getScoreAway();
        if ($score_home == $score_away) {
            return 1;
        }
        return $score_home > $score_away ? 0 : 3;
    }

    public function getNumSpiele()
    {
        return count($this->spiele);
    }

    /**
     * TODO: in eine eigene Klasse?
     *
     * Compare results $a and $b for sorting, i.e. return -1, 0 or +1
     *
     * @param array $a
     * @param array $b
     */
    public static function compareMannschaftResults($a, $b)
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