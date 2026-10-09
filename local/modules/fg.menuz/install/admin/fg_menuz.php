<?php

/**
 * Страница настройки разделов левого меню (модуль fg.menuz).
 *
 * Вкладки: разделы и пункты, порядок в меню, скрытые пункты.
 *
 * @package FG\Menuz
 */

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\UI\Extension::load([
    'ui.icon-set.main',
    'ui.icon-set.crm',
    'ui.icon-set.actions',
    'ui.icon-set.outline',
    'ui.icon-set.solid',
    'ui.icons.base',
    'ui.icons.b24',
    'ui.icons.service',
    'ui.icons.disk',
    'ui.buttons.icons',
]);

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use FG\Menuz\Sections;
use FG\Menuz\Settings;

global $APPLICATION, $USER;

if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm("Доступ запрещён");
}

Loader::includeModule("fg.menuz");

// Фразы берём из каталога модуля: страница выполняется из /bitrix/admin/,
// поэтому loadMessages(__FILE__) там lang-файл не находит
$fgzSelf = \Bitrix\Main\Loader::getLocal("modules/fg.menuz/install/admin/fg_menuz.php");
Loc::loadMessages($fgzSelf ?: __FILE__);

$module_id = "fg.menuz";
$saved = false;

// Доступные штатные иконки Bitrix24 для пикера
$ICONS = [
    // Общее
    'folder'      => 'Папка',
    'link'        => 'Ссылка',
    'star'        => 'Звезда',
    'heart'       => 'Сердце',
    'home'        => 'Дом',
    'info'        => 'Инфо',
    'flag'        => 'Флаг',
    'bookmark'    => 'Закладка',
    'tag'         => 'Тег',
    'pin'         => 'Пин',
    'bell'        => 'Уведомление',
    'clock'       => 'Часы',
    'calendar'    => 'Календарь',
    'check'       => 'Галочка',
    'plus'        => 'Плюс',
    'search'      => 'Поиск',
    'filter'      => 'Фильтр',
    'settings'    => 'Настройки',
    'user'        => 'Пользователь',
    'group'       => 'Группа',
    'globe'       => 'Глобус',
    'map'         => 'Карта',
    'list'        => 'Список',
    'grid'        => 'Сетка',
    'form'        => 'Форма',
    'clipboard'   => 'Буфер',
    'document'    => 'Документ',
    'book'        => 'Книга',
    'code'        => 'Код',
    'cube'        => 'Куб',
    'puzzle'      => 'Пазл',
    'package'     => 'Посылка',
    'archive'     => 'Архив',
    'qrcode'      => 'QR-код',
    'chart'       => 'График',
    'hierarchy'   => 'Иерархия',
    'kanban'      => 'Канбан',
    'flow'        => 'Поток',
    'target'      => 'Цель',
    'idea'        => 'Идея',
    'rocket'      => 'Ракета',
    'shield'      => 'Щит',
    'wrench'      => 'Гаечный ключ',
    'brush'       => 'Кисть',
    // Связь
    'chat'        => 'Чат',
    'phone'       => 'Телефон',
    'mail'        => 'Почта',
    'camera'      => 'Камера',
    'video'       => 'Видео',
    'microphone'  => 'Микрофон',
    'music'       => 'Музыка',
    'headset'     => 'Гарнитура',
    // Техно
    'signal'      => 'Сигнал',
    'wifi'        => 'Wi-Fi',
    'cloud'       => 'Облако',
    'database'    => 'База данных',
    'server'      => 'Сервер',
    'terminal'    => 'Терминал',
    'bug'         => 'Баг',
    'lock'        => 'Замок',
    'key'         => 'Ключ',
    // Деловое
    'crm'         => 'CRM',
    'tasks'       => 'Задачи',
    'disk'        => 'Диск',
    'company'     => 'Компания',
    'money'       => 'Деньги',
    'wallet'      => 'Кошелёк',
    'calculator'  => 'Калькулятор',
    'percent'     => 'Процент',
    'gift'        => 'Подарок',
    'cart'        => 'Корзина',
    'bag'         => 'Сумка',
    'shop'        => 'Магазин',
    'truck'       => 'Грузовик',
    'factory'     => 'Завод',
    'building'    => 'Здание',
    'bank'        => 'Банк',
    'gavel'       => 'Молоток судьи',
    'scale'       => 'Весы',
    'clipboard'   => 'Опрос',
    // Медицина/наука/образование
    'hospital'    => 'Больница',
    'school'      => 'Школа',
    'stethoscope' => 'Стетоскоп',
    'leaf'        => 'Лист',
    'recycle'     => 'Переработка',
    'diamond'     => 'Алмаз',
    'crown'       => 'Корона',
    'medal'       => 'Медаль',
    'trophy'      => 'Кубок',
    // Транспорт
    'plane'       => 'Самолёт',
    'car'         => 'Машина',
    'bus'         => 'Автобус',
    'train'       => 'Поезд',
    'ship'        => 'Корабль',
    'bike'        => 'Велосипед',
    'walk'        => 'Пешком',
    'run'         => 'Бег',
    'swim'        => 'Плавание',
    // Природа
    'mountain'    => 'Гора',
    'tree'        => 'Дерево',
    'flower'      => 'Цветок',
    'sun'         => 'Солнце',
    'sunrise'     => 'Рассвет',
    'moon'        => 'Луна',
    'rainbow'     => 'Радуга',
    'snowflake'   => 'Снежинка',
    'umbrella'    => 'Зонт',
    'fire'        => 'Огонь',
    'flash'       => 'Молния',
    'battery'     => 'Батарея',
    'plug'        => 'Розетка',
    'crane'       => 'Кран',
    // Прочее
    'smile'       => 'Улыбка',
    'thumbs-up'   => 'Палец вверх',
    'ticket'      => 'Билет',
	// Добавление
    'crm-group'         => 'CRM: группа',
    'funnel-1'          => 'CRM: воронка',
    'funnel-2'          => 'CRM: воронка 2',
    'funnels'           => 'CRM: воронки',
    'deal'              => 'CRM: сделка',
    'deal-1'            => 'CRM: сделка 1',
    'deal-plus'         => 'CRM: сделка +',
    'lead'              => 'CRM: лид',
    'company'           => 'CRM: компания',
    'contact'           => 'CRM: контакт',
    'customer-card'     => 'CRM: карточка клиента',
    'customer-card-1'   => 'CRM: карточка клиента 1',
    'customer-cards'    => 'CRM: карточки клиентов',
    'proposal'          => 'CRM: коммерческое предложение',
    'proposal-done'     => 'CRM: КП выполнено',
    'commercial-offer'  => 'CRM: коммерческое предложение',
    'invoice'           => 'CRM: счёт',
    'crm-payment'       => 'CRM: оплата',
    'crm-letters'       => 'CRM: письма',
    'crm-map'           => 'CRM: карта',
    'crm-search'        => 'CRM: поиск',
    'timeline'          => 'CRM: таймлайн',
    'stages'            => 'CRM: стадии',
    'stage'             => 'CRM: стадия',
    'business-process'  => 'CRM: бизнес-процесс',
    'smart-activities'  => 'CRM: умные дела',
    'cart'              => 'CRM: корзина',
    'wallet'            => 'CRM: кошелёк',
	    // Главное / Навигация
    'home-page'      => 'Главная',
    'home'           => 'Дом',
    'feed'           => 'Лента',
    'feed-bold'      => 'Лента (жирная)',
    'tasks'          => 'Задачи',
    'calendar-1'     => 'Календарь',
    'calendar-2'     => 'Календарь (2)',
    'calendar-24'    => 'Календарь 24',
    'calendar-check' => 'Календарь-галочка',
    'calendar-slots' => 'Календарь со слотами',
    'clock-1'        => 'Часы',
    'clock-2'        => 'Часы (2)',
    'stopwatch'      => 'Секундомер',
    'alarm'          => 'Будильник',

    // Почта / Чаты
    'mail'           => 'Почта',
    'mail-2'         => 'Почта (2)',
    'mail-in'        => 'Входящее',
    'mail-out'       => 'Исходящее',
    'mail-read'      => 'Прочитано',
    'mail-reply'     => 'Ответ',
    'mail-money'     => 'Почта-деньги',
    'chat-button'    => 'Чат-кнопка',
    'chats-1'        => 'Чаты',
    'chats-2'        => 'Чаты (2)',
    'chats-3'        => 'Чаты (3)',
    'chat-message'   => 'Сообщение',
    'chats-persons'  => 'Чаты с людьми',
    'call-chat'      => 'Звонок-чат',
    'dialogue'       => 'Диалог',
    'dialogue-1'     => 'Диалог (2)',
    'chat-1'         => 'Чат',
    'send'           => 'Отправить',
    'send-file'      => 'Отправить файл',
    'feedback'       => 'Обратная связь',
    'add-chat'       => 'Создать чат',

    // Люди / Команда
    'person'              => 'Человек',
    'person-2-checks'     => 'Человек с галочками',
    'person-plus'         => 'Человек +',
    'person-plus-3'       => 'Человек + (3)',
    'person-check'        => 'Человек-галочка',
    'person-clock'        => 'Человек-часы',
    'person-clock-2'      => 'Человек-часы (2)',
    'person-flag'         => 'Человек-флаг',
    'person-letter'       => 'Человек-письмо',
    'person-message'      => 'Человек-сообщение',
    'person-phone'        => 'Человек-телефон',
    'person-call'         => 'Человек-звонок',
    'person-camera'       => 'Человек-камера',
    'person-handset'      => 'Человек-трубка',
    'person-location'     => 'Человек-локация',
    'persons-2'           => 'Люди (2)',
    'persons-3'           => 'Люди (3)',
    'persons-hand'        => 'Люди-руки',
    'persons-deny'        => 'Люди запрещено',
    'persons-storage'     => 'Люди-хранилище',
    'group'               => 'Группа',
    'company'             => 'Компания',
    'hr-automation'       => 'HR-автоматизация',
    'graduation-cap'      => 'Университет',
    'collab'              => 'Коллабы',
    'collaboration'       => 'Сотрудничество',

    // CRM
    'crm'                 => 'CRM',
    'crm-group'           => 'CRM-группа',
    'crm-letters'         => 'CRM-письма',
    'crm-map'             => 'CRM-карта',
    'crm-payment'         => 'CRM-оплата',
    'crm-search'          => 'CRM-поиск',
    'deal'                => 'Сделка',
    'deal-1'              => 'Сделка (2)',
    'deal-plus'           => 'Сделка +',
    'lead'                => 'Лид',
    'contact'             => 'Контакт',
    'customer-card'       => 'Карточка клиента',
    'customer-card-1'     => 'Карточка клиента (2)',
    'customer-card-plus'  => 'Карточка клиента +',
    'customer-cards'      => 'Карточки клиентов',
    'funnel'              => 'Воронка',
    'funnel-1'            => 'Воронка (2)',
    'funnel-2'            => 'Воронка (3)',
    'funnels'             => 'Воронки',
    'stages'              => 'Стадии',
    'stage'               => 'Стадия',
    'timeline'            => 'Таймлайн',
    'timeline-plus'       => 'Таймлайн +',
    'invoice'             => 'Счёт',
    'proposal'            => 'Коммерческое предложение',
    'proposal-done'       => 'КП выполнено',
    'commercial-offer'    => 'КП (2)',
    'bitrix-1c'           => 'Bitrix-1C',

    // Документы / Знания
    'disk'                  => 'Диск',
    'document'              => 'Документ',
    'document-plus'         => 'Документ +',
    'document-sign'         => 'Подпись',
    'document-stream'       => 'Поток документов',
    'file'                  => 'Файл',
    'file-2'                => 'Файл (2)',
    'file-3'                => 'Файл (3)',
    'file-check'            => 'Файл-галочка',
    'file-delete'           => 'Файл-удалить',
    'file-download'         => 'Файл-скачать',
    'file-upload'           => 'Файл-загрузить',
    'book-closed'           => 'Книга закрыта',
    'book-open-1'           => 'Книга открыта',
    'book-opened-with-arrow'=> 'Книга со стрелкой',
    'copy-file'             => 'Копировать файл',
    'add-file'              => 'Добавить файл',

    // Автоматизация
    'robot'                => 'Робот',
    'smart-process'        => 'Умный процесс',
    'business-process'     => 'Бизнес-процесс',
    'bp'                   => 'Бизнес-процесс (2)',
    'graphs-diagram'       => 'График-диаграмма',
    'sigma-summ'           => 'Сумма',
    'sigma-summ-a'         => 'Сумма (2)',
    'gantt-graphs'         => 'Диаграмма Ганта',
    'condition'            => 'Условие',
    'complete'             => 'Готово',
    'switch'               => 'Переключатель',
    'sequential-queue'     => 'Последовательная очередь',
    'parallel-queue'       => 'Параллельная очередь',
    'filter-1'             => 'Фильтр',
    'filter-2'             => 'Фильтр (2)',
    'filter-plus'          => 'Фильтр +',
    'list'                 => 'Список',
    'list-ai'              => 'Список AI',
    'waiting-list'         => 'Список ожидания',
    'waiting-points'       => 'Точки ожидания',
    'table'                => 'Таблица',
    'elements'             => 'Элементы',

    // Сервисы
    'settings'              => 'Настройки',
    'services'              => 'Сервисы',
    'apps'                  => 'Приложения',
    'market-1'              => 'Маркетплейс',
    'market-2'              => 'Маркетплейс (2)',
    'marketing'             => 'Маркетинг',
    'tag'                   => 'Тег',
    'sale-tag'              => 'Скидка',
    'gift'                  => 'Подарок',
    'calculator'            => 'Калькулятор',
    'wallet'                => 'Кошелёк',
    'credit-debit-card'     => 'Карта',
    'cart-with-cursor'      => 'Корзина',
    'cart'                  => 'Корзина (2)',
    'receipt-1'             => 'Чек',
    'receipt-2'             => 'Чек (2)',
    'check-receipt'         => 'Проверить чек',
    'payment-terminal'      => 'Платёжный терминал',
    'inventory-management'  => 'Склад',
    'shop-list'             => 'Список магазинов',
    'shop-seen'             => 'Просмотрено',
    'delivery-1'            => 'Доставка',
    'delivery-2'            => 'Доставка (2)',
    'delivery-car'          => 'Машина доставки',
    'box'                   => 'Коробка',
    'package'               => 'Посылка',
    'drawer'                => 'Ящик',

    // Сообщения / Уведомления
    'bell'                 => 'Уведомление',
    'bell-1'               => 'Уведомление (2)',
    'notifications-on'     => 'Уведомления вкл',
    'notifications-off'    => 'Уведомления выкл',
    'note'                 => 'Заметка',
    'note-circle'          => 'Заметка-круг',
    'warning'              => 'Предупреждение',
    'warning-circle'       => 'Предупреждение-круг',
    'info'                 => 'Инфо',
    'info-1'               => 'Инфо (2)',
    'info-circle'          => 'Инфо-круг',
    'help'                 => 'Помощь',
    'question'             => 'Вопрос',

    // Метки / Избранное
    'heart'                => 'Сердце',
    'like'                 => 'Нравится',
    'dislike'              => 'Не нравится',
    'favorite-0'           => 'Избранное (пусто)',
    'favorite-1'           => 'Избранное',
    'bookmark-1'           => 'Закладка',
    'pin-1'                => 'Пин',
    'pin-2'                => 'Пин (2)',
    'flag-1'               => 'Флаг',
    'flag-2'               => 'Флаг (2)',
    'flag-with-cross'      => 'Флаг с крестом',

    // AI / Bitrix24
    'copilot-ai'           => 'Copilot',
    'copilot-ai-1'         => 'Copilot (2)',
    'copilot-ai-2'         => 'Copilot (3)',
    'ai'                   => 'AI',
    'b-24'                 => 'Bitrix24',
    'prompt'               => 'Промпт',
    'prompts-library'      => 'Библиотека промптов',
    'create-prompt'        => 'Создать промпт',
    'save-prompt'          => 'Сохранить промпт',
    'magic-wand'           => 'Волшебная палочка',
    'magic-image'          => 'Магическое изображение',
    'idea-lamp'            => 'Идея',
    'rocket'               => 'Ракета',
    'light-bold'           => 'Молния',
    'light-bold-sparkle'   => 'Молния с искрой',
    'fire'                 => 'Огонь',

    // Безопасность
    'shield'               => 'Щит',
    'shield-2-plain'       => 'Щит простой',
    'shield-2-checked'     => 'Щит с галочкой',
    'shield-2-attention'   => 'Щит внимание',
    'shield-2-defended'    => 'Щит защита',
    'key'                  => 'Ключ',
    'lock'                 => 'Замок',
    'opened-eye'           => 'Открытый глаз',
    'crossed-eye'          => 'Закрытый глаз',

    // Гео / Время
    'map'                  => 'Карта',
    'earth'                => 'Земля',
    'earth-language'       => 'Земля-язык',
    'earth-time'           => 'Земля-время',
    'location-1'           => 'Локация',
    'location-2'           => 'Локация (2)',
    'location-plus'        => 'Локация +',
    'compass'              => 'Компас',
    'time-picker'          => 'Выбор времени',
    'sun'                  => 'Солнце',

    // Медиа
    'video-1'              => 'Видео',
    'video-2'              => 'Видео (2)',
    'video-3'              => 'Видео (3)',
    'video-and-chat'       => 'Видео и чат',
    'no-video'             => 'Без видео',
    'camera'               => 'Камера',
    'picture'              => 'Картинка',
    'no-picture'           => 'Без картинки',
    'microphone-on'        => 'Микрофон вкл',
    'microphone-off'       => 'Микрофон выкл',
    'sound-on'             => 'Звук вкл',
    'sound-off'            => 'Звук выкл',
    'headset'              => 'Гарнитура',
    'speakerphone'         => 'Громкая связь',
    'music-note-1'         => 'Нота',
    'music-note-2'         => 'Нота (2)',
    'music-note-3'         => 'Нота (3)',
    'screen-share'         => 'Демонстрация экрана',
    'demonstration-on-1'   => 'Демонстрация вкл',
    'demonstration-off'    => 'Демонстрация выкл',
    'record-video'         => 'Запись видео',

    // Прочее
    'folder-24'            => 'Папка',
    'folder-plus'          => 'Папка +',
    'folder-empty'         => 'Папка пустая',
    'folders'              => 'Папки',
    'suitcase'             => 'Портфель',
    'spanner'              => 'Гаечный ключ',
    'wrench'               => 'Гаечный ключ (2)',
    'trash-bin'            => 'Корзина',
    'cut'                  => 'Вырезать',
    'paste'                => 'Вставить',
    'copy-plates'          => 'Копировать',
    'check'                => 'Галочка',
    'circle-check'         => 'Галочка в круге',
    'circle-plus'          => 'Плюс в круге',
    'circle-minus'         => 'Минус в круге',
    'search-1'             => 'Поиск',
    'search-2'             => 'Поиск (2)',
    'qr-code-1'            => 'QR-код',
    'qr-code-2'            => 'QR-код (2)',
    'smile'                => 'Улыбка',
    'target'               => 'Цель',
    'target-1'             => 'Цель (2)',
    'target-timer'         => 'Цель-таймер',
    'sitemap'              => 'Карта сайта',
    'feed-bold'            => 'Лента (жирная)',
    'activity'             => 'Активность',
    'pulse'                => 'Пульс',
    'pulse-circle'         => 'Пульс в круге',
    'analytics'            => 'Аналитика',
    'trend-up'             => 'Тренд вверх',
    'trend-down'           => 'Тренд вниз',
];

