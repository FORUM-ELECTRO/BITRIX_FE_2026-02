<?php

/**
 * Каталог штатных пунктов и групп меню Bitrix24.
 *
 * @package FG\Menuz
 */

namespace FG\Menuz;

use Bitrix\Main\Config\Option;

/**
 * Хранение каталога штатных пунктов и групп, собранного коллектором.
 */
final class Catalog
{
    /** @var string Идентификатор модуля. */
    private const MODULE = "fg.menuz";
    /** @var string Ключ Option с каталогом. */
    private const OPT = "catalog";

    /**
     * Возвращает весь каталог вместе с меткой времени обновления.
     *
     * @return array
     */
    public static function all(): array
    {
        $raw = Option::get(self::MODULE, self::OPT, "");
        $data = $raw === "" ? [] : @unserialize($raw, ["allowed_classes" => false]);
        return is_array($data) ? $data : [];
    }

    /**
     * Сохраняет каталог.
     *
     * Пункты без id или названия и дубликаты отбрасываются, строки
     * обрезаются. Вместе с данными сохраняется метка времени.
     *
     * @param array $items  Пункты меню.
     * @param array $groups Группы меню.
     *
     * @return void
     */
    public static function save(array $items, array $groups = []): void
    {
        $clean = [];
        $seen  = [];
        foreach ($items as $it) {
            $id    = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)($it["id"] ?? ""));
            $title = mb_substr(trim((string)($it["title"] ?? "")), 0, 200);
            $link  = mb_substr(trim((string)($it["link"] ?? "")), 0, 500);
            $icon  = trim((string)($it["icon"] ?? ""));
            $counter = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)($it["counter"] ?? ""));
            $group = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)($it["group"] ?? ""));
            if ($id === "" || $title === "" || isset($seen[$id])) continue;
            $seen[$id] = true;
            $clean[] = ["id" => $id, "title" => $title, "link" => $link, "icon" => $icon, "counter" => $counter, "group" => $group];
        }

        $gclean = [];
        $gseen  = [];
        foreach ($groups as $g) {
            $gid = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)($g["id"] ?? ""));
            $gt  = mb_substr(trim((string)($g["title"] ?? "")), 0, 200);
            if ($gid === "" || $gt === "" || isset($gseen[$gid])) continue;
            $gseen[$gid] = true;
            $gclean[] = ["id" => $gid, "title" => $gt];
        }

        Option::set(self::MODULE, self::OPT, serialize(["ts" => time(), "items" => $clean, "groups" => $gclean]));
    }

    /**
     * Возвращает список штатных групп.
     *
     * @return array
     */
    public static function groups(): array
    {
        $all = self::all();
        return isset($all["groups"]) && is_array($all["groups"]) ? $all["groups"] : [];
    }

    /**
     * Возвращает список штатных пунктов.
     *
     * Поддерживает и новый формат с обёрткой, и старый плоский массив.
     *
     * @return array
     */
    public static function items(): array
    {
        $all = self::all();
        if (isset($all["items"]) && is_array($all["items"])) {
            return $all["items"];
        }
        // fallback: если вдруг массив без обёртки
        if (is_array($all) && isset($all[0])) {
            return $all;
        }
        return [];
    }
}