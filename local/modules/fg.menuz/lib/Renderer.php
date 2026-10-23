<?php

/**
 * Построение левого меню на публичной части (модуль fg.menuz).
 *
 * @package FG\Menuz
 */

namespace FG\Menuz;

use Bitrix\Main\Web\Json;

/**
 * Отдаёт CSS, конфигурацию и JS, перестраивающие штатное меню Bitrix24.
 */
final class Renderer
{
    /**
     * Обработчик main:OnProlog.
     *
     * @return void
     */
    public static function onProlog(): void
    {
        try {
            self::render();
        } catch (\Throwable $e) {
            \AddMessage2Log("fg.menuz: " . $e->getMessage(), "fg.menuz");
        }
    }

    /**
     * Формирует CSS, JSON-конфигурацию и клиентский JS и добавляет их в head страницы.
     *
     * @return void
     */
    private static function render(): void
    {
        global $APPLICATION;

        if (!is_object($APPLICATION)) return;
        if (defined("ADMIN_SECTION") && ADMIN_SECTION === true) return;
        if (!self::isEnabledForUser()) return;
        if ((string)($_GET["fgz_raw"] ?? "") === "1") return;

        if ((string)($_GET["fgz_collect"] ?? "") === "1") {
            global $USER;
            if (is_object($USER) && $USER->IsAdmin()) {
                $APPLICATION->AddHeadString(
                    '<script data-fg-menuz="collect">' . self::collectJs() . '</script>',
                    true
                );
            }
            return;
        }

        if ((string)($_GET["fgz_collect_sitemap"] ?? "") === "1") {
            global $USER;
            if (is_object($USER) && $USER->IsAdmin()) {
                $APPLICATION->AddHeadString(
                    '<script data-fg-menuz="collect-sitemap">' . self::collectSitemapJs() . '</script>',
                    true
                );
            }
            return;
        }

        $script = (string)($_SERVER["SCRIPT_NAME"] ?? "");
        if (strncmp($script, "/bitrix/admin/", 14) === 0) return;
        if (strncmp($script, "/local/admin/", 13) === 0) return;
        $cur = (string)$APPLICATION->GetCurPage();
        if ($cur === "/desktop/menu/" || $cur === "/desktop/menu/index.php") return;


        if (!Sections::isEnabled()) return;

        $sections = Sections::all();
        $sections = array_values(array_filter(
            $sections,
            // Раздел показываем даже без пунктов, чтобы в него можно было перетаскивать
            static fn($s) => !empty($s["native"]) || trim((string)($s["title"] ?? "")) !== ""
        ));
        if (!$sections) return;

        $out = [];
        foreach ($sections as $s) {
            $isNative = !empty($s["native"]);
            $out[] = [
                "id"     => $isNative ? (string)$s["id"] : "fgz_" . (int)$s["id"],
                "native" => $isNative,
                "title"  => (string)$s["title"],
                "icon"   => (string)($s["icon"] ?? ""),
                // Единый формат пункта: url -> link, id, sort, source
                "items" => array_map(
                    static fn(array $it) => Catalog::normalizeItem($it, "section"),
                    $s["items"]
                ),
            ];
        }

        $hideNative       = Settings::flag("hide_native");
        $hideNativeGroups = Settings::flag("hide_native_groups");

        // Общий порядок: разделы + корневые пункты
        $order = Settings::get("order", []);
        if (!is_array($order)) $order = [];

        // Скрытые пункты
        $hidden = Settings::get("hidden", []);
        if (!is_array($hidden)) $hidden = [];
        $rootOverrides = Settings::get("root_overrides", []);
        if (!is_array($rootOverrides)) $rootOverrides = [];
        $badges = Settings::get("badges", []);
        if (!is_array($badges)) $badges = [];

        // Корневые пункты вне разделов: единый формат пункта и стабильный id
        $rootItemsRaw = Settings::get("root_items", []);
        if (!is_array($rootItemsRaw)) $rootItemsRaw = [];
        $rootItems = [];
        foreach ($rootItemsRaw as $ri) {
            $rootItems[] = [
                "title"   => (string)($ri["title"]   ?? ""),
                "url"     => (string)($ri["url"]     ?? ""),
                "icon"    => (string)($ri["icon"]    ?? ""),
                "id"      => (string)($ri["id"]      ?? ""),
                "counter" => (string)($ri["counter"] ?? ""),
                "sort"    => isset($ri["sort"]) ? (int)$ri["sort"] : null,
                "addUrl"  => (string)($ri["add_url"] ?? ""),
            ];
        }
        $rootItems = array_map(static function (array $it): array {
            $norm = Catalog::normalizeItem($it, "root");
            $norm["id"] = Catalog::rootId($it);
            return $norm;
        }, $rootItems);

        global $USER;
        $userId = (is_object($USER) && $USER->IsAuthorized()) ? (int)$USER->GetID() : 0;
        if (isset($_GET["fgz_p"])) {
            echo "USER_ID=" . (is_object($USER) ? $USER->GetID() : "no user") . "\n";
            echo "isAuth=" . (is_object($USER) && $USER->IsAuthorized() ? "Y" : "N") . "\n";
            echo "userId=" . $userId . "\n";
            $raw = \CUserOptions::GetOption("intranet", "left_menu_standard_items_S1", [], $userId);
            echo "raw type=" . gettype($raw) . "\n";
            echo "raw count=" . (is_array($raw) ? count($raw) : 0) . "\n";
            print_r($raw);
            exit;
        }

        // Личные ссылки пользователя: left_menu_self_items_s1 (новый) или left_menu_standard_items_s1 (старый)
        $selfItems = [];
        if ($userId > 0) {
            $selfKeys = ["left_menu_self_items_s1", "left_menu_standard_items_s1"];
            $selfRaw = [];
            foreach ($selfKeys as $optKey) {
                $candidate = \CUserOptions::GetOption("intranet", $optKey, [], $userId);
                if (is_array($candidate) && $candidate) {
                    $selfRaw = $candidate;
                    break;
                }
            }
            // ID пунктов, которые уже в SECTIONS — не тащить в «Избранное»
            $sectionItemIds = [];
            if (isset($sections) && is_array($sections)) {
                foreach ($sections as $__s) {
                    foreach (($__s["items"] ?? []) as $__it) {
                        if (!empty($__it["id"])) {
                            $sectionItemIds[(string)$__it["id"]] = true;
                        }
                    }
                }
            }

            if (is_array($selfRaw)) {
                foreach ($selfRaw as $sid => $val) {
                    $sid = (string)$sid;
                    if (preg_match('/^menu_/i', $sid)) continue;
                    if (!is_array($val)) continue;
                    if (empty($val["LINK"])) continue;
                    $sid = (string)($val["ID"] ?? $sid);
                    if ($sid === "") continue;

                    // пункт уже в SECTIONS — не тащить
                    if (isset($sectionItemIds[$sid])) continue;

                    $selfItems[] = $sid;
                }
            }
        }
        $selfItems = array_values(array_unique($selfItems));

        // ID кастомных разделов Битрикса (b_intranet_custom_section_page)
        $customSectionIds = [];
        global $DB;
        if (is_object($DB)) {
            $rsCS = $DB->Query("SELECT ID FROM b_intranet_custom_section_page");
            while ($rCS = $rsCS->Fetch()) {
                $customSectionIds[] = (string)$rCS["ID"];
            }
        }

        $json = Json::encode(
            [
                "sections"   => $out,
                "hideNative" => $hideNative,
                "hideNativeGroups" => $hideNativeGroups,
                "order"      => $order,
                "hidden"     => $hidden,
                "rootOverrides" => $rootOverrides,
                "userId"     => $userId,
                "badges"     => $badges,
                "selfItems"  => $selfItems,
                "rootItems"  => $rootItems,
                "customSectionIds" => $customSectionIds,
            ],
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
        );
        $json = str_replace("</", "<\\/", $json);


        // CSS для меню
        $css = '';
        $css .= '#menu-items-block .fgz-custom .ui-icon-set{background-color:currentColor!important;}';
        // Скрываем шестерёнку и drag-handle у ВСЕХ пунктов и разделов
        $css .= '#menu-items-block [data-role="item-edit-control"]{display:none!important;}';
        $css .= '#menu-items-block #bx_left_menu_fgz_personal_parent [data-role="item-edit-control"]{display:inline-flex!important;}';
        $css .= '#menu-items-block .menu-favorites-draggable{display:none!important;}';
        $css .= '#menu-items-block .menu-fav-draggable-icon{display:none!important;}';
        $css .= '#menu-items-block .fgz-custom .ui-icon-set:hover,';
        $css .= '#menu-items-block .fgz-custom.menu-item-active .ui-icon-set{background-color:currentColor!important;}';
        $css .= '#menu-items-block .menu-item-group .ui-icon-set{background-color:currentColor!important;}';
        $css .= '#menu-items-block li.menu-item-group[data-collapse-mode="collapsed"] + li.menu-item-group-more{display:none!important;}';
        $css .= '#menu-items-block .fgz-section li.menu-item-block.fgz-moved .menu-item-icon{background-color:currentColor!important;}';
        $css .= '#menu-items-block .fgz-section li.menu-item-block.fgz-moved .menu-item-icon-box > span{background-color:currentColor!important;}';

        // Скрываем шильдики при свёрнутом левом меню
        $css .= '.root.menu-collapsed-mode #menu-items-block .ui-label{display:none!important;}';

        // Скрытые пункты — сразу через CSS
        $hidden = Settings::get("hidden", []);
        if (is_array($hidden) && $hidden) {
            foreach ($hidden as $h) {
                $h = (string)$h;
                if ($h === "") continue;
                if (strpos($h, "://") !== false || (strlen($h) > 0 && $h[0] === "/")) {
                    // по URL
                    $esc = addcslashes($h, '"\\');
                    $css .= '#menu-items-block li.menu-item-block[data-link="' . $esc . '"]{display:none!important;}';
                } else {
                    // по data-id
                    $id = preg_replace('/[^a-zA-Z0-9_\-]/', '', $h);
                    if ($id !== "") {
                        $css .= '#menu-items-block li.menu-item-block[data-id="' . $id . '"]{display:none!important;}';
                    }
                }
            }
        }
        // Подсветка «Избранного» — лёгкий оранжевый фон
        $css .= '#menu-items-block #bx_left_menu_fgz_personal{background-color:rgba(100,180,255,0.10);border-radius:6px;}';
        $css .= '#menu-items-block #bx_left_menu_fgz_personal:hover{background-color:rgba(100,180,255,0.16);}';
        $css .= '#menu-items-block #bx_left_menu_fgz_personal.menu-item-active{background-color:rgba(100,180,255,0.20);}';
        // Анти-FOUC: меню скрыто, но CSS-анимация покажет его через 1.5с, даже если JS не выполнится
        $css .= '#menu-items-block .menu-items-body-inner{visibility:hidden;animation:fgzPatReveal 0s linear 1500ms forwards;}';
        $css .= '#menu-items-block.fgz-ready .menu-items-body-inner{visibility:visible;animation:none;}';
        $css .= '@keyframes fgzPatReveal{to{visibility:visible;}}';
        // Насыщенные цвета шильдиков (вместо мягких ui-label-*)
        $css .= '#menu-items-block .ui-label.ui-label-success{background-color:#1cae6a!important;color:#fff!important;border-color:#1cae6a!important;}';
        $css .= '#menu-items-block .ui-label.ui-label-success *{color:#fff!important;}';
        $css .= '#menu-items-block .ui-label.ui-label-warning{background-color:#e8a643!important;color:#fff!important;border-color:#e8a643!important;}';
        $css .= '#menu-items-block .ui-label.ui-label-warning *{color:#fff!important;}';
        $css .= '#menu-items-block .ui-label.ui-label-danger{background-color:#e7433a!important;color:#fff!important;border-color:#e7433a!important;}';
        $css .= '#menu-items-block .ui-label.ui-label-danger *{color:#fff!important;}';
        $css .= '#menu-items-block .ui-label.ui-label-primary{background-color:#2fc7f7!important;color:#fff!important;border-color:#2fc7f7!important;}';
        $css .= '#menu-items-block .ui-label.ui-label-primary *{color:#fff!important;}';
        
        

        // Скрываем штатные пункты, пока меню не готово — но НЕ весь .menu-items-body-inner
        $css .= '#menu-items-block:not(.fgz-ready) .menu-items-body-inner ul.menu-items > li.menu-item-block:not(.fgz-section):not(.fgz-custom):not(.fgz-moved):not([data-fgz-id]):not([data-fgz-root]):not(.main-page):not(.menu-live-feed){display:none!important;}';

        if ($hideNative) {
            // Скрываем все штатные пункты верхнего уровня
            $css .= '#menu-items-block .menu-items-body-inner ul.menu-items > li.menu-item-block:not(.fgz-section):not(.fgz-custom):not(.fgz-moved):not([data-fgz-id]){display:none!important;}';

            // Скрываем штатные группы и их контейнеры
            $css .= '#menu-items-block .menu-items-body-inner ul.menu-items > li.menu-item-group-more:not([data-fgz-parent]){display:none!important;}';

            // Скрываем блок "Скрытые" внизу меню
            $css .= '#menu-items-block .menu-item-favorites-more{display:none!important;}';
        } elseif ($hideNativeGroups) {
            // Скрываем только штатные группы (menu-item-group без fgz-section)
            $css .= '#menu-items-block .menu-items-body-inner ul.menu-items > li.menu-item-group:not(.fgz-section){display:none!important;}';

            // Скрываем их контейнеры (menu-item-group-more без fgz-parent)
            $css .= '#menu-items-block .menu-items-body-inner ul.menu-items > li.menu-item-group-more:not([data-fgz-parent]){display:none!important;}';
        }

        \Bitrix\Main\UI\Extension::load([
            'ui.label',
            'ui.icon-set.main',
            'ui.icon-set.crm',
            'ui.icon-set.actions',
            'ui.icon-set.outline',
            'ui.icon-set.solid',
            'ui.icons.base',
            'ui.icons.b24',
            'ui.icons.service',
            'ui.icons.disk',
        ]);

        $APPLICATION->AddHeadString('<style data-fg-menuz="css">' . $css . '</style>', true);

        $APPLICATION->AddHeadString(
            '<script type="text/template" data-fg-menuz="data">' . $json . '</script>',
            true
        );

        $APPLICATION->AddHeadString(
            '<script data-fg-menuz="client">' . self::clientJs() . '</script>',
            true
        );
    }

