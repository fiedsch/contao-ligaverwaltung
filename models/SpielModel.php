<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */
class SpielModel extends \Model
{

    const TYPE_EINZEL = 1;
    const TYPE_DOPPEL = 2;

    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = "tl_spiel";
}