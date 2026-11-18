<?php

/**
 * Пункт настроек модуля в меню администрирования.
 *
 * @package Fg\ChatButtons
 */

namespace Fg\ChatButtons\Handler;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Добавляет страницу настроек в раздел FG общего меню админки.
 */
final class AdminMenu
{
    /** @var string id родительского раздела FG в глобальном меню. */
    private const PARENT = "global_menu_fg_menuz";

    /** @var int Порядок раздела FG относительно остальных пунктов. */
    private const PARENT_SORT = 510;

    /**
     * Обработчик main:OnBuildGlobalMenu.
     *
     * Родительский раздел создаётся, только если его не добавил другой модуль FG.
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

        if (!isset($adminMenu[self::PARENT])) {
            $adminMenu[self::PARENT] = [
                "menu_id"   => "fg_menuz",
                "text"      => Loc::getMessage("FG_CB_ADMIN_MENU_TEXT"),
                "title"     => Loc::getMessage("FG_CB_ADMIN_MENU_TITLE"),
                "url"       => null,
                "sort"      => self::PARENT_SORT,
                "items_id"  => self::PARENT,
                "icon"      => "util_menu_icon",
                "page_icon" => "util_page_icon",
                "items"     => [],
            ];
        }

        $item = [
            "text"  => Loc::getMessage("FG_CB_ADMIN_ITEM_TEXT"),
            "title" => Loc::getMessage("FG_CB_ADMIN_ITEM_TITLE"),
            "url"   => "/bitrix/admin/fg_chatbuttons.php",
            "icon"  => "settings_menu_icon",
        ];

        foreach ($adminMenu[self::PARENT]["items"] as $existing) {
            if (($existing["url"] ?? "") === $item["url"]) {
                return;
            }
        }

        $adminMenu[self::PARENT]["items"][] = $item;
    }
}
