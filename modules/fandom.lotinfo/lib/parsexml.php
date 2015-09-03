<?php
/**
 * Created by PhpStorm.
 * User: Khadeev Fanis
 * Date: 03/09/15
 * Time: 22:57
 */

namespace Fandom\Lotinfo;

class ParseXml
{
    public $error = '';
    private $file = '';
    private $tmpDir = '';

    public function __construct($xml_file, $tmpDir)
    {
        $this->file = $xml_file;
        $this->tmpDir = $tmpDir;
    }

    public function getData()
    {
        $xml_str = file_get_contents($this->file);

        $xml = new \CDataXML();
        $xml->LoadString($xml_str);
        $arXml = $xml->GetArray();
        unset($xml_str);

        return $this->toTmp($arXml);
    }

    private function toTmp($arXml)
    {
        $arResult = self::prepareArray($arXml);

        if ($arResult) {
            foreach ($arResult as $key=>$arType) {
                $jsonData = json_encode($arType);

                if ($jsonData) {
                    $jsonFile = $this->tmpDir."/{$key}.json";
                    file_put_contents($jsonFile, $jsonData);
                } else {
                    $this->error .= \Helper::boldColorText(json_last_error_msg()." msg: ".json_last_error(), "red");
                    return false;
                }
            }

            unset($arResult);

            return true;
        } else {
            return false;
        }

    }

    private function prepareArray($arXml){
        $arResult = false;
        $myArray = array();
        $arData = $arXml;
        foreach($arData['OBJECTS']['#']['OBJECT'] as $data){
            foreach($data['#'] as $tag=>$arTags){
                if ($tag == 'media') {
                    foreach ($arTags[0]['#']['image'] as $arImage) {
                        $myArray['image'][] = $arImage['#'];
                    }
                } elseif ($tag == 'objectType') {
                    foreach ($arTags as $arTag) {
                        $objectType = $arTag['#'];
                    }
                } else {
                    $myArray[$tag] = $arTags[0]['#'];
                }
            }

            unset($data);

            if ($objectType) {
                $arResult[$objectType][] = $myArray;
            }

            $objectType = false;
            $myArray = array();
        }

        unset($arData);
        unset($arXml);

        return $arResult;
    }
}