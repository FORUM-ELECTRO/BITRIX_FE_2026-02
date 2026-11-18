<?php

/**
 * Точка входа модуля fg.chatbuttons.
 *
 * @package Fg\ChatButtons
 */

use Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    return;
}

// PSR-4 автозагрузка классов lib/.
Loader::registerNamespace("Fg\\ChatButtons", __DIR__ . "/lib");
