<?php

/**
 * Описание активности "Поиск компании Сбер".
 *
 * @package CompanySearchSberActivity
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\FieldType;

Loc::loadMessages(__FILE__);

$arActivityDescription = [
    // Основная информация
    'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_NAME'),
    'DESCRIPTION' => Loc::getMessage('COMPANY_SEARCH_SBER_DESC'),
    'TYPE' => 'activity',
    'CLASS' => 'CompanySearchSberActivity',
    'JSCLASS' => 'BizProcActivity',

    // Категория в конструкторе бизнес-процессов
    'CATEGORY' => [
        'ID' => 'service',
        'OWN_ID' => 'service',
        'OWN_NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_CATEGORY'),
    ],

    // Входные параметры активности
    'PROPERTIES' => [
        'INN' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_INN'),
            'TYPE' => FieldType::STRING,
            'REQUIRED' => true,
        ],
        'KPP' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_KPP'),
            'TYPE' => FieldType::STRING,
            'REQUIRED' => false,
        ],
    ],

    // Возвращаемые значения
    'RETURN' => [
        // Основные данные компании
        'INN' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_INN_COMPANY'),
            'TYPE' => FieldType::STRING,
        ],
        'KPP' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_KPP_COMPANY'),
            'TYPE' => FieldType::STRING,
        ],

        // СберРейтинг
        'RatingName' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_RATING_NAME'),
            'TYPE' => FieldType::STRING,
        ],
        'RatingLevel' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_RATING_LEVEL'),
            'TYPE' => FieldType::STRING,
        ],
        'RatingDescription' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_RATING_DESC'),
            'TYPE' => FieldType::STRING,
        ],
        'RatingHint' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_RATING_HINT'),
            'TYPE' => FieldType::STRING,
        ],

        // Риск блокировки
        'RiskName' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_RISK_NAME'),
            'TYPE' => FieldType::STRING,
        ],
        'RiskLevel' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_RISK_LEVEL'),
            'TYPE' => FieldType::STRING,
        ],
        'RiskDescription' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_RISK_DESC'),
            'TYPE' => FieldType::STRING,
        ],
        'RiskHint' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_RISK_HINT'),
            'TYPE' => FieldType::STRING,
        ],

        // Госисточники
        'StateName' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_STATE_NAME'),
            'TYPE' => FieldType::STRING,
        ],
        'StateLevel' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_STATE_LEVEL'),
            'TYPE' => FieldType::STRING,
        ],
        'StateDescription' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_STATE_DESC'),
            'TYPE' => FieldType::STRING,
        ],
        'StateHint' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_STATE_HINT'),
            'TYPE' => FieldType::STRING,
        ],

        // Финансовый анализ
        'FinanceName' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_FINANCE_NAME'),
            'TYPE' => FieldType::STRING,
        ],
        'FinanceLevel' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_FINANCE_LEVEL'),
            'TYPE' => FieldType::STRING,
        ],
        'FinanceDescription' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_FINANCE_DESC'),
            'TYPE' => FieldType::STRING,
        ],
        'FinanceHint' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_FINANCE_HINT'),
            'TYPE' => FieldType::STRING,
        ],

        // Отчёт
        'ReportLink' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_REPORT_LINK'),
            'TYPE' => FieldType::STRING,
        ],
        'ReportFile' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_REPORT_FILE'),
            'TYPE' => FieldType::STRING,
        ],

        // Ошибки
        'ErrorMessage' => [
            'NAME' => Loc::getMessage('COMPANY_SEARCH_SBER_ERROR'),
            'TYPE' => FieldType::STRING,
        ],
    ],
];