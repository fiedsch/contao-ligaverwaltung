<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */
class BegegnungModel extends \Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = "tl_begegnung";

    /**
     * @return string Ergebnis der Begegnung
     */
    public function getScore()
    {
        $spiele = \SpielModel::findByPid($this->id);
        if (!$spiele) {
            return "";
        }
        $result = [0, 0];
        foreach ($spiele as $spiel) {
            list($home, $away) = $spiel->getScore();
            $result[0] += $home;
            $result[1] += $away;
        }
        return sprintf("%d:%d", $result[0], $result[1]);
    }

    /**
     * @param string $mode Art (Ausführlichkeit) des Labels ['full'|'medium'|'short']
     * @return string
     */
    public function getLabel($mode = 'full')
    {
        switch ($mode) {
            case 'full':
                return sprintf("%s:%s (%s; %s %s)",
                    $this->getRelated('home')->name,
                    $this->getRelated('away')->name,
                    \Date::parse(\Config::get('dateFormat'), $this->spiel_am),
                    $this->getRelated('pid')->name,
                    $this->getRelated('pid')->getRelated('saison')->name
                );
                break;
            case 'medium':
                return sprintf("%s:%s (%s %s)",
                    $this->getRelated('home')->name,
                    $this->getRelated('away')->name,
                    $this->getRelated('pid')->name,
                    $this->getRelated('pid')->getRelated('saison')->name
                );
                break;
            case 'short':
            default:
                return sprintf("%s:%s",
                    $this->getRelated('home')->name,
                    $this->getRelated('away')->name
                );
            break;
        }
    }
}