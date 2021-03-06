<?
$_SERVER["DOCUMENT_ROOT"] = preg_replace('/\/\w*\/\w*\/\w*\.php$/', '', __FILE__);

if (!is_dir($_SERVER["DOCUMENT_ROOT"]))
    die("DOCUMENT_ROOT - notDir");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
use Fandom\Lotinfo;

if (!\Bitrix\Main\Loader::includeModule("fandom.lotinfo"))
    die("Не удалось загрузить модуль fandom.lotinfo");

try {
    $data = new Lotinfo\Data($_SERVER["DOCUMENT_ROOT"]);
    $data->get();
} catch (Exception $e) {
    echo $e->getMessage();
}
