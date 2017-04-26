<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */
class HighlightModel extends \Model
{

    const TYPE_180 = 1;
    const TYPE_SHORTLEG = 2;
    const TYPE_HIGHFINISH = 3;

    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = "tl_highlight";

    /**
     * @return array
     */
    public static function getOptionsArray()
    {
        return [
            self::TYPE_180 => '180',
            self::TYPE_SHORTLEG => 'Short Leg',
            self::TYPE_HIGHFINISH => 'High Finish',
        ];
    }

}