<?php

/**
 * Обработчик события обновления сделки CRM.
 *
 * Проверяет принадлежность сделки к воронке "Тендеры" (CATEGORY_ID = 1)
 * и подготавливает данные для отображения всплывающего окна на стороне клиента.
 *
 * @link https://dev.1c-bitrix.ru/api_help/crm/crm_events.php OnAfterCrmDealUpdate
 */

// Подключаем ядро Битрикс24
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

// Подключаем модуль CRM
if (!\CModule::IncludeModule('crm')) {
    return;
}

\AddEventHandler('crm', 'OnAfterCrmDealUpdate', 'checkTenderTimeProcedure');

/**
 * Проверяет сделку из воронки "Тендеры" и сохраняет данные для попапа в сессию.
 *
 * Срабатывает после обновления сделки. Если сделка принадлежит к категории
 * тендеров (ID=1), данные записываются в $_SESSION для последующего вывода
 * через JS-попап на текущей странице.
 *
 * @param array{ID?: int|string, CATEGORY_ID?: int|string, TITLE?: string} $arFields Массив полей обновленной сделки.
 * @return void
 */
function checkTenderTimeProcedure(array $arFields): void
{
    // Записываем в лог для отладки
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/log.txt', 'Функция вызвана! ' . date('Y-m-d H:i:s') . ' - Deal ID: ' . ($arFields['ID'] ?? 'N/A') . ' - Category ID: ' . ($arFields['CATEGORY_ID'] ?? 'N/A') . PHP_EOL, FILE_APPEND);

    // Проверяем, что сделка существует
    if (empty($arFields['ID'])) {
        return;
    }

    // Проверяем воронку "Тендеры"
    if (!isset($arFields['CATEGORY_ID']) || (int) $arFields['CATEGORY_ID'] !== 1) {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/log.txt', 'Не тендерная сделка. Category ID: ' . ($arFields['CATEGORY_ID'] ?? 'N/A') . PHP_EOL, FILE_APPEND);
        return;
    }

    // Сохраняем в сессию для показа попапа
    $_SESSION['SHOW_TENDER_POPUP'] = [
        'deal_id' => (int) $arFields['ID'],
        'title'   => $arFields['TITLE'] ?? 'Тендер',
    ];
    
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/log.txt', 'Данные сохранены в сессию' . PHP_EOL, FILE_APPEND);
}

// Передаём данные из сессии
if (isset($_SESSION['SHOW_TENDER_POPUP'])) {
    $data = $_SESSION['SHOW_TENDER_POPUP'];
    unset($_SESSION['SHOW_TENDER_POPUP']);
    echo '<script>window.tenderPopupData = ' . json_encode($data) . ';</script>';
}

// Попробуем подключить JS-файл через HTML с проверкой
echo '<script>console.log("JS подключается");</script>';