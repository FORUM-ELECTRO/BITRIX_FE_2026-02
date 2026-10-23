<?php

/**
 * Хранилище истории запросов «Отчёт Сбер».
 *
 * @package CompanySearchSberActivity
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application;

/** @var string Имя таблицы истории. */
const SBER_HISTORY_TABLE = 'b_sber_search_history';

/**
 * Создаёт таблицу истории при её отсутствии.
 *
 * Ошибки DDL только логируются: отсутствие таблицы не должно валить БП.
 *
 * @return void
 */
function sber_history_ensure_table(): void
{
    try {
        $connection = Application::getConnection();
        if ($connection->isTableExists(SBER_HISTORY_TABLE)) {
            return;
        }

        $connection->queryExecute("
            CREATE TABLE IF NOT EXISTS `" . SBER_HISTORY_TABLE . "` (
                `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `INN` VARCHAR(12) NOT NULL,
                `KPP` VARCHAR(9) NOT NULL DEFAULT '',
                `COMPANY_NAME` VARCHAR(255) NOT NULL DEFAULT '',
                `RATING_LEVEL` VARCHAR(32) NOT NULL DEFAULT '',
                `RATING_NAME` VARCHAR(255) NOT NULL DEFAULT '',
                `RATING_DESCRIPTION` TEXT NULL,
                `RISK_LEVEL` VARCHAR(32) NOT NULL DEFAULT '',
                `STATE_LEVEL` VARCHAR(32) NOT NULL DEFAULT '',
                `FINANCE_LEVEL` VARCHAR(32) NOT NULL DEFAULT '',
                `REPORT_FILE` VARCHAR(500) NOT NULL DEFAULT '',
                `STATUS` VARCHAR(20) NOT NULL DEFAULT 'success',
                `ERROR_MESSAGE` TEXT NULL,
                `USER_ID` INT NOT NULL DEFAULT 0,
                `CREATED_AT` DATETIME NOT NULL,
                PRIMARY KEY (`ID`),
                INDEX `IX_SBER_HISTORY_INN` (`INN`),
                INDEX `IX_SBER_HISTORY_CREATED` (`CREATED_AT`)
            )
        ");
    } catch (\Throwable $e) {
        \AddMessage2Log('sber_history_ensure_table: ' . $e->getMessage(), 'companysearchsberactivity');
    }
}

/**
 * Добавляет результат запроса в историю.
 *
 * Ключи $data: inn, kpp, company_name, rating_level, rating_name,
 * rating_description, risk_level, state_level, finance_level,
 * report_file, status, error_message, user_id, created_at.
 *
 * @param array $data Поля записи, пустые значения допустимы.
 *
 * @return void
 */
function sber_history_add(array $data): void
{
    try {
        sber_history_ensure_table();

        $connection = Application::getConnection();
        $sql = $connection->getSqlHelper();

        $fields = [
            'INN'                => "'" . $sql->forSql((string)($data['inn'] ?? ''), 12) . "'",
            'KPP'                => "'" . $sql->forSql((string)($data['kpp'] ?? ''), 9) . "'",
            'COMPANY_NAME'       => "'" . $sql->forSql((string)($data['company_name'] ?? ''), 255) . "'",
            'RATING_LEVEL'       => "'" . $sql->forSql((string)($data['rating_level'] ?? ''), 32) . "'",
            'RATING_NAME'        => "'" . $sql->forSql((string)($data['rating_name'] ?? ''), 255) . "'",
            'RATING_DESCRIPTION' => "'" . $sql->forSql((string)($data['rating_description'] ?? '')) . "'",
            'RISK_LEVEL'         => "'" . $sql->forSql((string)($data['risk_level'] ?? ''), 32) . "'",
            'STATE_LEVEL'        => "'" . $sql->forSql((string)($data['state_level'] ?? ''), 32) . "'",
            'FINANCE_LEVEL'      => "'" . $sql->forSql((string)($data['finance_level'] ?? ''), 32) . "'",
            'REPORT_FILE'        => "'" . $sql->forSql((string)($data['report_file'] ?? ''), 500) . "'",
            'STATUS'             => "'" . $sql->forSql((string)($data['status'] ?? 'success'), 20) . "'",
            'ERROR_MESSAGE'      => "'" . $sql->forSql((string)($data['error_message'] ?? '')) . "'",
            'USER_ID'            => (int)($data['user_id'] ?? 0),
            'CREATED_AT'         => "'" . $sql->forSql($data['created_at'] ?? date('Y-m-d H:i:s')) . "'",
        ];

        $connection->queryExecute(
            "INSERT INTO " . SBER_HISTORY_TABLE . " (" . implode(', ', array_keys($fields)) . ") VALUES (" . implode(', ', array_values($fields)) . ")"
        );
    } catch (\Throwable $e) {
        \AddMessage2Log('sber_history_add: ' . $e->getMessage(), 'companysearchsberactivity');
    }
}

/**
 * Возвращает последние записи истории по убыванию ID.
 *
 * @param int $limit Количество записей (обрезается диапазоном 1..200).
 *
 * @return array Строки таблицы b_sber_search_history.
 */
function sber_history_get(int $limit = 50): array
{
    $rows = [];
    try {
        sber_history_ensure_table();

        $limit = max(1, min(200, $limit));
        $connection = Application::getConnection();
        $res = $connection->query(
            "SELECT * FROM " . SBER_HISTORY_TABLE . " ORDER BY ID DESC LIMIT " . (int)$limit
        );
        while ($row = $res->fetch()) {
            $rows[] = $row;
        }
    } catch (\Throwable $e) {
        \AddMessage2Log('sber_history_get: ' . $e->getMessage(), 'companysearchsberactivity');
    }

    return $rows;
}

/**
 * Возвращает последнюю успешную запись независимо от пользователя.
 *
 * @param int $userId Зарезервирован, в текущем запросе не участвует.
 *
 * @return array|null Строка записи либо null.
 */
function sber_history_last(int $userId = 0): ?array
{
    try {
        sber_history_ensure_table();

        $connection = Application::getConnection();
        $res = $connection->query(
            "SELECT * FROM " . SBER_HISTORY_TABLE . " WHERE STATUS = 'success' ORDER BY ID DESC LIMIT 1"
        );
        $row = $res->fetch();

        return $row ?: null;
    } catch (\Throwable $e) {
        \AddMessage2Log('sber_history_last: ' . $e->getMessage(), 'companysearchsberactivity');

        return null;
    }
}

/**
 * Возвращает последний успешный результат по конкретному ИНН.
 *
 * Используется для кэширования анализа: запись младше часа не запрашивается заново.
 *
 * @param string $inn ИНН контрагента.
 *
 * @return array|null Строка записи либо null.
 */
function sber_history_get_last(string $inn): ?array
{
    try {
        sber_history_ensure_table();

        $connection = Application::getConnection();
        $sqlHelper = $connection->getSqlHelper();
        $safeInn = $sqlHelper->forSql($inn, 12);

        $res = $connection->query(
            "SELECT * FROM " . SBER_HISTORY_TABLE
            . " WHERE INN = '" . $safeInn . "' AND STATUS = 'success'"
            . " ORDER BY ID DESC LIMIT 1"
        );
        $row = $res->fetch();

        return $row ?: null;
    } catch (\Throwable $e) {
        \AddMessage2Log('sber_history_get_last: ' . $e->getMessage(), 'companysearchsberactivity');

        return null;
    }
}