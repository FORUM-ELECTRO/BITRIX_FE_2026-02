<?php

/**
 * Кастомная активность для поиска компании через Sber API.
 *
 * @package CompanySearchSberActivity
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\Activity\PropertiesDialog;

Loc::loadMessages(__FILE__);

// Хранилище истории запросов (общее со страницей «Отчёт Сбер» / page/*)
$sberHistoryFile = __DIR__ . '/page/sber_history.php';
if (is_file($sberHistoryFile)) {
    require_once $sberHistoryFile;
}

/**
 * Класс активности для работы с Sber API.
 */
class CBPCompanySearchSberActivity extends BaseActivity
{
    /** @var array Конфигурация из config.php */
    private $config;

    /**
     * Конструктор активности.
     *
     * Инициализирует свойства, их типы и загружает конфигурацию из config.php.
     *
     * @param string $name Имя активности.
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->arProperties = [
            'INN' => '',
            'KPP' => '',

            // СберРейтинг
            'RatingName' => null,
            'RatingLevel' => null,
            'RatingDescription' => null,
            'RatingHint' => null,

            // Риск блокировки
            'RiskName' => null,
            'RiskLevel' => null,
            'RiskDescription' => null,
            'RiskHint' => null,

            // Госисточники
            'StateName' => null,
            'StateLevel' => null,
            'StateDescription' => null,
            'StateHint' => null,

            // Финансовый анализ
            'FinanceName' => null,
            'FinanceLevel' => null,
            'FinanceDescription' => null,
            'FinanceHint' => null,

            // Отчёт
            'ReportLink' => null,
            'FileId' => null,
            'Random' => null,
            'ReportFile' => null,

            'ErrorMessage' => null,
        ];

        $this->SetPropertiesTypes([
            'INN' => ['Type' => FieldType::STRING],
            'KPP' => ['Type' => FieldType::STRING],
            'RatingName' => ['Type' => FieldType::STRING],
            'RatingLevel' => ['Type' => FieldType::STRING],
            'RatingDescription' => ['Type' => FieldType::STRING],
            'RatingHint' => ['Type' => FieldType::STRING],
            'RiskName' => ['Type' => FieldType::STRING],
            'RiskLevel' => ['Type' => FieldType::STRING],
            'RiskDescription' => ['Type' => FieldType::STRING],
            'RiskHint' => ['Type' => FieldType::STRING],
            'StateName' => ['Type' => FieldType::STRING],
            'StateLevel' => ['Type' => FieldType::STRING],
            'StateDescription' => ['Type' => FieldType::STRING],
            'StateHint' => ['Type' => FieldType::STRING],
            'FinanceName' => ['Type' => FieldType::STRING],
            'FinanceLevel' => ['Type' => FieldType::STRING],
            'FinanceDescription' => ['Type' => FieldType::STRING],
            'FinanceHint' => ['Type' => FieldType::STRING],
            'ReportLink' => ['Type' => FieldType::STRING],
            'FileId' => ['Type' => FieldType::STRING],
            'Random' => ['Type' => FieldType::STRING],
            'ReportFile' => ['Type' => FieldType::STRING],
            'ErrorMessage' => ['Type' => FieldType::STRING],
        ]);

        $this->config = include __DIR__ . '/config.php';
    }

    /**
     * Возвращает путь к текущему файлу.
     *
     * @return string
     */
    protected static function getFileName(): string
    {
        return __FILE__;
    }

