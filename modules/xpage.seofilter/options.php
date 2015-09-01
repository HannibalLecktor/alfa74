<?$module_id = "xpage.seofilter";

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $module_id . "/include.php");
IncludeModuleLangFile(__FILE__);
\Bitrix\Main\Loader::includeModule('iblock');

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

$arAllOptions = [
    ["IBLOCK", "Инфоблок", "", ["selectbox", $arIblocks]],
    ["SEO_TITLE", "Шаблон заголовка окна браузера", "", ["textarea", 5, 75]],
    ["SEO_H1", "Шаблон заголовка страницы", "", ["textarea", 5, 75]],
    ["SEO_KEYWORDS", "Шаблон мета keywords", "", ["textarea", 5, 75]],
    ["SEO_DESCRIPTION", "Шаблон мета description", "", ["textarea", 5, 75]],
];

$aTabs = [
    [
        "DIV"   => "edit1",
        "TAB"   => "Настроечки",
        "ICON"  => "",
        "TITLE" => "Сеофильтр!"
    ],
];
$tabControl = new CAdminTabControl("tabControl", $aTabs);?>

<?
if(($REQUEST_METHOD == "POST") && (strlen($Update . $Apply) > 0) && check_bitrix_sessid()) {
    foreach($arAllOptions as $option) {
        if(!is_array($option)) continue;
        $name = $option[0];
        $val = ${$name};
        COption::SetOptionString($module_id, $name, $val, $option[1]);
    }
    $Update = $Update . $Apply;
    ob_start();
    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php");
    ob_end_clean();
    LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . urlencode($mid) . "&lang=" . urlencode(LANGUAGE_ID) . "&" . $tabControl->ActiveTabParam());
}
?>

<? $tabControl->Begin(); ?>
    <form method="POST"
          action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($mid) ?>&amp;lang=<?= LANGUAGE_ID ?>">
        <? $tabControl->BeginNextTab(); ?>
        <? __AdmSettingsDrawList("xpage.seofilter", $arAllOptions); ?>
        <? $tabControl->Buttons(); ?>
        <input type="submit" name="Update" value="Сохранить">
        <input type="submit" name="Apply" value="Применить">
        <?= bitrix_sessid_post(); ?>
    </form>
<? $tabControl->End(); ?>