<?php
define('ADMIN_MODULE_NAME', 'fandom.lotinfo');

use Bitrix\Main\Localization\Loc;
use Fandom\Lotinfo\LotinfoTypeToIBlockTable;

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';

if(!\Bitrix\Main\Loader::includeModule("fandom.lotinfo"))
    die('module not included!');
Loc::loadMessages(__FILE__);

$listTableId = "tbl_lotinfo_list";
$adminList = new CAdminList($listTableId);

if(($arID = $adminList->GroupAction())) {
    if($_REQUEST['action_target'] == 'selected') {
        $arID = [];
        $rsData = LotinfoTypeToIBlockTable::getList([
            "select" => [
                "ID"
            ],
        ]);

        while($arRes = $rsData->fetch()) {
            $arID[] = $arRes['ID'];
        }
    }

    foreach($arID as $ID) {
        $ID = intval($ID);
        if($ID <= 0) {
            continue;
        }

        switch($_REQUEST['action']) {
            case "delete":
                LotinfoTypeToIBlockTable::delete($ID);
                break;
        }
    }
}

$obGroups = LotinfoTypeToIBlockTable::getList(
    [
        'order'  =>
            [
                'ID' => 'ASC'
            ],
    ]
);

$obGroups = new CAdminResult($obGroups, $listTableId);
$obGroups->NavStart();

$adminList->NavText($obGroups->GetNavPrint("Разделы"));

$colHeaders = [
    [
        "id"      => 'ID',
        "content" => 'ID',
        "sort"    => 1,
        "default" => true
    ],
    [
        "id"      => 'TRANSACTION',
        "content" => 'TRANSACTION',
        "sort"    => 2,
        "default" => true
    ],
    [
        "id"      => 'LOTINFO_TYPE',
        "content" => 'LOTINFO_TYPE',
        "sort"    => 3,
        "default" => true
    ],
    [
        "id"      => 'SECTION_ID',
        "content" => 'SECTION_ID',
        "sort"    => 3,
        "default" => true
    ],
];

$adminList->AddHeaders($colHeaders);

$visibleHeaderColumns = $adminList->GetVisibleHeaderColumns();
$arUsersCache = [];

while($arRes = $obGroups->GetNext()) {
    $row =& $adminList->AddRow($arRes["ID"], $arRes);
    $arActions = [
        [
            "ICON"   => "delete",
            "TEXT"   => "Удалить",
            "ACTION" => $adminList->ActionDoGroup($arRes["ID"], "delete"),
        ],
        [
            "ICON"    => "edit",
            "TEXT"    => "Редактировать",
            "ACTION"  => $adminList->ActionRedirect("lotinfo_types_edit.php?ID=" . $arRes["ID"] . "&lang=" . LANGUAGE_ID),
            "DEFAULT" => true,
        ]
    ];

    $row->AddActions($arActions);
}

$adminList->AddFooter(
    [
        [
            "title" => "Всего",
            "value" => $obGroups->SelectedRowsCount()
        ],
        [
            "counter" => true,
            "title"   => "Отмечено",
            "value"   => "0"
        ],
    ]
);
$adminList->AddGroupActionTable(["delete" => "Удалить"]);
$aContext = [
    [
        "TEXT"  => GetMessage("MAIN_ADD"),
        "LINK"  => "lotinfo_types_edit.php",
        "TITLE" => GetMessage("POST_ADD_TITLE"),
        "ICON"  => "btn_new",
    ],
];
$adminList->AddAdminContextMenu($aContext);
$adminList->CheckListMode();

$APPLICATION->SetTitle(loc::getMessage("PAGE_TITLE"));

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php';

$adminList->DisplayList();

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php';
