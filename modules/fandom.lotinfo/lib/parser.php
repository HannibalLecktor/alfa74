<?php
/**
 * Created by PhpStorm.
 * User: Khadeev Fanis
 * Date: 06/09/15
 * Time: 21:06
 */

namespace Fandom\Lotinfo;

class Parser
{
    private $arParams = array(
        'TMP_DIR' => 'dir',
        'XML_DIR' => 'dir',
        'XML_FILE' => '',
        'LOG_FILE' => 'dir',
        'DEBUG' => ''
    );
    public $errors = '';
    public $message = '';
    static private $MODULE_NAME = 'fandom.lotinfo';
    private $objType = '';
    private $transactionType = '';

    public function __construct($docRoot, $objType, $transactionType)
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

        $tmpFiles = array_diff(scandir($this->arParams['TMP_DIR']), ['..', '.']);
        if (empty($tmpFiles))
            $this->errors = \Helper::boldColorText("Tmp files is absent", "red");

        if (!$objType || !$transactionType)
            $this->errors = \Helper::boldColorText("Не указан тип недвижимости или тип сделки", "red");
        else {
            $this->objType = $objType;
            $this->transactionType = $transactionType;
        }

        if (!empty($this->errors)) {
            $logFile = $this->arParams['LOG_FILE'];
            if ($logFile == 'dir')
                $logFile = $docRoot . '/local/logs/lot_info';

            file_put_contents($this->errors, $logFile, FILE_APPEND);

            throw new \Exception($this->errors);
        }

