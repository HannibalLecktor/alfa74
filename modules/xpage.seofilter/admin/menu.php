<?
if(\Bitrix\Main\ModuleManager::isModuleInstalled('xpage.seofilter')) {
    IncludeModuleLangFile(__FILE__);

    $aMenu = [
        "parent_menu" => "global_menu_services",
        "section"     => "seofilter",
        "sort"        => 500,
        "text"        => "Сеофильтр",
        "title"       => "Сеофильтр",
        "icon"        => "",
        "page_icon"   => "",
        "module_id"   => "xpage.seofilter",
        "items_id"    => "menu_seofilter",
        "items"       => [
            [
                'url'  => 'seofilter_property.php',
                'text' => 'Свойства'
            ],
            [
                'url'  => 'seofilter_page.php',
                'text' => 'Страницы'
            ],
        ]
    ];

    return $aMenu;
}