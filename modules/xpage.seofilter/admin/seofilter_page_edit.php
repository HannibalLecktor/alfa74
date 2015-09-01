<?
// подключим все необходимые файлы:
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог

\Bitrix\Main\Loader::includeModule('xpage.seofilter');
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
        "URL"              => $URL,
        "PAGE_TITLE"       => $PAGE_TITLE,
        "META_KEYWORDS"    => $META_KEYWORDS,
        "META_DESCRIPTION" => $META_DESCRIPTION
    ];

    // сохранение данных
    if($ID > 0) {
        $res = \Xpage\SeoFilter\PageTable::update($ID, $arFields);
    } else {
        $res = \Xpage\SeoFilter\PageTable::add($arFields);
    }

    if($res->isSuccess()) {
        if(!$ID) {
            $ID = $res->getId();
        }
        if($apply != "") {
            LocalRedirect("/bitrix/admin/seofilter_page_edit.php?ID=" . $ID . "&mess=ok");
        } else {
            LocalRedirect("/bitrix/admin/seofilter_page.php");
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

// выборка данных
if($ID > 0) {
    $arPage = \Xpage\SeoFilter\PageTable::getById($ID)->fetch();
    $APPLICATION->SetTitle("Edit {$arPage['URL']}");
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
        "LINK"  => "seofilter_page.php",
        "ICON"  => "btn_list",
    ],
    [
        "TEXT"  => "Удалить",
        "TITLE" => "Удалить",
        "LINK"  => "javascript:if(confirm('" . "Да, прошу вас" . "')) window.location='/bitrix/admin/seofilter_page.php?ID=" . $ID . "&action=delete&lang=" . LANGUAGE_ID . "&" . bitrix_sessid_get() . "';",
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
        <td width="40%">URL</td>
        <td width="60%">
            <input size="50" type="text" name="URL" value="<?= ($bVarsFromForm) ? $_POST['URL'] : $arPage['URL'] ?>" />
        </td>
    </tr>
    <tr>
        <td width="40%">Заголовок страницы</td>
        <td width="60%">
            <textarea cols="50" rows="3" name="PAGE_TITLE">
                <?= ($bVarsFromForm) ? $_POST['PAGE_TITLE'] : $arPage['PAGE_TITLE'] ?>
            </textarea>
        </td>
    </tr>
    <tr>
        <td width="40%">Ключевые слова</td>
        <td width="60%">
            <textarea cols="50" rows="3" name="META_KEYWORDS">
                <?= ($bVarsFromForm) ? $_POST['META_KEYWORDS'] : $arPage['META_KEYWORDS'] ?>
            </textarea>
        </td>
    </tr>
    <tr>
        <td width="40%">Описание</td>
        <td width="60%">
            <textarea cols="50" rows="3" name="META_DESCRIPTION">
                <?= ($bVarsFromForm) ? $_POST['META_DESCRIPTION'] : $arPage['META_DESCRIPTION'] ?>
            </textarea>
        </td>
    </tr>
<?
// завершение формы - вывод кнопок сохранения изменений
$tabControl->Buttons(
    [
        "back_url" => "seofilter_page.php",
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