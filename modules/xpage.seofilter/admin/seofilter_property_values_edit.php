<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('xpage.seofilter');
\Bitrix\Main\Loader::includeModule('iblock');
CJSCore::init('jquery');

IncludeModuleLangFile(__FILE__);

$aTabs = [
    ["DIV" => "edit1", "TAB" => "Основное", "ICON" => "main_user_edit", "TITLE" => "Настройки"],
];
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);        // идентификатор редактируемой записи
$message = null;        // сообщение об ошибке
$bVarsFromForm = false; // флаг "Данные получены с формы", обозначающий, что выводимые данные получены с формы, а не из БД.

// ******************************************************************** //
//                ОБРАБОТКА ИЗМЕНЕНИЙ ФОРМЫ                             //
// ******************************************************************** //

if(
    $REQUEST_METHOD == "POST" // проверка метода вызова страницы
    &&
    ($save != "" || $apply != "") // проверка нажатия кнопок "Сохранить" и "Применить"
    &&
    check_bitrix_sessid()     // проверка идентификатора сессии
) {
    // обработка данных формы

    // сохранение данных
    if($ID > 0) {
        $res = \Xpage\SeoFilter\PropertyValueAltTable::updateValues($PROP_ENUM);
    }

    if($res->isSuccess()) {
        if(!$ID) {
            $ID = $res->getId();
        }
        if($apply != "") {
            LocalRedirect("/bitrix/admin/seofilter_property_values_edit.php?ID=" . $ID . "&mess=ok");
        } else {
            LocalRedirect("/bitrix/admin/seofilter_property.php");
        }
    } else {
        foreach($res->getErrorMessages() as $error_message) {
            $message = new CAdminMessage($error_message);
            break;
        }

        $bVarsFromForm = true;
    }
}

// ******************************************************************** //
//                ВЫБОРКА И ПОДГОТОВКА ДАННЫХ ФОРМЫ                     //
// ******************************************************************** //

$allProperties = [];

// выборка данных
if($ID > 0) {
    $obEnumValues = \Bitrix\Iblock\PropertyEnumerationTable::getList([
        'select'  =>
            [
                'ID',
                'VALUE',
                'VALUE_ALT'     => 'VALUE_ALT_TABLE.VALUE',
                'PROPERTY_NAME' => 'PROPERTY.NAME'
            ],
        'filter'  =>
            [
                'PROPERTY_ID' => $ID
            ],
        'order' =>
            [
                'VALUE' => 'ASC'
            ],
        'runtime' =>
            [
                new \Bitrix\Main\Entity\ReferenceField(
                    'VALUE_ALT_TABLE',
                    '\\Xpage\\Seofilter\\PropertyValueAlt',
                    ['this.ID' => 'ref.ENUM_ID']
                ),
            ]
    ]);
    $arEnumValues = $obEnumValues->fetchAll();
    $APPLICATION->SetTitle("Замена значений для свойства \"{$arEnumValues[0]['PROPERTY_NAME']}\"");
}

// ******************************************************************** //
//                ВЫВОД ФОРМЫ                                           //
// ******************************************************************** //

// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

// конфигурация административного меню
$aMenu = [
    [
        "TEXT"  => "Список",
        "TITLE" => "Список",
        "LINK"  => "seofilter_property.php",
        "ICON"  => "btn_list",
    ],
];

// создание экземпляра класса административного меню
$context = new CAdminContextMenu($aMenu);

// вывод административного меню
$context->Show();
?>

<?
// если есть сообщения об ошибках или об успешном сохранении - выведем их.
if($_REQUEST["mess"] == "ok" && $ID > 0) {
    CAdminMessage::ShowMessage(["MESSAGE" => 'Success', "TYPE" => "OK"]);
}

if($message) {
    echo $message->Show();
}
?>
<form method="POST" action="<?= $APPLICATION->GetCurPage() ?>" name="property_group_form" id="property_group_form">
<? // проверка идентификатора сессии ?>
<? echo bitrix_sessid_post(); ?>
<?
// отобразим заголовки закладок
$tabControl->Begin();
?>
<?
//********************
// первая закладка - форма редактирования параметров рассылки
//********************
$tabControl->BeginNextTab();
?>
<? foreach($arEnumValues as $arEnum): ?>
    <tr>
        <td width="40%">
            <?= $arEnum['VALUE'] ?>
        </td>
        <td width="60%">
            <input type="text" size="40" name="PROP_ENUM[<?= $arEnum['ID'] ?>]"
                   value="<?= ($bVarsFromForm) ? $_POST['PROP_ENUM'][$arEnum['ID']] : $arEnum['VALUE_ALT'] ?>">
        </td>
    </tr>
<? endforeach; ?>
<?
// завершение формы - вывод кнопок сохранения изменений
$tabControl->Buttons(
    [
        "back_url" => "seofilter_property.php",
    ]
);
?>

<? if($ID > 0 && !$bCopy): ?>
    <input type="hidden" name="ID" value="<?= $ID ?>">
<? endif; ?>
<?
// завершаем интерфейс закладок
$tabControl->End();
?>

<?
// дополнительное уведомление об ошибках - вывод иконки около поля, в котором возникла ошибка
$tabControl->ShowWarnings("post_form", $message);
?>

<?
// завершение страницы
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>