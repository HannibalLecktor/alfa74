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
        'GET_PARAMS' => ""
    );
    private $fileAppend = FILE_APPEND;
    public $errors = false;
    public $message = '';
    static private $MODULE_NAME = "fandom.lotinfo";
    static private $GET_PARAM_OBJECT = '&objectType%3D';

    public function __construct($docRoot) {
        foreach ($this->arParams as $key=>$value) {
            $paramValue = COption::GetOptionString(self::$MODULE_NAME, $key);

            if (!$paramValue) {
                $this->errors = Helper::boldColorText("Not value for {$key}", "red");
            } elseif ($value == "dir") {
                $this->arParams[$key] = $docRoot . $paramValue;
            } else {
                $this->arParams[$key];
            }
        }

        if ($this->errors)
            throw new Exception($this->errors);
    }

    public function get() {

    }
}