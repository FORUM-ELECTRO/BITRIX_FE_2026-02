<?php

/**
 * Раздел модуля fg.menuz в меню админки.
 *
 * @package FG\Menuz
 */

namespace FG\Menuz;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Добавляет модуль в глобальное меню администрирования.
 */
final class AdminMenu
{
    /**
     * Обработчик main:OnBuildGlobalMenu.
     *
     * @param array &$adminMenu  Глобальное меню администрирования.
     * @param array &$moduleMenu Меню модулей.
     *
     * @return void
     */
    public static function onBuildGlobalMenu(&$adminMenu, &$moduleMenu): void
    {
        global $USER;

        if (!is_object($USER) || !$USER->IsAdmin()) {
            return;
        }
        if (!is_array($adminMenu)) {
            return;
        }

        // Создаём родителя, если его ещё нет (порядок вызова модулей не гарантирован)
        if (!isset($adminMenu["global_menu_fg_menuz"])) {
            $adminMenu["global_menu_fg_menuz"] = [
                "menu_id"   => "fg_menuz",
                "text"      => Loc::getMessage("FG_MENUZ_ADMIN_MENU_TEXT"),
                "title"     => Loc::getMessage("FG_MENUZ_ADMIN_MENU_TITLE"),
                "url"       => null,
                "sort"      => 510,
                "items_id"  => "global_menu_fg_menuz",
                "icon"      => "util_menu_icon",
                "page_icon" => "util_page_icon",
                "items"     => [],
            ];
        }

        // Защита от дубля
        foreach ($adminMenu["global_menu_fg_menuz"]["items"] as $it) {
            if (($it["text"] ?? "") === Loc::getMessage("FG_MENUZ_ADMIN_MENU_ITEM")) {
                return;
            }
        }

        // Дописываем свой подпункт
        $adminMenu["global_menu_fg_menuz"]["items"][] = [
            "text"  => Loc::getMessage("FG_MENUZ_ADMIN_MENU_ITEM"),
            "title" => Loc::getMessage("FG_MENUZ_ADMIN_MENU_ITEM_TITLE"),
            "url"   => "/bitrix/admin/fg_menuz.php",
            "icon"  => "settings_menu_icon",
        ];
    }
}