// Сохранение формы
if ($_SERVER["REQUEST_METHOD"] === "POST" && check_bitrix_sessid()) {
    Settings::setFlag("enabled", ($_POST["enabled"] ?? "") === "Y");
    Settings::setFlag("hide_native", ($_POST["hide_native"] ?? "") === "Y");
    Settings::setFlag("hide_native_groups", ($_POST["hide_native_groups"] ?? "") === "Y");

    $sections = [];
    $ids    = (array)($_POST["sec_id"] ?? []);
    $titles = (array)($_POST["sec_title"] ?? []);
    $natives = (array)($_POST["sec_native"] ?? []);
    $icons  = (array)($_POST["sec_icon"] ?? []);
    $tItem  = (array)($_POST["sec_item_title"] ?? []);
    $tUrl   = (array)($_POST["sec_item_url"] ?? []);
    $tIcon  = (array)($_POST["sec_item_icon"] ?? []);
    $tIid   = (array)($_POST["sec_item_id"] ?? []);
    $tGrp   = (array)($_POST["sec_item_group"] ?? []);
    $tCnt   = (array)($_POST["sec_item_counter"] ?? []);
    $tSort  = (array)($_POST["sec_item_sort"] ?? []);
    $dels   = (array)($_POST["sec_delete"] ?? []);

    foreach ($titles as $i => $title) {
        $title = trim((string)$title);
        $isNative = !empty($natives[$i]);
        if ($title === "" && !$isNative) continue;

        $id = (int)($ids[$i] ?? 0);
        if ($id > 0 && !empty($dels[$id])) continue;
        // для native id — строка, поэтому доп. проверка не нужна

        $items = [];
        $t2 = (array)($tItem[$i] ?? []);
        $u2 = (array)($tUrl[$i] ?? []);
        $c2 = (array)($tIcon[$i] ?? []);
        $i2 = (array)($tIid[$i] ?? []);
        $g2 = (array)($tGrp[$i] ?? []);
        $n2 = (array)($tCnt[$i] ?? []);
        $s2 = (array)($tSort[$i] ?? []);
        foreach ($t2 as $j => $t) {
            $t  = trim((string)$t);
            $u  = trim((string)($u2[$j] ?? ""));
            $ic = trim((string)($c2[$j] ?? ""));
            $ii = trim((string)($i2[$j] ?? ""));
            $gr = trim((string)($g2[$j] ?? ""));
            $cn = trim((string)($n2[$j] ?? ""));
            if ($t === "" || $u === "") continue;
            $sortVal = $s2[$j] ?? "";
            $sortVal = ($sortVal === "" || $sortVal === null) ? null : (int)$sortVal;
            $items[] = [
                "title"   => mb_substr($t, 0, 200),
                "url"     => mb_substr($u, 0, 500),
                "icon"    => str_replace('#', '%23', mb_substr($ic, 0, 5000)),
                "id"      => mb_substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', $ii), 0, 100),
                "group"   => mb_substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', $gr), 0, 100),
                "counter" => mb_substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', $cn), 0, 100),
                "sort"    => $sortVal,
            ];
        }
        $isNative = !empty($natives[$i]);
        $sections[] = [
            "id"     => $isNative ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)($ids[$i] ?? "")) : ($id ?: (time() + $i)),
            "title"  => mb_substr($title, 0, 100),
            "icon"   => trim((string)($icons[$i] ?? "")),
            "items"  => $items,
            "native" => $isNative ?: null,
        ];
    }

    Sections::save($sections);

    // Сохраняем порядок
    $oTypes = (array)($_POST["order_type"] ?? []);
    $oIds   = (array)($_POST["order_id"] ?? []);
    $oSorts = (array)($_POST["order_sort"] ?? []);
    $order = [];
    foreach ($oTypes as $i => $t) {
        $t = (string)$t;
        $id = (string)($oIds[$i] ?? "");
        $s = (int)($oSorts[$i] ?? 100);
        if ($t === "" || $id === "") continue;
        $order[] = ["type" => $t, "id" => $id, "sort" => $s];
    }
    Settings::set("order", $order);

    // Переопределения иконок корневых пунктов
    $ro = [];
    $orderIcons = (array)($_POST["order_icon"] ?? []);
    foreach ($oIds as $idx => $id) {
        $type = (string)($oTypes[$idx] ?? "");
        if ($type !== "root") continue;
        $ic = trim((string)($orderIcons[$idx] ?? ""));
        if ($ic === "") continue;
        $ro[(string)$id] = ["icon" => $ic];
    }
    Settings::set("root_overrides", $ro);

    // Шильдики
    $badges = [];
    $bKeys   = (array)($_POST["badge_key"] ?? []);
    $bTexts  = (array)($_POST["badge_text"] ?? []);
    $bColors = (array)($_POST["badge_color"] ?? []);
    foreach ($bKeys as $idx => $k) {
        $k = trim((string)$k);
        if ($k === "") continue;
        $t = trim((string)($bTexts[$idx] ?? ""));
        $c = (string)($bColors[$idx] ?? "success");
        if ($t === "") continue;
        if (!in_array($c, ["success","warning","danger","info"], true)) $c = "success";
        $badges[$k] = ["text" => mb_substr($t, 0, 50), "color" => $c];
    }
    Settings::set("badges", $badges);

    // Скрытые пункты
    $hidden = [];
    $hiddenIds = (array)($_POST["hidden_ids"] ?? []);
    foreach ($hiddenIds as $h) {
        $h = trim((string)$h);
        if ($h !== "") $hidden[] = $h;
    }
    $newUrl = trim((string)($_POST["hidden_new_url"] ?? ""));
    if ($newUrl !== "" && !in_array($newUrl, $hidden, true)) {
        $hidden[] = $newUrl;
    }
    Settings::set("hidden", array_values(array_unique($hidden)));

    $saved = true;
}

