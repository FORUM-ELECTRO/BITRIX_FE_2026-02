<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

// AJAX-обработчик страницы «Анализ компании Сбер»: поиск, недавние и анализ контрагента.
Loc::loadMessages(__FILE__);

header('Content-Type: application/json; charset=UTF-8');

if (!Loader::includeModule('crm')) {
    echo json_encode(['error' => Loc::getMessage('SBER_AJAX_ERROR_CRM')]);
    die();
}

$historyFile = $_SERVER['DOCUMENT_ROOT'] . '/local/activities/custom/companysearchsberactivity/page/sber_history.php';
if (is_file($historyFile)) {
    require_once $historyFile;
}

$config = include $_SERVER['DOCUMENT_ROOT'] . '/local/activities/custom/companysearchsberactivity/config.php';

$request = Context::getCurrent()->getRequest();
$action = $request->getPost('action');

/**
 * Возвращает реквизиты компании из CRM по её ID.
 *
 * @param int $companyId ID компании (ccrm_company.ID).
 *
 * @return array{0: string, 1: string} Пары [ИНН, КПП].
 */
function getCompanyRequisites(int $companyId): array
{
    $inn = '';
    $kpp = '';
    $requisite = new \Bitrix\Crm\EntityRequisite();
    $dbRequisite = $requisite->getList([
        'filter' => [
            'ENTITY_ID' => $companyId,
            'ENTITY_TYPE_ID' => \CCrmOwnerType::Company,
        ],
        'select' => ['RQ_INN', 'RQ_KPP'],
        'limit' => 1,
    ]);
    if ($req = $dbRequisite->fetch()) {
        $inn = $req['RQ_INN'];
        $kpp = $req['RQ_KPP'];
    }
    return [$inn, $kpp];
}

// Поиск компаний по названию, а при цифровом запросе — ещё и по ИНН.
if ($action === 'search') {
    $query = trim($request->getPost('query'));
    if (strlen($query) < 2) {
        echo json_encode(['companies' => []]);
        die();
    }

    $companies = [];
    $res = CCrmCompany::GetList(
        ['TITLE' => 'ASC'],
        ['CHECK_PERMISSIONS' => 'Y', '%TITLE' => $query],
        ['ID', 'TITLE'],
        ['nTopCount' => 10]
    );
    while ($row = $res->Fetch()) {
        list($inn, $kpp) = getCompanyRequisites((int)$row['ID']);
        $companies[] = [
            'ID' => (int)$row['ID'],
            'TITLE' => $row['TITLE'],
            'INN' => $inn,
            'KPP' => $kpp,
        ];
    }

    if (empty($companies) && ctype_digit($query)) {
        $requisite = new \Bitrix\Crm\EntityRequisite();
        $dbRequisite = $requisite->getList([
            'filter' => ['%RQ_INN' => $query],
            'select' => ['ENTITY_ID'],
            'limit' => 10,
        ]);
        while ($req = $dbRequisite->fetch()) {
            $company = CCrmCompany::GetByID($req['ENTITY_ID']);
            if ($company) {
                list($inn, $kpp) = getCompanyRequisites((int)$company['ID']);
                $companies[] = [
                    'ID' => (int)$company['ID'],
                    'TITLE' => $company['TITLE'],
                    'INN' => $inn,
                    'KPP' => $kpp,
                ];
            }
        }
    }

    echo json_encode(['companies' => $companies]);
    die();
}

// Список последних созданных компаний (блок «Популярные»).
if ($action === 'popular') {
    $companies = [];
    $res = CCrmCompany::GetList(
        ['ID' => 'DESC'],
        ['CHECK_PERMISSIONS' => 'Y'],
        ['ID', 'TITLE'],
        ['nTopCount' => 5]
    );
    while ($row = $res->Fetch()) {
        list($inn, $kpp) = getCompanyRequisites((int)$row['ID']);
        $companies[] = [
            'ID' => (int)$row['ID'],
            'TITLE' => $row['TITLE'],
            'INN' => $inn,
            'KPP' => $kpp,
        ];
    }
    echo json_encode(['companies' => $companies]);
    die();
}

