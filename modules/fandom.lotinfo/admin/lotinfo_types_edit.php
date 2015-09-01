<?
define('ADMIN_MODULE_NAME', 'fandom.lotinfo');
// подключим все необходимые файлы:
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог

use Bitrix\Main\Localization\Loc;
use Fandom\Lotinfo\LotinfoTypeToIBlockTable;

\Bitrix\Main\Loader::includeModule('fandom.lotinfo');
\Bitrix\Main\Loader::includeModule('iblock');
CJSCore::init('jquery');

// подключим языковой файл
Loc::loadMessages(__FILE__);

$aTabs = [
    ["DIV" => "edit1", "TAB" => "Основное", "ICON" => "main_user_edit", "TITLE" => "Настройки"],
];
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);        // идентификатор редактируемой записи
$message = null;        // сообщение об ошибке
$bVarsFromForm = false; // флаг "Данные получены с формы", обозначающий, что выводимые данные получены с формы, а не из БД.

$iblockID = COption::GetOptionInt(ADMIN_MODULE_NAME, 'IBLOCK_ID');

if ($iblockID) {

    $arSections = [];
    $arTransaction = LotinfoTypeToIBlockTable::$TRANSACTIONS;

    $sectionOb = \Bitrix\Iblock\SectionTable::getList(
        [
            'filter' => [
                'IBLOCK_ID' => $iblockID,
                'ACTIVE' => 'Y'
            ],
            'select' => [
                'NAME',
                'CODE',
                'ID'
            ]
        ]
    );

    while ($section = $sectionOb->fetch()) {
        $arSections[$section['ID']] = $section['NAME'];
    }



// ******************************************************************** //
//                ОБРАБОТКА ИЗМЕНЕНИЙ ФОРМЫ                             //
// ******************************************************************** //

    if ($arSections) {

        if(
            $REQUEST_METHOD == "POST" // проверка метода вызова страницы
            &&
            ($save != "" || $apply != "") // проверка нажатия кнопок "Сохранить" и "Применить"
            &&
            check_bitrix_sessid()     // проверка идентификатора сессии
        ) {
            // обработка данных формы
            $arFields = [
                "TRANSACTION" => $TRANSACTION,
                "LOTINFO_TYPE"       => $LOTINFO_TYPE,
                "SECTION_ID"       => $SECTION_ID,
            ];

            // сохранение данных

            if($ID > 0) {
                $res = LotinfoTypeToIBlockTable::update($ID, $arFields);
            } else {
                $res = LotinfoTypeToIBlockTable::add($arFields);
            }

            if($res->isSuccess()) {
                if(!$ID) {
                    $ID = $res->getId();
                }
                if($apply != "") {
                    LocalRedirect($_SERVER['SCRIPT_NAME'] . "?ID=" . $ID . "&mess=ok");
                } else {
                    LocalRedirect(str_replace("_edit", "", $_SERVER['SCRIPT_NAME']));
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
            $arPage = LotinfoTypeToIBlockTable::getById($ID)->fetch();
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
                "LINK"  => "lotinfo_types.php",
                "ICON"  => "btn_list",
            ],
            [
                "TEXT"  => "Удалить",
                "TITLE" => "Удалить",
                "LINK"  => "javascript:if(confirm('" . "Да, прошу вас" . "')) window.location=" .
                    $_SERVER['SCRIPT_NAME'] . "'?ID=" . $ID . "&action=delete&lang=" .
                    LANGUAGE_ID . "&" . bitrix_sessid_get() . "';",
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

        $sectionSelect = "<select name='SECTION_ID'>";
        foreach ($arSections as $key=>$section) {
            $selected = '';

            if ($_POST['SECTION_ID'] == $key || $arPage['SECTION_ID'] == $key)
                $selected = 'selected';

            $sectionSelect .= "<option value='{$key}' {$selected}>" . $section . "</option>";
        }
        $sectionSelect .= "</select>";

        $transactionSelect = "<select name='TRANSACTION'>";
        foreach ($arTransaction as $transaction) {
            $selected = '';

            if ($_POST['TRANSACTION'] == $transaction || $arPage['TRANSACTION'] == $transaction)
                $selected = 'selected';

            $transactionSelect .= "<option value='{$transaction}' {$selected}>" . $transaction . "</option>";
        }
        $transactionSelect .= "</select>";
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
            <td width="40%">TRANSACTION</td>
            <td width="60%">
                <?=$transactionSelect;?>
            </td>
        </tr>
        <tr>
            <td width="40%">LOTINFO_TYPE</td>
            <td width="60%">
                <input size="50" type="text" name="LOTINFO_TYPE" value="<?= ($bVarsFromForm) ? $_POST['LOTINFO_TYPE'] : $arPage['LOTINFO_TYPE'] ?>" />
            </td>
        </tr>
        <tr>
            <td width="40%">SECTION_ID</td>
            <td width="60%">
                <?=$sectionSelect;?>
            </td>
        </tr>
        <?
// завершение формы - вывод кнопок сохранения изменений
        $tabControl->Buttons(
            [
                "back_url" => "lotinfo_types.php",
            ]
        );
        ?>

        <? if($ID > 0 && !$bCopy): ?>
            <input type="hidden" name="ID" value="<?= $ID ?>">
        <? endif; ?>
        <?
        // завершаем интерфейс закладок
        $tabControl->End();

// дополнительное уведомление об ошибках - вывод иконки около поля, в котором возникла ошибка
        $tabControl->ShowWarnings("post_form", $message);
    } else {
        echo Loc::getMessage("NEED_IBLOCK_PROPS");
    }
} else {
    echo Loc::getMessage("NEED_IBLOCK_ID");
}
// завершение страницы
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");