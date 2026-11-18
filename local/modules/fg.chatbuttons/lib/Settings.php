<?php

/**
 * Хранилище настроек модуля fg.chatbuttons.
 *
 * @package Fg\ChatButtons
 */

namespace Fg\ChatButtons;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

/**
 * Глобальный переключатель и набор кнопок мессенджера.
 */
final class Settings
{
    /** @var string Идентификатор модуля. */
    public const MODULE = "fg.chatbuttons";

    /** @var string Ключ Option глобальной активности модуля. */
    private const OPT_ACTIVE = "ACTIVE";

    /** @var string Суффикс Option-ключа флага типа. */
    private const SUFFIX_ACTIVE = "_ACTIVE";

    /** @var string Суффикс Option-ключа иконки типа. */
    private const SUFFIX_ICON = "_ICON";

    /**
     * Каталог кнопок, которые можно показать в навигации мессенджера.
     *
     * @var array<string, array{label: string, icon: string, default: string}>
     */
    private const TYPES = [
        "tasks" => [
            "label"   => "FG_CB_TYPE_TASKS",
            "icon"    => "--o-task",
            "default" => "Y",
        ],
        "projects" => [
            "label"   => "FG_CB_TYPE_PROJECTS",
            "icon"    => "--o-collaboration",
            "default" => "Y",
        ],
        "chats" => [
            "label"   => "FG_CB_TYPE_CHATS",
            "icon"    => "--o-chats",
            "default" => "N",
        ],
        "channels" => [
            "label"   => "FG_CB_TYPE_CHANNELS",
            "icon"    => "--o-speaker",
            "default" => "N",
        ],
    ];

    /**
     * Разрешённые для выбора CSS-классы иконок.
     *
     * @var array<int, string>
     */
    private const ICONS = [
        "--o-task",
        "--o-important-task",
        "--o-complete-task-list",
        "--o-collaboration",
        "--o-collab",
        "--o-group",
        "--o-open-channels",
        "--o-speaker",
        "--o-chats",
        "--o-chat-list",
        "--o-recent-chats",
        "--o-notification",
    ];

    /**
     * Включён ли модуль целиком.
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return Option::get(self::MODULE, self::OPT_ACTIVE, "Y") === "Y";
    }

    /**
     * Включает или выключает модуль.
     *
     * @param bool $value Новое состояние.
     *
     * @return void
     */
    public static function setEnabled(bool $value): void
    {
        Option::set(self::MODULE, self::OPT_ACTIVE, $value ? "Y" : "N");
    }

    /**
     * Возвращает каталог типов кнопок.
     *
     * @return array<string, array{label: string, icon: string, default: string}>
     */
    public static function getTypes(): array
    {
        return self::TYPES;
    }

    /**
     * Возвращает допустимый список иконок.
     *
     * @return array<int, string>
     */
    public static function getIcons(): array
    {
        return self::ICONS;
    }

    /**
     * Показана ли кнопка указанного типа.
     *
     * @param string $type Идентификатор типа.
     *
     * @return bool
     */
    public static function isTypeActive(string $type): bool
    {
        if (!isset(self::TYPES[$type])) {
            return false;
        }

        return Option::get(
            self::MODULE,
            $type . self::SUFFIX_ACTIVE,
            self::TYPES[$type]["default"]
        ) === "Y";
    }

    /**
     * Сохраняет видимость кнопки указанного типа.
     *
     * @param string $type  Идентификатор типа.
     * @param bool   $value Показывать кнопку или нет.
     *
     * @return void
     */
    public static function setTypeActive(string $type, bool $value): void
    {
        if (!isset(self::TYPES[$type])) {
            return;
        }

        Option::set(self::MODULE, $type . self::SUFFIX_ACTIVE, $value ? "Y" : "N");
    }

    /**
     * Возвращает выбранную иконку типа, приводя её к списку разрешённых.
     *
     * @param string $type Идентификатор типа.
     *
     * @return string CSS-класс иконки.
     */
    public static function getTypeIcon(string $type): string
    {
        if (!isset(self::TYPES[$type])) {
            return "";
        }

        $saved = Option::get(self::MODULE, $type . self::SUFFIX_ICON, "");

        return in_array($saved, self::ICONS, true) ? $saved : self::TYPES[$type]["icon"];
    }

    /**
     * Сохраняет иконку типа. Некорректное значение отбрасывается.
     *
     * @param string $type Идентификатор типа.
     * @param string $icon CSS-класс иконки.
     *
     * @return void
     */
    public static function setTypeIcon(string $type, string $icon): void
    {
        if (!isset(self::TYPES[$type]) || !in_array($icon, self::ICONS, true)) {
            return;
        }

        Option::set(self::MODULE, $type . self::SUFFIX_ICON, $icon);
    }

    /**
     * Возвращает локализованное название типа кнопки.
     *
     * @param string $type Идентификатор типа.
     *
     * @return string
     */
    public static function getTypeLabel(string $type): string
    {
        if (!isset(self::TYPES[$type])) {
            return $type;
        }

        Loc::loadMessages(__FILE__);

        return Loc::getMessage(self::TYPES[$type]["label"]) ?: $type;
    }

    /**
     * Собирает конфигурацию для JS-расширения.
     *
     * @return array<int, array{id: string, title: string, icon: string}>
     */
    public static function getJsItems(): array
    {
        $items = [];

        foreach (array_keys(self::TYPES) as $type) {
            if (!self::isTypeActive($type)) {
                continue;
            }

            $items[] = [
                "id"    => $type,
                "title" => self::getTypeLabel($type),
                "icon"  => self::getTypeIcon($type),
            ];
        }

        return $items;
    }
}
