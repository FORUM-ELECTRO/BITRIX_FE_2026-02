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
/**
 * Возвращает реквизиты пачки компаний одним запросом.
 *
 * @param array $companyIds Идентификаторы компаний.
 *
 * @return array ID => ['inn' => string, 'kpp' => string].
 */
/**
 * Подбирает КПП по ИНН через Dadata.
 *
 * Сбер формирует отчёт только при известном КПП, а для ручного ввода его
 * нет. Dadata отдаёт КПП по ИНН, поэтому пользователю достаточно ИНН.
 *
 * @param string $inn ИНН организации.
 *
 * @return string КПП или пустая строка.
 */
function dadataParty(string $inn): array
{
    static $cache = [];

    if (isset($cache[$inn])) {
        return $cache[$inn];
    }

    $cache[$inn] = ['kpp' => '', 'name' => ''];

    if (!preg_match('/^\d{10}$|^\d{12}$/', $inn)) {
        return $cache[$inn];
    }

    $configFile = $_SERVER['DOCUMENT_ROOT']
        . '/local/activities/custom/companysearchactivity/config.php';

    if (!is_file($configFile)) {
        return $cache[$inn];
    }

    $dadata = include $configFile;
    $apiKey = trim((string)($dadata['api_key'] ?? ''));

    if ($apiKey === '') {
        return $cache[$inn];
    }

    $ch = curl_init('https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['query' => $inn]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Token ' . $apiKey,
        ],
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode((string)$response, true);
    $party = $data['suggestions'][0] ?? [];

    $cache[$inn] = [
        'kpp' => (string)($party['data']['kpp'] ?? ''),
        'name' => (string)($party['data']['name']['short_with_opf'] ?? ($party['value'] ?? '')),
    ];

    return $cache[$inn];
}

/**
 * Подбирает КПП по ИНН через Dadata.
 *
 * @param string $inn ИНН организации.
 *
 * @return string КПП или пустая строка.
 */
function findKppByInn(string $inn): string
{
    return dadataParty($inn)['kpp'];
}

function getRequisitesMap(array $companyIds): array
{
    $map = [];

    if (!$companyIds) {
        return $map;
    }

    $res = \Bitrix\Crm\RequisiteTable::getList([
        'filter' => [
            '@ENTITY_ID' => $companyIds,
            '=ENTITY_TYPE_ID' => \CCrmOwnerType::Company,
        ],
        'select' => ['ENTITY_ID', 'RQ_INN', 'RQ_KPP'],
    ]);

    while ($row = $res->fetch()) {
        $map[(int)$row['ENTITY_ID']] = [
            'inn' => (string)$row['RQ_INN'],
            'kpp' => (string)$row['RQ_KPP'],
        ];
    }

    return $map;
}

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
if ($action === 'list') {
    // Список компаний по алфавиту: только те, у кого в реквизитах есть ИНН.
    // Перебираем с запасом, чтобы даже при малом числе заполненных карточек
    // набрать осмысленный список.
    $rows = [];
    $res = \Bitrix\Crm\CompanyTable::getList([
        'select' => ['ID', 'TITLE'],
        'order' => ['TITLE' => 'ASC'],
        'limit' => 1000,
    ]);
    while ($row = $res->fetch()) {
        $rows[] = $row;
    }

    $requisites = getRequisitesMap(array_map(static function ($row) {
        return (int)$row['ID'];
    }, $rows));

    $companies = [];

    foreach ($rows as $row) {
        $companyId = (int)$row['ID'];
        $req = $requisites[$companyId] ?? ['inn' => '', 'kpp' => ''];

        if ($req['inn'] === '' || !preg_match('/^\d{10}$|^\d{12}$/', $req['inn'])) {
            continue;
        }

        $title = trim((string)$row['TITLE']);

        // Карточки с числом вместо названия («123456789», «0») в списке бесполезны.
        if ($title === '' || !preg_match('/[а-яa-z]/iu', $title)) {
            continue;
        }

        $companies[] = [
            'ID' => $companyId,
            'TITLE' => $title,
            'INN' => $req['inn'],
            'KPP' => $req['kpp'],
        ];

        if (count($companies) >= 20) {
            break;
        }
    }

    echo json_encode(['companies' => $companies]);
    die();
}

