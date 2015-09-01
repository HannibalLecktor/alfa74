<?php namespace Xpage\SeoFilter;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;

Loc::loadMessages(__FILE__);

class PropertyTable extends Entity\DataManager
{
    public static function getTableName() {
        return 'xpage_seofilter_property';
    }

    public static function getMap() {
        return [
            new Entity\IntegerField('ID', [
                'title'        => 'ID',
                'primary'      => true,
                'autocomplete' => true
            ]),
            new Entity\StringField('TITLE', [
                'title' => 'Название'
            ]),
            new Entity\IntegerField('SORT', [
                'title' => 'Порядок'
            ]),
            new Entity\IntegerField('PROPERTY_ID', [
                'title'    => 'Свойство',
                'unique'   => true,
                'required' => true
            ]),
            new Entity\ReferenceField(
                'PROPERTY',
                '\\Bitrix\\Iblock\\Property',
                ['=this.PROPERTY_ID' => 'ref.ID'],
                ['title' => 'Свойство']
            ),
        ];
    }
}