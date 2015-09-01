<?php
/**
 * Created by PhpStorm.
 * User: fans
 * Date: 20.03.15
 * Time: 12:45
 */
define(DEFAULT_TEMPLATE_PATH, '/local/templates/.default');

CModule::AddAutoloadClasses(
    '',
    array(
        'Helper' => '/local/php_interface/libs/helper.php',
//        'EventManager' => '/local/php_interface/EventManager.php',
//        'Common' => '/local/php_interface/common.php',
//        'Registry' => '/local/php_interface/include/registry.php',
    )
);

//$eventManager = \Bitrix\Main\EventManager::getInstance();
//$eventManager->addEventHandler("iblock", "OnAfterIBlockSectionUpdate", ['EventManager', 'OnAfterIBlockSectionUpdate']);
