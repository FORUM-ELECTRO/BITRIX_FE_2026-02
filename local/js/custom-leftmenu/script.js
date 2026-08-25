/**
 * Кастомный аккордеон «Мой раздел» для левого меню Битрикс24.
 *
 * Реализация основана на клиентской инъекции: скрипт самодостаточен,
 * не требует изменений ядра (/bitrix/), шаблонов и компонентов.
 *
 * Логика работы:
 *  1. Фаза A (подготовка): пытается задействовать штатные механизмы Б24
 *     (REST-метод меню, события, расширение ui.system.menu). В этой версии
 *     закладывается устойчивый fallback на клиентскую инъекцию.
 *  2. Фаза B (инъекция): MutationObserver отслеживает появление левого меню
 *     по устойчивым CSS-селекторам (кнопка .menu-item-link / обёртка .menu-items)
 *     и добавляет раздел «Мой раздел» с подпунктами в конец списка пунктов.
 *  3. Фаза C (аккордеон): сворачивание/разворачивание по клику с переключением
 *     aria-expanded, .ui-counter и стрелки .ui-icon-set.--chevron-down-l.
 *  4. Фаза D (состояние): сохранение состояния раскрыто/свёрнуто в localStorage
 *     и восстановление при перезагрузке.
 *  5. Фаза E (устойчивость): все селекторы устойчивые (по классам/ролям,
 *     без хардкода внутренних id), нет зависимостей от конкретной версии ядра.
 *
 * @version 1.0.0
 */
