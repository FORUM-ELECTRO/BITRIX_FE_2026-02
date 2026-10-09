<?php

/**
 * Раздел модуля fg.menuz в меню админки.
 *
 * @package FG\Menuz
 */

namespace FG\Menuz;

/**
 * Добавляет модуль в глобальное меню администрирования.
 */
final class AdminMenu
{
    /**
     * Обработчик main:OnBuildGlobalMenu.
     *
     * Создаёт меню «ФЭ» и пункт «Разделы меню».
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
                "text"      => "ФЭ",
                "title"     => "Модули FG",
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
            if (($it["text"] ?? "") === "Разделы меню") {
                return;
            }
        }

        // Дописываем свой подпункт
        $adminMenu["global_menu_fg_menuz"]["items"][] = [
            "text"  => "Разделы меню",
            "title" => "Разделы и пункты левого меню",
            "url" => "/bitrix/admin/fg_menuz.php",
            "icon"  => "settings_menu_icon",
        ];
    }
}