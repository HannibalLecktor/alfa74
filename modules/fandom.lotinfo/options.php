<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', 'fandom.lotinfo');

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\String;

if (!$USER->isAdmin()) {
    $APPLICATION->authForm('Nope');
}

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

Loc::loadMessages($context->getServer()->getDocumentRoot()."/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);
\Bitrix\Main\Loader::includeModule('iblock');

function ShowParamsHTMLByArray($arParams)
{
    foreach($arParams as $Option)
    {
        __AdmSettingsDrawRow(ADMIN_MODULE_NAME, $Option);
    }
}

/*
 *
 * VALUES
 *
 * */
$debug = [
    'N',
    'Y'
];

$obIblocks = \Bitrix\Iblock\IblockTable::getList([
    'filter' =>
        [
            'ACTIVE' => 'Y'
        ],
    'select' =>
        [
            'ID',
            'NAME'
        ]
]);

$arIblocks = [];
while($arIblock = $obIblocks->fetch()) {
    $arIblocks[$arIblock['ID']] = $arIblock['NAME'];
}

/**/

$tabControl = new CAdminTabControl("tabControl", [
    [
        "DIV" => "edit1",
        "TAB" => Loc::getMessage("MAIN_TAB_SET"),
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET"),
    ],
    [
        "DIV" => "edit2",
        "TAB" => Loc::getMessage("TAB_TWO"),
        "ICON" => "",
        "TITLE" => Loc::getMessage("TAB_TWO_TITLE")
    ]
]);

$arAllOptions = [
    "settings" => [
        ["DEBUG", Loc::getMessage("DEBUG"), "N", ["selectbox", $debug]],
        ["TMP_DIR", Loc::getMessage("TMP_DIR"), "/upload/tmp_xml", ["text"]],
        ["XML_FILE", Loc::getMessage("XML_FILE"), "lot_info_%ID.xml", ["text"]],
        ["XML_DIR", Loc::getMessage("XML_DIR"), "", ["text"]],
        ["LOG_FILE", Loc::getMessage("LOG_FILE"), "/local/logs/lot_info", ["text"]],
        ["API_URL", Loc::getMessage("API_URL"), "", ["text"]],
        ["API_KEY", Loc::getMessage("API_KEY"), "", ["text"]],
        ["API_CMD", Loc::getMessage("API_CMD"), "", ["text"]],
        ["GET_PARAMS", Loc::getMessage("GET_PARAMS"), "", ["text"]],
        ["IBLOCK_ID", Loc::getMessage("IBLOCK_ID"), "N", ["selectbox", $arIblocks]],
        ["SITIES_ID", Loc::getMessage("SITIES_ID"), "N", ["selectbox", $arIblocks]]
    ]
];

if($REQUEST_METHOD=="POST" && strlen($Update.$Apply.$RestoreDefaults)>0 && check_bitrix_sessid())
{
    if(strlen($RestoreDefaults)>0)
    {
        COption::RemoveOption("iblock");
    }
    else
    {
        foreach($arAllOptions as $arOption)
        {
            $name=$arOption[0];
            $val=$_REQUEST[$name];
            if($arOption[2][0]=="checkbox" && $val!="Y")
                $val="N";
            COption::SetOptionString(ADMIN_MODULE_NAME, $name, $val, $arOption[1]);
        }
    }
    if(strlen($Update)>0 && strlen($_REQUEST["back_url_settings"])>0)
        LocalRedirect($_REQUEST["back_url_settings"]);
    else
        LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
}

$tabControl->begin();
?>
    <form method="post" action="<?=sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID)?>">
        <? $tabControl->BeginNextTab(); ?>
        <? ShowParamsHTMLByArray($arAllOptions["settings"]); ?>
        <? $tabControl->Buttons(); ?>
        <input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
        <input type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
        <?if(strlen($_REQUEST["back_url_settings"])>0):?>
            <input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
            <input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
        <?endif?>
        <input type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
        <?=bitrix_sessid_post();?>
    </form>
<?php
$tabControl->end();
?>
