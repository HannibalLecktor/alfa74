<?php namespace Xpage\SeoFilter;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;

Loc::loadMessages(__FILE__);

class PageTable extends Entity\DataManager
{
    public static function getTableName() {
        return 'xpage_seofilter_page';
    }

    public static function getMap() {
        return [
            new Entity\IntegerField('ID', [
                'title'        => 'ID',
                'primary'      => true,
                'autocomplete' => true
            ]),
            new Entity\StringField('URL', [
                'title' => 'URL'
            ]),
            new Entity\StringField('PAGE_TITLE', [
                'title' => 'PAGE_TITLE'
            ]),
            // new Entity\StringField('PAGE_TITLE_H1', [
            //     'title' => 'PAGE_TITLE_H1'
            // ]),
            new Entity\TextField('META_DESCRIPTION', [
                'title' => 'META_DESCRIPTION'
            ]),
            new Entity\TextField('META_KEYWORDS', [
                'title' => 'META_KEYWORDS'
            ]),
        ];
    }
}