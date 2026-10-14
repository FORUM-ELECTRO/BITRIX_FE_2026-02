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
     * Добавляет пункты к существующему каталогу.
     *
     * Используется для дополнительных источников (sitemap, manual).
     * Дедуп по id. Если id нет — генерируется по хэшу от link.
     *
     * @param array  $items  Новые пункты: [["link"=>..., "title"=>..., "source"=>...], ...]
     * @param string $source Метка источника (например, "sitemap").
     *
     * @return int Количество добавленных пунктов.
     */
    public static function saveExtra(array $items, string $source = "extra"): int
    {
        $all = self::all();
        $existing    = [];   // по id
        $existingUrl = [];   // по link
        if (isset($all["items"]) && is_array($all["items"])) {
            foreach ($all["items"] as $it) {
                if (!empty($it["id"]))   $existing[$it["id"]] = true;
                if (!empty($it["link"])) $existingUrl[$it["link"]] = true;
            }
        }

        $clean = [];
        $added = 0;
        foreach ($items as $it) {
            $link  = mb_substr(trim((string)($it["link"] ?? "")), 0, 500);
            $title = mb_substr(trim((string)($it["title"] ?? "")), 0, 200);
            if ($link === "" || $title === "") continue;

            // Дедуп по URL — не добавлять, если ссылка уже есть (в любом источнике)
            if (isset($existingUrl[$link])) continue;

            $id = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)($it["id"] ?? ""));
            if ($id === "") {
                $id = "sitemap_" . substr(md5($link), 0, 12);
            }
            if (isset($existing[$id])) continue;

            $clean[] = [
                "id"      => $id,
                "title"   => $title,
                "link"    => $link,
                "icon"    => trim((string)($it["icon"] ?? "")),
                "counter" => "",
                "group"   => "",
                "source"  => $source,
            ];
            $existing[$id] = true;
            $existingUrl[$link] = true;
            $added++;
        }

        if (!$clean) return 0;

        $merged = isset($all["items"]) && is_array($all["items"]) ? $all["items"] : [];
        foreach ($clean as $c) $merged[] = $c;

        $groups = isset($all["groups"]) && is_array($all["groups"]) ? $all["groups"] : [];
        Option::set(self::MODULE, self::OPT, serialize([
            "ts"     => time(),
            "items"  => $merged,
            "groups" => $groups,
        ]));

        return $added;
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
     * Возвращает ВСЕ пункты каталога в сыром виде: штатные + sitemap + manual.
     *
     * Единый источник истины для полного списка. Использовать только там,
     * где нужен весь список: автокомплит URL в админке.
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

    /**
     * Возвращает только "настоящие" штатные пункты — без sitemap и manual.
     *
     * Использовать во вкладках «Порядок», «Скрытые», «Шильдики» — там
     * sitemap/manual-пункты не нужны.
     *
     * @return array
     */
    public static function itemsNative(): array
    {
        $all = self::items();
        $out = [];
        foreach ($all as $it) {
            $src = (string)($it["source"] ?? "");
            if ($src === "sitemap" || $src === "manual") continue;
            $out[] = $it;
        }
        return $out;
    }

    /**
     * Нормализует пункт меню к единому формату.
     *
     * Единый формат сущности меню: id, title, link, icon, counter, sort,
     * source, flat. Принимает пункт из любого источника — каталога (link),
     * разделов (url), корневых пунктов (url).
     *
     * @param array  $it     Пункт в любом из форматов.
     * @param string $source Источник по умолчанию, если в пункте нет source.
     *
     * @return array
     */
    public static function normalizeItem(array $it, string $source = ""): array
    {
        $link = (string)($it["link"] ?? ($it["url"] ?? ""));
        $sort = $it["sort"] ?? null;
        if ($sort === "" || $sort === null) {
            $sort = null;
        } else {
            $sort = (int)$sort;
        }

        return [
            "id"      => (string)($it["id"] ?? ""),
            "title"   => (string)($it["title"] ?? ""),
            "link"    => $link,
            "icon"    => (string)($it["icon"] ?? ""),
            "counter" => (string)($it["counter"] ?? ""),
            "sort"    => $sort,
            "source"  => (string)($it["source"] ?? $source),
            "flat"    => true,
            "addUrl"  => (string)($it["add_url"] ?? ($it["addUrl"] ?? "")),
        ];
    }

    /**
     * Все пункты каталога в едином формате (штатные + sitemap + manual).
     *
     * Использовать для автокомплита URL и любых мест, где нужен полный список.
     *
     * @return array
     */
    public static function allItems(): array
    {
        return array_map(
            static fn(array $it) => self::normalizeItem($it, (string)($it["source"] ?? "native")),
            self::items()
        );
    }

    /**
     * Только штатные пункты каталога в едином формате.
     *
     * Использовать во вкладках «Порядок», «Скрытые», «Шильдики».
     *
     * @return array
     */
    public static function nativeItems(): array
    {
        return array_map(
            static fn(array $it) => self::normalizeItem($it, "native"),
            self::itemsNative()
        );
    }

    /**
     * Стабильный id корневого пункта.
     *
     * Если id не задан — генерируется по URL (fgz_root_<md5>), чтобы
     * админка, order, root_items и DOM на публичке использовали один ключ.
     *
     * @param array $it Пункт из root_items.
     *
     * @return string
     */
    public static function rootId(array $it): string
    {
        $id = (string)($it["id"] ?? "");
        if ($id !== "") return $id;
        $link = (string)($it["link"] ?? ($it["url"] ?? ""));
        return "fgz_root_" . substr(md5($link), 0, 12);
    }
}