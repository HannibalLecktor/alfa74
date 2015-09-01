<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

if(!\Bitrix\Main\Loader::includeModule("xpage.seofilter")) die('module not included!');
IncludeModuleLangFile(__FILE__);

$listTableId = "tbl_seofilter_page";
$adminList = new CAdminList($listTableId);

if(($arID = $adminList->GroupAction())) {
    if($_REQUEST['action_target'] == 'selected') {
        $arID = [];
        $rsData = \Xpage\Seofilter\PageTable::getList([
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
                \Xpage\Seofilter\PageTable::delete($ID);
                break;
        }
    }
}

$obGroups = \Xpage\Seofilter\PageTable::getList(
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
        "id"      => 'URL',
        "content" => 'URL',
        "sort"    => 2,
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
            "ACTION"  => $adminList->ActionRedirect("seofilter_page_edit.php?ID=" . $arRes["ID"] . "&lang=" . LANGUAGE_ID),
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
        "LINK"  => "seofilter_page_edit.php",
        "TITLE" => GetMessage("POST_ADD_TITLE"),
        "ICON"  => "btn_new",
    ],
];
$adminList->AddAdminContextMenu($aContext);
$adminList->CheckListMode();

$APPLICATION->SetTitle("Свойства сеофильтра");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
$adminList->DisplayList();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>