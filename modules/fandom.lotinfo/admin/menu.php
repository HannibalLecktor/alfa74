<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Localization\Loc;

if(\Bitrix\Main\ModuleManager::isModuleInstalled('fandom.lotinfo')) {

    Loc::loadMessages(__FILE__);

    $aMenu = array(
        array(
            'parent_menu' => 'global_menu_services',
            'sort' => 400,
            'text' => Loc::getMessage("PARENT_TEXT"),
            'title' => Loc::getMessage("PARENT_TITLE"),
            'items_id' => 'menu_lotinfo',
            'items' => [
                [
                    'text' => Loc::getMessage("FIELDS_TO_PROPS"),
                    'url' => 'lotinfo_props.php',
                    'title' => Loc::getMessage("FIELDS_TO_PROPS"),
                ],
                [
                    'text' => Loc::getMessage("TYPES_TO_SECTION"),
                    'url' => 'lotinfo_types.php',
                    'title' => Loc::getMessage("TYPES_TO_SECTION"),
                ]
            ],

        ),
    );

    return $aMenu;

}