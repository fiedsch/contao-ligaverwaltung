<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */
class MannschaftModel extends \Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = "tl_mannschaft";

    /**
     * Alle Mannschaften, die aktiv sind, d.h. in eine liga (tl_liga) spielen, die aktiv ist
     *
     * @return \Collection|null
     */
    public static function findAllActive()
    {
        $result = \Database::getInstance()
            ->prepare('SELECT m.* FROM  tl_mannschaft m LEFT JOIN tl_liga l ON (m.pid=l.id) WHERE l.aktiv=?')
            ->execute(1);
        return \Model::createCollectionFromDbResult($result, 'tl_mannschaft');
    }

    /**
     * Alle Mannschaften, die in einer Liga spielen
     *
     * @param int $liga_id
     */
    public static function findByLiga($liga_id)
    {
        return self::findBy(['pid=?'],[$liga_id]);
    }

}