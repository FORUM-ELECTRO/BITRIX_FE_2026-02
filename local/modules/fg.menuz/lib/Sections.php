<?php

/**
 * Разделы и пункты левого меню модуля fg.menuz.
 *
 * @package FG\Menuz
 */

namespace FG\Menuz;

/**
 * Хранение разделов меню и проверка активности модуля.
 */
final class Sections
{
    /**
     * Проверяет, включён ли модуль.
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return Settings::flag("enabled");
    }

    /**
     * Возвращает все сохранённые разделы.
     *
     * @return array
     */
    public static function all(): array
    {
        $s = Settings::get("sections", []);
        return is_array($s) ? array_values($s) : [];
    }

    /**
     * Сохраняет разделы меню.
     *
     * Пустые разделы и пункты без названия или ссылки отбрасываются,
     * строки обрезаются, служебные поля чистятся.
     *
     * @param array $sections Массив разделов из админ-формы.
     *
     * @return void
     */
    public static function save(array $sections): void
    {
        $clean = [];
        foreach ($sections as $s) {
            // Пустой нештатный раздел не сохраняем
            $title = trim((string)($s["title"] ?? ""));
            if ($title === "" && empty($s["native"])) continue;

            $items = [];
            foreach ((array)($s["items"] ?? []) as $it) {
                $t = trim((string)($it["title"] ?? ""));
                $u = trim((string)($it["url"] ?? ""));
                $ic = trim((string)($it["icon"] ?? ""));
                // Пункт без названия или ссылки не сохраняем
                if ($t === "" || $u === "") continue;
                $sortVal = $it["sort"] ?? null;
                $items[] = [
                    "title"   => mb_substr($t, 0, 200),
                    "url"     => mb_substr($u, 0, 500),
                    "icon"    => $ic,
                    "id"      => mb_substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)($it["id"] ?? "")), 0, 100),
                    "counter" => mb_substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)($it["counter"] ?? "")), 0, 100),
                    "sort"    => ($sortVal === "" || $sortVal === null) ? null : (int)$sortVal,
                ];
            }
            $isNative = !empty($s["native"]);
            $clean[] = [
                "id"     => $isNative ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$s["id"]) : (int)($s["id"] ?? 0),
                "title"  => mb_substr($title, 0, 100),
                "icon"   => trim((string)($s["icon"] ?? "")),
                "items"  => $items,
                "native" => $isNative ?: null,
            ];
        }
        Settings::set("sections", $clean);
    }
}