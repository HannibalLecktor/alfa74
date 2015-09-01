<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
/**
 * Created by PhpStorm.
 * User: Khadeev Fanis
 * Date: 01/09/15
 * Time: 00:11
 */

use \Fandom\Lotinfo;

class EventManager
{
    static function OnAfterIBlockSectionUpdate(&$fields) {

        $ob = Lotinfo\LotinfoTypeToIBlockTable::getList([
            ["select" => "ID"],
            ["filter" => "SECTION_ID"]
        ]);
    }
}