// Компании из истории запросов (блок «Недавние»).
if ($action === 'recent') {
    $companies = [];
    if (function_exists('sber_history_get')) {
        $rows = sber_history_get(10);
        $seen = [];
        foreach ($rows as $row) {
            $inn = $row['INN'] ?? '';
            if ($inn === '' || isset($seen[$inn])) continue;
            $seen[$inn] = true;

            $companyId = 0;
            $title = $row['COMPANY_NAME'] ?: $inn;
            $requisite = new \Bitrix\Crm\EntityRequisite();
            $dbRequisite = $requisite->getList([
                'filter' => ['=RQ_INN' => $inn],
                'select' => ['ENTITY_ID'],
                'limit' => 1,
            ]);
            if ($req = $dbRequisite->fetch()) {
                $companyId = (int)$req['ENTITY_ID'];
                $company = CCrmCompany::GetByID($companyId);
                if ($company) $title = $company['TITLE'];
            }
            $companies[] = [
                'ID' => $companyId,
                'TITLE' => $title,
                'INN' => $inn,
                'KPP' => $row['KPP'] ?? '',
            ];
        }
    }
    echo json_encode(['companies' => $companies]);
    die();
}

// Анализ контрагента: вызываем API Сбера напрямую.
if ($action === 'analyze') {
    $inn = trim($request->getPost('inn'));
    $kpp = trim($request->getPost('kpp'));
    $companyId = (int)$request->getPost('company_id');

    if (empty($inn)) {
        echo json_encode(['error' => Loc::getMessage('SBER_AJAX_ERROR_INN_EMPTY')]);
        die();
    }

    // Берём данные из истории, если запись моложе часа
    if (function_exists('sber_history_get_last')) {
        $history = sber_history_get_last($inn);
        if ($history && $history['STATUS'] === 'success') {
            $created = strtotime($history['CREATED_AT']);
            if (time() - $created < 3600) {
                echo json_encode([
                    'success' => true,
                    'indicators' => [
                        'compliance' => [
                            'level' => $history['RISK_LEVEL'] ?: 'BLANK',
                            'name'  => Loc::getMessage('SBER_AJAX_IND_COMPLIANCE'),
                            'desc'  => '',
                        ],
                        'state_sources' => [
                            'level' => $history['STATE_LEVEL'] ?: 'BLANK',
                            'name'  => Loc::getMessage('SBER_AJAX_IND_STATE_SOURCES'),
                            'desc'  => '',
                        ],
                        'finance' => [
                            'level' => $history['FINANCE_LEVEL'] ?: 'BLANK',
                            'name'  => Loc::getMessage('SBER_AJAX_IND_FINANCE'),
                            'desc'  => '',
                        ],
                        'sber_rating' => [
                            'level' => $history['RATING_LEVEL'] ?: 'BLANK',
                            'name'  => $history['RATING_NAME'] ?: Loc::getMessage('SBER_AJAX_IND_RATING'),
                            'desc'  => '',
                        ],
                    ],
                    'report_file' => preg_replace('#^https?://[^/]+#', '', $history['REPORT_FILE'] ?: ''),
                ]);
                die();
            }
        }
    }

    // Данных нет или они устарели — обращаемся к API Сбера
    try {
        $result = callSberApi($inn, $kpp, $config);

        // Сохраняем результат в историю
        if (function_exists('sber_history_add')) {
            global $USER;
            $userId = (is_object($USER) && $USER->IsAuthorized()) ? (int)$USER->GetID() : 0;
            $companyName = '';
            if ($companyId) {
                $company = CCrmCompany::GetByID($companyId);
                if ($company) $companyName = $company['TITLE'];
            }

            sber_history_add([
                'inn' => $inn,
                'kpp' => $kpp,
                'company_name' => $companyName,
                'rating_level' => $result['rating_level'],
                'rating_name' => $result['rating_name'],
                'rating_description' => $result['rating_description'],
                'risk_level' => $result['risk_level'],
                'state_level' => $result['state_level'],
                'finance_level' => $result['finance_level'],
                'report_file' => $result['report_file'],
                'status' => 'success',
                'user_id' => $userId,
            ]);
        }

        echo json_encode([
            'success' => true,
            'indicators' => [
                'compliance' => ['level' => $result['risk_level'] ?: 'BLANK', 'desc' => Loc::getMessage('SBER_AJAX_IND_COMPLIANCE')],
                'state_sources' => ['level' => $result['state_level'] ?: 'BLANK', 'desc' => Loc::getMessage('SBER_AJAX_IND_STATE_SOURCES')],
                'finance' => ['level' => $result['finance_level'] ?: 'BLANK', 'desc' => Loc::getMessage('SBER_AJAX_IND_FINANCE')],
                'sber_rating' => ['level' => $result['rating_level'] ?: 'BLANK', 'desc' => $result['rating_description'] ?: Loc::getMessage('SBER_AJAX_IND_NO_DATA')],
            ],
            'report_file' => $result['report_file'],
        ]);
    } catch (\Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    die();
}

/**
 * Запрос индикаторов надёжности и PDF-отчёта к API Сбера.
 *
 * @param string $inn    ИНН контрагента.
 * @param string $kpp    КПП контрагента (пусто для ИП).
 * @param array  $config Конфигурация из config.php.
 *
 * @return array Массив rating_*, risk_*, state_*, finance_level, report_file.
 *
 * @throws \Exception При отсутствии данных или ошибке HTTP.
 */
function callSberApi(string $inn, string $kpp, array $config): array
{
    $isIp = (strlen($inn) === 12);

    // Индикаторы («светофоры») по списку организаций
    $apiUrl = $config['api_url'] . $config['endpoints']['traffic_lights'];
    $data = ['organizations' => [['inn' => $inn]]];
    if ($isIp) {
        $data['organizations'][0]['kpp'] = '';
    } elseif (!empty($kpp)) {
        $data['organizations'][0]['kpp'] = $kpp;
    }

    $response = curlRequest($apiUrl, json_encode($data), $config, true);
    $responseData = json_decode($response, true);

    if (empty($responseData['message']['organizations'][0]['indicators'])) {
        throw new \Exception(Loc::getMessage('SBER_AJAX_ERROR_NO_DATA'));
    }

    $indicators = $responseData['message']['organizations'][0]['indicators'];
    $result = [
        'rating_level' => '',
        'rating_name' => '',
        'rating_description' => '',
        'risk_level' => '',
        'state_level' => '',
        'finance_level' => '',
        'report_file' => '',
    ];

    foreach ($indicators as $ind) {
        $name = $ind['indicatorSystemName'] ?? '';
        $level = $ind['levelSystemName'] ?? '';
        $desc = $ind['levelDescription'] ?? '';

        if ($name === 'SBER_RATING_TOTAL') {
            $result['rating_name'] = $ind['indicatorName'] ?? '';
            $result['rating_level'] = $level;
            $result['rating_description'] = $desc;
        } elseif ($name === 'COMPLIANCE') {
            $result['risk_level'] = $level;
        } elseif ($name === 'STATE_SOURCES') {
            $result['state_level'] = $level;
        } elseif ($name === 'INNER_BANK_SOURCES') {
            $result['finance_level'] = $level;
        }
    }

    // Ссылка на PDF-отчёт (отдельный запрос)
    try {
        $reportUrl = $config['api_url'] . $config['endpoints']['report_link'];
        $reportData = ['inn' => $inn];
        if ($isIp) {
            $reportData['kpp'] = '0';
        } elseif (!empty($kpp)) {
            $reportData['kpp'] = $kpp;
        }

        $reportResponse = curlRequest($reportUrl, json_encode($reportData), $config, true);
        $reportJson = json_decode($reportResponse, true);

        if (!empty($reportJson['externalLink'])) {
            // Скачиваем PDF
            $fileContent = curlRequest($reportJson['externalLink'], null, $config, false);
            if (!empty($fileContent)) {
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/upload/sber_reports/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
                $fileName = 'report_' . $inn . '_' . date('Ymd_His') . '.pdf';
                file_put_contents($uploadDir . $fileName, $fileContent);
                $result['report_file'] = '/upload/sber_reports/' . $fileName;
            }
        }
    } catch (\Exception $e) {
        // Отчёт необязателен, не прерываем анализ
    }

    return $result;
}

/**
 * Выполняет cURL-запрос к API Сбера.
 *
 * @param string  $url     Полный URL.
 * @param string|null $payload JSON-тело POST (null для GET).
 * @param array       $config  Конфигурация из config.php.
 * @param bool        $isJson  true для JSON-запроса (устанавливает Content-Type).
 *
 * @return string Ответ сервера.
 *
 * @throws \Exception При CURL, HTTP-ошибке или истечении токена (401).
 */
function curlRequest(string $url, ?string $payload, array $config, bool $isJson): string
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $config['timeout'] ?? 30);
    curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12');
    curl_setopt($ch, CURLOPT_SSLCERT, $config['cert_path']);
    curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $config['cert_password']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $config['access_token'],
        ]);
    } else {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/octet-stream']);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) throw new \Exception(Loc::getMessage('SBER_AJAX_ERROR_CURL') . $error);
    if ($httpCode === 401) throw new \Exception(Loc::getMessage('SBER_AJAX_ERROR_TOKEN'));
    if ($httpCode !== 200) throw new \Exception(Loc::getMessage('SBER_AJAX_ERROR_HTTP', ['#CODE#' => $httpCode]));

    return $response;
}