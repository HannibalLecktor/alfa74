<?php namespace Xpage\SeoFilter;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class PropertyValueAltTable extends Entity\DataManager
{
    public static function getTableName() {
        return 'xpage_seofilter_property_value_alt';
    }

    public static function getMap() {
        return [
            new Entity\IntegerField(
                'ENUM_ID',
                [
                    'title'   => 'ENUM_ID',
                    'primary' => true,
                ]
            ),
            new Entity\ReferenceField(
                'ENUM_TABLE',
                'Bitrix\\Iblock\\PropertyEnumerationTable',
                ['=this.ENUM_ID' => 'ref.ID']
            ),
            new Entity\StringField(
                'VALUE',
                ['title' => 'Значение']
            )
        ];
    }

    public function updateValues($values) {
        $result = new Entity\AddResult();
        foreach($values as $enum_id => $value) {
            self::delete($enum_id);
            $result = self::add([
                'ENUM_ID' => $enum_id,
                'VALUE'   => $value
            ]);
            if(!$result->isSuccess()) return false;
        }

        return $result;
    }
}