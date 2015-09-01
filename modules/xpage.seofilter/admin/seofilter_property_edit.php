<?
// подключим все необходимые файлы:
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог

\Bitrix\Main\Loader::includeModule('xpage.seofilter');
\Bitrix\Main\Loader::includeModule('iblock');
CJSCore::init('jquery');

// подключим языковой файл
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
    $arFields = [
        "TITLE"       => $TITLE,
        "SORT"        => $SORT,
        "PROPERTY_ID" => $PROPERTY_ID
    ];

    // сохранение данных
    if($ID > 0) {
        $res = \Xpage\SeoFilter\PropertyTable::update($ID, $arFields);
    } else {
        $res = \Xpage\SeoFilter\PropertyTable::add($arFields);
    }

    if($res->isSuccess()) {
        if(!$ID) {
            $ID = $res->getId();
        }
        if($apply != "") {
            LocalRedirect("/bitrix/admin/seofilter_property_edit.php?ID=" . $ID . "&mess=ok");
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
    $seoProperty = \Xpage\SeoFilter\PropertyTable::getById($ID)->fetch();
    $APPLICATION->SetTitle("Edit {$seoProperty['ID']}");
}

$obProperties = \Bitrix\Iblock\PropertyTable::getList([
    'select'  =>
        [
            'ID',
            'NAME',
        ],
    'filter'  =>
        [
            'IBLOCK_ID'                     => \Bitrix\Main\Config\Option::get('xpage.seofilter', 'IBLOCK', 1),
            'SECTION_PROPERTY.SMART_FILTER' => 'Y',
            'PROPERTY_TYPE'                 => \Bitrix\Iblock\PropertyTable::TYPE_LIST
        ],
    'runtime' =>
        [
            new Bitrix\Main\Entity\ReferenceField(
                'SECTION_PROPERTY',
                'Bitrix\Iblock\SectionProperty',
                ['this.ID' => 'ref.PROPERTY_ID'],
                ['join_type' => 'LEFT']
            )
        ],
]);

while($arProperty = $obProperties->fetch()) {
    $allProperties[] = $arProperty;
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
    [
        "TEXT"  => "Удалить",
        "TITLE" => "Удалить",
        "LINK"  => "javascript:if(confirm('" . "Да, прошу вас" . "')) window.location='/bitrix/admin/seofilter_property.php?ID=" . $ID . "&action=delete&lang=" . LANGUAGE_ID . "&" . bitrix_sessid_get() . "';",
        "ICON"  => "btn_delete"
    ]
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
    <tr>
        <td width="40%"><? echo "Название" ?></td>
        <td width="60%"><input type="text" size="40" name="TITLE"
                               value="<?= ($bVarsFromForm) ? $_POST['TITLE'] : $seoProperty['TITLE'] ?>"></td>
    </tr>
    <tr>
        <td width="40%"><? echo "Сортировка" ?></td>
        <td width="60%"><input type="text" name="SORT"
                               value="<?= ($bVarsFromForm) ? $_POST['SORT'] : $seoProperty['SORT'] ?>"></td>
    </tr>
    <tr>
        <select name="PROPERTY_ID" required="required">
            <? foreach($allProperties as $arProperty): ?>
                <option
                    value="<?= $arProperty['ID'] ?>"<? if($seoProperty['PROPERTY_ID'] == $arProperty['ID']): ?> selected<? endif; ?>>
                    <?= $arProperty['NAME'] ?>
                </option>
            <? endforeach; ?>
        </select>
    </tr>
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