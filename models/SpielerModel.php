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
     * Get the full name (lastname, firstname) for a member
     *
     * @param \MemberModel $member
     */
    protected static function getFullNameFor(\MemberModel $member = null)
    {
        if ($member) {
            return sprintf("%s, %s",
                $member->lastname,
                $member->firstname
            );
        } else {
            return "Kein Member";
        }
    }

    /**
     * @param int $id
     * @ return string
     */
    public static function getMemberNameById($id) {
        $spieler = self::findById($id);
        if ($spieler) {
            $member = $spieler->getRelated('member_id');
            return self::getFullNameFor($member);
        }
        return "kein Name für Spieler " . $id;
    }

    /**
     * @return string
     */
    public function getMemberName() {
        $member = $this->getRelated('member_id');
        self::getFullNameFor($member);
    }

    /**
     * @return string
     */
    public function getFullMemberName() {
        $member = $this->getRelated('member_id');
        $membername = self::getFullNameFor($member);

        $mannschaft = $this->getRelated('pid');
        if ($mannschaft) {
            $mannschaftsname = $mannschaft->name;
            $liga = $mannschaft->getRelated('liga');
            if ($liga) {
                $mannschaftsname .= ' '. $liga->name;
                $saison = $liga->getRelated('saison');
                if ($saison) {
                    $mannschaftsname .= ', ' . $saison->name;
                }
            }
        } else {
            $mannschaftsname = "Mannschaft ex. nicht (mehr)";
        }



        return $membername . ', ' . $mannschaftsname;

    }
}