$sections = Sections::all();
if (!$sections) {
    $sections = [];
}

// Добавляем штатные группы из каталога, которых ещё нет в $sections,
// чтобы они сразу отображались в форме как редактируемые разделы.
$existingNative = [];
foreach ($sections as $s) {
    if (!empty($s["native"])) $existingNative[(string)$s["id"]] = true;
}
$catalogGroups = \FG\Menuz\Catalog::groups();
$catalogItems  = \FG\Menuz\Catalog::items();
foreach ($catalogGroups as $g) {
    if (isset($existingNative[$g["id"]])) continue;
    $gItems = [];
    foreach ($catalogItems as $it) {
        if (($it["group"] ?? "") !== $g["id"]) continue;
        $gItems[] = [
            "id"      => $it["id"],
            "title"   => $it["title"],
            "url"     => $it["link"],
            "icon"    => $it["icon"] ?? "",
            "counter" => $it["counter"] ?? "",
            "group"   => $g["id"],
        ];
    }
    array_unshift($sections, [
        "id"     => $g["id"],
        "native" => true,
        "title"  => $g["title"],
        "icon"   => "",
        "items"  => $gItems,
    ]);
}
if (!$sections) {
    $sections = [[ "id" => 0, "title" => "", "items" => [] ]];
}

// Добавляем штатные группы, которых ещё нет в sections, чтобы они сразу были в форме
$existingNative = [];
foreach ($sections as $s) {
    if (!empty($s["native"])) $existingNative[(string)$s["id"]] = true;
}
$groups = \FG\Menuz\Catalog::groups();
$items  = \FG\Menuz\Catalog::items();
foreach ($groups as $g) {
    if (isset($existingNative[$g["id"]])) continue;
    // Собираем пункты этой группы из каталога — чтобы при сохранении они не потерялись
    $gItems = [];
    foreach ($items as $it) {
        if (($it["group"] ?? "") !== $g["id"]) continue;
        $gItems[] = [
            "id"      => $it["id"],
            "title"   => $it["title"],
            "url"     => $it["link"],
            "icon"    => $it["icon"] ?? "",
            "counter" => $it["counter"] ?? "",
            "group"   => $g["id"],
        ];
    }
    $sections[] = [
        "id"     => $g["id"],
        "native" => true,
        "title"  => $g["title"],
        "icon"   => "",
        "items"  => $gItems,
    ];
}
if (!$sections) {
    $sections = [[ "id" => 0, "title" => "", "items" => [] ]];
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
?>

<!-- Сообщение об успешном сохранении -->
<?php if ($saved): ?>
    <div class="adm-info-message-wrap adm-info-message-green">
        <div class="adm-info-message"><?= Loc::getMessage("FG_MENUZ_SAVED") ?></div>
    </div>
<?php endif; ?>

<!-- Стили страницы -->
<style>
.fgz-block { background:#fff; border:1px solid #dce0e5; border-radius:8px; padding:16px; margin-bottom:20px; }
.fgz-sec-head { display:flex; gap:10px; align-items:center; margin-bottom:12px; }
.fgz-sec-head input[type=text] { font-size:14px; padding:6px 8px; width:320px; }
.fgz-items { border-top:1px solid #eef1f4; padding-top:12px; }
.fgz-row { display:flex; gap:8px; margin-bottom:8px; align-items:center; }
.fgz-row input[type=text] { padding:6px 8px; font-size:13px; }
.fgz-row .fgz-name { width:230px; }
.fgz-row .fgz-url  { flex:1; position:relative; }
.fgz-row .fgz-uid  { width:1px; height:1px; opacity:0; position:absolute; pointer-events:none; }
.fgz-native-list {
    position:absolute; z-index:999; top:100%; left:0; right:0;
    background:#fff; border:1px solid #c9cdd3; border-radius:6px;
    box-shadow:0 6px 24px rgba(0,0,0,.12);
    max-height:280px; overflow:auto; display:none;
}
.fgz-native-list.open { display:block; }
.fgz-native-item {
    padding:6px 10px; cursor:pointer; font-size:13px;
    border-bottom:1px solid #f2f4f7;
}
.fgz-native-item:hover { background:#f5fbff; }
.fgz-native-item .fgz-nid { color:#888; font-size:11px; margin-left:6px; }
.fgz-native-empty { padding:8px 10px; color:#888; font-size:12px; }
.fgz-row .fgz-icon-wrap { position:relative; }
.fgz-row .fgz-icon-btn {
    width:38px; height:34px; border:1px solid #c9cdd3; border-radius:6px;
    background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center;
    font-size:18px; padding:0;
}
.fgz-row .fgz-icon-btn:hover { border-color:#2fc7f7; }
.fgz-row .fgz-icon-btn .fgz-mask-icon {
    display: inline-block !important;
    width: 22px;
    height: 22px;
    background-color: #aeb3b9;
    -webkit-mask-size: contain;
    mask-size: contain;
    -webkit-mask-repeat: no-repeat;
    mask-repeat: no-repeat;
    -webkit-mask-position: center;
    mask-position: center;
}
.fgz-row .fgz-del  { cursor:pointer; color:#c00; font-weight:bold; padding:0 8px; user-select:none; }
.fgz-row .fgz-del:hover { color:#f00; }
.fgz-add { margin-top:6px; }
.fgz-actions { margin-top:16px; }

/* пикер */
.fgz-picker {
    position:absolute; z-index:1000; top:38px; left:0;
    background:#fff; border:1px solid #c9cdd3; border-radius:8px;
    box-shadow:0 6px 24px rgba(0,0,0,.12);
    width:420px; max-height:360px; overflow:auto; padding:10px;
    display:none;
}
.fgz-picker.open { display:block; }
.fgz-picker-grid { display:grid; grid-template-columns:repeat(8,1fr); gap:6px; }
.fgz-picker-item {
    width:40px; height:40px; border:1px solid #eef1f4; border-radius:6px;
    cursor:pointer; display:flex; align-items:center; justify-content:center;
    background:#fff; color:#5f6b7a;
}
.fgz-picker-item:hover { border-color:#2fc7f7; background:#f5fbff; }
.fgz-picker-item .ui-icon-set { font-size:20px; }
.fgz-picker-sep { grid-column:1/-1; height:1px; background:#eef1f4; margin:6px 0; }
.fgz-picker-custom { grid-column:1/-1; display:flex; gap:6px; margin-top:6px; }
.fgz-picker-custom input { flex:1; padding:6px 8px; font-size:12px; }
.fgz-picker-clear { grid-column:1/-1; text-align:center; color:#888; font-size:12px; cursor:pointer; padding:4px; }
.fgz-picker-clear:hover { color:#c00; }
</style>

<?php
$tabControl = new CAdminTabControl("fgzTabs", [
    ["DIV" => "fgz_tab_sections", "TAB" => Loc::getMessage("FG_MENUZ_TAB_SECTIONS"), "TITLE" => Loc::getMessage("FG_MENUZ_TAB_SECTIONS_TITLE")],
    ["DIV" => "fgz_tab_order",    "TAB" => Loc::getMessage("FG_MENUZ_TAB_ORDER"),    "TITLE" => Loc::getMessage("FG_MENUZ_TAB_ORDER_TITLE")],
    ["DIV" => "fgz_tab_hidden",   "TAB" => Loc::getMessage("FG_MENUZ_TAB_HIDDEN"),   "TITLE" => Loc::getMessage("FG_MENUZ_TAB_HIDDEN_TITLE")],
    ["DIV" => "fgz_tab_badges",   "TAB" => Loc::getMessage("FG_MENUZ_TAB_BADGES"),   "TITLE" => Loc::getMessage("FG_MENUZ_TAB_BADGES_TITLE")],
]);

$tabControl->Begin();
?>

<form method="post" action="">
    <?= bitrix_sessid_post() ?>

    <?php $tabControl->BeginNextTab(); // открываем содержимое первой вкладки ?>
    <tr><td colspan="2">
    <?php // Вкладка 1: общие настройки и разделы меню ?>

    <?php // Общие настройки модуля ?>
    <div class="fgz-block">
        <label style="display:block;">
            <input type="checkbox" name="enabled" value="Y"<?= Settings::flag("enabled") ? " checked" : "" ?>>
            <b><?= Loc::getMessage("FG_MENUZ_ENABLED") ?></b>
        </label>
        <label style="display:block; margin-top:10px;">
            <input type="checkbox" name="hide_native" value="Y"<?= Settings::flag("hide_native") ? " checked" : "" ?>>
            <b><?= Loc::getMessage("FG_MENUZ_HIDE_NATIVE") ?></b>
            <span style="color:#666; font-size:12px;">
                <?= Loc::getMessage("FG_MENUZ_HIDE_NATIVE_HINT") ?>
            </span>
        </label>
        <label style="display:block; margin-top:10px;">
            <input type="checkbox" name="hide_native_groups" value="Y"<?= Settings::flag("hide_native_groups") ? " checked" : "" ?>>
            <b><?= Loc::getMessage("FG_MENUZ_HIDE_GROUPS") ?></b>
            <span style="color:#666; font-size:12px;">
                <?= Loc::getMessage("FG_MENUZ_HIDE_GROUPS_HINT") ?>
            </span>
        </label>
    </div>
    <?php // Сбор штатных пунктов с портала ?>
    <div class="fgz-block" style="display:flex; gap:12px; align-items:center;">
        <button type="button" class="adm-btn" id="fgz-reload-native"><?= Loc::getMessage("FG_MENUZ_RELOAD_NATIVE") ?></button>
        <span id="fgz-reload-status" style="color:#666; font-size:12px;">
            <?php
            $nativeItems = \FG\Menuz\Catalog::items();
            $nativeAll   = \FG\Menuz\Catalog::all();
            $nativeTs    = (int)($nativeAll["ts"] ?? 0);
            if ($nativeItems) {
                echo Loc::getMessage("FG_MENUZ_CATALOG_COUNT", ["#COUNT#" => count($nativeItems)]);
                if ($nativeTs) {
                    echo ' ' . Loc::getMessage("FG_MENUZ_CATALOG_UPDATED", ["#DATE#" => date('d.m.Y H:i', $nativeTs)]);
                }
            } else {
                echo Loc::getMessage("FG_MENUZ_CATALOG_EMPTY");
            }
            ?>
        </span>
    </div>

    <?php // Редактор разделов ?>
    <div id="fgz-sections">
        <?php foreach ($sections as $si => $sec): ?>
            <?php $isNative = !empty($sec["native"]); ?>
            <div class="fgz-block fgz-section<?= $isNative ? ' fgz-native' : '' ?>" data-sec-index="<?= $si ?>">
                <div class="fgz-sec-head">
                    <input type="hidden" name="sec_id[]" value="<?= $isNative ? htmlspecialcharsbx($sec["id"]) : (int)$sec["id"] ?>">
                    <?php if ($isNative): ?>
                        <input type="hidden" name="sec_native[]" value="1">
                    <?php endif; ?>
                    <input type="text" name="sec_title[]" value="<?= htmlspecialcharsbx($sec["title"]) ?>"
                        placeholder="<?= Loc::getMessage("FG_MENUZ_PLACEHOLDER_SECTION") ?>"
                        <?= $isNative ? 'readonly style="background:#f5f5f5;"' : '' ?>>
                    <?php if (!$isNative): ?>
                    <div class="fgz-icon-wrap">
                        <input type="hidden" name="sec_icon[]" class="fgz-icon-value"
                               value="<?= htmlspecialcharsbx($sec["icon"] ?? "") ?>">
                        <button type="button" class="fgz-icon-btn"
                                data-icon="<?= htmlspecialcharsbx($sec["icon"] ?? "") ?>">
                            <?php if (!empty($sec["icon"])): ?>
                                <?php if (preg_match('~^(https?:|data:|/)~i', $sec["icon"])): ?>
                                    <img src="<?= htmlspecialcharsbx($sec["icon"]) ?>" alt="">
                                <?php else: ?>
                                    <span class="ui-icon-set --<?= htmlspecialcharsbx($sec["icon"]) ?>"></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="ui-icon-set --folder-24"></span>
                            <?php endif; ?>
                        </button>
                    </div>
                                <?php endif; ?>
                    <?php if ($isNative): ?>
                        <input type="hidden" name="sec_icon[]" value="">
                    <?php endif; ?>
                    <?php if (!$isNative && (int)$sec["id"] > 0): ?>
                        <label style="margin-left:auto;font-size:12px;color:#666;">
                            <input type="checkbox" name="sec_delete[<?= (int)$sec["id"] ?>]" value="Y"> <?= Loc::getMessage("FG_MENUZ_DELETE_SECTION") ?>
                        </label>
                    <?php endif; ?>
                </div>

                <div class="fgz-items" data-items-for="<?= $si ?>">
                    <?php
                    $catalogItems2 = \FG\Menuz\Catalog::items();
                    $items = $sec["items"] ?: [["title" => "", "url" => "", "icon" => ""]];
                    foreach ($items as &$_it) {
                        if (empty($_it["icon"]) && !empty($_it["id"])) {
                            foreach ($catalogItems2 as $_ci) {
                                if (($_ci["id"] ?? "") === $_it["id"] && !empty($_ci["icon"])) {
                                    $_it["icon"] = $_ci["icon"];
                                    break;
                                }
                            }
                        }
                    }
                    unset($_it);
                    // Сортируем по sort (null — в конец)
                    usort($items, function($a, $b){
                        $sa = (isset($a["sort"]) && $a["sort"] !== null && $a["sort"] !== "") ? (int)$a["sort"] : 999999;
                        $sb = (isset($b["sort"]) && $b["sort"] !== null && $b["sort"] !== "") ? (int)$b["sort"] : 999999;
                        return $sa - $sb;
                    });
                    foreach ($items as $it): ?>
                        <div class="fgz-row">
                            <div class="fgz-icon-wrap">
                                <button type="button" class="fgz-icon-btn" data-icon="<?= htmlspecialcharsbx($it["icon"]) ?>">
                                    <?php if ($it["icon"] !== ""): ?>
                                                                                <?php if (preg_match('~^data:image/~i', $it["icon"])): ?>
                                            <img src="<?= htmlspecialcharsbx($it["icon"]) ?>" alt="" style="width:22px;height:22px;filter:brightness(0) saturate(100%) opacity(0.85);">
                                        <?php elseif (preg_match('~^(https?:|/)~i', $it["icon"])): ?>
                                            <img src="<?= htmlspecialcharsbx($it["icon"]) ?>" alt="">
                                        <?php else: ?>
                                            <span class="ui-icon-set --<?= htmlspecialcharsbx($it["icon"]) ?>"></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="display:inline-block;width:22px;height:22px;line-height:22px;text-align:center;font-weight:600;color:#5f6b7a;font-size:12px;"><?= htmlspecialcharsbx(mb_strtoupper(mb_substr($it["title"], 0, 1))) ?></span>
                                    <?php endif; ?>
                                </button>
                                <input type="hidden"
                                       name="sec_item_icon[<?= $si ?>][]"
                                       class="fgz-icon-value"
                                       value="<?= htmlspecialcharsbx($it["icon"]) ?>">
                            </div>
                            <input type="text"
                                   name="sec_item_title[<?= $si ?>][]"
                                   class="fgz-name"
                                   value="<?= htmlspecialcharsbx($it["title"]) ?>"
                                   placeholder="<?= Loc::getMessage("FG_MENUZ_PLACEHOLDER_ITEM") ?>">
                            <input type="hidden"
                                   name="sec_item_id[<?= $si ?>][]"
                                   class="fgz-uid"
                                   value="<?= htmlspecialcharsbx($it["id"] ?? "") ?>">
                            <input type="hidden"
                                   name="sec_item_group[<?= $si ?>][]"
                                   class="fgz-group"
                                   value="<?= htmlspecialcharsbx($it["group"] ?? "") ?>">
                            <input type="text"
                                   name="sec_item_counter[<?= $si ?>][]"
                                   class="fgz-counter"
                                   placeholder="counter"
                                   value="<?= htmlspecialcharsbx($it["counter"] ?? "") ?>"
                                   style="width:90px;">
                            <input type="number"
                                   name="sec_item_sort[<?= $si ?>][]"
                                   class="fgz-sort"
                                   placeholder="sort"
                                   value="<?= htmlspecialcharsbx($it["sort"] ?? "") ?>"
                                   style="width:70px;">      
                            <div class="fgz-url">
                                <input type="text"
                                       name="sec_item_url[<?= $si ?>][]"
                                       class="fgz-url-input"
                                       value="<?= htmlspecialcharsbx($it["url"]) ?>"
                                       placeholder="/url/ или https://...">
                                <div class="fgz-native-list"></div>
                            </div>
                            <span class="fgz-del" title="<?= Loc::getMessage("FG_MENUZ_DELETE_ITEM") ?>">✕</span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="adm-btn fgz-add"><?= Loc::getMessage("FG_MENUZ_ADD_ITEM") ?></button>
            </div>
        <?php endforeach; ?>
    </div>

    <?php // Кнопка добавления нового раздела ?>
    <div style="margin:16px 0;">
        <button type="button" class="adm-btn" id="fgz-add-section"><?= Loc::getMessage("FG_MENUZ_ADD_SECTION") ?></button>
    </div>

    </td></tr><!-- /контент вкладки «Разделы» -->

        <?php
    // Собираем все разделы и корневые пункты для блока «Порядок»
    $orderItems = [];
    $savedOrder = \FG\Menuz\Settings::get("order", []);
    $sortMap = [];
    if (is_array($savedOrder)) {
        foreach ($savedOrder as $o) {
            $key = $o["type"] . ":" . $o["id"];
            // Если sort уже был не 100 (не дефолт) — оставляем
            $s = (int)($o["sort"] ?? 100);
            $sortMap[$key] = $s;
        }
    }
    // Проверяем: если все sort одинаковые (=100) — генерируем новые
    $uniqueSorts = array_unique(array_values($sortMap));
    if (count($uniqueSorts) <= 1 && count($sortMap) > 0) {
        $sortMap = []; // сбросить, пусть генерация по порядку
    }

    // Разделы
    foreach ($sections as $sec) {
        // для своих разделов — префикс fgz_, для native — как есть
        $secId = !empty($sec["native"]) ? (string)$sec["id"] : "fgz_" . (int)$sec["id"];
        $key = "section:" . $secId;
        $orderItems[] = [
            "type"  => "section",
            "id"    => $secId,
            "title" => (string)$sec["title"] . (!empty($sec["native"]) ? Loc::getMessage("FG_MENUZ_ORDER_NATIVE_SUFFIX") : ""),
            "sort"  => $sortMap[$key] ?? 100,
        ];
    }

    // Корневые пункты (из каталога, которых НЕТ ни в одном разделе
    // и которые сами не являются разделами)
    $usedIds = [];
    foreach ($sections as $sec) {
        // сам раздел — тоже исключаем
        $usedIds[(string)$sec["id"]] = true;
        foreach ($sec["items"] as $it) {
            if (!empty($it["id"])) $usedIds[$it["id"]] = true;
        }
    }
    // Переопределения иконок корневых пунктов
    $rootOverrides = \FG\Menuz\Settings::get("root_overrides", []);
    if (!is_array($rootOverrides)) $rootOverrides = [];

    // Прокидываем icon в каждый orderItem
    foreach ($orderItems as &$_oi) {
        if ($_oi["type"] === "root") {
            $_oi["icon"] = (string)($rootOverrides[$_oi["id"]]["icon"] ?? "");
        } else {
            $_oi["icon"] = "";
        }
    }
    unset($_oi);

    foreach (\FG\Menuz\Catalog::items() as $ci) {
        if (isset($usedIds[$ci["id"]])) continue;
        $key = "root:" . $ci["id"];
        $orderItems[] = [
            "type"  => "root",
            "id"    => (string)$ci["id"],
            "title" => (string)$ci["title"] . Loc::getMessage("FG_MENUZ_ORDER_ROOT_SUFFIX"),
            "icon"  => (string)($rootOverrides[(string)$ci["id"]]["icon"] ?? ""),
            "sort"  => $sortMap[$key] ?? 500,
        ];
    }
        // Сортируем по sort (при равных — по title)
    usort($orderItems, function($a, $b){
        if ($a["sort"] !== $b["sort"]) return $a["sort"] - $b["sort"];
        return strcmp($a["title"], $b["title"]);
    });
    ?>

    <?php // Вкладка 2: порядок в меню ?>
    <?php $tabControl->BeginNextTab(); ?>
    <tr><td colspan="2">

    <div class="fgz-block" id="fgz-order-items">
        <h3 style="margin-top:0;"><?= Loc::getMessage("FG_MENUZ_TAB_ORDER") ?></h3>
        <p style="color:#666;font-size:12px;margin-top:-8px;">
            <?= Loc::getMessage("FG_MENUZ_ORDER_DESC") ?>
        </p>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th style="text-align:left;padding:8px;"><?= Loc::getMessage("FG_MENUZ_ORDER_WHAT") ?></th>
                    <th style="text-align:left;padding:8px;"><?= Loc::getMessage("FG_MENUZ_ORDER_TYPE") ?></th>
                    <th style="text-align:left;padding:8px;"><?= Loc::getMessage("FG_MENUZ_ORDER_ICON") ?></th>
                    <th style="text-align:left;padding:8px;"><?= Loc::getMessage("FG_MENUZ_ORDER_ID") ?></th>
                    <th style="text-align:left;padding:8px;width:100px;"><?= Loc::getMessage("FG_MENUZ_ORDER_SORT") ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $i => $oi): ?>
                    <tr>
                        <td style="padding:6px;border-bottom:1px solid #eee;">
                            <?= htmlspecialcharsbx($oi["title"]) ?>
                        </td>
                        <td style="padding:6px;border-bottom:1px solid #eee;">
                            <?= $oi["type"] === "section" ? Loc::getMessage("FG_MENUZ_ORDER_SECTION") : Loc::getMessage("FG_MENUZ_ORDER_ITEM") ?>
                        </td>
                        <td style="padding:6px;border-bottom:1px solid #eee;">
                            <?php if ($oi["type"] === "root"): ?>
                                <div class="fgz-icon-wrap">
                                    <input type="hidden" name="order_icon[]" class="fgz-icon-value" value="<?= htmlspecialcharsbx($oi["icon"]) ?>">
                                    <button type="button" class="fgz-icon-btn" data-icon="<?= htmlspecialcharsbx($oi["icon"]) ?>">
                                        <?php if ($oi["icon"] !== ""): ?>
                                            <?php if (preg_match('~^(https?:|data:|/)~i', $oi["icon"])): ?>
                                                <img src="<?= htmlspecialcharsbx($oi["icon"]) ?>" alt="">
                                            <?php else: ?>
                                                <span class="ui-icon-set --<?= htmlspecialcharsbx($oi["icon"]) ?>"></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="ui-icon-set --link"></span>
                                        <?php endif; ?>
                                    </button>
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="order_icon[]" value="">
                            <?php endif; ?>
                        </td>
                        <td style="padding:6px;border-bottom:1px solid #eee;color:#888;font-size:11px;">
                            <?= htmlspecialcharsbx($oi["id"]) ?>
                        </td>
                        <td style="padding:6px;border-bottom:1px solid #eee;">
                            <input type="hidden" name="order_type[]" value="<?= htmlspecialcharsbx($oi["type"]) ?>">
                            <input type="hidden" name="order_id[]" value="<?= htmlspecialcharsbx($oi["id"]) ?>">
                            <input type="number" name="order_sort[]" value="<?= (int)$oi["sort"] ?>" style="width:80px;">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    </td></tr><!-- /контент вкладки «Порядок в меню» -->

    <?php // Вкладка 3: скрытые пункты ?>
    <?php $tabControl->BeginNextTab(); ?>
    <tr><td colspan="2">

    <?php
    $hiddenSaved = \FG\Menuz\Settings::get("hidden", []);
    if (!is_array($hiddenSaved)) $hiddenSaved = [];
    $hiddenMap = array_flip($hiddenSaved);
    ?>

    <div class="fgz-block">
        <h3 style="margin-top:0;"><?= Loc::getMessage("FG_MENUZ_TAB_HIDDEN") ?></h3>
        <p style="color:#666;font-size:12px;margin-top:-8px;">
            <?= Loc::getMessage("FG_MENUZ_HIDDEN_DESC") ?>
        </p>

        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th style="text-align:left;padding:8px;width:40px;"><?= Loc::getMessage("FG_MENUZ_HIDDEN_COL_HIDE") ?></th>
                    <th style="text-align:left;padding:8px;"><?= Loc::getMessage("FG_MENUZ_HIDDEN_COL_NAME") ?></th>
                    <th style="text-align:left;padding:8px;"><?= Loc::getMessage("FG_MENUZ_HIDDEN_COL_ID") ?></th>
                    <th style="text-align:left;padding:8px;"><?= Loc::getMessage("FG_MENUZ_HIDDEN_COL_TYPE") ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $catalogItemsH = \FG\Menuz\Catalog::items();
                foreach ($catalogItemsH as $ci):
                    $ciId = (string)($ci["id"] ?? "");
                    if ($ciId === "") continue;
                    $checked = isset($hiddenMap[$ciId]);
                ?>
                    <tr>
                        <td style="padding:6px;border-bottom:1px solid #eee;">
                            <input type="checkbox"
                                   name="hidden_ids[]"
                                   value="<?= htmlspecialcharsbx($ciId) ?>"
                                   <?= $checked ? " checked" : "" ?>>
                        </td>
                        <td style="padding:6px;border-bottom:1px solid #eee;">
                            <?= htmlspecialcharsbx($ci["title"]) ?>
                        </td>
                        <td style="padding:6px;border-bottom:1px solid #eee;color:#888;font-size:11px;">
                            <?= htmlspecialcharsbx($ciId) ?>
                        </td>
                        <td style="padding:6px;border-bottom:1px solid #eee;color:#888;font-size:11px;">
                            <?= Loc::getMessage("FG_MENUZ_HIDDEN_TYPE_NATIVE") ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php
                $extraUrls = [];
                foreach ($hiddenSaved as $h) {
                    if (strpos($h, "://") !== false || (strlen($h) > 0 && $h[0] === "/")) {
                        $extraUrls[] = $h;
                    }
                }
                foreach ($extraUrls as $u):
                ?>
                    <tr>
                        <td style="padding:6px;border-bottom:1px solid #eee;">
                            <input type="checkbox" name="hidden_ids[]" value="<?= htmlspecialcharsbx($u) ?>" checked>
                        </td>
                        <td style="padding:6px;border-bottom:1px solid #eee;">
                            —
                        </td>
                        <td style="padding:6px;border-bottom:1px solid #eee;color:#888;font-size:11px;">
                            <?= htmlspecialcharsbx($u) ?>
                        </td>
                        <td style="padding:6px;border-bottom:1px solid #eee;color:#888;font-size:11px;">
                            <?= Loc::getMessage("FG_MENUZ_HIDDEN_TYPE_URL") ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <tr>
                    <td style="padding:6px;border-bottom:1px solid #eee;">
                        <input type="checkbox" name="hidden_ids[]" value="" id="hidden_new" checked>
                    </td>
                    <td style="padding:6px;border-bottom:1px solid #eee;">
                        <input type="text" name="hidden_new_title" placeholder="<?= Loc::getMessage("FG_MENUZ_HIDDEN_CUSTOM_TITLE") ?>" style="width:200px;">
                    </td>
                    <td style="padding:6px;border-bottom:1px solid #eee;">
                        <input type="text" name="hidden_new_url" placeholder="/url/ или https://..." style="width:300px;">
                    </td>
                    <td style="padding:6px;border-bottom:1px solid #eee;color:#888;font-size:11px;">
                        <?= Loc::getMessage("FG_MENUZ_HIDDEN_TYPE_CUSTOM") ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <p style="color:#888;font-size:11px;margin-top:8px;">
            <?= Loc::getMessage("FG_MENUZ_HIDDEN_NOTE") ?>
        </p>
    </div>

    </td></tr><!-- /контент вкладки «Скрытые пункты» -->

    <?php // Вкладка 4: шильдики ?>
    <?php $tabControl->BeginNextTab(); ?>
    <tr><td colspan="2">

    <?php
    $badgesSaved = \FG\Menuz\Settings::get("badges", []);
    if (!is_array($badgesSaved)) $badgesSaved = [];
    ?>

    <div class="fgz-block">
        <h3 style="margin-top:0;"><?= Loc::getMessage("FG_MENUZ_TAB_BADGES") ?></h3>
        <p style="color:#666;font-size:12px;margin-top:-8px;">
            <?= Loc::getMessage("FG_MENUZ_BADGE_DESC") ?>
        </p>

        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th style="text-align:left;padding:8px;"><?= Loc::getMessage("FG_MENUZ_BADGE_COL_NAME") ?></th>
                    <th style="text-align:left;padding:8px;"><?= Loc::getMessage("FG_MENUZ_BADGE_COL_ID") ?></th>
                    <th style="text-align:left;padding:8px;width:140px;"><?= Loc::getMessage("FG_MENUZ_BADGE_COL_TEXT") ?></th>
                    <th style="text-align:left;padding:8px;width:140px;"><?= Loc::getMessage("FG_MENUZ_BADGE_COL_COLOR") ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Собираем все пункты: сначала штатные из каталога, потом кастомные из sections
                $allItemsForBadges = [];
                foreach (\FG\Menuz\Catalog::items() as $ci) {
                    if (empty($ci["id"])) continue;
                    $allItemsForBadges[$ci["id"]] = [
                        "id"    => $ci["id"],
                        "title" => $ci["title"] ?? "",
                    ];
                }
                foreach ($sections as $sec) {
                    foreach ($sec["items"] as $it) {
                        $iid = $it["id"] ?? "";
                        if ($iid !== "" && isset($allItemsForBadges[$iid])) continue;
                        $key = $iid !== "" ? $iid : "url:" . ($it["url"] ?? "");
                        if ($key === "url:") continue;
                        $allItemsForBadges[$key] = [
                            "id"    => $key,
                            "title" => $it["title"] ?? "",
                        ];
                    }
                }
                ksort($allItemsForBadges);
                foreach ($allItemsForBadges as $ai):
                    $bKey   = $ai["id"];
                    $bCfg   = $badgesSaved[$bKey] ?? [];
                    $bText  = (string)($bCfg["text"] ?? "");
                    $bColor = (string)($bCfg["color"] ?? "success");
                ?>
                    <tr>
                        <td style="padding:6px;border-bottom:1px solid #eee;">
                            <?= htmlspecialcharsbx($ai["title"]) ?>
                        </td>
                        <td style="padding:6px;border-bottom:1px solid #eee;color:#888;font-size:11px;">
                            <?= htmlspecialcharsbx($bKey) ?>
                        </td>
                        <td style="padding:6px;border-bottom:1px solid #eee;">
                            <input type="hidden" name="badge_key[]" value="<?= htmlspecialcharsbx($bKey) ?>">
                            <input type="text" name="badge_text[]" value="<?= htmlspecialcharsbx($bText) ?>" placeholder="NEW" style="width:120px;">
                        </td>
                        <td style="padding:6px;border-bottom:1px solid #eee;">
                            <select name="badge_color[]" style="width:120px;">
                                <option value="success"<?= $bColor === "success" ? " selected" : "" ?>><?= Loc::getMessage("FG_MENUZ_BADGE_GREEN") ?></option>
                                <option value="warning"<?= $bColor === "warning" ? " selected" : "" ?>><?= Loc::getMessage("FG_MENUZ_BADGE_YELLOW") ?></option>
                                <option value="danger"<?=  $bColor === "danger"  ? " selected" : "" ?>><?= Loc::getMessage("FG_MENUZ_BADGE_RED") ?></option>
                                <option value="info"<?=    $bColor === "info"    ? " selected" : "" ?>><?= Loc::getMessage("FG_MENUZ_BADGE_BLUE") ?></option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    </td></tr><!-- /контент вкладки «Шильдики» -->

<?php $tabControl->Buttons(["btnCancel" => false]); ?>
</form>
<?php $tabControl->End(); ?>

<!-- Шаблон пикера иконок (клонируется в JS и вставляется в body) -->
<div id="fgz-picker-template" style="display:none">
    <div class="fgz-picker">
        <div class="fgz-picker-grid">
            <?php foreach ($ICONS as $key => $label): ?>
                <div class="fgz-picker-item" data-icon="<?= htmlspecialcharsbx($key) ?>" title="<?= htmlspecialcharsbx($label) ?>">
                    <span class="ui-icon-set --<?= htmlspecialcharsbx($key) ?>"></span>
                </div>
            <?php endforeach; ?>
            <div class="fgz-picker-sep"></div>
            <div class="fgz-picker-custom">
                <input type="text" class="fgz-picker-url" placeholder="<?= Loc::getMessage("FG_MENUZ_PICKER_URL") ?>">
                <button type="button" class="adm-btn fgz-picker-url-apply"><?= Loc::getMessage("FG_MENUZ_PICKER_OK") ?></button>
            </div>
            <div class="fgz-picker-clear"><?= Loc::getMessage("FG_MENUZ_PICKER_CLEAR") ?></div>
        </div>
    </div>
</div>

<script>
(function () {
    // Элементы страницы и копия пикера
    var wrap   = document.getElementById('fgz-sections');
    var tpl    = document.getElementById('fgz-picker-template');
    var picker = tpl.querySelector('.fgz-picker').cloneNode(true);
    var secCount = wrap.querySelectorAll('.fgz-section').length;
    var current = null; // { btn, valueInput }

    document.body.appendChild(picker);

    function closePicker() {
        picker.classList.remove('open');
        current = null;
    }

    // Отрисовывает иконку на кнопке (штатная, картинка или первая буква)
    function setIcon(btn, valInput, icon) {
        valInput.value = icon || '';
        btn.innerHTML = '';
        if (!icon) {
            var row = btn.closest('.fgz-row');
            var nameInput = row ? row.querySelector('.fgz-name') : null;
            var firstLetter = nameInput ? (nameInput.value || '').trim().charAt(0).toUpperCase() : '';
            if (firstLetter) {
                btn.innerHTML = '<span style="display:inline-block;width:22px;height:22px;line-height:22px;text-align:center;font-weight:600;color:#5f6b7a;font-size:12px;">' + firstLetter + '</span>';
            } else {
                btn.innerHTML = '<span class="ui-icon-set --folder"></span>';
            }
        } else if (/^data:image\//i.test(icon)) {
            var img = document.createElement('img');
            img.src = icon;
            img.style.width = '22px';
            img.style.height = '22px';
            img.style.filter = 'brightness(0) saturate(100%) opacity(0.85)';
            btn.appendChild(img);
        } else if (/^(https?:|\/)/i.test(icon)) {
            var img = document.createElement('img');
            img.src = icon;
            btn.appendChild(img);
        } else {
            btn.innerHTML = '<span class="ui-icon-set --' + icon + '"></span>';
        }
    }

    // Открывает пикер у нажатой кнопки
    function openPicker(btn, valInput) {
        var r = btn.getBoundingClientRect();
        picker.style.top  = (r.bottom + window.scrollY + 4) + 'px';
        picker.style.left = (r.left  + window.scrollX) + 'px';
        picker.classList.add('open');
        current = { btn: btn, val: valInput };
    }

    // клик по кнопке иконки
    wrap.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('.fgz-icon-btn') : null;
        if (btn) {
            e.preventDefault();
            var wrapEl = btn.parentNode;
            var valInput = wrapEl.querySelector('.fgz-icon-value');
            if (picker.classList.contains('open') && current && current.btn === btn) {
                closePicker();
            } else {
                openPicker(btn, valInput);
            }
            return;
        }
        // удалить строку
        if (e.target.classList.contains('fgz-del')) {
            var row = e.target.closest('.fgz-row');
            var items = row.parentNode;
            row.remove();
            if (!items.querySelector('.fgz-row')) items.appendChild(makeRow(items.dataset.itemsFor));
            return;
        }
        // добавить пункт
        if (e.target.classList.contains('fgz-add')) {
            var block = e.target.closest('.fgz-section');
            block.querySelector('.fgz-items').appendChild(makeRow(block.dataset.secIndex));
        }
    });

    // Пикер иконок для корневых пунктов в таблице «Порядок»
    var orderWrap = document.getElementById('fgz-order-items');
    if (orderWrap) {
        orderWrap.addEventListener('click', function (e) {
            var btn = e.target.closest ? e.target.closest('.fgz-icon-btn') : null;
            if (!btn) return;
            e.preventDefault();
            var valInput = btn.parentNode.querySelector('.fgz-icon-value');
            if (picker.classList.contains('open') && current && current.btn === btn) {
                closePicker();
            } else {
                openPicker(btn, valInput);
            }
        });
    }

    // клики внутри пикера
    picker.addEventListener('click', function (e) {
        if (!current) return;
        var item = e.target.closest ? e.target.closest('.fgz-picker-item') : null;
        if (item) {
            setIcon(current.btn, current.val, item.dataset.icon);
            closePicker();
            return;
        }
        if (e.target.classList.contains('fgz-picker-url-apply')) {
            var inp = picker.querySelector('.fgz-picker-url');
            var url = (inp.value || '').trim();
            if (url) setIcon(current.btn, current.val, url);
            inp.value = '';
            closePicker();
            return;
        }
        if (e.target.classList.contains('fgz-picker-clear')) {
            setIcon(current.btn, current.val, '');
            closePicker();
            return;
        }
    });

    document.addEventListener('click', function (e) {
        if (!picker.classList.contains('open')) return;
        if (picker.contains(e.target)) return;
        if (e.target.classList.contains('fgz-icon-btn') || (e.target.closest && e.target.closest('.fgz-icon-btn'))) return;
        closePicker();
    });

    // Создаёт строку пункта меню с полями формы
    function makeRow(idx) {
        var row = document.createElement('div');
        row.className = 'fgz-row';
        row.innerHTML =
            '<div class="fgz-icon-wrap">' +
                '<button type="button" class="fgz-icon-btn" data-icon=""><span class="ui-icon-set --folder"></span></button>' +
                '<input type="hidden" name="sec_item_icon[' + idx + '][]" class="fgz-icon-value" value="">' +
            '</div>' +
            '<input type="text" name="sec_item_title[' + idx + '][]" class="fgz-name" value="" placeholder="<?= Loc::getMessage("FG_MENUZ_PLACEHOLDER_ITEM") ?>">' +
            '<input type="hidden" name="sec_item_id[' + idx + '][]" class="fgz-uid" value="">' +
            '<input type="hidden" name="sec_item_group[' + idx + '][]" class="fgz-group" value="">' +
            '<input type="text" name="sec_item_counter[' + idx + '][]" class="fgz-counter" placeholder="counter" value="" style="width:90px;">' +
            '<input type="number" name="sec_item_sort[' + idx + '][]" class="fgz-sort" placeholder="sort" value="" style="width:70px;">' +
            '<div class="fgz-url">' +
                '<input type="text" name="sec_item_url[' + idx + '][]" class="fgz-url-input" value="" placeholder="/url/ или https://...">' +
                '<div class="fgz-native-list"></div>' +
            '</div>' +
            '<span class="fgz-del" title="<?= Loc::getMessage("FG_MENUZ_DELETE_ITEM") ?>">✕</span>';
        return row;
    }

    // Добавление нового раздела
    document.getElementById('fgz-add-section').addEventListener('click', function () {
        var idx = secCount++;
        var block = document.createElement('div');
        block.className = 'fgz-block fgz-section';
        block.dataset.secIndex = idx;
        block.innerHTML =
            '<div class="fgz-sec-head">' +
                '<input type="hidden" name="sec_id[]" value="0">' +
                '<input type="text" name="sec_title[]" value="" placeholder="<?= Loc::getMessage("FG_MENUZ_PLACEHOLDER_SECTION") ?>">' +
            '</div>' +
            '<div class="fgz-items" data-items-for="' + idx + '"></div>' +
            '<button type="button" class="adm-btn fgz-add"><?= Loc::getMessage("FG_MENUZ_ADD_ITEM") ?></button>';
        block.querySelector('.fgz-items').appendChild(makeRow(idx));
        wrap.appendChild(block);
    });

    //     document.getElementById('fgz-add-native').addEventListener('click', function () {
    //     var sel = document.getElementById('fgz-native-select');
    //     var gid = sel.value;
    //     if (!gid) { alert('Выбери штатную группу'); return; }
    //     var gtitle = sel.options[sel.selectedIndex].text;

    //     var exists = false;
    //     document.querySelectorAll('#fgz-sections .fgz-section').forEach(function (b) {
    //         var idInput = b.querySelector('input[name="sec_id[]"]');
    //         if (idInput && idInput.value === gid) exists = true;
    //     });
    //     if (exists) { alert('Эта группа уже добавлена'); return; }

    //     var idx = secCount++;
    //     var block = document.createElement('div');
    //     block.className = 'fgz-block fgz-section fgz-native';
    //     block.dataset.secIndex = idx;
    //     block.innerHTML =
    //         '<div class="fgz-sec-head">' +
    //             '<input type="hidden" name="sec_id[]" value="' + gid + '">' +
    //             '<input type="hidden" name="sec_native[]" value="1">' +
    //             '<input type="text" name="sec_title[]" value="' + gtitle + '" readonly style="background:#f5f5f5;">' +
    //             '<span style="margin-left:8px;color:#888;font-size:11px;">штатная группа Bitrix</span>' +
    //         '</div>' +
    //         '<div class="fgz-items" data-items-for="' + idx + '"></div>' +
    //         '<button type="button" class="adm-btn fgz-add">+ Добавить пункт</button>';
    //     document.getElementById('fgz-sections').appendChild(block);
    // });

    // Автокомплит штатных ссылок через iframe
    var NATIVE = <?= json_encode(\FG\Menuz\Catalog::items(), JSON_UNESCAPED_UNICODE) ?>;
    var nativeReady = true;

    // (function loadNative() {
    //     var ifr = document.createElement('iframe');
    //     ifr.src = '/?fgz_raw=1';
    //     ifr.style.cssText = 'position:fixed;left:-9999px;width:1px;height:1px;';
    //     document.body.appendChild(ifr);

    //     ifr.addEventListener('load', function () {
    //         try {
    //             var doc = ifr.contentDocument;
    //             if (!doc) return;
    //             var root = doc.getElementById('menu-items-block');
    //             if (!root) return;
    //             var seen = {};
    //             root.querySelectorAll('li.menu-item-block[data-id]').forEach(function (li) {
    //                 if (li.classList.contains('fgz-section')) return;
    //                 if (li.classList.contains('fgz-custom')) return;
    //                 if (li.classList.contains('fgz-moved')) return;
    //                 var id = li.getAttribute('data-id') || '';
    //                 if (!id || id.indexOf('fgz_') === 0 || seen[id]) return;
    //                 seen[id] = true;
    //                 var t = (li.querySelector('.menu-item-link-text') || {}).textContent || '';
    //                 var a = li.querySelector('a.menu-item-link');
    //                 var u = a ? (a.getAttribute('href') || '') : '';
    //                 NATIVE.push({ id: id, title: t.trim(), link: u });
    //             });
    //         } catch (e) {}
    //         nativeReady = true;
    //         ifr.remove();
    //     });
    // })();

    // Список штатных пунктов для автокомплита
    function showNative(box) {
        if (!nativeReady) {
            box.innerHTML = '<div class="fgz-native-empty"><?= Loc::getMessage("FG_MENUZ_JS_LOADING") ?></div>';
            box.classList.add('open');
            return;
        }
        if (!NATIVE.length) {
            box.innerHTML = '<div class="fgz-native-empty"><?= Loc::getMessage("FG_MENUZ_JS_EMPTY") ?></div>';
            box.classList.add('open');
            return;
        }
        box.innerHTML = NATIVE.map(function (n) {
            var safeTitle = (n.title || '').replace(/"/g, '&quot;');
            var safeLink  = (n.link  || '').replace(/"/g, '&quot;');
            var safeCounter = (n.counter || '').replace(/"/g, '&quot;');
            var safeGroup   = (n.group || '').replace(/"/g, '&quot;');
            var safeIcon = (n.icon || '').replace(/"/g, '&quot;');
            return '<div class="fgz-native-item" data-id="' + n.id + '" data-title="' + safeTitle + '" data-link="' + safeLink + '" data-counter="' + safeCounter + '" data-group="' + safeGroup + '" data-icon="' + safeIcon + '">'
                 + n.title + ' <span class="fgz-nid">' + n.id + '</span></div>';
        }).join('');
        box.classList.add('open');
    }

    wrap.addEventListener('focusin', function (e) {
        var inp = e.target.closest ? e.target.closest('.fgz-url-input') : null;
        if (!inp) return;
        var box = inp.parentNode.querySelector('.fgz-native-list');
        if (box) showNative(box);
    });

    wrap.addEventListener('click', function (e) {
        var item = e.target.closest ? e.target.closest('.fgz-native-item') : null;
        if (!item) return;
        var row = item.closest('.fgz-row');
        if (!row) return;
        row.querySelector('.fgz-url-input').value = item.getAttribute('data-link');
        row.querySelector('.fgz-name').value      = item.getAttribute('data-title');
        row.querySelector('.fgz-uid').value       = item.getAttribute('data-id');
        var gInp = row.querySelector('.fgz-group');
        if (gInp) gInp.value = item.getAttribute('data-group') || '';
        var cInp = row.querySelector('.fgz-counter');
                // Иконка из каталога
        var iconVal = item.getAttribute('data-icon') || '';
        var iconInput = row.querySelector('.fgz-icon-value');
        if (iconInput && iconVal) {
            iconInput.value = iconVal;
            var iconBtn = row.querySelector('.fgz-icon-btn');
            if (iconBtn && typeof setIcon === 'function') {
                setIcon(iconBtn, iconInput, iconVal);
            }
        }
        if (cInp) cInp.value = item.getAttribute('data-counter') || '';
        item.parentNode.classList.remove('open');
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest && e.target.closest('.fgz-url')) return;
        document.querySelectorAll('.fgz-native-list.open').forEach(function (b) { b.classList.remove('open'); });
    });

        // Обновление списка штатных пунктов
    var reloadBtn    = document.getElementById('fgz-reload-native');
    var reloadStatus = document.getElementById('fgz-reload-status');

    if (reloadBtn) {
        reloadBtn.addEventListener('click', function () {
            if (reloadStatus) reloadStatus.textContent = '<?= Loc::getMessage("FG_MENUZ_JS_OPENING") ?>';
            // Открываем публичку с параметром сбора — там сработает редирект на страницу сбора
            var w = window.open('/stream/?fgz_collect=1', '_blank');
            // Следим за закрытием окна
            var check = setInterval(function () {
                if (!w || w.closed) {
                    clearInterval(check);
                    if (reloadStatus) reloadStatus.textContent = '<?= Loc::getMessage("FG_MENUZ_JS_UPDATING") ?>';
                    location.reload();
                }
            }, 500);
        });
    }
})();
</script>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>