    /**
     * Основной метод выполнения активности.
     *
     * Читает ИНН/КПП, запрашивает индикаторы и PDF-отчёт, сохраняет
     * результаты в свойства и пишет запись в историю запросов.
     *
     * @return ErrorCollection Ошибки выполнения (пусто при успехе).
     */
    protected function internalExecute(): ErrorCollection
    {
        $this->log(Loc::getMessage('COMPANY_SEARCH_LOG_START'));

        $errors = parent::internalExecute();

        $inn = trim($this->INN ?? '');
        $kpp = trim($this->KPP ?? '');

        $this->log(Loc::getMessage('COMPANY_SEARCH_LOG_INN_KPP', ['#INN#' => $inn, '#KPP#' => $kpp]));

        if (empty($inn)) {
            $this->preparedProperties['ErrorMessage'] = Loc::getMessage('COMPANY_SEARCH_ERROR_INN_REQUIRED');
            $this->log(Loc::getMessage('COMPANY_SEARCH_LOG_ERROR_EMPTY_INN'));
            $errors->add([new Error(Loc::getMessage('COMPANY_SEARCH_ERROR_INN_REQUIRED'))]);
            return $errors;
        }

        $this->log(Loc::getMessage('COMPANY_SEARCH_LOG_SEND_API'));

        try {
            $companyData = $this->getCompanyDataFromSber($inn, $kpp);

            $this->preparedProperties['INN'] = $companyData['inn'] ?? $inn;
            $this->preparedProperties['KPP'] = $companyData['kpp'] ?? $kpp;

            $this->preparedProperties['RatingName'] = $companyData['rating_name'] ?? '';
            $this->preparedProperties['RatingLevel'] = $companyData['rating_level'] ?? '';
            $this->preparedProperties['RatingDescription'] = $companyData['rating_description'] ?? '';
            $this->preparedProperties['RatingHint'] = $companyData['rating_hint'] ?? '';

            $this->preparedProperties['RiskName'] = $companyData['risk_name'] ?? '';
            $this->preparedProperties['RiskLevel'] = $companyData['risk_level'] ?? '';
            $this->preparedProperties['RiskDescription'] = $companyData['risk_description'] ?? '';
            $this->preparedProperties['RiskHint'] = $companyData['risk_hint'] ?? '';

            $this->preparedProperties['StateName'] = $companyData['state_name'] ?? '';
            $this->preparedProperties['StateLevel'] = $companyData['state_level'] ?? '';
            $this->preparedProperties['StateDescription'] = $companyData['state_description'] ?? '';
            $this->preparedProperties['StateHint'] = $companyData['state_hint'] ?? '';

            $this->preparedProperties['FinanceName'] = $companyData['finance_name'] ?? '';
            $this->preparedProperties['FinanceLevel'] = $companyData['finance_level'] ?? '';
            $this->preparedProperties['FinanceDescription'] = $companyData['finance_description'] ?? '';
            $this->preparedProperties['FinanceHint'] = $companyData['finance_hint'] ?? '';

            // Получение ссылки на отчёт и сохранение PDF
            if (!empty($inn)) {
                try {
                    $reportData = $this->getReportLink($inn, $kpp);
                    $this->preparedProperties['ReportLink'] = $reportData['link'] ?? '';
                    $this->preparedProperties['Random'] = $reportData['random'] ?? '';
                    $this->preparedProperties['FileId'] = $reportData['fileId'] ?? '';

                    if (!empty($reportData['link'])) {
                        $fileContent = $this->downloadFile($reportData['link']);

                        // Создаём папку, если её нет
                        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/upload/sber_reports/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0775, true);
                        }

                        // Формируем имя файла с датой
                        $fileName = 'report_' . $inn . '_' . date('Ymd_His') . '.pdf';
                        $filePath = $uploadDir . $fileName;

                        // Сохраняем файл
                        file_put_contents($filePath, $fileContent);

                        // Сохраняем ссылку на файл для публичного доступа
                        $this->preparedProperties['ReportFile'] = '/upload/sber_reports/' . $fileName;
                        $this->log(Loc::getMessage('COMPANY_SEARCH_LOG_PDF_SAVED') . $filePath);
                    }
                } catch (\Exception $e) {
                    $this->log(Loc::getMessage('COMPANY_SEARCH_LOG_REPORT_ERROR') . $e->getMessage());
                    $this->preparedProperties['ReportLink'] = $reportData['link']
                        ?? Loc::getMessage('COMPANY_SEARCH_ERROR_REPORT_LINK_FAIL');
                }
            }

