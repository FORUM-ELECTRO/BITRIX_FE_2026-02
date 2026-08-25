<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */

$this->__file = '/bitrix/templates/bitrix24/components/bitrix/menu/left_vertical/template.php';
$this->__folder = '/bitrix/templates/bitrix24/components/bitrix/menu/left_vertical/';
$asset = \Bitrix\Main\Page\Asset::getInstance();
$asset->addJs($this->__folder . '/script.js');
$asset->addCss($this->__folder . '/style.css');
include_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/bitrix24/components/bitrix/menu/left_vertical/result_modifier.php";

if (\Bitrix\Main\Loader::includeModule('bestrank.interfaces')
    && \Bitrix\Main\Config\Option::get('bestrank.interfaces', 'LM_ACTIVE') == 'Y'
) {
    $leftMenuComponent = new \Bestrank\Interfaces\LeftMenu\Component($arResult);
    $arResult = $leftMenuComponent->getArResult();
}