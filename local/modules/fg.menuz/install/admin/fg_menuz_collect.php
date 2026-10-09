<?php

/**
 * Приёмник каталога штатных пунктов меню (модуль fg.menuz).
 *
 * @package FG\Menuz
 */

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use FG\Menuz\Catalog;

// Фразы берём из каталога модуля (см. комментарий в fg_menuz.php)
$fgzSelf = \Bitrix\Main\Loader::getLocal("modules/fg.menuz/install/admin/fg_menuz_collect.php");
Loc::loadMessages($fgzSelf ?: __FILE__);

global $USER;

header("Content-Type: application/json; charset=utf-8");

if (!$USER->IsAdmin()) {
    echo json_encode(["error" => Loc::getMessage("FG_MENUZ_COLLECT_DENIED")]);
    exit;
}

Loader::includeModule("fg.menuz");

$raw  = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(["error" => Loc::getMessage("FG_MENUZ_COLLECT_BAD_PAYLOAD")]);
    exit;
}

// check_bitrix_sessid() принимает имя параметра запроса, а не значение,
// поэтому для JSON-тела sessid сравниваем напрямую.
$sessid = (string)($data["sessid"] ?? "");
if ($sessid !== "" && $sessid !== bitrix_sessid()) {
    echo json_encode(["error" => Loc::getMessage("FG_MENUZ_COLLECT_BAD_SESSID")]);
    exit;
}

// Каталог штатных пунктов
$items = is_array($data["items"] ?? null) ? $data["items"] : [];
$groups = is_array($data["groups"] ?? null) ? $data["groups"] : [];
Catalog::save($items, $groups);
echo json_encode(["ok" => true, "type" => "catalog", "count" => count($items)]);