    /**
     * Возвращает JS-коллектор штатных пунктов меню.
     *
     * @return string
     */
    private static function collectJs(): string
    {
        return <<<'JS'
(function () {
    /**
     * Отправляет собранный каталог на сервер и закрывает вкладку.
     *
     * @param {Object} payload Тело запроса: items и groups.
     *
     * @return {void}
     */
    function send(payload) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/bitrix/admin/fg_menuz_collect.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onload = function () {
            try { localStorage.setItem('fgz_collect_done', Date.now().toString()); } catch (e) {}
            window.close();
        };
        xhr.onerror = function () {
            document.body.innerHTML = '<p>Ошибка отправки. Закройте вкладку.</p>';
        };
        xhr.send(JSON.stringify(payload));
    }

    /**
     * Ждёт #menu-items-block и собирает с него пункты и группы.
     *
     * @param {number} tries Количество сделанных попыток.
     *
     * @return {void|number}
     */
    function run(tries) {
        var root = document.getElementById('menu-items-block');
        if (!root) {
            if (tries > 100) return;
            return setTimeout(function () { run(tries + 1); }, 100);
        }
        setTimeout(function () {
            var items = [];
            var seen = {};
            root.querySelectorAll('li.menu-item-block[data-id]').forEach(function (li) {
                if (li.classList.contains('fgz-section')) return;
                if (li.classList.contains('fgz-custom')) return;
                if (li.classList.contains('fgz-moved')) return;
                var id = li.getAttribute('data-id') || '';
                if (!id || id.indexOf('fgz_') === 0 || seen[id]) return;
                seen[id] = true;
                var t = (li.querySelector('.menu-item-link-text') || {}).textContent || '';
                var a = li.querySelector('a.menu-item-link');
                var u = a ? (a.getAttribute('href') || '') : '';
                var ic = li.querySelector('.menu-item-icon');
                var icon = '';
                if (ic) {
                    var cl = ic.className || '';
                    var m = cl.match(/--([a-z0-9\-]+)/i);
                    if (m) {
                        icon = m[1];
                    } else {
                        // ui-icon-set нет — берём mask-image (data:URI)
                        var cs = getComputedStyle(ic);
                        var mask = cs.webkitMaskImage || cs.maskImage;
                        if (mask && mask !== 'none' && mask.indexOf('url(') === 0) {
                            var murl = mask.slice(5, -2).replace(/^["']|["']$/g, '');
                            if (murl && murl.indexOf('data:') === 0) icon = murl;
                        }
                    }
                }
                var counter = li.getAttribute('data-counter-id') || '';
                var grp = '';
                var par = li.parentNode;
                while (par && par !== root) {
                    if (par.classList && par.classList.contains('menu-item-group-more')) {
                        grp = par.getAttribute('data-group-id') || '';
                        break;
                    }
                    par = par.parentNode;
                }
                items.push({ id: id, title: t.trim(), link: u, icon: icon, counter: counter, group: grp });
            });
            var groups = [];
            var gseen = {};
            // Сортировка — в порядке появления li в DOM
            var orderedGroups = [];
            root.querySelectorAll('li.menu-item-group[data-id]').forEach(function (li) {
                if (li.classList.contains('fgz-section')) return;
                var gid = li.getAttribute('data-id') || '';
                if (!gid || gseen[gid]) return;
                gseen[gid] = true;
                var gt = (li.querySelector('.menu-item-link-text') || {}).textContent || '';
                orderedGroups.push({ id: gid, title: gt.trim() });
            });
            groups = orderedGroups;
            send({ items: items, groups: groups });
        }, 500);
    }
    run(0);
})();
JS;
    }

    /**
     * JS-коллектор карты сайта: собирает ссылки из main и шлёт на бэклог.
     *
     * @return string
     */
    private static function collectSitemapJs(): string
    {
        return <<<'JS'
(function () {
    /**
     * Отправляет ссылки с карты сайта на сервер и закрывает вкладку.
     *
     * @param {Object} payload Тело запроса: items.
     *
     * @return {void}
     */
    function send(payload) {
        var sessid = (window.BX && BX.bitrix_sessid) ? BX.bitrix_sessid() : '';
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/bitrix/admin/fg_menuz_collect_sitemap.php?sessid=' + encodeURIComponent(sessid), true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onload = function () {
            try { localStorage.setItem('fgz_collect_sitemap_done', Date.now().toString()); } catch (e) {}
            window.close();
        };
        xhr.onerror = function () {
            document.body.innerHTML = '<p>Ошибка отправки. Закройте вкладку.</p>';
        };
        xhr.send(JSON.stringify(payload));
    }

    /**
     * Ждёт <main> и выписывает из него все пригодные для меню ссылки.
     *
     * @param {number} tries Количество сделанных попыток.
     *
     * @return {void|number}
     */
    function run(tries) {
        var root = document.querySelector('main');
        if (!root) {
            if (tries > 100) return;
            return setTimeout(function () { run(tries + 1); }, 100);
        }
        setTimeout(function () {
            var seen = {};
            var items = [];
            Array.from(root.querySelectorAll('a[href]')).forEach(function (a) {
                var h = (a.getAttribute('href') || '').trim();
                var t = (a.textContent || '').trim();
                if (!h || !t) return;
                if (h.charAt(0) === '#') return;
                if (h.indexOf('javascript:') === 0) return;
                if (h.indexOf('/bitrix/') === 0) return;
                if (h.indexOf('/auth') === 0) return;
                if (h.indexOf('/login') === 0) return;
                if (h.indexOf('/search') === 0) return;
                if (h.indexOf('/sitemap') === 0) return;
                // обрезаем query и якоря — они нам не нужны
                var qPos = h.indexOf('?');
                if (qPos !== -1) h = h.slice(0, qPos);
                var hPos = h.indexOf('#');
                if (hPos !== -1) h = h.slice(0, hPos);
                if (h === "") return;
                if (seen[h]) return;
                seen[h] = 1;
                items.push({ link: h, title: t });
            });
            send({ items: items });
        }, 500);
    }
    run(0);
})();
JS;
    }

