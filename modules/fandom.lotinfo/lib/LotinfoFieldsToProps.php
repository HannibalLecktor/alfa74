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

class LotinfoFieldsToPropsTable extends DataManager
{
    public static function getTableName()
    {
        return 'fd_lotinfo_fields';
    }

    public static function getMap()
    {
        return [
            new IntegerField('ID', [
                'autocomplete' => true,
                'primary' => true,
                'title' => Loc::getMessage('ID'),
            ]),
            new StringField('LOTINFO_FIELD', [
                'required' => true,
                'title' => Loc::getMessage('LOTINFO_FIELD'),
            ]),
            new StringField('PROP_ID', [
                'title' => Loc::getMessage('PROP_ID'),
            ]),
            new StringField('FIELD_ID', [
                'title' => Loc::getMessage('FIELD_ID'),
            ])
        ];
    }
}
