<?php

// Регистрация обработчика создания нового пользователя
AddEventHandler("main", "OnAfterUserAdd", "AddHRCalendarAccessHandler");

// Функция логирования
function HRLog($message)
{
    $logFile = __DIR__ . '/hr_calendar_access.log';
    $date = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[{$date}] {$message}\n", FILE_APPEND | LOCK_EX);
}

// Лог: регистрация обработчика выполнена
HRLog("Обработчик OnAfterUserAdd зарегистрирован");

// Функция запускается после создания пользователя
function AddHRCalendarAccessHandler(&$arFields)
{
    // Лог: обработчик вызван
    HRLog("Вызван обработчик для ID=" . $arFields["ID"]);
    
    // Проверка существования ID пользователя
    if ($arFields["ID"] <= 0) {
        HRLog("Ошибка: ID пользователя не получен");
        return;
    }
    
    // Вызов функции добавления прав
    AddHRCalendarAccessAgent($arFields["ID"]);
}

// Функция добавления прав HR на календарь
function AddHRCalendarAccessAgent($userId)
{
    global $DB;
    
    // Лог: начало поиска календаря
    HRLog("Поиск календаря для пользователя ID=" . $userId);
    
    // Поиск ID личного календаря пользователя
    $sql = "SELECT ID FROM b_calendar_section 
            WHERE OWNER_ID = " . intval($userId) . " 
            AND CAL_TYPE = 'user' 
            ORDER BY ID ASC LIMIT 1";
    
    $result = $DB->Query($sql);
    
    // Если календарь найден - добавляем права
    if ($row = $result->Fetch()) {
        // Проверяем, есть ли уже права
        $checkSql = "SELECT COUNT(*) as cnt FROM b_calendar_access 
                     WHERE ACCESS_CODE = 'DR24' AND TASK_ID = 35 AND SECT_ID = " . $row['ID'];
        $checkResult = $DB->Query($checkSql);
        $checkRow = $checkResult->Fetch();
        
        if ($checkRow['cnt'] > 0) {
            HRLog("Права уже есть для SECT_ID=" . $row['ID'] . ", пропускаем");
            return;
        }
        
        // Добавляем права
        $DB->Query("
            INSERT INTO b_calendar_access (ACCESS_CODE, TASK_ID, SECT_ID) 
            VALUES ('DR24', 35, " . $row['ID'] . ")
        ");
        
        HRLog("УСПЕХ: Добавлены права DR24 для SECT_ID=" . $row['ID'] . " (пользователь ID=" . $userId . ")");
    } else {
        HRLog("ОШИБКА: Календарь не найден для пользователя ID=" . $userId);
    }
}