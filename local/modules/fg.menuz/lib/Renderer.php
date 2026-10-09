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
     * Формирует CSS, JSON-конфигурацию разделов и клиентский JS
     * и добавляет их в head страницы.
     *
     * Админка и служебные запросы пропускаются. При fgz_collect=1
     * вместо меню подключается коллектор штатных пунктов.
     *
     * @return void
     */
    private static function render(): void
    {
        global $APPLICATION;

        if (!is_object($APPLICATION)) return;
        if (defined("ADMIN_SECTION") && ADMIN_SECTION === true) return;
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
                "items" => array_map(static function ($it) {
                    return [
                        "link"    => (string)($it["url"] ?? ""),
                        "title"   => (string)($it["title"] ?? ""),
                        "icon"    => (string)($it["icon"] ?? ""),
                        "id"      => (string)($it["id"] ?? ""),
                        "counter" => (string)($it["counter"] ?? ""),
                        "sort"    => isset($it["sort"]) ? (int)$it["sort"] : null,
                    ];
                }, $s["items"]),
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
        global $USER;
        $userId = (is_object($USER) && $USER->IsAuthorized()) ? (int)$USER->GetID() : 0;

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
            ],
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
        );
        $json = str_replace("</", "<\\/", $json);

        // CSS для меню
        $css = '';
        $css .= '#menu-items-block .fgz-custom .ui-icon-set{background-color:currentColor!important;}';
        // Скрываем шестерёнку и drag-handle у ВСЕХ пунктов и разделов
        $css .= '#menu-items-block [data-role="item-edit-control"]{display:none!important;}';
        $css .= '#menu-items-block .menu-favorites-draggable{display:none!important;}';
        $css .= '#menu-items-block .menu-fav-draggable-icon{display:none!important;}';
        $css .= '#menu-items-block .fgz-custom .ui-icon-set:hover,';
        $css .= '#menu-items-block .fgz-custom.menu-item-active .ui-icon-set{background-color:currentColor!important;}';
        $css .= '#menu-items-block .menu-item-group .ui-icon-set{background-color:currentColor!important;}';
        $css .= '#menu-items-block li.menu-item-group[data-collapse-mode="collapsed"] + li.menu-item-group-more{display:none!important;}';
        $css .= '#menu-items-block .fgz-section li.menu-item-block.fgz-moved .menu-item-icon{background-color:currentColor!important;}';
        $css .= '#menu-items-block .fgz-section li.menu-item-block.fgz-moved .menu-item-icon-box > span{background-color:currentColor!important;}';

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

        // Анти-FOUC: пока не построены разделы — меню скрыто
        $css .= '#menu-items-block:not(.fgz-ready) .menu-items-body-inner{visibility:hidden;}';
        $css .= '#menu-items-block.fgz-ready .menu-items-body-inner{visibility:visible;}';

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
     * JS-коллектор штатных пунктов меню.
     *
     * Собирает со страницы пункты и группы и отправляет их на
     * /bitrix/admin/fg_menuz_collect.php.
     *
     * @return string
     */
    private static function collectJs(): string
    {
        return <<<'JS'
(function () {
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
     * Клиентский JS, перестраивающий меню.
     *
     * Создаёт разделы и пункты, переносит штатные пункты в разделы,
     * скрывает скрытые пункты и применяет общий порядок.
     *
     * @return string
     */
    private static function clientJs(): string
    {
        return <<<'JS'
(function () {
    if (window.__fgzClient) return;
    window.__fgzClient = 1;

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

    // Разовая очистка старых ключей без userId (fgz-mode-XXX)
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

    function block() { return document.getElementById('menu-items-block'); }
    function menuUl() {
        var b = block();
        if (!b) return null;
        return b.querySelector('.menu-items-body-inner ul.menu-items')
            || b.querySelector('ul.menu-items')
            || b.querySelector('.menu-items-body-inner');
    }
    function safeId(v) { return ('' + v).replace(/[^a-zA-Z0-9_\-]/g, ''); }

    // // Раз в сессию браузера (при новом заходе) сбрасываем localStorage разделов
    // (function resetModesOnce() {
    //     try {
    //         if (!sessionStorage.getItem('fgz-session-init')) {
    //             sessionStorage.setItem('fgz-session-init', '1');
    //             Object.keys(localStorage).forEach(function(k){
    //                 if (k.indexOf('fgz-mode-') === 0) localStorage.removeItem(k);
    //             });
    //         }
    //     } catch (e) {}
    // })();

    function modeKey(id) {
        return 'fgz-mode-' + USER_ID + '-' + id;
    }

    function savedMode(id) {
        try {
            var stored = localStorage.getItem(modeKey(id));
            if (stored === 'collapsed') return 'collapsed';
            if (stored === 'expanded') return 'expanded';
            return 'collapsed';
        } catch (e) { return 'collapsed'; }
    }

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

    function watchCollapse(header) {
        if (header.__fgzWatch) return;
        header.__fgzWatch = 1;
        var id = header.getAttribute('data-id');
        if (!id) return;
        var btn = header.querySelector('.menu-item-link');
        if (!btn) return;

        function sync() {
            var exp = btn.getAttribute('aria-expanded');
            if (exp !== 'true' && exp !== 'false') return;
            var mode = exp === 'true' ? 'expanded' : 'collapsed';
            header.setAttribute('data-collapse-mode', mode);
            try { localStorage.setItem(modeKey(id), mode); } catch (e) {}
        }

        new MutationObserver(sync).observe(btn, { attributes: true, attributeFilter: ['aria-expanded'] });
        sync();
    }

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
            try { localStorage.setItem(modeKey(header.getAttribute('data-id')), mode); } catch (ex) {}
        }, true);
    }

    function makeCustom(it) {
        var li = document.createElement('li');
        li.className = 'menu-item-block fgz-custom';
        li.setAttribute('data-status', 'show');
        li.setAttribute('data-id', 'fgz_custom_' + safeId(it.link || it.title));
        li.setAttribute('data-role', 'item');
        li.setAttribute('data-type', 'custom');
        li.setAttribute('data-storage', '');
        li.setAttribute('data-link', it.link || '');
        li.setAttribute('data-all-links', '');
        li.setAttribute('data-delete-perm', 'N');
        li.setAttribute('data-new-page', 'N');

        if (it.counter) li.setAttribute('data-counter-id', it.counter);
        var a = document.createElement('a');
        a.className = 'menu-item-link';
        a.href = it.link || '#';

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
        return li;
    }

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

    function apply() {
        var ul = menuUl();
        if (!ul) return;
        if (obs) obs.disconnect();
        try {
            // Скрываем пункты из списка hidden
            // Ищем по всему #menu-items-block — включая li, уже перенесённые в разделы
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
                        } else {
                            var ncid = 'fgz_custom_' + safeId(nit.link || nit.title);
                            if (!nativeUl.querySelector('li[data-id="' + ncid + '"]')) {
                                nativeUl.appendChild(makeCustom(nit));
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

                // Bitrix registerItem вешает jsDD на li и потом перетаскивает их
                // по b_user_option.menu_sort. Отключаем drag для всех li в разделе.
                (function disableDragInside(innerUlLocal){
                    if (!window.jsDD || typeof jsDD.unregisterObject !== 'function') return;
                    if (!innerUlLocal) return;
                    innerUlLocal.querySelectorAll('li.menu-item-block').forEach(function(li){
                        try { jsDD.unregisterObject(li); } catch (e) {}
                    });
                })(innerUl); 

                var innerUl = content.querySelector('ul.menu-item-group-more-ul');
                if (!innerUl) continue;

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
                    } else {
                        var cid = 'fgz_custom_' + safeId(it.link || it.title);
                        if (innerUl.querySelector('li[data-id="' + cid + '"]')) continue;
                        innerUl.appendChild(makeCustom(it));
                    }
                }

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

            // // Сворачиваем native-разделы Bitrix (Совместная работа, Приложения)
            // (function collapseNativeGroups(){
            //     var b = block();
            //     if (!b) return;
            //     b.querySelectorAll('li.menu-item-group:not(.fgz-section)').forEach(function(li){
            //         if (li.getAttribute('data-collapse-mode') !== 'collapsed') {
            //             li.setAttribute('data-collapse-mode', 'collapsed');
            //         }
            //         var btn = li.querySelector('.menu-item-link');
            //         if (btn && btn.getAttribute('aria-expanded') !== 'false') {
            //             btn.setAttribute('aria-expanded', 'false');
            //         }
            //     });
            // })();

            // Отключаем drag&drop у всех пунктов меню
            (function disableDrag(){
                if (!window.jsDD || typeof jsDD.unregisterObject !== 'function') return;
                var b = block();
                if (!b) return;
                b.querySelectorAll('li.menu-item-block, li.menu-item-group').forEach(function(li){
                    try { jsDD.unregisterObject(li); } catch (e) {}
                });
            })();

            // Шильдики для КОРНЕВЫХ пунктов
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
                            // находим li раздела (header + content)
                            var secId = item.id;
                            var header = document.getElementById('bx_left_menu_' + secId)
                                      || document.getElementById('bx_left_menu_fgz_' + secId)
                                      || ulRoot.querySelector('li.fgz-section[data-fgz-id="' + secId + '"]')
                                      || ulRoot.querySelector('li.fgz-section[data-fgz-id="fgz_' + secId + '"]')
                                      || ulRoot.querySelector('li.menu-item-group[data-id="' + secId + '"]');
                            if (!header) return;
                            var content = header.nextElementSibling;
                            while (content && !content.classList.contains('menu-item-group-more')) {
                                content = content.nextElementSibling;
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
        } finally {
            var b = block();
            if (b) b.classList.add('fgz-ready');
            connect();
        }
    }

    function connect() {
        var b = block();
        if (!b) return;
        if (!obs) obs = new MutationObserver(function () { schedule(); });
        obs.observe(b, { childList: true, subtree: true });
    }

    function schedule() {
        if (pending) return;
        pending = true;
        setTimeout(function () { pending = false; apply(); }, 150);
    }

    function start() {
        apply();
        setTimeout(apply, 500);
        setTimeout(apply, 1500);
    }

    var tries = 0;
    (function wait() {
        var ul = menuUl();
        if (!ul) {
            if (++tries > 150) return;
            return setTimeout(wait, 100);
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
}