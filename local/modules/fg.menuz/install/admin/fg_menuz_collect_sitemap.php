<?php

/**
 * Приёмник ссылок, собранных со страницы карты сайта (модуль fg.menuz).
 *
 * @package FG\Menuz
 */

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Loader;

global $USER;

header('Content-Type: application/json; charset=utf-8');

// Ответ отдаётся в JSON; поле "err" различает причины отказа.
if (!Loader::includeModule("fg.menuz")) {
    echo json_encode(["ok" => false, "err" => "module"]);
    exit;
}

if (!$USER->IsAdmin()) {
    echo json_encode(["ok" => false, "err" => "auth"]);
    exit;
}

// sessid передаётся в query, поэтому check_bitrix_sessid() достаточен.
if (!check_bitrix_sessid()) {
    echo json_encode(["ok" => false, "err" => "sessid"]);
    exit;
}

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!is_array($data) || !isset($data["items"]) || !is_array($data["items"])) {
    echo json_encode(["ok" => false, "err" => "no_items"]);
    exit;
}

$added = \FG\Menuz\Catalog::saveExtra($data["items"], "sitemap");

echo json_encode([
    "ok"    => true,
    "added" => $added,
    "got"   => count($data["items"]),
]);