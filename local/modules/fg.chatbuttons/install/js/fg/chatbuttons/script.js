/**
 * Кнопки чатов в компактной навигации мессенджера (fg.chatbuttons).
 *
 * Список кнопок, их иконки и подписи приходят из PHP-настроек модуля. Компактная навигация принадлежит ядру и перерисовывается Vue.
 */
(() => {
	const EXTENSION_ID = 'fg.chatbuttons';
	const NAVIGATION_CONTAINER = '.bx-im-compact-navigation__container';
	const NAVIGATION_ITEMS = '.bx-im-compact-navigation__items';
	const NAVIGATION_ICON = '.bx-im-compact-navigation__icon';
	const NAVIGATION_COUNTER = '.bx-im-compact-navigation__icon-counter';
	const ICON_NODE = '.ui-icon-set';
	const BUTTON_CLASS = 'fg-im-chat-buttons__item';
	const COUNTER_LIMIT = 100;

	/**
	 * Соответствие типа кнопки идентификатору навигации и счётчику ядра.
	 */
	const TYPES = {
		tasks: {
			nav: 'tasksTask',
			counter: 'counters/getTotalTaskCounter',
			feature: 'isTasksRecentListAvailable',
		},
		projects: {
			nav: 'collab',
			counter: 'counters/getTotalCollabCounter',
			feature: 'collabAvailable',
		},
		chats: {
			nav: 'chat',
			counter: 'counters/getTotalChatCounter',
		},
		channels: {
			nav: 'channel',
			counter: (getters) => getters['counters/getCounterByRecentType']('channel'),
		},
	};

	/** @type {Map<string, HTMLElement>} Кнопки по id типа: узлы переживают перерисовку навигации. */
	const buttons = new Map();

	/** @type {Map<string, number>} Последние значения счётчиков, чтобы не терять их при перерисовке. */
	const counters = new Map();

	let countersWatched = false;

	const getClass = (path) => BX.Reflection.getClass(path);

	const getStore = () => getClass('BX.Messenger.v2.Application.Core')?.getStore();

	const getMessenger = () => getClass('BX.Messenger.v2.Lib.Messenger');

	const isFeatureAvailable = (feature) => {
		const FeatureManager = getClass('BX.Messenger.v2.Lib.FeatureManager');
		const Feature = getClass('BX.Messenger.v2.Lib.Feature');

		if (!FeatureManager || !Feature || !Feature[feature])
		{
			return false;
		}

		return Boolean(FeatureManager.isFeatureAvailable(Feature[feature]));
	};

	const formatCounter = (value) => (value >= COUNTER_LIMIT ? '99+' : String(value));

	const getItems = () => BX.Extension.getSettings(EXTENSION_ID).get('items', []);

	const openItem = (navigationId) => {
		void getMessenger()?.openNavigationItem({id: navigationId});
	};

	const paint = (node, counter) => {
		const counterNode = node.querySelector(NAVIGATION_COUNTER);
		const iconNode = node.querySelector(ICON_NODE);
		const hasCounter = counter > 0;

		if (!counterNode || !iconNode)
		{
			return;
		}

		counterNode.textContent = hasCounter ? formatCounter(counter) : '';
		counterNode.hidden = !hasCounter;

		iconNode.style.setProperty(
			'--ui-icon-set__icon-color',
			hasCounter ? 'var(--ui-color-design-outline-content)' : 'var(--ui-color-design-outline-na-content)',
		);
	};

	/**
	 * Известное значение счётчика.
	 */
	const paintLast = (id) => {
		const node = buttons.get(id);

		if (node?.isConnected)
		{
			paint(node, counters.get(id) ?? 0);
		}
	};

	const createButton = (item) => {
		const icon = BX.Tag.render`<div class="ui-icon-set ${item.icon}"></div>`;
		const counter = BX.Tag.render`<div class="bx-im-compact-navigation__icon-counter" hidden></div>`;
		const type = TYPES[item.id];

		return BX.Tag.render`
			<div
				class="bx-im-compact-navigation__icon --ui-hoverable ${BUTTON_CLASS} ${BUTTON_CLASS}--${item.id}"
				title="${BX.Text.encode(item.title)}"
				onclick="${() => openItem(type.nav)}"
			>
				${icon}
				${counter}
			</div>
		`;
	};

	/**
	 * Подписывается на счётчики ядра один раз за жизнь страницы.
	 */
	const watchCounters = () => {
		const store = getStore();

		if (countersWatched || !store)
		{
			return;
		}

		countersWatched = true;

		for (const item of getItems())
		{
			const type = TYPES[item.id];

			if (!type)
			{
				continue;
			}

			try
			{
				store.watch(
					(state, getters) => typeof type.counter === 'function'
						? type.counter(getters)
						: getters[type.counter],
					(value) => {
						counters.set(item.id, Number(value) || 0);
						paintLast(item.id);
					},
					{immediate: true},
				);
			}
			catch
			{
				// Счётчик этого раздела ещё не готов
			}
		}
	};

	const isTypeVisible = (item) => Boolean(TYPES[item.id])
		&& (!TYPES[item.id].feature || isFeatureAvailable(TYPES[item.id].feature));

	/**
	 * Вставляет кнопки в начало компактной навигации.
	 */
	const mount = () => {
		const anchor = document.querySelector(`${NAVIGATION_CONTAINER} ${NAVIGATION_ITEMS} > ${NAVIGATION_ICON}`);

		// Мессенджер ещё не инициализирован либо его нет на этой странице.
		if (!anchor || !getMessenger())
		{
			return;
		}

		let previous = anchor;

		for (const item of getItems())
		{
			if (!isTypeVisible(item))
			{
				continue;
			}

			if (!buttons.has(item.id))
			{
				buttons.set(item.id, createButton(item));
			}

			const node = buttons.get(item.id);

			if (node.previousElementSibling !== previous)
			{
				previous.after(node);
			}

			paintLast(item.id);

			previous = node;
		}

		watchCounters();
	};

	/** @type {boolean} Перерисовка уже поставлена в очередь кадра. */
	let queued = false;

	/**
	 * Ставим перерисовку в очередь кадра.
	 */
	const schedule = () => {
		if (queued)
		{
			return;
		}

		queued = true;

		window.requestAnimationFrame(() => {
			queued = false;

			try
			{
				mount();
			}
			catch (error)
			{
				BX.debug(error, 'fg.chatbuttons');
			}
		});
	};

	BX.ready(() => {
		try
		{
			if (!getItems().length)
			{
				return;
			}

			schedule();

			new MutationObserver(schedule).observe(document.body, {childList: true, subtree: true});
		}
		catch (error)
		{
			// Ошибка модуля не должна обрывать очередь чужих обработчиков BX.ready.
			BX.debug(error, 'fg.chatbuttons');
		}
	});
})();
