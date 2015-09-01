<?php namespace Xpage\SeoFilter;

use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Config\Option;
use Bitrix\Iblock\SectionTable;

class MetaManager
{
    public static function setPageMeta($sectionId, $propertyValues) {
        $page = self::getPageByUrl($GLOBALS['APPLICATION']->GetCurPage(false));

        if(!$page) {
            $seoTitleTemplate = Option::get('xpage.seofilter', 'SEO_TITLE', null);
            $seoDescriptionTemplate = Option::get('xpage.seofilter', 'SEO_DESCRIPTION', null);
            $seoKeywordsTemplate = Option::get('xpage.seofilter', 'SEO_KEYWORDS', null);
            $pageTitleTemplate = Option::get('xpage.seofilter', 'SEO_H1', null);
            $page['PAGE_TITLE'] = self::compileTemplate($seoTitleTemplate, $sectionId, $propertyValues);
            $page['PAGE_TITLE_H1'] = self::compileTemplate($pageTitleTemplate, $sectionId, $propertyValues);
            $page['META_KEYWORDS'] = self::compileTemplate($seoKeywordsTemplate, $sectionId, $propertyValues);
            $page['META_DESCRIPTION'] = self::compileTemplate($seoDescriptionTemplate, $sectionId, $propertyValues);
        }

        if($page['PAGE_TITLE_H1']) $GLOBALS['APPLICATION']->SetTitle(trim($page['PAGE_TITLE_H1']));
        if($page['PAGE_TITLE']) $GLOBALS['APPLICATION']->SetPageProperty('title', trim($page['PAGE_TITLE']));
        if($page['META_KEYWORDS']) $GLOBALS['APPLICATION']->SetPageProperty('keywords', trim($page['META_KEYWORDS']));
        if($page['META_DESCRIPTION']) $GLOBALS['APPLICATION']->SetPageProperty('description', trim($page['META_DESCRIPTION']));
    }

    private static function getPageByUrl($url) {
        $page = PageTable::getList([
            'filter' =>
                [
                    '=URL' => $url
                ]
        ])->fetch();

        return $page;
    }

    private static function compileTemplate($template, $sectionId, $propertyValues) {
        $section = SectionTable::getList([
            'filter' =>
                [
                    'ID' => $sectionId
                ]
        ])->fetch();

        $search = [
            '#SECTION_NAME#',
        ];

        $replace = [
            strtolower($section['NAME']),
        ];

        $obPropertiesValues = PropertyEnumerationTable::getList([
            'order' =>
                [
                    'SEOFILTER_PROPERTY_TABLE.SORT' => 'ASC'
                ],
            'select' =>
                [
                    'ID',
                    'VALUE',
                    'VALUE_ALT' => 'VALUE_ALT_TABLE.VALUE'
                ],
            'filter' =>
                [
                    'ID' => array_values($propertyValues)
                ],
            'runtime' =>
                [
                    new \Bitrix\Main\Entity\ReferenceField(
                        'VALUE_ALT_TABLE',
                        '\\Xpage\\Seofilter\\PropertyValueAlt',
                        ['this.ID' => 'ref.ENUM_ID']
                    ),
                    new \Bitrix\Main\Entity\ReferenceField(
                        'SEOFILTER_PROPERTY_TABLE',
                        '\\Xpage\\Seofilter\\Property',
                        ['this.PROPERTY_ID' => 'ref.PROPERTY_ID']
                    ),

                ]
        ]);

        $arPropertyValues = [];
        while($arPropertyValue = $obPropertiesValues->fetch()) {
            $arPropertyValue['VALUE'] = $arPropertyValue['VALUE_ALT'] ?: strtolower($arPropertyValue['VALUE']);
            $arPropertyValues[] = $arPropertyValue;
        }

        $strPropertyValues = implode(" ", \Xpage\Helper::array_pluck($arPropertyValues, 'VALUE'));

        $search[] = "#PROPERTY_VALUES#";
        $replace[] = $strPropertyValues;

        $compiledTemplate = preg_replace('/#.+#/', '', str_replace($search, $replace, $template));

        return \Xpage\Helper::ucfirst($compiledTemplate);
    }
}