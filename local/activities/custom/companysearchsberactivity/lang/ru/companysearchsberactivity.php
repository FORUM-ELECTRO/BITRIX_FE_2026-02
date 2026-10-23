<?php

// Названия полей в настройках активности
$MESS['SEARCHBYINN_ACTIVITY_FIELD_SUBJECT'] = 'ИНН';
$MESS['SEARCHBYINN_ACTIVITY_FIELD_KPP'] = 'КПП';

// Логи
$MESS['COMPANY_SEARCH_LOG_START'] = '1. Начало выполнения';
$MESS['COMPANY_SEARCH_LOG_INN_KPP'] = '2. ИНН: #INN#, КПП: #KPP#';
$MESS['COMPANY_SEARCH_LOG_ERROR_EMPTY_INN'] = 'Ошибка: ИНН не указан';
$MESS['COMPANY_SEARCH_LOG_SEND_API'] = '3. Параметры заполнены, отправляем запрос к API';
$MESS['COMPANY_SEARCH_LOG_DATA_RECEIVED'] = '4. Данные получены';
$MESS['COMPANY_SEARCH_LOG_FINISH'] = '5. Завершение выполнения';
$MESS['COMPANY_SEARCH_LOG_API_ERROR'] = 'Ошибка API: ';
$MESS['COMPANY_SEARCH_LOG_REPORT_ERROR'] = 'Ошибка получения отчета: ';
$MESS['COMPANY_SEARCH_LOG_REPORT_LINK'] = 'Ссылка на отчет: ';
$MESS['COMPANY_SEARCH_LOG_DOWNLOAD_FILE'] = 'Скачивание файла: ';
$MESS['COMPANY_SEARCH_LOG_API_REQUEST'] = 'Запрос к API: ';
$MESS['COMPANY_SEARCH_LOG_PDF_SAVED'] = 'PDF сохранён: ';
$MESS['COMPANY_SEARCH_LOG_HTTP_CODE'] = 'HTTP код: ';
$MESS['COMPANY_SEARCH_LOG_RESPONSE_SIZE'] = 'Размер ответа: ';
$MESS['COMPANY_SEARCH_LOG_RESPONSE_BODY'] = 'Тело ответа (первые 500 символов): ';
$MESS['COMPANY_SEARCH_LOG_NO_REPORT_DATA'] = 'HTTP 204: Нет данных для формирования отчёта';
$MESS['COMPANY_SEARCH_LOG_RESPONSE'] = 'Ответ API: ';

// Ошибки
$MESS['COMPANY_SEARCH_ERROR_INN_REQUIRED'] = 'Необходимо указать ИНН компании';
$MESS['COMPANY_SEARCH_ERROR_CURL'] = 'CURL ошибка: ';
$MESS['COMPANY_SEARCH_ERROR_HTTP'] = 'HTTP ошибка: ';
$MESS['COMPANY_SEARCH_ERROR_NO_DATA'] = 'Данные по компании не найдены';
$MESS['COMPANY_SEARCH_ERROR_REPORT_LINK_NOT_FOUND'] = 'Ссылка на отчет не получена';
$MESS['COMPANY_SEARCH_ERROR_REPORT_LINK_FAIL'] = 'Ошибка: ссылка не получена';
$MESS['COMPANY_SEARCH_ERROR_API'] = 'Ошибка API: ';
$MESS['COMPANY_SEARCH_ERROR_DOWNLOAD'] = 'Ошибка скачивания: ';