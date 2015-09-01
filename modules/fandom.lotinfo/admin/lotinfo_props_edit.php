<?
define('ADMIN_MODULE_NAME', 'fandom.lotinfo');
// подключим все необходимые файлы:
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог

use Bitrix\Main\Localization\Loc;
use Fandom\Lotinfo\LotinfoFieldsToPropsTable;

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

// ******************************************************************** //
//                ОБРАБОТКА ИЗМЕНЕНИЙ ФОРМЫ                             //
// ******************************************************************** //

    if ($arProps) {

        if(
            $REQUEST_METHOD == "POST" // проверка метода вызова страницы
            &&
            ($save != "" || $apply != "") // проверка нажатия кнопок "Сохранить" и "Применить"
            &&
            check_bitrix_sessid()     // проверка идентификатора сессии
        ) {
            // обработка данных формы
            $arFields = [
                "LOTINFO_FIELD" => $LOTINFO_FIELD,
                "PROP_ID"       => $PROP_ID,
                "FIELD_ID"       => $FIELD_ID,
            ];

            // сохранение данных
            if($ID > 0) {
                $res = LotinfoFieldsToPropsTable::update($ID, $arFields);
            } else {
                $res = LotinfoFieldsToPropsTable::add($arFields);
            }

            if($res->isSuccess()) {
                if(!$ID) {
                    $ID = $res->getId();
                }
                if($apply != "") {
                    LocalRedirect($_SERVER['SCRIPT_NAME'] . "?ID=" . $ID . "&mess=ok");
                } else {
                    LocalRedirect($_SERVER['SCRIPT_NAME']);
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
            $arPage = LotinfoFieldsToPropsTable::getById($ID)->fetch();
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
                "LINK"  => "lotinfo_props.php",
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

        $propsSelect = "<select name='PROP_ID'>";
        $propsSelect .= "<option value='' ></option>";
        foreach ($arProps as $key=>$prop) {
            $selected = '';

            if ($_POST['PROP_ID'] == $key || $arPage['PROP_ID'] == $key) {
                $selected = 'selected';
            }

            $propsSelect .= "<option value='{$key}' {$selected} >" . $prop . "</option>";
        }
        $propsSelect .= "</select>";
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
            <td width="40%">LOTINFO_FIELD</td>
            <td width="60%">
                <input size="50" type="text" name="LOTINFO_FIELD" value="<?= ($bVarsFromForm) ? $_POST['LOTINFO_FIELD'] : $arPage['LOTINFO_FIELD'] ?>" />
            </td>
        </tr>
        <tr>
            <td width="40%">PROP_ID</td>
            <td width="60%">
                <?=$propsSelect;?>
            </td>
        </tr>
        <tr>
            <td width="40%">FIELD_ID</td>
            <td width="60%">
                <input size="50" type="text" name="FIELD_ID" value="<?= ($bVarsFromForm) ? $_POST['FIELD_ID'] : $arPage['FIELD_ID'] ?>" />
            </td>
        </tr>
        <?
// завершение формы - вывод кнопок сохранения изменений
        $tabControl->Buttons(
            [
                "back_url" => "lotinfo_props.php",
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