            $this->log(Loc::getMessage('COMPANY_SEARCH_LOG_DATA_RECEIVED'));
        } catch (\Exception $e) {
            $this->preparedProperties['ErrorMessage'] = Loc::getMessage('COMPANY_SEARCH_ERROR_API') . $e->getMessage();
            $this->log(Loc::getMessage('COMPANY_SEARCH_LOG_API_ERROR') . $e->getMessage());
            $errors->add([new Error($e->getMessage())]);
        }

        // Сохраняем результат в историю запросов (её показывает слайдер «Отчёт Сбер»)
        $this->writeHistory($inn, $kpp);

        $this->log(Loc::getMessage('COMPANY_SEARCH_LOG_FINISH'));

        return $errors;
    }

    /**
     * Пишет результат текущего выполнения в таблицу истории запросов.
     *
     * Таблица создаётся автоматически (см. page/sber_history.php).
     *
     * @param string      $inn ИНН.
     * @param string|null $kpp КПП.
     *
     * @return void
     */
    private function writeHistory(string $inn, ?string $kpp = null): void
    {
        if (!function_exists('sber_history_add')) {
            return;
        }

        $userId = 0;
        global $USER;
        if (is_object($USER) && $USER->IsAuthorized()) {
            $userId = (int)$USER->GetID();
        }

        $error = (string)($this->preparedProperties['ErrorMessage'] ?? '');

        if ($error !== '') {
            sber_history_add([
                'inn'           => $inn,
                'kpp'           => $kpp,
                'status'        => 'error',
                'error_message' => $error,
                'user_id'       => $userId,
            ]);
            return;
        }

        // Отчёт: приоритет сохранённому PDF, иначе — исходная ссылка Сбера
        $reportFile = (string)($this->preparedProperties['ReportFile'] ?? '');
        if ($reportFile === '') {
            $reportFile = (string)($this->preparedProperties['ReportLink'] ?? '');
        }

        sber_history_add([
            'inn'                => (string)($this->preparedProperties['INN'] ?? $inn),
            'kpp'                => (string)($this->preparedProperties['KPP'] ?? $kpp),
            'rating_level'       => (string)($this->preparedProperties['RatingLevel'] ?? ''),
            'rating_name'        => (string)($this->preparedProperties['RatingName'] ?? ''),
            'rating_description' => (string)($this->preparedProperties['RatingDescription'] ?? ''),
            'risk_level'         => (string)($this->preparedProperties['RiskLevel'] ?? ''),
            'state_level'        => (string)($this->preparedProperties['StateLevel'] ?? ''),
            'finance_level'      => (string)($this->preparedProperties['FinanceLevel'] ?? ''),
            'report_file'        => $reportFile,
            'status'             => 'success',
            'user_id'            => $userId,
        ]);
    }

    /**
     * Получение данных о компании через API Сбера (метод traffic-lights).
     *
     * При ответе 401 один раз обновляет токен и повторяет запрос.
     *
     * @param string      $inn ИНН.
     * @param string|null $kpp КПП (для ИП не передаётся).
     *
     * @return array Массив с rating_*, risk_*, state_*, finance_* полями.
     *
     * @throws \Exception При CURL/HTTP-ошибке или отсутствии данных.
     */
    private function getCompanyDataFromSber(string $inn, ?string $kpp = null): array
    {
        $apiUrl = $this->config['api_url'] . $this->config['endpoints']['traffic_lights'];

        $this->log(Loc::getMessage('COMPANY_SEARCH_LOG_API_REQUEST') . $apiUrl);

        // Определяем ИП по длине ИНН (12 цифр)
        $isIp = (strlen($inn) === 12);

        // Формируем массив с ИНН
        $data = [
            'organizations' => [
                [
                    'inn' => $inn,
                ],
            ],
        ];

        // Для ИП передаём КПП = "0", для юрлиц — если передан
        if ($isIp) {
            $data['organizations'][0]['kpp'] = '';
        } elseif (!empty($kpp)) {
            $data['organizations'][0]['kpp'] = $kpp;
        }

        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['timeout'] ?? 30);

        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12');
        curl_setopt($ch, CURLOPT_SSLCERT, $this->config['cert_path']);
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->config['cert_password']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $accessToken = $this->config['access_token'] ?? '';

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $accessToken,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Если токен истёк — обновляем и повторяем запрос
        if ($httpCode === 401) {
            $this->refreshToken();
            // Создаём новый curl и повторяем запрос
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['timeout'] ?? 30);
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12');
            curl_setopt($ch, CURLOPT_SSLCERT, $this->config['cert_path']);
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->config['cert_password']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $this->config['access_token'],
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
        }

        if ($error) {
            throw new \Exception(Loc::getMessage('COMPANY_SEARCH_ERROR_CURL') . $error);
        }

        if ($httpCode !== 200) {
            throw new \Exception(Loc::getMessage('COMPANY_SEARCH_ERROR_HTTP') . $httpCode . ', ответ: ' . $response);
        }

        $data = json_decode($response, true);

        $this->log(Loc::getMessage('COMPANY_SEARCH_LOG_RESPONSE') . print_r($data, true));

        if (empty($data['message']['organizations'][0]['indicators'])) {
            throw new \Exception(Loc::getMessage('COMPANY_SEARCH_ERROR_NO_DATA'));
        }

        $orgData = $data['message']['organizations'][0];
        $indicators = $orgData['indicators'];

        $result = [
            'inn' => $orgData['inn'] ?? $inn,
            'kpp' => $orgData['kpp'] ?? $kpp,
            'rating_name' => '',
            'rating_level' => '',
            'rating_description' => '',
            'rating_hint' => '',
            'risk_name' => '',
            'risk_level' => '',
            'risk_description' => '',
            'risk_hint' => '',
            'state_name' => '',
            'state_level' => '',
            'state_description' => '',
            'state_hint' => '',
            'finance_name' => '',
            'finance_level' => '',
            'finance_description' => '',
            'finance_hint' => '',
        ];

        foreach ($indicators as $indicator) {
            $systemName = $indicator['indicatorSystemName'] ?? '';
            $name = $indicator['indicatorName'] ?? '';
            $level = $indicator['levelSystemName'] ?? '';
            $description = $indicator['levelDescription'] ?? '';
            $hint = $indicator['hintText'] ?? '';

            if ($systemName === 'SBER_RATING_TOTAL') {
                $result['rating_name'] = $name;
                $result['rating_level'] = $level;
                $result['rating_description'] = $description;
                $result['rating_hint'] = $hint;
            } elseif ($systemName === 'COMPLIANCE') {
                $result['risk_name'] = $name;
                $result['risk_level'] = $level;
                $result['risk_description'] = $description;
                $result['risk_hint'] = $hint;
            } elseif ($systemName === 'STATE_SOURCES') {
                $result['state_name'] = $name;
                $result['state_level'] = $level;
                $result['state_description'] = $description;
                $result['state_hint'] = $hint;
            } elseif ($systemName === 'INNER_BANK_SOURCES') {
                $result['finance_name'] = $name;
                $result['finance_level'] = $level;
                $result['finance_description'] = $description;
                $result['finance_hint'] = $hint;
            }
        }

        return $result;
    }

    /**
     * Получение ссылки на PDF-отчёт.
     *
     * @param string      $inn ИНН.
     * @param string|null $kpp КПП (может быть null для ИП).
     *
     * @return array{link: string, random: string, fileId: string}
     *
     * @throws \Exception При CURL/HTTP-ошибке или отсутствии ссылки в ответе.
     */
    private function getReportLink(string $inn, ?string $kpp = null): array
    {
        $apiUrl = $this->config['api_url'] . $this->config['endpoints']['report_link'];

        // Определяем ИП по длине ИНН (12 цифр)
        $isIp = (strlen($inn) === 12);

        // Формируем массив с ИНН
        $data = ['inn' => $inn];

        // Для ИП передаём КПП = "0", для юрлиц — если передан
        if ($isIp) {
            $data['kpp'] = '0';
        } elseif (!empty($kpp)) {
            $data['kpp'] = $kpp;
        }

        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['timeout'] ?? 30);

        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12');
        curl_setopt($ch, CURLOPT_SSLCERT, $this->config['cert_path']);
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->config['cert_password']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $accessToken = $this->config['access_token'] ?? '';

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $accessToken,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception(Loc::getMessage('COMPANY_SEARCH_ERROR_CURL') . $error);
        }

        if ($httpCode !== 200) {
            throw new \Exception(Loc::getMessage('COMPANY_SEARCH_ERROR_HTTP') . $httpCode . ', ответ: ' . $response);
        }

        $data = json_decode($response, true);

        $this->log(Loc::getMessage('COMPANY_SEARCH_LOG_REPORT_LINK') . print_r($data, true));

        if (empty($data['externalLink'])) {
            throw new \Exception(Loc::getMessage('COMPANY_SEARCH_ERROR_REPORT_LINK_NOT_FOUND'));
        }

        $link = $data['externalLink'];

        preg_match('/download-file\/([^\/]+)\/([^\/]+)/', $link, $matches);

        return [
            'link' => $link,
            'random' => $matches[1] ?? '',
            'fileId' => $matches[2] ?? '',
        ];
    }

    /**
     * Скачивание PDF-файла по ссылке.
     *
     * @param string $fileUrl Полная ссылка на файл.
     *
     * @return string Бинарное содержимое файла (пусто при HTTP 204).
     *
     * @throws \Exception При CURL/HTTP-ошибке.
     */
    private function downloadFile(string $fileUrl): string
    {
        $this->log(Loc::getMessage('COMPANY_SEARCH_LOG_DOWNLOAD_FILE') . $fileUrl);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fileUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12');
        curl_setopt($ch, CURLOPT_SSLCERT, $this->config['cert_path']);
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->config['cert_password']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $accessToken = $this->config['access_token'] ?? '';
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/octet-stream',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Логируем результат
        $this->log(Loc::getMessage('COMPANY_SEARCH_LOG_HTTP_CODE') . $httpCode);
        $this->log(Loc::getMessage('COMPANY_SEARCH_LOG_RESPONSE_SIZE') . strlen($response));
        if ($httpCode !== 200) {
            $this->log(Loc::getMessage('COMPANY_SEARCH_LOG_RESPONSE_BODY') . substr($response, 0, 500));
        }

        if ($error) {
            throw new \Exception(Loc::getMessage('COMPANY_SEARCH_ERROR_CURL') . $error);
        }

        if ($httpCode === 204) {
            $this->log(Loc::getMessage('COMPANY_SEARCH_LOG_NO_REPORT_DATA'));
            return '';
        }

        if ($httpCode !== 200) {
            throw new \Exception(Loc::getMessage('COMPANY_SEARCH_ERROR_DOWNLOAD') . $httpCode);
        }

        return $response;
    }

    /**
     * Обновление пары токенов по refresh_token.
     *
     * Обновляет access_token/refresh_token во внутреннем конфиге;
     * при отсутствии токена в ответе молча выходит.
     *
     * @return void
     */
    private function refreshToken()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fintech.sberbank.ru:9443/ic/sso/api/v2/oauth/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => '35107',
            'client_secret' => $this->config['client_secret'],
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->config['refresh_token'] ?? ''
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12');
        curl_setopt($ch, CURLOPT_SSLCERT, $this->config['cert_path']);
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->config['cert_password']);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (empty($data['access_token'])) {
            return;
        }

        $this->config['access_token'] = $data['access_token'];
        $this->config['refresh_token'] = $data['refresh_token'];
    }

    /**
     * Карта свойств для диалога настройки в конструкторе БП.
     *
     * @param PropertiesDialog|null $dialog Диалог свойств (не используется).
     *
     * @return array[]
     */
    public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
    {
        return [
            'INN' => [
                'Name' => Loc::getMessage('SEARCHBYINN_ACTIVITY_FIELD_SUBJECT'),
                'FieldName' => 'inn',
                'Type' => FieldType::STRING,
                'Required' => true,
            ],
            'KPP' => [
                'Name' => Loc::getMessage('SEARCHBYINN_ACTIVITY_FIELD_KPP'),
                'FieldName' => 'kpp',
                'Type' => FieldType::STRING,
                'Required' => false,
            ],
        ];
    }
}