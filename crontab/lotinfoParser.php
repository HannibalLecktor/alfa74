$<?
error_reporting(E_ALL);
$_SERVER["DOCUMENT_ROOT"] = preg_replace('/\/\w*\/\w*\/\w*\.php$/', '', __FILE__);

if (!is_dir($_SERVER["DOCUMENT_ROOT"]))
    die("DOCUMENT_ROOT - notDir");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
use Fandom\Lotinfo;

if (!\Bitrix\Main\Loader::includeModule("fandom.lotinfo"))
    die("Не удалось загрузить модуль fandom.lotinfo");

try {
    $parsing = new Lotinfo\Parser(
        $_SERVER["DOCUMENT_ROOT"],
        $argv[1],
        $argv[2]
    );

    $pars = $parsing->importData();
    $logFile = $parsing->arParams['LOG_FILE'];
    $text = '';
    if (!empty($parsing->errors)) {
        $errors = \Helper::boldColorText("Errors: ", "black");
        $text .= $errors . $parsing->errors;
    }
    if (!empty($parsing->message)) {
        $messages = \Helper::boldColorText("Messages: ", "black");
        $text .= $messages . $parsing->message;
    }

    file_put_contents($logFile, $text, FILE_APPEND);
    Lotinfo\Common::sendMail($text, "parsing", true);
} catch (Exception $e) {
    echo $e->getMessage();
}

//Email::sendMail($email_text, true);