if ($action === 'lookup') {
    // Название организации по ИНН — для ручного ввода без карточки в CRM.
    $inn = trim($request->getPost('inn'));
    $party = dadataParty($inn);

    echo json_encode([
        'name' => $party['name'],
        'kpp' => $party['kpp'],
    ]);
    die();
}

if ($action === 'search') {
    $query = trim($request->getPost('query'));
    if (mb_strlen($query) < 2) {
        echo json_encode(['companies' => []]);
        die();
    }

    $companies = [];
    $seen = [];

    // 1. Поиск по названию: подстрока, а не только начало строки.
    // Берём с запасом: фильтр по ИНН применяем после выборки, иначе
    // первые десять мусорных карточек съедают весь лимит.
    $rows = [];
    $res = \Bitrix\Crm\CompanyTable::getList([
        'select' => ['ID', 'TITLE'],
        'filter' => ['%TITLE' => $query],
        'order' => ['TITLE' => 'ASC'],
        'limit' => 1000,
    ]);
    while ($row = $res->fetch()) {
        $rows[] = $row;
    }

    $requisites = getRequisitesMap(array_map(static function ($row) {
        return (int)$row['ID'];
    }, $rows));

    foreach ($rows as $row) {
        $companyId = (int)$row['ID'];
        $req = $requisites[$companyId] ?? ['inn' => '', 'kpp' => ''];

        // Карточки без ИНН (технические записи вроде «2000х600х1000: поз 8»)
        // в отчёт не годятся: анализ идёт по ИНН.
        if ($req['inn'] === '') {
            continue;
        }

        $companies[] = [
            'ID' => $companyId,
            'TITLE' => $row['TITLE'],
            'INN' => $req['inn'],
            'KPP' => $req['kpp'],
        ];
        $seen[$companyId] = true;

        if (count($companies) >= 20) {
            break;
        }
    }

    @file_put_contents(
        $_SERVER['DOCUMENT_ROOT'] . '/upload/sber_search.log',
        date('c') . ' query=' . $query . ' total=' . \Bitrix\Crm\CompanyTable::getCount()
            . ' companies=' . count($rows) . ' result=' . count($companies) . "\n",
        FILE_APPEND
    );

    // 2. Поиск по ИНН/КПП: выполняется всегда, а не только когда по названию пусто.
    if (preg_match('/^\d+$/', $query)) {
        $requisite = new \Bitrix\Crm\EntityRequisite();
        $dbRequisite = $requisite->getList([
            // ENTITY_TYPE_ID = 4 — компания: иначе по ID можно поймать контакт.
            'filter' => ['%RQ_INN' => $query, 'ENTITY_TYPE_ID' => \CCrmOwnerType::Company],
            'select' => ['ENTITY_ID'],
            'limit' => 10,
        ]);
        while ($req = $dbRequisite->fetch()) {
            $companyId = (int)$req['ENTITY_ID'];
            if (isset($seen[$companyId]) || $companyId <= 0) {
                continue;
            }
            $company = CCrmCompany::GetByID($companyId);
            if (!$company) {
                continue;
            }
            list($inn, $kpp) = getCompanyRequisites($companyId);
            $companies[] = [
                'ID' => $companyId,
                'TITLE' => $company['TITLE'],
                'INN' => $inn,
                'KPP' => $kpp,
            ];
            $seen[$companyId] = true;
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
        [],
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
        // Из истории берём только записи с готовым отчётом: иначе кешируется пустой ответ.
        if ($history && $history['STATUS'] === 'success' && !empty($history['REPORT_FILE'])) {
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

    // КПП нужен Сберу для отчёта: если его нет, подбираем по ИНН.
    if (empty($kpp)) {
        $kpp = findKppByInn($inn);
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
        // Диагностика: причина сбоя анализа нужна при разборе жалоб.
        @file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/upload/sber_analyze_errors.log',
            date('c') . ' inn=' . $inn . ' kpp=' . $kpp . ' company=' . $companyId
                . ' :: ' . $e->getMessage() . "\n",
            FILE_APPEND
        );

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
function callSberApi(string $inn, string $kpp, array &$config): array
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

    try {
    $response = curlRequest($apiUrl, json_encode($data), $config, true);
} catch (\Exception $e) {
    if (strpos($e->getMessage(), 'Токен') !== false) {
        $newToken = refreshSberToken($config);
        if ($newToken) {
            $response = curlRequest($apiUrl, json_encode($data), $config, true);
        } else {
            throw $e;
        }
    } else {
        throw $e;
    }
}
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
            // Файл у Сбера готовится не мгновенно: пустой ответ 204 повторяем.
            $fileContent = '';

            for ($attempt = 0; $attempt < 3; $attempt++) {
                try {
                    $fileContent = curlRequest($reportJson['externalLink'], null, $config, false);
                } catch (\Exception $e) {
                    $fileContent = '';
                }

                if (!empty($fileContent) && strpos($fileContent, '%PDF') === 0) {
                    break;
                }

                sleep(1);
            }

            if (!empty($fileContent) && strpos($fileContent, '%PDF') === 0) {
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/upload/sber_reports/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
                $fileName = 'report_' . $inn . '_' . date('Ymd_His') . '.pdf';
                $filePath = $uploadDir . $fileName;
                $written = file_put_contents($filePath, $fileContent);
                if ($written && is_file($filePath)) {
                    $result['report_file'] = '/upload/sber_reports/' . $fileName;
                }
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
function curlRequest(string $url, ?string $payload, array &$config, bool $isJson): string
{
    $maxRetries = 2;
    $attempt = 0;

    while ($attempt < $maxRetries) {
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

        // Если 401 — обновляем токен и повторяем
        if ($httpCode === 401 && $attempt === 0) {
            $newToken = refreshSberToken($config);
            if ($newToken) {
                $attempt++;
                continue;
            }
            throw new \Exception(Loc::getMessage('SBER_AJAX_ERROR_TOKEN'));
        }

        if ($httpCode !== 200) {
            throw new \Exception(Loc::getMessage('SBER_AJAX_ERROR_HTTP', ['#CODE#' => $httpCode]));
        }

        return $response;
    }

    throw new \Exception(Loc::getMessage('SBER_AJAX_ERROR_TOKEN'));
}

/**
 * Обновляет токен через refresh_token и сохраняет в config.php.
 *
 * @param array $config Конфигурация (по ссылке).
 * @return string|null Новый access_token или null при ошибке.
 */
function refreshSberToken(array &$config): ?string
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fintech.sberbank.ru:9443/ic/sso/api/v2/oauth/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => '35107',
        'client_secret' => $config['client_secret'],
        'grant_type' => 'refresh_token',
        'refresh_token' => $config['refresh_token'] ?? '',
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12');
    curl_setopt($ch, CURLOPT_SSLCERT, $config['cert_path']);
    curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $config['cert_password']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (empty($data['access_token'])) {
        return null;
    }

    $config['access_token'] = $data['access_token'];
    $config['refresh_token'] = $data['refresh_token'];

    // Сохраняем в config.php
    $configContent = "<?php\n\n/**\n * Конфигурация для Sber API.\n *\n * @package CompanySearchSberActivity\n */\n\nreturn " . var_export($config, true) . ";\n";
    @file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/activities/custom/companysearchsberactivity/config.php', $configContent);

    return $data['access_token'];
}