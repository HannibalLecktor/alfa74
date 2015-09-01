<?

$includePath = $_SERVER['DOCUMENT_ROOT'] . '/local/modules/xpage.seofilter/lib/';
include_once $includePath . 'property.php';
include_once $includePath . 'page.php';
include_once $includePath . 'propertyvaluealt.php';

IncludeModuleLangFile(__FILE__);

if(class_exists("xpage_seofilter")) {
    return;
}

class xpage_seofilter extends CModule
{
    public $MODULE_ID = "xpage.seofilter";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_CSS;

    public function __construct() {
        $arModuleVersion = [];

        include(dirname(__FILE__) . "/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = "Seo filter";
        $this->MODULE_DESCRIPTION = "Seo filter";
    }

    public function InstallDB() {
        $propertyTableCreateSql = Xpage\SeoFilter\PropertyTable::getEntity()->compileDbTableStructureDump()[0];
        $pageTableCreateSql = Xpage\SeoFilter\PageTable::getEntity()->compileDbTableStructureDump()[0];
        $propertyValueAltTableCreateSql = Xpage\SeoFilter\PropertyValueAltTable::getEntity()->compileDbTableStructureDump()[0];
        Bitrix\Main\Application::getConnection()->query($propertyTableCreateSql);
        Bitrix\Main\Application::getConnection()->query($pageTableCreateSql);
        Bitrix\Main\Application::getConnection()->query($propertyValueAltTableCreateSql);
    }

    public function UnInstallDB() {
        $propertyTableName = Xpage\SeoFilter\PropertyTable::getTableName();
        Bitrix\Main\Application::getConnection()->dropTable($propertyTableName);
        $pageTableName = Xpage\SeoFilter\PageTable::getTableName();
        Bitrix\Main\Application::getConnection()->dropTable($pageTableName);
        $propertyValueAltTableName = Xpage\SeoFilter\PropertyValueAltTable::getTableName();
        Bitrix\Main\Application::getConnection()->dropTable($propertyValueAltTableName);
    }

    public function InstallFiles() {
        CopyDirFiles(dirname(__FILE__) . "/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true);

        return true;
    }

    public function UnInstallFiles() {
        DeleteDirFiles(dirname(__FILE__) . "/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");

        return true;
    }

    public function DoInstall() {
        RegisterModule($this->MODULE_ID);
        $this->InstallDB();
        $this->InstallFiles();
    }

    public function DoUninstall() {
        UnRegisterModule($this->MODULE_ID);
        $this->UnInstallDB();
        $this->UnInstallFiles();
    }
}