<?
echo "ddfg";
die();
$_SERVER["DOCUMENT_ROOT"] = exec("cd ../../; pwd");

if (is_dir("")) {
    echo "dir";
} else {
    echo "notDir";
}

die();
if (!empty($_SERVER["DOCUMENT_ROOT"] )) {
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
    $moduleLogFile = COption::GetOptionString("fandom.lotinfo", "LOG_FILE");
    $logs = "";

    use Fandom\Lotinfo;

    if (!empty($moduleLogFile)) {
        $moduleLogFile = $_SERVER["DOCUMENT_ROOT"] . $moduleLogFile;
    }

    if (!\Bitrix\Main\Loader::includeModule("fandom.lotinfo")) {
        $logs = Helper::boldColorText("Не удалось загрузить модуль fandom.lotinfo", "red");
    } else {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/xml.php');

        $data = new Lotinfo\Data($_SERVER["DOCUMENT_ROOT"]);

        function getData($objectType, $xml_file, $config, $errors, $message, $tmpDir, $email_text, $log_file, $clearJsonDir = false)
        {
            $config['api']['get']['getData'] .= '&objectType%3D'.$objectType;
            $file_append = FILE_APPEND;

            if ($clearJsonDir) {
                $file_append = 0;
                ParseXml::recRMDir($config['xmlDir']);
            }

            $curlResult = Curl::getFile(
                $xml_file,
                $config['xmlDir'],
                $objectType,
                $config['api']['url'],
                $config['api']['get']
            );

            if (!$curlResult) {
                $errors .= "<b style='color:red'>Curl::getFile error: ".Curl::$error."</b><br>";
            } else {
                try {
                    $parseXml = new ParseXml(
                        $curlResult,
                        $tmpDir
                    );

                    $result= $parseXml->getData($clearJsonDir);

                    if (!$result) {
                        $errors .= "<b style='color:red'>ObjectType - $objectType: ".$parseXml->error."</b><br>";
                    } else {
                        $message .= "<b style='color:green'>ObjectType - $objectType: Файл получен и обработан</b><br>";
                    }

                } catch (Exception $e) {
                    $errors .= "<b style='color:red'>".$e->getMessage()."</b>";
                }
            }

            if ($errors)
                $email_text .= $errors."<br>";

            if ($message)
                $email_text .= $message."<br>";

            file_put_contents($log_file, $email_text, $file_append);
        }

        foreach ($config[$objectTypeConfig] as $arConfig) {
            foreach ($arConfig as $type=>$arType) {
                $arTypes[] = $type;
            }
        }

        if (!empty($arTypes)) {
            while ($i < $config['countOfRequests']) {

                if ($i != 0) {
                    $clearJsonDir = false;
                }

                if ($arTypes[$i])
                    getData($arTypes[$i], $xml_file, $config, $errors, $message, $tmpDir, $email_text, $log_file, $clearJsonDir);

                $i++;
            }
        }
    }
} else {
    die("Не определилась корневая директория проекта");
}