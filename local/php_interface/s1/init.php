<?

// Подключение общих для всех скриптов констант
include_once(__DIR__ . '/init_scripts/constants.php');

// Ответ чат-бота поддержки в задаче потока
// require_once(__DIR__ . '/BotManager.php');

// Добавление прав HR на календари новых пользователей
//include_once(__DIR__ . '/init_scripts/event_handlers/add_hr_calendar_access.php');

// Подключаем обработчик для графика отсутствий
//require_once __DIR__ . '/init_scripts/event_handlers/absence_handler.php';

// Изменение настроек пользователей при загрузке страницы. Используется для задания колонок по умолчанию в списочном представлении СП Проекты
include_once(__DIR__ . '/init_scripts/event_handlers/edit_user_options_on_prolog.php');

// Логика для вставки кастомного компонента на страницу потоков
// require_once(__DIR__ . '/init_scripts/event_handlers/meeting_funnel_handler.php');

// Используется для скрытия: некоторых полей и разделов в карточках CRM, "шестерёнок" в списочных представлениях CRM, кнопки создания задач в группах
include_once(__DIR__ . '/init_scripts/event_handlers/hide_view_elements_on_prolog.php');

// Обработка новых почтовых сообщений. Использовался раньше для обработки писем в техническую поддержку
// include_once(__DIR__ . '/init_scripts/event_handlers/on_mail_message_new.php');

// Обработка нового сообщения форума. Используется для кастомных уведомлений по комментариям БП и добавления упоминаемых в наблюдатели СП
include_once(__DIR__ . '/init_scripts/event_handlers/on_after_message_add.php');

// Обработка события после добавления элемента инфоблока. Используется для авто-запуска БП по элементам Истории состояний
include_once(__DIR__ . '/init_scripts/event_handlers/on_after_iblock_element_add.php');

// Зависимые поля в СRM и Инфоблоках
include_once(__DIR__ . '/init_scripts/event_handlers/field_dependency.php');

// Используется для: расширения select на странице элемента инфоблока, двойного ряда вкладок в СП Проекты, скрытия кнопки обсуждения в таймлайне некоторых СП, доработки для запуска БП при создании задачи в Потоках
include_once(__DIR__ . '/init_scripts/event_handlers/change_styles_on_prolog.php');

// Обязательность заполнения реквизитов Компании CRM
include_once(__DIR__ . '/init_scripts/event_handlers/company_requisites_required.php');

// Скрытие кнопки "Выбрать элемент" во вкладках карточки CRM
//include_once(__DIR__ . '/init_scripts/event_handlers/hide_pick_element_button.php');

// Изменение стилей и логики после загрузки страницы. Используется для части доработки по запуску БП при создании задачи в Потоках
include_once(__DIR__ . '/init_scripts/event_handlers/on_epilog_change_styles.php');

// Обработка создания новой задачи
// include_once(__DIR__ . '/init_scripts/event_handlers/on_task_add.php');

// Изменение настроек списочных представлений при загрузке страницы. Используется для изменения списочных представлений CRM через список "Настройки отображения списков"
include_once(__DIR__ . '/init_scripts/event_handlers/edit_user_options_list_view.php');

// Отключение всех уведомлений портала
// include_once(__DIR__ . '/init_scripts/event_handlers/disable_notifications.php');

// Отключение уведомлений типа: "добавил комментарий к процессу"
include_once(__DIR__ . '/init_scripts/event_handlers/disable_specific_notifications.php');

// Кнопка Платежи в цифровом рабочем месте
// include_once(__DIR__ . '/init_scripts/event_handlers/invoiceButton.php');

// Валидация длины отчета weekly с переопределением js файла
include_once(__DIR__ . '/init_scripts/event_handlers/report_validator_on_epilog.php');

// Перенос отчётов из рабочего времени в рабочие отчёты
//include_once(__DIR__ . '/init_scripts/event_handlers/timeman_report_handler.php');

// Меню-слайдер в левом меню Битрикс24 на пункте "CRM"
// include_once(__DIR__ . '/init_scripts/crm_menu/connect_menu.php');

// Переопределение сервиса crm.service.container. Используется для проверки зависимых полей при создании/изменении СП
//include_once(__DIR__ . '/init_scripts/services/crm_service_container.php');

// Обработчик изменения описания задачи
include_once(__DIR__ . '/init_scripts/event_handlers/task_update_handler.php');

// Обработчик скрытия кнопки завершения задачи
include_once(__DIR__ . '/init_scripts/event_handlers/task_button_hide_handler.php');

// Подмена класса диска для расширения прав на просмотр файлов в комментариях бизнес-процессов 
require_once(__DIR__ . '/override.php');

// Переопределение Feed бизнес-процессов для доступа к добавлению комментария
include_once(__DIR__ . '/init_scripts/event_handlers/forum_comments_feed.php');

// Вызов попап окна для воронки "Тендеры" с возможность обновить дату процедуры
//include_once(__DIR__ . '/init_scripts/event_handlers/on_after_crm_deal_update.php');

// Подключаем JS-файл для попапа (если не подключено другим способом)
// Важно: JS-файл должен быть подключен в нужном месте, иначе попап не будет работать

// Кастомное поле "Индикатор риска (Сбер)" для CRM
// require_once(__DIR__ . '/lib/RiskIndicator.php');

// ============================================
// ============== ПРАВКИ БД ===================
// ============================================

// Отключение уведомлений о зависших процессах для всех групп (no|bizproc|wi_locked|1 -> N) Таблица: b_im_option_state
// SQL: UPDATE b_im_option_state SET VALUE = 'N' WHERE NAME = 'no|bizproc|wi_locked|1' AND VALUE = 'Y';

// ============================================
// ============== ПРАВКИ ЯДРА =================
// ============================================

// Добавлена проверка if ($this->getEntity()->getType() === 'WF') { return true; } доступ к комментариям для всех пользователей, не только участников БП
// /bitrix/modules/forum/lib/comments/feed.php

// Увеличен лимит вывода результатов поиска с 7 до 12 (строка с for (var itemId in category.ITEMS)) 
// /bitrix/js/intranet/search_title/search.title.js

// Отключение уведомлений о зависших процессах для activity, delegate_task, wi_locked (PUSH => N)
// /bitrix/modules/bizproc/lib/integration/notifyschema.php

?>