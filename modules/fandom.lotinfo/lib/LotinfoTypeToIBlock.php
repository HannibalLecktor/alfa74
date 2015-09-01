<?php
namespace Fandom\Lotinfo;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\EnumField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\Validator;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class LotinfoTypeToIBlockTable extends DataManager
{
    public static $TRANSACTIONS = ['sale', 'rent'];

    public static function getTableName()
    {
        return 'fd_lotinfo_types';
    }

    public static function getMap()
    {
        return [
            new IntegerField('ID', [
                'autocomplete' => true,
                'primary' => true,
                'title' => Loc::getMessage('ID'),
            ]),
            new EnumField('TRANSACTION', [
                'required' => true,
                'title' => Loc::getMessage('TRANSACTION'),
                'values' => self::$TRANSACTIONS
            ]),
            new IntegerField('LOTINFO_TYPE', [
                'required' => true,
                'title' => Loc::getMessage('LOTINFO_TYPE'),
            ]),
            new IntegerField('SECTION_ID', [
                'required' => true,
                'title' => Loc::getMessage('SECTION_ID'),
            ])
        ];
    }
}