        \CModule::IncludeModule("iblock");
    }

    private function addElement($arFields){
        $el = new CIBlockElement;
        if($elID = $el->Add($arFields)){
            $this->message .= "<b style='color: green'>Елемент - <{$arFields['XML_ID']}> успешно добавлен<b><br/>";
        }else{
            $err = "Добавление элемента <{$arFields['XML_ID']}> не удалось((( - {$el->LAST_ERROR}";
            $this->errors = \Helper::boldColorText($err, "red");
        }
    }

    private function getArImage($images){
        $arImage = array();
        foreach ($images as $arImg){
            $img = CFile::MakeFileArray($arImg);
            if($img['type'] != 'application/octet-stream')
                $arImage[] = $img;
        }

        return $arImage;
    }

    private function getEnumIdByFilter($iblock_id, $prop_id, $fIlter)
    {
        $arFIlter = array(
            'IBLOCK_ID' => $iblock_id,
            'PROPERTY_ID' => $prop_id
        );

        $ob = CIBlockPropertyEnum::GetList(array(), array_merge($arFIlter, $fIlter));
        if ($res = $ob->Fetch()) {
            return $res['ID'];
        } elseif ($fIlter['VALUE']) {
            $val = str_replace('%', '', $fIlter['VALUE']);
            $id = $this->addPropEnum($iblock_id, $prop_id, $val);

            if ($id) {
                return $id;
            }
        }

        return false;

    }

    private function addPropEnum($iblock_id, $prop_id, $value)
    {
        $obPropEnum = new CIBlockPropertyEnum();
        $prop_id = $obPropEnum->Add(array('PROPERTY_ID' => $prop_id, 'VALUE' => $value));

        if ($prop_id) {
            return $prop_id;
        } else {
            $this->redError("Не удалось добавить значение: {$value} свойства №{$prop_id} для инфоблока {$iblock_id}");
        }

        return false;
    }

    private function getProps($iblock_id, $arItem, $typeOfTransaction, $lotInfoKey, $new, $arProps)
    {
        $props = '';

        foreach ($this->iblockProps[$iblock_id] as $key=>$arProp) {
            switch ($key) {
                case 'PROP_IMAGES':
                    if ($new)
                        $props[$arProp] = $this->getArImage($arItem[$arProps[$key]]);
                    break;
                case 'PROP_FLOOR':
                case 'PROP_ROOM_COUNT':
                case 'PROP_TYPE_OF_HOME':

                    if ($key == 'PROP_TYPE_OF_HOME') {
                        $arFilter = array(
                            'VALUE' => iconv('utf-8', 'windows-1251', $arItem[$arProps[$key]]).'%'
                        );
                    } else {
                        $arFilter = array(
                            'XML_ID' => $arItem[$arProps[$key]]
                        );
                    }

                    $res = $this->getEnumIdByFilter($iblock_id, $arProp, $arFilter);

                    $props[$arProp] = $res;

                    break;
                case 'PROP_TYPE_OF_TRANSACTION':
                    $props[$arProp] = $this->getEnumIdByFilter(
                        $iblock_id,
                        $arProp,
                        array('XML_ID' => $typeOfTransaction)
                    );
                    break;
                case 'PROP_TYPE_OF_APARTMENT':
                    if ($iblock_id == 30 || $iblock_id == 38) {
                        if ($lotInfoKey == 3) {
                            $xmlID = 'apartment';
                        } elseif ($lotInfoKey == 5) {
                            $xmlID = 'rooms';
                        }
                        $props[$arProp] = $this->getEnumIdByFilter(
                            $iblock_id,
                            $this->iblockProps[$iblock_id][$key],
                            array(
                                'XML_ID' => $xmlID
                            )
                        );
                    }
                    break;
                case 'PROP_INFO':
                    $props[$arProp] = array(
                        "VALUE" => array(
                            'TEXT' => iconv('utf-8', 'windows-1251', $arItem[$arProps[$key]]),
                            "TYPE" => 'html'
                        )
                    );
                    break;
                default:
                    if ($key == 'PROP_NAME_OF_REILTOR' && !empty($arItem[$arProps[$key]]))
                        $value = iconv('utf-8', 'windows-1251', $arItem[$arProps[$key]]);
                    else
                        $value = $arItem[$arProps[$key]];

                    $props[$arProp] = $value;
                    break;
            }
        }

        return $props;
    }

    private function getExistsElements($arResult, $iblockId, $sectionId, $arProps){

        foreach($arResult as $arItem){
            $xml[] = $arItem[$arProps['XML_ID']];
        }

        $arFilter = array(
            'IBLOCK_ID' => $iblockId,
            'SECTION_ID' => $sectionId,
            'XML_ID' => $xml,
        );

        $arSelect = array(
            'IBLOCK_ID',
            'ID',
            'XML_ID',
            'NAME',
        );

        $ob = \CIBlockElement::GetList(array('SORT'=>'ASC'), $arFilter, false, false, $arSelect);
        while ($res = $ob->GetNext()) {
            $result[$res['XML_ID']] = $res;
        }

        return $result;
    }

    private function updateElement($arFields, $id){
        $el = new \CIBlockElement;

        if($el->Update($id, $arFields)){
            $this->message .= "<span style='color: green'>Елемент - <{$arFields['XML_ID']}> успешно Обновлен<span><br/>";
        }else{
            $this->error .= "<b style='color:red'>Не удалось обновить Елемент <{$arFields['XML_ID']}>((( - {$el->LAST_ERROR}</span><br/>";
        }
    }

    private function delFile($itemID, $iblock_id){

        $image_prop = $this->iblockProps[$iblock_id]['PROP_IMAGE'];

        if($image_prop){

            $ob = \CIBlockElement::GetProperty($iblock_id, $itemID, array(), array('CODE' => 'photo', 'EMPTY' => 'N'));

            while($res = $ob->Fetch()){
                $arFile[] = $res['PROPERTY_VALUE_ID'];
            }

            foreach($arFile as $arVal){
                $arValue[$arVal] = array("VALUE" => array("del" => 'Y'));
            }

            \CIBlockElement::SetPropertyValueCode($itemID, $image_prop, $arValue);

        }

    }

    private function deleteItems($items, $typeOfTransaction, $iblockId, $sectionId){

        foreach ($items as $arItem) {
            $unDel[] = $arItem['XML_ID'];
        }

        $arFilter = array(
            'IBLOCK_ID' => $iblockId,
            'SECTION_ID' => $sectionId,
            '!XML_ID' => $unDel,
            'PROPERTY_type_sdelka' => $this->getEnumIdByFilter(
                $iblockId,
                $this->iblockProps[$iblockId]['PROP_TYPE_OF_TRANSACTION'],
                array('XML_ID' => $typeOfTransaction)
            )
        );

        $arSelect = array('ID', 'XML_ID');
        $ob = \CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);

        while ($res = $ob->Fetch()) {
            \CIBlockElement::Delete($res['ID']);
        }


    }

    private function get3D($item_id, $iblock_id){

        $ob = CIBlockElement::GetProperty($iblock_id, $item_id, array(),array('CODE' => 'three_d'));

        if($res = $ob->Fetch()){
            $ar3d['key'] = $res['ID'];
            $ar3d['value'] = $res['VALUE'];
            return $ar3d;
        }else
            return false;
    }

    private function getNewSections(){

        $res = array();

        $arFilter = array(
            'SECTION_ID' => self::NEW_SECTION,
            'IBLOCK_ID' => self::NEW_IBLOCK,
        );

        $obSection = CIBlockSection::GetList(array(), $arFilter, false, array('ID', 'NAME'));

        while($resSec = $obSection->Fetch()){
            $res[$resSec['NAME']] = $resSec['ID'];
        }

        return $res;
    }

    private function addNewSection($sectionName){

        $newSection = new CIBlockSection();
        $sectionCode = CUtil::translit(
            $sectionName,
            'ru',
            array(
                'replace_space' => '-',
                'replace_other' => '-'
            )
        );

        $fields = array(
            'ACTIVE' => 'Y',
            'IBLOCK_SECTION_ID' => self::NEW_SECTION,
            'IBLOCK_ID' => self::NEW_IBLOCK,
            'NAME' => $sectionName,
            'CODE' => $sectionCode,
        );

        $id = $newSection->Add($fields);

        if($id){
            $this->newSections[$sectionName] = $id;
            return $id;
        }else{
            $this->error .= "<b style='color:red'>Добавление раздела новостроек '".$sectionName."' не удалось((( - ".$newSection->LAST_ERROR."</span><br/>";
            return self::NEW_SECTION;
        }
    }

    private function redError($err)
    {
        $this->error .= \Helper::boldColorText($err, 'red');
    }

    private function getSettingsSectionID () {
        $ob = LotinfoTypeToIBlockTable::getList(
            [
                'filter' => [
                    'TRANSACTION' => $this->transactionType,
                    'LOTINFO_TYPE' => $this->objType
                ],
                'select' => [
                    'SECTION_ID'
                ]
            ]
        );

        if ($arSection = $ob->fetch()) {
            return $arSection['SECTION_ID'];
        } else
            return false;
    }

    private function getPropsCompliance ()
    {
        $props = [];
        $ob = LotinfoFieldsToPropsTable::getList(
            [
                'filter' => [
                    '!LOTINFO_FIELD' => false
                ],
                'select' => [
                    'LOTINFO_FIELD',
                    'PROP_ID',
                    'FIELD_ID',
                ]
            ]
        );

        while ($arProps = $ob->fetch()) {
            $value = ($arProps['PROP_ID'])?: $arProps['FIELD_ID'];
            if ($value)
                $props[$arProps['LOTINFO_FIELD']] = $value;

        };

        if (!empty($props))
            return $props;
        else
            return false;
    }

    private function getIblockProps($iblockID) {
        $arProps = [];

        $propsOb = \Bitrix\Iblock\PropertyTable::getList(
            [
                'filter' => [
                    'IBLOCK_ID' => $iblockID,
                    'ACTIVE' => 'Y'
                ],
                'select' => [
                    'NAME',
                    'CODE',
                ]
            ]
        );

        while ($props = $propsOb->fetch()) {
            $arProps[$props['CODE']] = $props['NAME'];
        }
    }

    public function importData()
    {
        $this->message = \Helper::boldColorText(
            'Parsing type: ' . $this->transactionType . ' Object: ' . $this->objType, 'green'
        );
        $file = $this->arParams['TMP_DIR'] . $this->objType . '.json';
        $iblockID = \COption::GetOptionInt(ADMIN_MODULE_NAME, 'IBLOCK_ID');
        $sectionID = $this->getSettingsSectionID();
        $arProps = $this->getPropsCompliance();

        if (!file_exists($file)) {
            $this->errors = \Helper::boldColorText("File {$file} is absent", "red");
            return false;
        }

        if (!$iblockID) {
            $this->errors = \Helper::boldColorText("Не указан ИД инфоблока в настройках модуля", "red");
            return false;
        } else {
            $iblockProps = $this->getIblockProps($iblockID);
        }

        if (!$sectionID) {
            $this->errors = \Helper::boldColorText(
                "Не указан ИД раздела настройках модуля для типа недвижимости: {$this->transactionType},
                Ид типа недвижимости: {$this->objType}", "red"
            );
            return false;
        }

        if (!$arProps) {
            $this->errors = \Helper::boldColorText("Не указаны соответсвия свойств", "red");
            return false;
        } elseif ($arProps['XML_ID']) {
            $this->errors = \Helper::boldColorText("Не указано соответсвие для поле XML_ID", "red");
            return false;
        }

        $json = file_get_contents($file);
        $arResult = json_decode($json, true);
        if ($arResult) {
            $existsElements = $this->getExistsElements($arResult, $iblockID, $sectionID, $arProps);
            if ($this->arParams['DEBUG'] == 'N') {
                $this->deleteItems($existsElements, $this->objType, $iblockID, $sectionID);
            }

            foreach ($arResult as $arItem) {

                $itemXmlID = $arItem[$arProps['XML_ID']];
                $new = !array_key_exists($itemXmlID, $existsElements)?: false;
                $props = $this->getProps(
                    $iblockID,
                    $arItem,
                    $this->objType,
                    $this->transactionType,
                    $new,
                    $arProps
                );
                $city = iconv('utf-8', 'windows-1251', $arItem[$arProps['PROP_CITY']]);
                if (empty($arItem[$arProps['PROP_STREET']])) {
                    $name = $city;
                } else {
                    // Search Street By ID
                    //$name = $this->getStreetName($arItem[$arProps['PROP_STREET']]);
                    $name = iconv('utf-8', 'windows-1251', $arItem[$arProps['PROP_STREET']]);

                    if (!$name) {
                        $this->redError(
                            "Не удалось найти улицу ID: {$arItem[$arProps['PROP_STREET']]},
                                        город: {$city}"
                        );

                        $name = $city;
                    } elseif (!empty($arItem[$arProps['PROP_HOME']])) {
                        $name .= ', '.$arItem[$arProps['PROP_HOME']];
                    }
                }

                $arFields = array(
                    'XML_ID' => $itemXmlID,
                    'NAME' => $name,
                    'IBLOCK_ID' => $iblockID,
                    'IBLOCK_SECTION_ID' => $sectionID,
                    'ACTIVE' => 'Y',
                    'PROPERTY_VALUES' => $props,
                );

                //Helper::pR($arFields);
                //die();

                if(array_key_exists($itemXmlID, $existsElements)){
                    //$this->delFile($existsElements[$itemXmlID]['ID'], $arObject['IBLOCK_ID']);
                    if($three_d = $this->get3D($existsElements[$itemXmlID]['ID'], $iblockID)){
                        $arFields['PROPERTY_VALUES'][$three_d['key']] = $three_d['value'];
                    }
                    $this->updateElement($arFields, $existsElements[$itemXmlID]['ID']);
                }else
                    $this->addElement($arFields);
            }

        } else {
            $this->redError($file.": ".json_last_error_msg().". msgID: ".json_last_error());
        }

        unlink($file);

    }
}