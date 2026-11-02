<?php

/**
 * Парсер PDF-отчёта Сбера: извлекает текст, разделы и уровни рисков.
 *
 * Принимает POST-параметр report_file (путь к сохранённому отчёту),
 * отвечает JSON с full_text, indicators, sections, years и pages.
 *
 * @package CompanySearchSberActivity
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

use Bitrix\Main\Localization\Loc;
use Smalot\PdfParser\Parser;

Loc::loadMessages(__FILE__);

require_once(__DIR__ . '/sber_pdf_parser.php');

header('Content-Type: application/json; charset=UTF-8');

$reportUrl = $_POST['report_file'] ?? '';
if (empty($reportUrl)) {
    echo json_encode(['error' => Loc::getMessage('SBER_PDF_ERROR_URL_EMPTY')]);
    die();
}

$reportUrl = preg_replace('#^https?://[^/]+#', '', $reportUrl);
$filePath = $_SERVER['DOCUMENT_ROOT'] . $reportUrl;

if (!file_exists($filePath)) {
    echo json_encode(['error' => Loc::getMessage('SBER_PDF_ERROR_FILE_NOT_FOUND')]);
    die();
}

try {
    $pdf = (new Parser())->parseFile($filePath);
    $parsed = (new SberPdfParser())->parse($pdf);

    echo json_encode([
        'success' => true,
        'full_text' => $parsed['full_text'],
        'indicators' => $parsed['indicators'],
        'sections' => $parsed['sections'],
        'years' => $parsed['years'],
        'pages' => count($pdf->getPages()),
    ], JSON_UNESCAPED_UNICODE);
} catch (\Exception $e) {
    echo json_encode(['error' => Loc::getMessage('SBER_PDF_ERROR_PARSE') . $e->getMessage()]);
}
