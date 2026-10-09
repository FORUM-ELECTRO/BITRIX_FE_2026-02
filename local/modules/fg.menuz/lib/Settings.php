<?php

/**
 * Настройки модуля fg.menuz.
 *
 * @package FG\Menuz
 */

namespace FG\Menuz;

use Bitrix\Main\Config\Option;

/**
 * Обёртка над Option для хранения настроек модуля.
 */
final class Settings
{
    /** @var string Идентификатор модуля. */
    private const MODULE = "fg.menuz";

    /**
     * Возвращает настройку по ключу.
     *
     * @param string $key     Ключ настройки.
     * @param mixed  $default Значение по умолчанию.
     *
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $raw = Option::get(self::MODULE, $key, null);
        if ($raw === null || $raw === "") return $default;
        $un = @unserialize($raw, ["allowed_classes" => false]);
        return $un === false ? $raw : $un;
    }

    /**
     * Сохраняет настройку.
     *
     * @param string $key   Ключ настройки.
     * @param mixed  $value Значение.
     *
     * @return void
     */
    public static function set(string $key, $value): void
    {
        Option::set(self::MODULE, $key, serialize($value));
    }

    /**
     * Возвращает булевый флаг ("Y"/"N").
     *
     * @param string $key Ключ настройки.
     *
     * @return bool
     */
    public static function flag(string $key): bool
    {
        return Option::get(self::MODULE, $key, "N") === "Y";
    }

    /**
     * Сохраняет булевый флаг ("Y"/"N").
     *
     * @param string $key Ключ настройки.
     * @param bool   $v   Значение.
     *
     * @return void
     */
    public static function setFlag(string $key, bool $v): void
    {
        Option::set(self::MODULE, $key, $v ? "Y" : "N");
    }
}