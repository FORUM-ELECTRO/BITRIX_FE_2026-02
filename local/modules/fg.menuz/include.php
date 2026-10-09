<?php

/**
 * Точка входа модуля fg.menuz.
 *
 * @package FG\Menuz
 */

use Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) return;

// Автозагрузка классов библиотеки
Loader::registerAutoLoadClasses("fg.menuz", [
    "FG\\Menuz\\Settings" => "lib/Settings.php",
    "FG\\Menuz\\Sections" => "lib/Sections.php",
    "FG\\Menuz\\Renderer" => "lib/Renderer.php",
    "FG\\Menuz\\AdminMenu" => "lib/AdminMenu.php",
    "FG\\Menuz\\Catalog" => "lib/Catalog.php",
]);

// Построение левого меню на публичной части
AddEventHandler("main", "OnProlog", ["\\FG\\Menuz\\Renderer", "onProlog"]);