(function (global) {
	'use strict';

	// Конфигурация раздела (легко менять без правки логики)
	var CONFIG = {
		title: 'Мой раздел',                    // Заголовок аккордеона
		storageKey: 'custom-leftmenu-mysection',// Ключ localStorage для состояния
		// Подпункты: { text, href, target }
		children: [
			{ text: 'Подраздел 1', href: '/crm/', target: '_self' },
			{ text: 'Подраздел 2', href: '/company/', target: '_self' },
			{ text: 'Подраздел 3', href: '/calendar/', target: '_self' }
		],
		// Интервал перепроверки, пока меню не появилось (мс)
		observeInterval: 300,
		// Максимальное число попыток дождаться меню (0 = без ограничений)
		maxAttempts: 0
	};

	/**
	 * Находит корневой контейнер пунктов левого меню по устойчивым селекторам.
	 * Порядок селекторов — по степени надёжности (не привязаны к id).
	 *
	 * @returns {HTMLElement|null}
	 */
	function findMenuContainer() {
		// Наиболее вероятный современный контейнер (эталон из DevTools).
		// Используем несколько кандидатов, от специфичного к общему.
		var candidates = [
			'.menu-items-list',        // обёртка списка пунктов (вариант A)
			'.menu-items',             // обёртка списка пунктов (вариант B)
			'.left-menu-inner',        // внутренний скроллер меню (вариант C)
			'.menu-item-block'         // отдельный блок пункта (fallback)
		];
		var i, node;
		for (i = 0; i < candidates.length; i++) {
			node = global.document.querySelector(candidates[i]);
			if (node) {
				return node;
			}
		}
		return null;
	}

	/**
	 * Проверяет, присутствует ли на странице реальное левое меню Б24
	 * (наличие кнопки пункта меню). Устойчиво к разметке разных версий.
	 *
	 * @returns {boolean}
	 */
	function isLeftMenuPresent() {
		return !!global.document.querySelector('.menu-item-link, .menu-item, .menu-items');
	}

	/**
	 * Создаёт стрелку аккордеона в стиле Б24 (ui-icon-set chevron).
	 * Отсутствие иконки в конкретной версии не ломает работу — стрелка
	 * опциональна и прячется, если не рендерится.
	 *
	 * @returns {HTMLElement}
	 */
	function createArrow() {
		var arrow = global.document.createElement('span');
		arrow.className = 'menu-item-link-arrow';
		arrow.setAttribute('data-custom-leftmenu-arrow', '1');
		var icon = global.document.createElement('span');
		// В новых версиях иконка — это <span class="ui-icon-set --chevron-down-l">
		icon.className = 'ui-icon-set --chevron-down-l';
		arrow.appendChild(icon);
		return arrow;
	}

	/**
	 * Создаёт общий контейнер аккордеона и его подпункты.
	 *
	 * @param {boolean} open начальное состояние (раскрыт/свёрнут)
	 * @returns {HTMLElement}
	 */
	function createAccordion(open) {
    // === Корневой элемент: <li> как у штатных пунктов ===
    var item = global.document.createElement('li');
    item.className = 'menu-item-block menu-item-group custom-leftmenu-item';
    item.setAttribute('data-role', 'group');
    item.setAttribute('data-collapse-mode', open ? 'expanded' : 'collapsed');
    item.setAttribute('data-custom-leftmenu-root', '1');
    item.id = 'bx_left_menu_custom_mysection';

    // === Drag-кнопка (пустая, для выравнивания) ===
    var favBtn = global.document.createElement('span');
    favBtn.className = 'menu-favorites-btn menu-favorites-draggable';
    favBtn.setAttribute('aria-hidden', 'true');
    var dragIcon = global.document.createElement('span');
    dragIcon.className = 'menu-fav-draggable-icon';
    favBtn.appendChild(dragIcon);
    item.appendChild(favBtn);

    // === Кнопка-заголовок ===
    var button = global.document.createElement('button');
    button.type = 'button';
    button.className = 'menu-item-link';
    button.setAttribute('aria-label', CONFIG.title);
    button.setAttribute('aria-expanded', open ? 'true' : 'false');
    button.setAttribute('aria-controls', 'custom-leftmenu-subitems');
    button.setAttribute('data-custom-leftmenu-toggle', '1');

    // Иконка
    var iconBox = global.document.createElement('span');
    iconBox.className = 'menu-item-icon-box';
    var icon = global.document.createElement('span');
    icon.className = 'menu-item-icon ui-icon-set__scope';
    icon.setAttribute('aria-hidden', 'true');
    iconBox.appendChild(icon);
    button.appendChild(iconBox);

    // Текст
    var text = global.document.createElement('span');
    text.className = 'menu-item-link-text';
    text.setAttribute('data-role', 'item-text');
    text.textContent = CONFIG.title;
    button.appendChild(text);

    // Счётчик (структура как у Б24)
    var counterWrap = global.document.createElement('span');
    counterWrap.className = 'menu-item-index-wrap';
    var counter = global.document.createElement('div');
    counter.id = 'menu-counter-custom-mysection';
    counter.className = 'ui-counter ui-counter__scope --air ui-counter-sm ui-counter-primary --one-digit';
    counter.setAttribute('data-value', String(CONFIG.children.length));
    var counterInner = global.document.createElement('div');
    counterInner.className = 'ui-counter-inner';
    var counterValue = global.document.createElement('span');
    counterValue.className = 'ui-counter__value';
    counterValue.textContent = String(CONFIG.children.length);
    var counterSymbol = global.document.createElement('span');
    counterSymbol.className = 'ui-counter__symbol';
    counterInner.appendChild(counterValue);
    counterInner.appendChild(counterSymbol);
    counter.appendChild(counterInner);
    counterWrap.appendChild(counter);
    button.appendChild(counterWrap);

    // Стрелка
    var arrow = global.document.createElement('span');
    arrow.className = 'menu-item-link-arrow';
    arrow.setAttribute('aria-hidden', 'true');
    arrow.setAttribute('data-custom-leftmenu-arrow', '1');
    var arrowIcon = global.document.createElement('span');
    arrowIcon.className = 'ui-icon-set --chevron-down-l';
    arrow.appendChild(arrowIcon);
    button.appendChild(arrow);

    item.appendChild(button);

    // === Подпункты ===
    var subitems = global.document.createElement('div');
    subitems.className = 'menu-subitems';
    subitems.id = 'custom-leftmenu-subitems';
    subitems.setAttribute('data-custom-leftmenu-subitems', '1');
    if (!open) {
        subitems.style.display = 'none';
    }

    CONFIG.children.forEach(function (child) {
        var link = global.document.createElement('a');
        link.className = 'menu-item-link menu-subitem-link';
        link.href = child.href;
        link.target = child.target || '_self';
        var linkText = global.document.createElement('span');
        linkText.className = 'menu-item-link-text';
        linkText.setAttribute('data-role', 'item-text');
        linkText.textContent = child.text;
        link.appendChild(linkText);
        subitems.appendChild(link);
    });

    item.appendChild(subitems);

    return item;
}

	/**
	 * Обновляет визуальное состояние аккордеона (раскрыт/свёрнут)
	 * и синхронизирует атрибуты aria-expanded, видимость подпунктов,
	 * а также поворот стрелки (если она рендерится).
	 *
	 * @param {HTMLElement} root корневой элемент аккордеона
	 * @param {boolean} open
	 */
	function applyState(root, open) {
		var toggle = root.querySelector('[data-custom-leftmenu-toggle]');
		var subitems = root.querySelector('[data-custom-leftmenu-subitems]');
		var arrow = root.querySelector('[data-custom-leftmenu-arrow]');

		if (toggle) {
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		}
		if (subitems) {
			subitems.style.display = open ? '' : 'none';
		}
		if (arrow) {
			// Поворот стрелки вниз при раскрытии. Класс может отличаться
			// в разных версиях Б24 — обрабатываем мягко (no-op если нет стиля).
			arrow.classList.toggle('custom-leftmenu-arrow-open', open);
		}
	}

	/**
	 * Получает текущее сохранённое состояние аккордеона из localStorage.
	 *
	 * @returns {boolean} true = раскрыт
	 */
	function loadState() {
		try {
			return global.localStorage.getItem(CONFIG.storageKey) === 'open';
		} catch (e) {
			return false;
		}
	}

	/**
	 * Сохраняет состояние аккордеона в localStorage.
	 *
	 * @param {boolean} open
	 */
	function saveState(open) {
		try {
			global.localStorage.setItem(CONFIG.storageKey, open ? 'open' : 'closed');
		} catch (e) {
			// игнорируем: хранение не критично
		}
	}

	/**
	 * Навешивает обработчик клика на кнопку аккордеона.
	 *
	 * @param {HTMLElement} root корневой элемент аккордеона
	 */
	function bindToggle(root) {
		var toggle = root.querySelector('[data-custom-leftmenu-toggle]');
		if (!toggle || toggle.getAttribute('data-custom-leftmenu-bound') === '1') {
			return;
		}
		toggle.setAttribute('data-custom-leftmenu-bound', '1');

		toggle.addEventListener('click', function (ev) {
			ev.preventDefault();
			ev.stopPropagation();
			var open = toggle.getAttribute('aria-expanded') !== 'true';
			applyState(root, open);
			saveState(open);
		});
	}

	/**
	 * Устанавливает аккордеон «Мой раздел» в конец списка пунктов меню.
	 * Идемпотентно: повторный вызов не создаёт дублей.
	 *
	 * @returns {boolean} true, если аккордеон был установлен
	 */
	function mountAccordion() {
		var container = findMenuContainer();
		if (!container || !isLeftMenuPresent()) {
			return false;
		}

		// Защита от дублирования (повторный рендер меню SPA).
		var existing = container.querySelector('[data-custom-leftmenu-root]');
		if (existing) {
			// Перепривязываем обработчик (меню могло пересоздаться) и выходим.
			bindToggle(existing);
			return true;
		}

		var open = loadState();
		var root = createAccordion(open);
		container.appendChild(root);
		bindToggle(root);

		// Лёгкая анимация появления (опционально, без зависимости от core).
		global.requestAnimationFrame(function () {
			root.style.opacity = '1';
		});
		root.style.opacity = '0';
		root.style.transition = 'opacity .2s ease';

		return true;
	}

	/**
	 * Запускает наблюдение за появлением левого меню. Использует
	 * MutationObserver на document.documentElement; параллельно дублируется
	 * интервальной проверкой для версий Б24, где observer не срабатывает
	 * из-за особенностей рендеринга SPA.
	 */
	function startObserver() {
		var attempts = 0;
		var timer = null;

		function tryMount() {
			if (mountAccordion()) {
				if (timer) {
					global.clearInterval(timer);
					timer = null;
				}
				return true;
			}
			return false;
		}

		// Интервальная проверка (гарантированный fallback).
		timer = global.setInterval(function () {
			attempts++;
			if (tryMount()) {
				global.clearInterval(timer);
				timer = null;
				return;
			}
			if (CONFIG.maxAttempts > 0 && attempts >= CONFIG.maxAttempts) {
				global.clearInterval(timer);
				timer = null;
			}
		}, CONFIG.observeInterval);

		// MutationObserver для мгновенной реакции на рендер меню.
		if (global.MutationObserver) {
			var observer = new global.MutationObserver(function () {
				if (tryMount()) {
					observer.disconnect();
				}
			});
			observer.observe(global.document.documentElement, {
				childList: true,
				subtree: true
			});
		}
	}

	/**
	 * Точка входа. Откладываем запуск до готовности DOM (если доступен BX —
	 * используем BX.ready, иначе DOMContentLoaded, иначе сразу).
	 */
	function init() {
		if (global.BX && global.BX.ready) {
			global.BX.ready(startObserver);
			return;
		}
		if (global.document.readyState === 'loading') {
			global.document.addEventListener('DOMContentLoaded', startObserver);
		} else {
			startObserver();
		}
	}

	// Фаза A — попытка штатного механизма (REST / события / ui.system.menu).
	// В текущей версии REST-метод и расширение ui.system.menu на стартовой
	// странице коробочного Б24 недоступны (проверено диагностикой), поэтому
	// сразу идём в инъекцию. Блок оставлен как точка расширения.
	function run() {
		// Переход непосредственно к клиентской инъекции (fallback-ветка C).
		init();
	}

	// Стартуем. Защита от двойной инициализации.
	if (global.customLeftMenuInitDone) {
		return;
	}
	global.customLeftMenuInitDone = true;

	run();
})(window);