<?php
/**
 * Created by PhpStorm.
 * User: fans
 * Date: 02.09.15
 * Time: 15:41
 */

namespace Fandom\Lotinfo;

class Data
{
    private $arParams = array(
        'TMP_DIR' => "dir",
        'XML_DIR' => "dir",
        'XML_FILE' => "",
        'LOG_FILE' => "dir",
        'API_URL' => "",
        'GET_PARAMS' => "",
        'API_KEY' => '',
        'API_CMD' => ''
    );
    public $errors = "";
    public $message = '';
    static private $MODULE_NAME = "fandom.lotinfo";

    public function __construct($docRoot)
    {
        foreach ($this->arParams as $key=>$value) {
            $paramValue = \COption::GetOptionString(self::$MODULE_NAME, $key);

            if (!$paramValue) {
                $this->errors .= \Helper::boldColorText("Not value for {$key}", "red");
            } elseif ($value == "dir") {
                $this->arParams[$key] = $docRoot . $paramValue;
                if ($key != 'LOG_FILE') {
                    if (!is_dir($this->arParams[$key])) {
                        if (!mkdir($this->arParams[$key])) {
                            $this->errors .= \Helper::boldColorText("Dir {$this->arParams[$key]} is absent", "red");
                        }
                    }
                }
            } else {
                $this->arParams[$key] = $paramValue;
            }
        }

        if (!empty($this->errors)) {
            $logFile = $this->arParams['LOG_FILE'];
            if ($logFile == 'dir')
                $logFile = $docRoot . '/local/logs/lot_info';

            file_put_contents($this->errors, $logFile, FILE_APPEND);

            throw new \Exception($this->errors);
        }
    }

    public function get()
    {
        $arLotinfoTypes = $this->getLotinfoTypes();

        if (!empty($arLotinfoTypes)) {

            Common::recRMDir($this->arParams['XML_DIR']);
            Common::recRMDir($this->arParams['TMP_DIR']);

            foreach ($arLotinfoTypes as $arType) {
                $curlResult = Curl::getFile(
                    $this->arParams['XML_FILE'],
                    $this->arParams['XML_DIR'],
                    $arType,
                    $this->arParams['API_URL'],
                    [
                        'apiKey' => $this->arParams['API_KEY'],
                        'cmd' => $this->arParams['API_CMD'],
                        'getData' => $this->arParams['GET_PARAMS']
                    ]
                );

                if (!$curlResult or !file_exists($curlResult)) {
                    $this->errors .= \Helper::boldColorText("Curl::getFile error: " . Curl::$ERROR, "red");
                } else {
                    try {
                        $parseXml = new ParseXml($curlResult, $this->arParams['TMP_DIR']);

                        $result= $parseXml->getData();

                        if (!$result) {
                            $this->errors .= \Helper::boldColorText("ObjectType - {$arType}: ".$parseXml->error, "red");
                        } else {
                            $this->message .= \Helper::boldColorText("ObjectType - {$arType}: Файл получен и обработан", "green");
                        }

                    } catch (Exception $e) {
                        $this->errors .= \Helper::boldColorText($e->getMessage(), "red");
                    }
                }
            }

        } else {
            $this->errors = \Helper::boldColorText("Нет номеров объектов Лотинфо", "red");
        }

        $log = (!empty($this->errors))? \Helper::boldColorText("Errors", "black") . $this->errors : "";
        $log .= (!empty($this->message))? \Helper::boldColorText("Messages", "black") . $this->message : "";
        file_put_contents($this->arParams['LOG_FILE'], $log, FILE_APPEND);
    }

    /*
     *
     */
    private function getLotinfoTypes()
    {
        $arLotinfoTypes = [];

        $lotInfoTypesOb = LotinfoTypeToIBlockTable::getList(
            [
                "select" => [
                    "LOTINFO_TYPE"
                ],
                "filter" => [
                    "!SECTION_ID" => false,
                    "!TRANSACTION" => false
                ]
            ]
        );

        while ($lotInfoTypes = $lotInfoTypesOb->fetch()) {
            $arLotinfoTypes[] = $lotInfoTypes['LOTINFO_TYPE'];
        }

        return $arLotinfoTypes;
    }
}