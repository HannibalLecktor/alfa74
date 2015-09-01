<?php
namespace Fandom\Lotinfo;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\Validator;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class LotinfoConfigTable extends DataManager
{
    public static function getTableName()
    {
        return 'fd_lotinfo_config';
    }

    public static function getMap()
    {
        return [
            new IntegerField('ID', [
                'autocomplete' => true,
                'primary' => true,
                'title' => Loc::getMessage('ID'),
            ]),
            new BooleanField('DEBUG', [
                'required' => true,
                'title' => Loc::getMessage('DEBUG'),
                'default_value' => 1
            ]),
            new StringField('TMP_DIR', [
                'required' => true,
                'title' => Loc::getMessage('TMP_DIR'),
                'default_value' => '/upload/tmp_xml'
            ]),
            new StringField('XML_FILE', [
                'required' => true,
                'title' => Loc::getMessage('XML_FILE'),
                'default_value' => 'lot_info_%ID.xml'
            ]),
            new StringField('XML_DIR', [
                'required' => true,
                'title' => Loc::getMessage('XML_DIR'),
                'default_value' => '/upload/lot_info'
            ]),
            new StringField('LOG_FILE', [
                'required' => true,
                'title' => Loc::getMessage('LOG_FILE'),
                'default_value' => '/local/logs/lot_info'
            ]),
            new StringField('API_URL', [
                'required' => true,
                'title' => Loc::getMessage('API_URL'),
            ]),
            new StringField('API_KEY', [
                'required' => true,
                'title' => Loc::getMessage('API_KEY'),
            ]),
            new StringField('API_CMD', [
                'required' => true,
                'title' => Loc::getMessage('API_CMD'),
            ]),
            new StringField('GET_PARAMS', [
                'required' => true,
                'title' => Loc::getMessage('GET_PARAMS'),
            ]),
            new IntegerField('IBLOCK_ID', [
                'required' => true,
                'title' => Loc::getMessage('IBLOCK_ID'),
            ]),
            new IntegerField('SITIES_ID', [
                'required' => true,
                'title' => Loc::getMessage('SITIES_ID'),
            ])
        ];
    }
}