    /**
     * Клиентский JS, перестраивающий меню.
     *
     * @return string
     */
    private static function clientJs(): string
    {
        return <<<'JS'
(function () {
    if (window.__fgzClient) return;
    window.__fgzClient = 1;
    window.__fgzT0 = performance.now();

    var node = document.querySelector('script[data-fg-menuz="data"]');
    if (!node) return;
    var CFG;
    try { CFG = JSON.parse(node.textContent); } catch (e) { return; }
    if (!CFG || !CFG.sections || !CFG.sections.length) return;
    var SECTIONS = CFG.sections;
    var HIDE_NAT        = !!CFG.hideNative;
    var HIDE_NAT_GROUPS = !!CFG.hideNativeGroups;
    var ORDER           = Array.isArray(CFG.order) ? CFG.order : [];
    var HIDDEN          = Array.isArray(CFG.hidden) ? CFG.hidden : [];
    var ROOT_OVERRIDES  = (CFG.rootOverrides && typeof CFG.rootOverrides === 'object') ? CFG.rootOverrides : {};
    var USER_ID         = CFG.userId || 0;
    var BADGES          = (CFG.badges && typeof CFG.badges === 'object') ? CFG.badges : {};
    var SELF_ITEMS      = Array.isArray(CFG.selfItems) ? CFG.selfItems : [];
    var ROOT_ITEMS      = Array.isArray(CFG.rootItems) ? CFG.rootItems : [];
    var SLIDER_URLS = {};
    SECTIONS.forEach(function(sec){
        (sec.items || []).forEach(function(it){
            if (it.slider === 'Y' && it.link) {
                var u = it.link.indexOf('sp:') === 0 ? it.link.slice(3) : it.link;
                SLIDER_URLS[u] = true;
            }
        });
    });
    var CUSTOM_SECTION_IDS = Array.isArray(CFG.customSectionIds) ? CFG.customSectionIds : [];

    // Разовая очистка старых ключей без userId (fgz-mode-XXX)
    /**
     * Очищает старые ключи fgz-mode-fgz_ и fgz-mode-\d+ из localStorage.
     *
     * @return {void}
     */
    (function cleanupOldModeKeys() {
        try {
            Object.keys(localStorage).forEach(function(k){
                if (/^fgz-mode-fgz_/.test(k) || /^fgz-mode-\d+$/.test(k)) {
                    // ключ старого формата (без userId в префиксе)
                    if (k.indexOf('fgz-mode-' + USER_ID + '-') !== 0) {
                        localStorage.removeItem(k);
                    }
                }
            });
        } catch (e) {}
    })();

    // Страница логина/логаута — сбросить состояние свёрнутости из sessionStorage
    if (!USER_ID) {
        try {
            Object.keys(sessionStorage).forEach(function(k){
                if (k.indexOf('fgz-mode-') === 0) sessionStorage.removeItem(k);
            });
        } catch (e) {}
    }

    /**
     * Возвращает корень левого меню на странице.
     *
     * @return {HTMLElement|null}
     */
    function block() { return document.getElementById('menu-items-block'); }

    /**
     * Возвращает контейнер <ul> с пунктами верхнего уровня.
     *
     * @return {HTMLElement|null}
     */
    function menuUl() {
        var b = block();
        if (!b) return null;
        return b.querySelector('.menu-items-body-inner ul.menu-items')
            || b.querySelector('ul.menu-items')
            || b.querySelector('.menu-items-body-inner');
    }

    /**
     * Убирает из строки символы, недопустимые в data-id.
     *
     * @param {string} v Исходное значение.
     *
     * @return {string}
     */
    function safeId(v) { return ('' + v).replace(/[^a-zA-Z0-9_\-]/g, ''); }

    /**
     * Добавляет на пункт кнопку «плюс» с переходом по addUrl.
     *
     * @param {HTMLElement} li    Элемент пункта.
     * @param {string}      addUrl Ссылка кнопки или код с префиксом js:/sp:.
     *
     * @return {void}
     */
    function addPlus(li, addUrl) {
        if (!li || !addUrl) return;
        var old = li.querySelector('.menu-item-plus');
        if (old) {
            if (old.getAttribute('href') !== addUrl) old.setAttribute('href', addUrl);
            return;
        }
        var plus = document.createElement('a');
        plus.className = 'menu-item-plus';
        plus.href = addUrl;
        plus.setAttribute('tabindex', '-1');
        plus.setAttribute('aria-hidden', 'true');
        plus.setAttribute('aria-label', 'Создать');
        var ic = document.createElement('span');
        ic.className = 'menu-item-plus-icon';
        ic.setAttribute('aria-hidden', 'true');
        plus.appendChild(ic);

        plus.addEventListener('click', function(e){
            e.preventDefault();
            e.stopPropagation();

            // js: — выполнить код прямо здесь
            if (addUrl.indexOf('js:') === 0) {
                var code = addUrl.slice(3);
                try { (new Function(code))(); }
                catch (ex) { if (window.console) console.warn('fgz addPlus js error:', ex); }
                return;
            }

            // sp:URL|js:КОД — открыть URL в SidePanel и выполнить КОД после загрузки
            if (addUrl.indexOf('sp:') === 0) {
                var rest = addUrl.slice(3);
                var sep = rest.indexOf('|js:');
                var url = sep === -1 ? rest : rest.slice(0, sep);
                var afterCode = sep === -1 ? '' : rest.slice(sep + 4);
                if (window.BX && BX.SidePanel && BX.SidePanel.Instance) {
                    BX.SidePanel.Instance.open(url, {
                        cacheable: false,
                        allowChangeHistory: false,
                        width: 1000,
                        events: {
                            onLoad: function(event){
                                if (!afterCode) return;
                                var slider = event.getSlider ? event.getSlider() : null;
                                var frame = slider && slider.getFrame ? slider.getFrame() : null;
                                var win = frame && frame.contentWindow ? frame.contentWindow : null;
                                if (!win) return;
                                try { (new Function('window', afterCode))(win); }
                                catch (ex) { if (window.console) console.warn('fgz sp js:', ex); }
                            }
                        }
                    });
                    return;
                }
                window.location.href = url;
                return;
            }

            // обычный URL — SidePanel, если он есть
            if (window.BX && BX.SidePanel && BX.SidePanel.Instance) {
                BX.SidePanel.Instance.open(addUrl, {
                    cacheable: false,
                    allowChangeHistory: false,
                    width: 1000
                });
                return;
            }
            window.location.href = addUrl;
        }, true);

        var link = li.querySelector('.menu-item-link');
        if (link) li.insertBefore(plus, link);
        else li.appendChild(plus);
    }

    /**
     * Открывает URL в SidePanel по ссылке с префиксом "sp:".
     *
     * @param {string} spLink Ссылка с префиксом "sp:" и необязательным "|label:".
     *
     * @return {void}
     */
    function openSidePanel(spLink) {
        var url = spLink;
        var label = null;
        if (spLink && spLink.indexOf('sp:') === 0) url = spLink.slice(3);
        var sep = url.indexOf('|label:');
        if (sep !== -1) {
            label = url.slice(sep + 7);
            url = url.slice(0, sep);
        }
        if (window.BX && BX.SidePanel && BX.SidePanel.Instance) {
            var opts = {
                cacheable: false,
                allowChangeHistory: false,
                width: 1000
            };
            if (label) opts.label = { text: label, color: '#008000' };
            BX.SidePanel.Instance.open(url, opts);
            return;
        }
        window.location.href = url;
    }

    /**
     * Перехватывает клик по ссылке с префиксом "sp:" и открывает слайдер.
     *
     * @param {HTMLElement} li   Элемент пункта.
     * @param {string}      link Ссылка пункта.
     *
     * @return {void}
     */
    function bindSpNav(li, link) {
        if (!li || !link || link.indexOf('sp:') !== 0) return;
        if (li.hasAttribute && li.hasAttribute('data-fgz-sp')) return;
        var a = li.querySelector('a.menu-item-link');
        if (!a) return;
        li.setAttribute('data-fgz-sp', '1');
        a.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            openSidePanel(link);
        }, true);
    }

    /**
     * Возвращает ключ пункта в DOM: it.id либо фолбэк fgz_custom_<ссылка или название>.
     *
     * @param {Object} it Пункт меню.
     *
     * @return {string}
     */
    function itemDataId(it) {
        return it.id ? safeId(it.id) : ('fgz_custom_' + safeId(it.link || it.title));
    }

    // При новой сессии браузера чистим старые ключи свёрнутости из localStorage
    (function resetModesOnce() {
        try {
            if (!sessionStorage.getItem('fgz-session-init')) {
                sessionStorage.setItem('fgz-session-init', '1');
                Object.keys(localStorage).forEach(function(k){
                    if (k.indexOf('fgz-mode-') === 0) localStorage.removeItem(k);
                });
            }
        } catch (e) {}
    })();

    /**
     * Возвращает стабильный ключ пункта в хранилище с учётом userId.
     *
     * @param {string} id Ключ раздела.
     *
     * @return {string}
     */
    function modeKey(id) {
        return 'fgz-mode-' + USER_ID + '-' + id;
    }

    /**
     * Возвращает сохранённое состояние свёрнутости раздела.
     *
     * @param {string} id Ключ раздела.
     *
     * @return {string} "collapsed" или "expanded".
     */
    function savedMode(id) {
        try {
            var stored = sessionStorage.getItem(modeKey(id));
            if (stored === 'collapsed') return 'collapsed';
            if (stored === 'expanded') return 'expanded';
            return 'collapsed';
        } catch (e) { return 'collapsed'; }
    }

    /**
     * Применяет иконку к элементу: ui-icon-set, data:URI, URL, SVG или первая буква.
     *
     * @param {HTMLElement} iconEl Контейнер иконки.
     * @param {string}      icon   Значение иконки из настроек.
     * @param {string}      title  Название пункта для буквы-фолбэка.
     *
     * @return {void}
     */
    function applyIcon(iconEl, icon, title) {
        icon = (icon || '').trim();
        title = (title || '').trim();
        iconEl.className = '';
        iconEl.style.cssText = '';
        iconEl.textContent = '';
        iconEl.style.color = 'rgba(255, 255, 255, 0.7)';

        if (/^data:image\//i.test(icon)) {
            // data:URI — не вырезаем кавычки! Оборачиваем в двойные
            iconEl.style.display = 'inline-block';
            iconEl.style.width = '22px';
            iconEl.style.height = '22px';
            iconEl.style.webkitMaskImage = 'url("' + icon + '")';
            iconEl.style.maskImage = 'url("' + icon + '")';
            iconEl.style.webkitMaskRepeat = 'no-repeat';
            iconEl.style.maskRepeat = 'no-repeat';
            iconEl.style.webkitMaskPosition = 'center';
            iconEl.style.maskPosition = 'center';
            iconEl.style.webkitMaskSize = '22px';
            iconEl.style.maskSize = '22px';
            iconEl.style.setProperty('background-color', 'rgba(255, 255, 255, 0.7)', 'important');
            iconEl.style.setProperty('color', 'rgba(255, 255, 255, 0.7)', 'important');
            return;
        }

        if (/^(https?:|\/)/i.test(icon)) {
            var safe = icon.replace(/["'<>\\]/g, '');
            iconEl.style.display = 'inline-block';
            iconEl.style.width = '22px';
            iconEl.style.height = '22px';
            iconEl.style.webkitMaskImage = "url('" + safe + "')";
            iconEl.style.maskImage = "url('" + safe + "')";
            iconEl.style.webkitMaskRepeat = 'no-repeat';
            iconEl.style.maskRepeat = 'no-repeat';
            iconEl.style.webkitMaskPosition = 'center';
            iconEl.style.maskPosition = 'center';
            iconEl.style.webkitMaskSize = '22px';
            iconEl.style.maskSize = '22px';
            iconEl.style.color = 'rgba(255, 255, 255, 0.7)';
            iconEl.style.setProperty('background-color', 'rgba(255, 255, 255, 0.7)', 'important');
            return;
        }

        if (icon.indexOf('<svg') !== -1) {
            iconEl.innerHTML = icon;
            iconEl.style.display = 'inline-flex';
            iconEl.style.alignItems = 'center';
            iconEl.style.justifyContent = 'center';
            iconEl.style.width = '22px';
            iconEl.style.height = '22px';
            iconEl.style.color = 'rgba(255, 255, 255, 0.7)';
            var svg = iconEl.querySelector('svg');
            if (svg) {
                svg.setAttribute('width', '22');
                svg.setAttribute('height', '22');
                svg.style.fill = 'rgba(255, 255, 255, 0.7)';
            }
            return;
        }

        if (icon && /^[a-z0-9\-]+$/i.test(icon)) {
            iconEl.className = 'ui-icon-set --' + icon;
            iconEl.style.display = 'inline-block';
            iconEl.style.width = '22px';
            iconEl.style.height = '22px';
            iconEl.style.setProperty('background-color', 'rgba(255, 255, 255, 0.7)', 'important');
            iconEl.style.setProperty('color', 'rgba(255, 255, 255, 0.7)', 'important');
            return;
        }

        iconEl.textContent = title ? title.charAt(0).toUpperCase() : '';
    }

    /**
     * Строит каркас раздела: заголовок-кнопку и контейнер для пунктов.
     *
     * @param {Object} sec Раздел из конфигурации.
     *
     * @return {{header: HTMLElement, content: HTMLElement, innerUl: HTMLElement}}
     */
    function buildShell(sec) {
        var header = document.createElement('li');
        header.id = 'bx_left_menu_' + sec.id;
        header.className = 'menu-item-block menu-item-group fgz-section';
        header.setAttribute('data-fgz-id', sec.id);
        var attrs = {
            'data-status': 'show',
            'data-id': sec.id,
            'data-role': 'group',
            'data-collapse-mode': savedMode(sec.id),
            'data-storage': '',
            'data-counter-id': '',
            'data-link': '',
            'data-all-links': '',
            'data-type': 'system_group',
            'data-delete-perm': 'N',
            'data-new-page': 'N'
        };
        for (var k in attrs) if (attrs.hasOwnProperty(k)) header.setAttribute(k, attrs[k]);

        var drag = document.createElement('span');
        drag.className = 'menu-favorites-btn menu-favorites-draggable';
        drag.innerHTML = '<span class="menu-fav-draggable-icon"></span>';

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'menu-item-link';
        btn.setAttribute('aria-expanded', savedMode(sec.id) === 'collapsed' ? 'false' : 'true');

        var iconBox = document.createElement('span');
        iconBox.className = 'menu-item-icon-box';
        var icon = document.createElement('span');
        icon.setAttribute('aria-hidden', 'true');
        applyIcon(icon, sec.icon || 'folder-24', sec.title);
        iconBox.appendChild(icon);

        var tx = document.createElement('span');
        tx.className = 'menu-item-link-text';
        tx.setAttribute('data-role', 'item-text');
        tx.textContent = sec.title || '';

        var arrowWrap = document.createElement('span');
        arrowWrap.className = 'menu-item-link-arrow';
        arrowWrap.innerHTML = '<span class="ui-icon-set --chevron-down-l"></span>';

        btn.appendChild(iconBox);
        btn.appendChild(tx);
        btn.appendChild(arrowWrap);

        var edit = document.createElement('span');
        edit.setAttribute('data-role', 'item-edit-control');
        edit.className = 'menu-fav-editable-btn menu-favorites-btn';
        edit.innerHTML = '<span class="menu-favorites-btn-icon"></span>';

        header.appendChild(drag);
        header.appendChild(btn);
        header.appendChild(edit);

        var content = document.createElement('li');
        content.id = 'bx_left_menu_' + sec.id + '_parent';
        content.className = 'menu-item-group-more';
        content.setAttribute('data-group-id', sec.id);
        content.setAttribute('data-role', 'group-content');
        content.setAttribute('data-fgz-parent', '1');
        var innerUl = document.createElement('ul');
        innerUl.className = 'menu-item-group-more-ul';
        content.appendChild(innerUl);

        return { header: header, content: content, innerUl: innerUl };
    }

    /**
     * Следит за aria-expanded заголовка и сохраняет состояние свёрнутости.
     *
     * @param {HTMLElement} header Заголовок раздела.
     *
     * @return {void}
     */
    function watchCollapse(header) {
        if (header.__fgzWatch) return;
        header.__fgzWatch = 1;
        var id = header.getAttribute('data-id');
        if (!id) return;
        var btn = header.querySelector('.menu-item-link');
        if (!btn) return;

        /**
         * Синхронизирует data-collapse-mode заголовка с aria-expanded кнопки.
         *
         * @return {void}
         */
        function sync() {
            var exp = btn.getAttribute('aria-expanded');
            if (exp !== 'true' && exp !== 'false') return;
            var mode = exp === 'true' ? 'expanded' : 'collapsed';
            header.setAttribute('data-collapse-mode', mode);
            try { sessionStorage.setItem(modeKey(id), mode); } catch (e) {}
        }

        new MutationObserver(sync).observe(btn, { attributes: true, attributeFilter: ['aria-expanded'] });
        sync();
    }

    /**
     * Регистрирует заголовок раздела в штатном контроллере пунктов меню Bitrix24.
     *
     * @param {HTMLElement} header Заголовок раздела.
     *
     * @return {void}
     */
    function registerNative(header) {
        if (header.__fgzReg) return;
        var ic = null;
        try {
            var lm = window.BX && BX.Intranet && BX.Intranet.LeftMenu;
            if (lm && lm.getItemsController) ic = lm.getItemsController();
        } catch (e) {}
        if (!ic || typeof ic.registerItem !== 'function') {
            header.classList.add('fgz-fallback');
            watchCollapse(header);
            return;
        }
        try {
            ic.registerItem(header);
            header.__fgzReg = 1;
            watchCollapse(header);
        } catch (e) {
            header.classList.add('fgz-fallback');
            watchCollapse(header);
        }
    }

    /**
     * Вешает fallback-обработчик сворачивания на заголовок раздела.
     *
     * @param {HTMLElement} header Заголовок раздела.
     *
     * @return {void}
     */
    function bindFallback(header) {
        if (header.__fgzReg || header.__fgzToggle) return;
        header.__fgzToggle = 1;
        header.addEventListener('click', function (e) {
            if (header.__fgzReg) return;
            var btn = e.target.closest ? e.target.closest('.menu-item-link') : null;
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            var mode = header.getAttribute('data-collapse-mode') === 'collapsed' ? 'expanded' : 'collapsed';
            header.setAttribute('data-collapse-mode', mode);
            try { sessionStorage.setItem(modeKey(header.getAttribute('data-id')), mode); } catch (ex) {}
        }, true);
    }

    /**
     * Создаёт DOM-элемент кастомного пункта меню.
     *
     * @param {Object} it Пункт меню в едином формате.
     *
     * @return {HTMLElement}
     */
    function makeCustom(it) {
        var li = document.createElement('li');
        li.className = 'menu-item-block fgz-custom';
        li.setAttribute('data-status', 'show');
        li.setAttribute('data-id', itemDataId(it));
        li.setAttribute('data-role', 'item');
        var __isMenu = (it && it.id && String(it.id).indexOf('menu_') === 0 && it.counter);
        li.setAttribute('data-type', __isMenu ? 'default' : 'custom');
        if (__isMenu) li.classList.add('menu-item-with-index');
        li.setAttribute('data-storage', '');
        li.setAttribute('data-link', it.link || '');
        li.setAttribute('data-all-links', '');
        li.setAttribute('data-delete-perm', 'N');
        li.setAttribute('data-new-page', 'N');

        if (it.counter) li.setAttribute('data-counter-id', it.counter);
        // Поддержка ссылки-слайдера: "sp:/url" — открываем в SidePanel
        var spNavUrl = (it.link && it.link.indexOf('sp:') === 0) ? it.link.slice(3) : null;
        var a = document.createElement('a');
        a.className = 'menu-item-link';
        a.href = spNavUrl || it.link || '#';

        var iconBox = document.createElement('span');
        iconBox.className = 'menu-item-icon-box';
        var icon = document.createElement('span');
        icon.setAttribute('aria-hidden', 'true');
        applyIcon(icon, it.icon, it.title);
        iconBox.appendChild(icon);

        var tx = document.createElement('span');
        tx.className = 'menu-item-link-text';
        tx.setAttribute('data-role', 'item-text');
        tx.textContent = it.title || '';

        a.appendChild(iconBox);
        a.appendChild(tx);

        var bCfgC = BADGES[it.id];
        if (!bCfgC && it.link) {
            bCfgC = BADGES['url:' + it.link] || BADGES[it.link];
        }
        if (bCfgC && bCfgC.text && window.BX && BX.UI && BX.UI.Label && BX.UI.LabelColor) {
            var cmC = {
                success: BX.UI.LabelColor.SUCCESS,
                warning: BX.UI.LabelColor.WARNING,
                danger:  BX.UI.LabelColor.DANGER,
                info:    BX.UI.LabelColor.PRIMARY
            };
            var lblC = new BX.UI.Label({
                text: bCfgC.text,
                color: cmC[bCfgC.color] || BX.UI.LabelColor.SUCCESS,
                size: BX.UI.LabelSize.SM,
                fill: true
            });
            var lblNodeC = lblC.render();
            if (lblNodeC) {
                lblNodeC.style.marginLeft = '6px';
                a.appendChild(lblNodeC);
            }
        }

        li.appendChild(a);
        if (spNavUrl) bindSpNav(li, it.link);
        return li;
    }

    /**
     * Скрывает штатные пункты и/или группы согласно настройкам hideNative.
     *
     * @return {void}
     */
    function hideNative() {
        if (!HIDE_NAT && !HIDE_NAT_GROUPS) return;

        var b = block();
        if (!b) return;
        var list = b.querySelector('.menu-items-body-inner ul.menu-items') || b.querySelector('ul.menu-items');
        if (!list) return;

        var lis = list.children;

        for (var i = 0; i < lis.length; i++) {
            var li = lis[i];
            if (!li.classList) continue;

            if (li.classList.contains('fgz-section')) continue;
            if (li.classList.contains('fgz-custom')) continue;
            if (li.classList.contains('fgz-moved')) continue;
            if (li.hasAttribute('data-fgz-id')) continue;
            if (li.hasAttribute('data-fgz-parent')) continue;

            if (HIDE_NAT) {
                li.style.setProperty('display', 'none', 'important');
            } else if (HIDE_NAT_GROUPS) {
                if (li.classList.contains('menu-item-group')) {
                    li.style.setProperty('display', 'none', 'important');
                }
                if (li.classList.contains('menu-item-group-more')) {
                    li.style.setProperty('display', 'none', 'important');
                }
            }
        }

        if (HIDE_NAT) {
            var fav = b.querySelector('.menu-item-favorites-more');
            if (fav) fav.style.setProperty('display', 'none', 'important');
        }
    }

    var obs = null, pending = false;

    /**
     * Перестраивает меню по конфигурации: разделы, порядок, шильдики, избранное.
     *
     * @return {void}
     */
    function apply() {
        var ul = menuUl();
        if (!ul) return;
        // В режиме редактирования меню — не перестраиваем
        var b = block();
        if (b && b.classList.contains('menu-items-edit-mode')) return;
        if (obs) obs.disconnect();
        if (window.console) console.time('apply');
        try {
            // Скрытые пункты — по всему #menu-items-block, включая уже перенесённые в разделы
            if (HIDDEN.length) {
                var bRoot = block();
                if (bRoot) {
                    HIDDEN.forEach(function(key){
                        var li = null;
                        if (key.indexOf("://") !== -1 || key.charAt(0) === "/") {
                            // по URL
                            li = bRoot.querySelector('li.menu-item-block[data-link="' + key + '"]');
                            if (!li) {
                                var links = bRoot.querySelectorAll('li.menu-item-block a.menu-item-link');
                                for (var k = 0; k < links.length; k++) {
                                    if ((links[k].getAttribute('href') || '') === key) {
                                        li = links[k].closest('li.menu-item-block');
                                        break;
                                    }
                                }
                            }
                        } else {
                            // по data-id
                            li = bRoot.querySelector('li.menu-item-block[data-id="' + safeId(key) + '"]');
                        }
                        if (li) {
                            li.style.setProperty('display', 'none', 'important');
                        }
                    });
                }
            }

            for (var s = 0; s < SECTIONS.length; s++) {
                var sec = SECTIONS[s];

                // Штатная группа Bitrix — вставляем пункты в её ul, не создаём свой shell
                if (sec.native) {
                    var nativeHeader = ul.querySelector('li.menu-item-group[data-id="' + safeId(sec.id) + '"]');
                    if (!nativeHeader) continue;
                    var nativeContent = nativeHeader.nextElementSibling;
                    while (nativeContent && !nativeContent.classList.contains('menu-item-group-more')) {
                        nativeContent = nativeContent.nextElementSibling;
                    }
                    if (!nativeContent) continue;
                    var nativeUl = nativeContent.querySelector('ul.menu-item-group-more-ul');
                    if (!nativeUl) continue;

                    var itemsSortedNative = sec.items.slice().sort(function(a, b){
                        var sa = (a.sort != null ? a.sort : 999999);
                        var sb = (b.sort != null ? b.sort : 999999);
                        return sa - sb;
                    });
                    for (var ni = 0; ni < itemsSortedNative.length; ni++) {
                        var nit = itemsSortedNative[ni];
                        var nli = null;
                        if (nit.id) {
                            nli = ul.querySelector('li.menu-item-block[data-id="' + safeId(nit.id) + '"]');
                        }
                        if (nli) {
                            nli.classList.add('fgz-moved');
                            nli.setAttribute('data-fgz-moved', '1');
                            nli.style.removeProperty('display');

                            // Шильдик из настроек админки
                            var bCfgN = BADGES[nit.id];
                            if (bCfgN && bCfgN.text && window.BX && BX.UI && BX.UI.Label && BX.UI.LabelColor) {
                                var oldLblN = nli.querySelector('.ui-label');
                                if (oldLblN) oldLblN.remove();
                                var cmN = {
                                    success: BX.UI.LabelColor.SUCCESS,
                                    warning: BX.UI.LabelColor.WARNING,
                                    danger:  BX.UI.LabelColor.DANGER,
                                    info:    BX.UI.LabelColor.PRIMARY
                                };
                                var lblN = new BX.UI.Label({
                                    text: bCfgN.text,
                                    color: cmN[bCfgN.color] || BX.UI.LabelColor.SUCCESS,
                                    size: BX.UI.LabelSize.SM
                                });
                                var lblNodeN = lblN.render();
                                if (lblNodeN) {
                                    lblNodeN.style.marginLeft = '6px';
                                    var linkElN = nli.querySelector('a.menu-item-link');
                                    if (linkElN) linkElN.appendChild(lblNodeN);
                                }
                            }

                            // Если пользователь задал свою иконку — применим её
                            if (nit.icon) {
                                var nic = nli.querySelector('.menu-item-icon')
                                      || nli.querySelector('.menu-item-icon-box > span')
                                      || nli.querySelector('.menu-item-icon-box');
                                if (nic) applyIcon(nic, nit.icon, nit.title);
                            }
                            if (nit.title) {
                                var ntx = nli.querySelector('.menu-item-link-text');
                                if (ntx) ntx.textContent = nit.title;
                            }
                            if (nit.counter) {
                                nli.setAttribute('data-counter-id', nit.counter);
                            }
                            nativeUl.appendChild(nli);
                            addPlus(nli, nit.addUrl || '');
                            bindSpNav(nli, nit.link);
                        } else {
                            var ncid = itemDataId(nit);
                            if (!nativeUl.querySelector('li[data-id="' + safeId(ncid) + '"]')) {
                                var _nli1 = makeCustom(nit);
                                addPlus(_nli1, nit.addUrl || '');
                                nativeUl.appendChild(_nli1);
                            }
                        }
                    }
                    continue;
                }

                var header  = document.getElementById('bx_left_menu_' + sec.id);
                var content = document.getElementById('bx_left_menu_' + sec.id + '_parent');

                if (!header || !content) {
                    var shell = buildShell(sec);
                    ul.appendChild(shell.header);
                    ul.appendChild(shell.content);
                    header = shell.header;
                    content = shell.content;
                }

                registerNative(header);
                bindFallback(header);

                var innerUl = content.querySelector('ul.menu-item-group-more-ul');
                if (!innerUl) continue;

                // Bitrix registerItem перетаскивает li по menu_sort — отключаем jsDD для всех li в разделе
                /**
                 * Отключает jsDD-перетаскивание для всех li внутри контейнера раздела.
                 *
                 * @param {HTMLElement} innerUlLocal Контейнер innerUl.
                 *
                 * @return {void}
                 */
                (function disableDragInside(innerUlLocal){
                    if (!window.jsDD || typeof jsDD.unregisterObject !== 'function') return;
                    if (!innerUlLocal) return;
                    innerUlLocal.querySelectorAll('li.menu-item-block').forEach(function(li){
                        try { jsDD.unregisterObject(li); } catch (e) {}
                    });
                })(innerUl);

                // Сортируем items по sort (null — в конце)
                var itemsSorted = sec.items.slice().sort(function(a, b){
                    var sa = (a.sort != null ? a.sort : 999999);
                    var sb = (b.sort != null ? b.sort : 999999);
                    return sa - sb;
                });

                for (var i = 0; i < itemsSorted.length; i++) {
                    var it = itemsSorted[i];
                    var li = null;

                    if (it.id) {
                        li = ul.querySelector('li.menu-item-block[data-id="' + safeId(it.id) + '"]');
                    }

                    if (li) {
                        li.classList.add('fgz-moved');
                        li.setAttribute('data-fgz-moved', '1');
                        li.style.removeProperty('display');
                        
                        if (it.title) {
                            var tx = li.querySelector('.menu-item-link-text');
                            if (tx) tx.textContent = it.title;
                        }
                        if (it.icon) {
                            var ic = li.querySelector('.menu-item-icon')
                                  || li.querySelector('.menu-item-icon-box > span')
                                  || li.querySelector('.menu-item-icon-box');
                            if (ic) applyIcon(ic, it.icon, it.title);
                        }
                        if (it.counter) li.setAttribute('data-counter-id', it.counter);

                        // Шильдик из настроек админки
                        var bCfgIt = BADGES[it.id];
                        if (!bCfgIt && it.link) {
                            bCfgIt = BADGES['url:' + it.link] || BADGES[it.link];
                        }
                        if (bCfgIt && bCfgIt.text && window.BX && BX.UI && BX.UI.Label && BX.UI.LabelColor) {
                            var oldLblIt = li.querySelector('.ui-label');
                            if (oldLblIt) oldLblIt.remove();
                            var cmIt = {
                                success: BX.UI.LabelColor.SUCCESS,
                                warning: BX.UI.LabelColor.WARNING,
                                danger:  BX.UI.LabelColor.DANGER,
                                info:    BX.UI.LabelColor.PRIMARY
                            };
                            var lblIt = new BX.UI.Label({
                                text: bCfgIt.text,
                                color: cmIt[bCfgIt.color] || BX.UI.LabelColor.SUCCESS,
                                size: BX.UI.LabelSize.SM,
                                fill: true
                            });
                            var lblNodeIt = lblIt.render();
                            if (lblNodeIt) {
                                lblNodeIt.style.marginLeft = '6px';
                                var linkElIt = li.querySelector('a.menu-item-link');
                                if (linkElIt) linkElIt.appendChild(lblNodeIt);
                            }
                        }

                        innerUl.appendChild(li);
                        addPlus(li, it.addUrl || '');
                        bindSpNav(li, it.link);
                    } else {
                        var cid = itemDataId(it);
                        if (innerUl.querySelector('li[data-id="' + safeId(cid) + '"]')) continue;
                        var _newLi2 = makeCustom(it);
                        addPlus(_newLi2, it.addUrl || '');
                        innerUl.appendChild(_newLi2);
                    }
                }

                // Пересортировка li внутри innerUl по itemsSorted
                itemsSorted.forEach(function(it){
                    var li = null;
                    if (it.id) {
                        li = innerUl.querySelector('li.menu-item-block[data-id="' + safeId(it.id) + '"]');
                    }
                    if (!li) {
                        var cid = itemDataId(it);
                        li = innerUl.querySelector('li[data-id="' + safeId(cid) + '"]');
                    }
                    if (li) {
                        innerUl.appendChild(li);
                        addPlus(li, it.addUrl || '');
                        bindSpNav(li, it.link);
                    }
                });

                // Суммарный счётчик раздела на его заголовке
                var counterSum = 0;
                innerUl.querySelectorAll(':scope > li.menu-item-block').forEach(function (item) {
                    var counterEl = item.querySelector('.ui-counter__value');
                    if (!counterEl) return;
                    var val = parseInt(counterEl.textContent, 10);
                    if (!isNaN(val)) counterSum += val;
                });

                var headerLink = header.querySelector('.menu-item-link');
                if (headerLink) {
                    var sumWrap = headerLink.querySelector('.fgz-sum-counter');
                    if (counterSum > 0) {
                        if (!sumWrap) {
                            sumWrap = document.createElement('span');
                            sumWrap.className = 'menu-item-index-wrap fgz-sum-counter';
                            var counter = document.createElement('div');
                            counter.className = 'ui-counter ui-counter__scope --air ui-counter-sm ui-counter-primary --style-filled-alert';
                            var inner = document.createElement('div');
                            inner.className = 'ui-counter-inner';
                            var valEl = document.createElement('span');
                            valEl.className = 'ui-counter__value';
                            var symEl = document.createElement('span');
                            symEl.className = 'ui-counter__symbol';
                            inner.appendChild(valEl);
                            inner.appendChild(symEl);
                            counter.appendChild(inner);
                            sumWrap.appendChild(counter);

                            var arrow = headerLink.querySelector('.menu-item-link-arrow');
                            if (arrow) headerLink.insertBefore(sumWrap, arrow);
                            else headerLink.appendChild(sumWrap);
                        }
                        sumWrap.querySelector('.ui-counter__value').textContent = counterSum;
                    } else if (sumWrap) {
                        sumWrap.remove();
                    }
                }
            }

            /**
             * Сворачивает штатные группы Bitrix по состоянию из sessionStorage.
             *
             * @return {void}
             */
            (function applyNativeGroupModes(){
                var b = block();
                if (!b) return;
                b.querySelectorAll('li.menu-item-group:not(.fgz-section)').forEach(function(li){
                    var id = li.getAttribute('data-id');
                    if (!id) return;
                    var mode = savedMode(id);
                    li.setAttribute('data-collapse-mode', mode);
                    var btn = li.querySelector('.menu-item-link');
                    if (btn) {
                        btn.setAttribute('aria-expanded', mode === 'collapsed' ? 'false' : 'true');
                    }
                    watchCollapse(li);
                });
            })();

            /**
             * Отключает drag&drop у всех пунктов меню через jsDD.
             *
             * @return {void}
             */
            (function disableDrag(){
                if (!window.jsDD || typeof jsDD.unregisterObject !== 'function') return;
                var b = block();
                if (!b) return;
                b.querySelectorAll('li.menu-item-block, li.menu-item-group').forEach(function(li){
                    try { jsDD.unregisterObject(li); } catch (e) {}
                });
            })();

            /**
             * Расставляет шильдики по корневым пунктам меню.
             *
             * @return {void}
             */
            (function applyBadgesToRoot() {
                var b = block();
                if (!b) return;
                var ulR = b.querySelector('.menu-items-body-inner ul.menu-items') || b.querySelector('ul.menu-items');
                if (!ulR) return;
                Object.keys(BADGES).forEach(function(bid){
                    var bCfg = BADGES[bid];
                    if (!bCfg || !bCfg.text) return;
                    if (!window.BX || !BX.UI || !BX.UI.Label || !BX.UI.LabelColor) return;

                    // ищем li по data-id в корне
                    var li = ulR.querySelector(':scope > li.menu-item-block[data-id="' + safeId(bid) + '"]');
                    if (!li) return;

                    // если уже внутри раздела — скип (там обрабатывает apply)
                    if (li.classList.contains('fgz-moved')) return;

                    var oldL = li.querySelector('.ui-label');
                    if (oldL) oldL.remove();

                    var cmR = {
                        success: BX.UI.LabelColor.SUCCESS,
                        warning: BX.UI.LabelColor.WARNING,
                        danger:  BX.UI.LabelColor.DANGER,
                        info:    BX.UI.LabelColor.PRIMARY
                    };
                    var lblR = new BX.UI.Label({
                        text: bCfg.text,
                        color: cmR[bCfg.color] || BX.UI.LabelColor.SUCCESS,
                        size: BX.UI.LabelSize.SM,
                        fill: true
                    });
                    var nodeR = lblR.render();
                    if (nodeR) {
                        nodeR.style.marginLeft = '6px';
                        var aR = li.querySelector('a.menu-item-link');
                        if (aR) aR.appendChild(nodeR);
                    }
                });
            })();

            // Корневые пункты вне разделов — создаём/обновляем li, позицию задаёт ORDER
            if (ROOT_ITEMS.length > 0) {
                try {
                    ROOT_ITEMS.forEach(function(it){
                        var cid = itemDataId(it);
                        var li = ul.querySelector('li.menu-item-block[data-id="' + safeId(cid) + '"]');
                        if (li) {
                            li.classList.add('fgz-root-item');
                            li.setAttribute('data-fgz-root', '1');
                            li.style.removeProperty('display');
                            if (it.title) {
                                var tx = li.querySelector('.menu-item-link-text');
                                if (tx) tx.textContent = it.title;
                            }
                            if (it.icon) {
                                var ic = li.querySelector('.menu-item-icon-box > span') || li.querySelector('.menu-item-icon');
                                if (ic) applyIcon(ic, it.icon, it.title);
                            }
                            // В корень возвращаем только если li не в корне и не внутри раздела
                            if (li.parentNode !== ul && !li.closest('.menu-item-group-more')) {
                                ul.appendChild(li);
                            }
                            return;
                        }
                        var newLi = makeCustom(it);
                        bindSpNav(newLi, it.link);
                        newLi.classList.add('fgz-root-item');
                        newLi.setAttribute('data-fgz-root', '1');
                        ul.appendChild(newLi);
                    });
                } catch (eRoot) {
                    if (window.console) console.warn('fgz root items:', eRoot);
                }
            }

            // Применяем общий порядок: разделы и корневые пункты вперемешку
            if (ORDER.length) {
                var ulRoot = menuUl();
                if (ulRoot) {
                    var sorted = ORDER.slice().sort(function(a, b){
                        var sa = (a.sort != null ? a.sort : 9999);
                        var sb = (b.sort != null ? b.sort : 9999);
                        if (sa !== sb) return sa - sb;
                        // при равных — по алфавиту title (если есть)
                        var ta = (a.title || '').toString().toLowerCase();
                        var tb = (b.title || '').toString().toLowerCase();
                        return ta.localeCompare(tb, 'ru');
                    });

                    sorted.forEach(function(item){
                        if (item.type === 'section') {
                            // «Избранное» — всегда внизу, ORDER его не трогает
                            if (String(item.id) === 'fgz_personal') return;
                            // находим li раздела (header + content)
                            var secId = item.id;
                            var header = document.getElementById('bx_left_menu_' + secId)
                                      || document.getElementById('bx_left_menu_fgz_' + secId)
                                      || ulRoot.querySelector('li.fgz-section[data-fgz-id="' + secId + '"]')
                                      || ulRoot.querySelector('li.fgz-section[data-fgz-id="fgz_' + secId + '"]')
                                      || document.querySelector('#menu-items-block li.menu-item-group[data-id="' + secId + '"]');
                            if (!header) return;

                            // Content ищем строго по data-group-id, чтобы не захватить чужой контейнер
                            var content = null;
                            var id1 = header.getAttribute('data-fgz-id');
                            var id2 = header.getAttribute('data-id');
                            var candidateIds = [];
                            if (id1) candidateIds.push(id1);
                            if (id2) candidateIds.push(id2);
                            if (secId) candidateIds.push(secId);

                            for (var ci = 0; ci < candidateIds.length && !content; ci++) {
                                content = document.querySelector('#menu-items-block li.menu-item-group-more[data-group-id="' + candidateIds[ci] + '"]');
                            }
                            // фолбэк — если ничего не нашли, берём nextElementSibling как раньше
                            if (!content) {
                                content = header.nextElementSibling;
                                while (content && !content.classList.contains('menu-item-group-more')) {
                                    content = content.nextElementSibling;
                                }
                            }
                            if (!content) return;
                            ulRoot.appendChild(header);
                            ulRoot.appendChild(content);
                        } else if (item.type === 'root') {
                            // корневой пункт — ищем li с data-id и переносим в конец
                            var li = ulRoot.querySelector('li.menu-item-block[data-id="' + safeId(item.id) + '"]');
                            if (!li) return;
                            // не трогаем, если он уже в разделе
                            if (li.classList.contains('fgz-moved')) return;
                            ulRoot.appendChild(li);
                        }
                    });
                }
            }

            // Применяем override иконок для корневых пунктов
            var bOver = block();
            if (bOver) {
                Object.keys(ROOT_OVERRIDES).forEach(function(id){
                    var li = bOver.querySelector('li.menu-item-block[data-id="' + safeId(id) + '"]');
                    if (!li) return;
                    var ov = ROOT_OVERRIDES[id];
                    if (!ov || !ov.icon) return;
                    var iconEl = li.querySelector('.menu-item-icon-box > span')
                              || li.querySelector('.menu-item-icon');
                    if (!iconEl) {
                        var box = li.querySelector('.menu-item-icon-box');
                        if (box) {
                            iconEl = document.createElement('span');
                            iconEl.setAttribute('aria-hidden', 'true');
                            box.appendChild(iconEl);
                        }
                    }
                    if (iconEl) applyIcon(iconEl, ov.icon, '');
                });
            }

            hideNative();

            // Раздел «Избранное»: личные ссылки пользователя
            if (SELF_ITEMS.length > 0) {
                try {
                    var personalHeader = document.getElementById('bx_left_menu_fgz_personal');
                    var personalContent = document.getElementById('bx_left_menu_fgz_personal_parent');
                    if (!personalHeader || !personalContent) {
                        var personalShell = buildShell({
                            id:    'fgz_personal',
                            title: 'Избранное',
                            icon:  'pin-1',
                        });
                        ul.appendChild(personalShell.header);
                        ul.appendChild(personalShell.content);
                        personalHeader = personalShell.header;
                        personalContent = personalShell.content;
                    }
                    registerNative(personalHeader);
                    bindFallback(personalHeader);

                    var personalUl = personalContent.querySelector('ul.menu-item-group-more-ul');
                    if (personalUl) {
                        SELF_ITEMS.forEach(function (sid) {
                            var li = ul.querySelector('li.menu-item-block[data-id="' + safeId(sid) + '"]');
                            if (!li) return;
                            if (li.closest('.fgz-section') === personalHeader) return;
                            li.classList.add('fgz-moved');
                            li.setAttribute('data-fgz-moved', '1');
                            li.style.removeProperty('display');
                            personalUl.appendChild(li);
                        });

                        // Динамика: переносим новые личные ссылки, которых нет в SELF_ITEMS
                        var allLis = ul.querySelectorAll(':scope > li.menu-item-block');
                        for (var i = 0; i < allLis.length; i++) {
                            var li2 = allLis[i];
                            if (!li2.dataset.id) continue;
                            var id2 = li2.dataset.id;

                            if (li2.classList.contains('fgz-section')) continue;
                            if (li2.classList.contains('fgz-custom')) continue;
                            if (li2.classList.contains('fgz-moved')) continue;
                            if (li2.classList.contains('fgz-root-item')) continue;
                            if (li2.getAttribute('data-fgz-root') === '1') continue;
                            if (id2 === 'main_page') continue;
                            if (id2 === 'fgz_personal') continue;
                            if (id2.indexOf('fgz_') === 0) continue;
                            if (id2.indexOf('menu_') === 0) continue;

                            // скрытые в модуле — не тащить в «Избранное»
                            if (HIDDEN.indexOf(id2) !== -1) continue;
                            var linkAttr2 = li2.getAttribute('data-link') || '';
                            if (linkAttr2 && HIDDEN.indexOf(linkAttr2) !== -1) continue;

                            // кастомные разделы Битрикса (не наши) — не тащить
                            if (CUSTOM_SECTION_IDS.indexOf(id2) !== -1) continue;

                            var isSection = false;
                            for (var s2 = 0; s2 < SECTIONS.length; s2++) {
                                var sid2 = SECTIONS[s2].native
                                    ? String(SECTIONS[s2].id)
                                    : 'fgz_' + SECTIONS[s2].id;
                                if (id2 === sid2) { isSection = true; break; }
                            }
                            if (isSection) continue;

                            // Проверка: id2 — это ПУНКТ какого-то раздела
                            var isSectionItem = false;
                            for (var s3 = 0; s3 < SECTIONS.length; s3++) {
                                var items3 = SECTIONS[s3].items || [];
                                for (var ii3 = 0; ii3 < items3.length; ii3++) {
                                    if (String(items3[ii3].id) === String(id2)) {
                                        isSectionItem = true; break;
                                    }
                                }
                                if (isSectionItem) break;
                            }
                            if (isSectionItem) continue;

                            li2.classList.add('fgz-moved');
                            li2.setAttribute('data-fgz-moved', '1');
                            li2.style.removeProperty('display');
                            personalUl.appendChild(li2);
                        }
                    }

                    /**
                     * Стабильно сортирует «Избранное»: sort из ORDER, индекс в SELF_ITEMS, порядок в DOM.
                     *
                     * @return {void}
                     */
                    (function sortPersonal(){
                        var orderMap = {};
                        ORDER.forEach(function(o){
                            if (!o || !o.id) return;
                            orderMap[(o.type || '') + ':' + String(o.id)] = (o.sort != null ? o.sort : 9999);
                        });

                        var selfIndex = {};
                        SELF_ITEMS.forEach(function(sid, i){ selfIndex[String(sid)] = i; });

                        var kids = Array.from(personalUl.children);
                        kids.forEach(function(li, idx){ li.__fgzIdx = idx; });

                        kids.sort(function(a, b){
                            var idA = a.getAttribute('data-fgz-key') || a.getAttribute('data-id') || '';
                            var idB = b.getAttribute('data-fgz-key') || b.getAttribute('data-id') || '';

                            var sA = orderMap['root:' + idA] != null ? orderMap['root:' + idA]
                                   : (orderMap['section:' + idA] != null ? orderMap['section:' + idA] : null);
                            var sB = orderMap['root:' + idB] != null ? orderMap['root:' + idB]
                                   : (orderMap['section:' + idB] != null ? orderMap['section:' + idB] : null);

                            if (sA != null && sB != null) return sA - sB;
                            if (sA != null) return -1;
                            if (sB != null) return 1;

                            var iA = selfIndex[idA] != null ? selfIndex[idA] : 999999;
                            var iB = selfIndex[idB] != null ? selfIndex[idB] : 999999;
                            if (iA !== iB) return iA - iB;

                            return (a.__fgzIdx || 0) - (b.__fgzIdx || 0);
                        });

                        kids.forEach(function(li){
                            delete li.__fgzIdx;
                            personalUl.appendChild(li);
                        });
                    })();

                    // «Избранное» всегда в самом низу
                    ul.appendChild(personalHeader);
                    ul.appendChild(personalContent);
                } catch (ePersonal) {
                    if (window.console) console.warn('fgz personal:', ePersonal);
                }
            }

            /**
             * Закрепляет раздел «Избранное» самым последним в контейнере меню.
             *
             * @return {void}
             */
            (function pinPersonalFinal(){
                var ph = document.getElementById('bx_left_menu_fgz_personal');
                if (!ph) return;
                var pc = ph.nextElementSibling;
                while (pc && !pc.classList.contains('menu-item-group-more')) {
                    pc = pc.nextElementSibling;
                }
                if (!pc) return;
                var root = menuUl();
            if (!root) return;
                root.appendChild(ph);
                root.appendChild(pc);
            })();

            /**
             * Вешает открытие в SidePanel на пункты с флагом «слайдер».
             *
             * @return {void}
             */
            (function bindMenuSlider(){
                var links = document.querySelectorAll('#menu-items-block a.menu-item-link');
                for (var i = 0; i < links.length; i++) {
                    var a = links[i];
                    if (a.__fgzSliderBound) continue;
                    if (a.classList.contains('menu-item-plus')) continue;
                    var li = a.closest('li.menu-item-block');
                    if (li && li.hasAttribute('data-fgz-sp')) continue;
                    var href = a.getAttribute('href') || '';
                    if (!href || href === '#' || href.indexOf('javascript:') === 0) continue;
                    if (!SLIDER_URLS[href]) continue;
                    a.__fgzSliderBound = 1;
                    a.addEventListener('click', function(e){
                        var href = this.getAttribute('href') || '';
                        if (!href || href === '#' || href.indexOf('javascript:') === 0) return;
                        if (!(window.BX && BX.SidePanel && BX.SidePanel.Instance)) return;
                        e.preventDefault();
                        e.stopPropagation();
                        BX.SidePanel.Instance.open(href, {
                            cacheable: false,
                            allowChangeHistory: true,
                            allowCrossOrigin: true,
                            width: 1000
                        });
                    }, true);
                }
            })();
        } finally {
            var b = block();
            if (b) b.classList.add('fgz-ready');
            connect();
            if (window.console) console.timeEnd('apply');
        }
    }

    /**
     * Включает наблюдение за изменениями DOM меню.
     *
     * @return {void}
     */
    function connect() {
        var b = block();
        if (!b) return;
        if (!obs) obs = new MutationObserver(function () { schedule(); });
        obs.observe(b, { childList: true, subtree: true });
    }

    var lastApply = 0;
    /**
     * Планирует ближайший вызов apply() с троттлингом.
     *
     * @return {void}
     */
    function schedule() {
        if (pending) return;
        if (window.__fgzApplying) return;
        var now = Date.now();
        if (now - lastApply < 500) return;
        pending = true;
        setTimeout(function () {
            pending = false;
            if (window.__fgzApplying) return;
            var now2 = Date.now();
            if (now2 - lastApply < 500) return;
            lastApply = now2;
            apply();
        }, 300);
    }

    /**
     * Запускает первую перестройку меню.
     *
     * @return {void}
     */
    function start() {
        apply();
    }

    var tries = 0;
    /**
     * Ждёт появления контейнера меню, затем стартует через BX.ready.
     *
     * @return {void|number}
     */
    (function wait() {
        var ul = menuUl();
        if (!ul) {
            if (++tries > 300) return;
            return setTimeout(wait, 10);
        }
        if (window.BX && BX.ready) BX.ready(start); else start();
    })();

    setTimeout(function () {
        var b = block();
        if (b) b.classList.add('fgz-ready');
    }, 2500);

    // Гарантированное скрытие пунктов из CFG.hidden
    [300, 1000, 2500, 5000].forEach(function(delay){
        setTimeout(function(){
            var bRoot = document.getElementById('menu-items-block');
            if (!bRoot) return;
            var hidden = (CFG && CFG.hidden) || [];
            hidden.forEach(function(key){
                var li = null;
                if (key.indexOf("://") !== -1 || key.charAt(0) === "/") {
                    li = bRoot.querySelector('li.menu-item-block[data-link="' + key + '"]');
                } else {
                    li = bRoot.querySelector('li.menu-item-block[data-id="' + key.replace(/[^a-zA-Z0-9_\-]/g, '') + '"]');
                }
                if (li) li.style.setProperty('display', 'none', 'important');
            });
        }, delay);
    });
})();
JS;
    }

    /**
     * Проверяет по правам модуля, показывать ли перестроенное меню текущему пользователю.
     *
     * @return bool
     */
    private static function isEnabledForUser(): bool
    {
        global $USER;
        if (!is_object($USER)) return false;

        $userId = (int)$USER->GetID();
        if ($userId <= 0) return false;

        // Администратор всегда видит меню
        if ($USER->IsAdmin()) return true;

        // Права групп из b_option: fg.menuz.group_rights = [gid => "D|R|W"]
        $rights = Settings::get("group_rights", []);
        if (!is_array($rights)) $rights = [];

        $userGroups = $USER->GetUserGroupArray();
        if (!is_array($userGroups)) $userGroups = [];

        $right = "";
        foreach ($userGroups as $gid) {
            $gid = (int)$gid;
            if (!isset($rights[$gid]) || $rights[$gid] === "") continue;
            $gRight = (string)$rights[$gid];
            // Запрет имеет приоритет
            if ($gRight === "D") return false;
            if ($right === "" || $gRight > $right) {
                $right = $gRight;
            }
        }

        // Ни одна группа не имеет явного права — берём значение по умолчанию
        if ($right === "") {
            $right = (string)Settings::get("group_default_right", "R");
        }

        return $right >= "R";
    }
}
