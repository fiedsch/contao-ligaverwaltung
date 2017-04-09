<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */
class SpielerModel extends \Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = "tl_spieler";

    /**
     * @param int $id
     * @ return string
     */
    public static function getMemberNameById($id) {
        $spieler = self::findById($id);
        if ($spieler) {
            $member = $spieler->getRelated('member_id');
            if ($member) {
                return sprintf("%s, %s",
                    $member->lastname,
                    $member->firstname
                );
            }
        }
        return "kein Name für Spieler " . $id;
    }

}