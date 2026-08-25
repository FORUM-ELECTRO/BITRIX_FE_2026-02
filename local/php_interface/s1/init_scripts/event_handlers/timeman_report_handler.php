<?php

use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'timeman',
    'OnAfterTMEntryUpdate',
    'TimemanReportHandler::onAfterTMEntryUpdate'
);

class TimemanReportHandler
{
    public static function onAfterTMEntryUpdate(array $arFields): void
    {
        // Пишем в отдельный файл
        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/test_timeman.log',
            date('Y-m-d H:i:s') . " Обработчик вызван\n",
            FILE_APPEND
        );

        // Простейшая проверка
        if (!isset($arFields['STATUS']) || $arFields['STATUS'] !== 'CLOSED') {
            file_put_contents(
                $_SERVER['DOCUMENT_ROOT'] . '/test_timeman.log',
                date('Y-m-d H:i:s') . " Не завершение дня\n",
                FILE_APPEND
            );
            return;
        }

        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/test_timeman.log',
            date('Y-m-d H:i:s') . " Завершение дня! USER_ID=" . (int)$arFields['USER_ID'] . "\n",
            FILE_APPEND
        );
    }
}