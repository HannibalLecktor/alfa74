<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Fandom\Lotinfo\LotinfoConfigTable;
use Fandom\Lotinfo\LotinfoFieldsToPropsTable;
use Fandom\Lotinfo\LotinfoTypeToIBlockTable;

Loc::loadMessages(__FILE__);

if (class_exists('fandom_lotinfo')) {
    return;
}

class fandom_lotinfo extends CModule
{
    /** @var string */
    public $MODULE_ID;

    /** @var string */
    public $MODULE_VERSION;

    /** @var string */
    public $MODULE_VERSION_DATE;

    /** @var string */
    public $MODULE_NAME;

    /** @var string */
    public $MODULE_DESCRIPTION;

    /** @var string */
    public $MODULE_GROUP_RIGHTS;

    /** @var string */
    public $PARTNER_NAME;

    /** @var string */
    public $PARTNER_URI;

    public function __construct()
    {
        $this->MODULE_ID = 'fandom.lotinfo';
        $this->MODULE_VERSION = '0.0.1';
        $this->MODULE_VERSION_DATE = '2015-08-19 23:08:14';
        $this->MODULE_NAME = Loc::getMessage('MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = "FanDom";
        $this->PARTNER_URI = "http://www.fandom.ru";
    }

    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->installDB();
        $this->installFiles();
    }

    public function doUninstall()
    {
        $this->uninstallDB();
        $this->unInstallFiles();
        ModuleManager::unregisterModule($this->MODULE_ID);
    }

    public function installDB()
    {
        if (Loader::includeModule($this->MODULE_ID)) {
            LotinfoConfigTable::getEntity()->createDbTable();
            LotinfoFieldsToPropsTable::getEntity()->createDbTable();
            LotinfoTypeToIBlockTable::getEntity()->createDbTable();
        }
    }

    public function uninstallDB()
    {
        if (Loader::includeModule($this->MODULE_ID)) {
            $connection = Application::getInstance()->getConnection();
            $connection->dropTable(LotinfoConfigTable::getTableName());
            $connection->dropTable(LotinfoFieldsToPropsTable::getTableName());
            $connection->dropTable(LotinfoTypeToIBlockTable::getTableName());
        }
    }

    public function installFiles() {
        CopyDirFiles(dirname(__FILE__) . "/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true);

        return true;
    }

    public function unInstallFiles() {
        DeleteDirFiles(dirname(__FILE__) . "/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");

        return true;
    }
}
