<?php

/**
 * Парсер PDF-отчёта Сбера: извлекает текст, разделы и уровни рисков.
 *
 * Принимает POST-параметр report_file (путь к сохранённому отчёту),
 * отвечает JSON с full_text, indicators, sections и pages.
 *
 * @package CompanySearchSberActivity
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

use Bitrix\Main\Localization\Loc;
use Smalot\PdfParser\Parser;

Loc::loadMessages(__FILE__);

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
    $parser = new Parser();
    $pdf = $parser->parseFile($filePath);
    $fullText = $pdf->getText();

    // Очистка текста: колонтитулы, повторяющиеся заголовки, пустые строки
    $fullText = preg_replace('/Отчёт о проверке контрагента за \d{2}\.\d{2}\.\d{4}/u', '', $fullText);
    $fullText = preg_replace('/Страница \d+ из \d+/u', '', $fullText);

    // Заголовки разделов повторяются на каждой странице, оборачиваем их маркером "== … =="
    $fullText = preg_replace('/\s*(Регистрационные данные|Финансовый анализ|Бухгалтерский баланс|Финансовые результаты|Суды|Госконтракты|Налоги|Лицензии|Проверки|Отзывы партнёров)\s*/u', "\n== $1 ==\n", $fullText);

    // Табуляция заменяется на « | », чтобы строки таблиц осталось удобно разбивать
    $fullText = str_replace("\t", ' | ', $fullText);

    $fullText = preg_replace('/\n{3,}/u', "\n\n", $fullText);
    $fullText = preg_replace('/^[ \t]+|[ \t]+$/mu', '', $fullText);

    // Дисклеймер банка всегда в конце, всё после него не нужно
    $fullText = preg_replace('/Информация, которая содержится в отчёте.*$/su', '', $fullText);
    $fullText = preg_replace('/^\s*$/m', '', $fullText);

    // Склейка разорванных строк: «ключ» + «значение» в одну
    $lines = explode("\n", $fullText);
    $merged = [];
    $i = 0;
    $total = count($lines);
    while ($i < $total) {
        $line = trim($lines[$i]);
        if ($line === '') { $i++; continue; }

        // Если строка уже содержит "|" — оставляем
        if (strpos($line, '|') !== false) {
            $merged[] = $line;
            $i++;
            continue;
        }

        // Строки баланса: название, затем код строки и три суммы
        // Если строка — название (без цифр), а следующие строки — код и суммы, склеиваем.
        // Пример: "Нематериальные активы", "1110", "0", "5475000", "3648000"
        if (!preg_match('/\d{4}/', $line)) {
            // Код строки баланса — 4 цифры в следующих 1-3 строках
            $codeIdx = -1;
            $code = '';
            for ($j = $i + 1; $j < min($i + 4, $total); $j++) {
                $candidate = trim($lines[$j]);
                if (preg_match('/^\d{4}$/', $candidate)) {
                    $codeIdx = $j;
                    $code = $candidate;
                    break;
                }
            }
            // Код найден — собираем суммы за три года
            if ($codeIdx !== -1) {
                $sums = [];
                $k = $codeIdx + 1;
                while ($k < $total && count($sums) < 3) {
                    $candidate = trim($lines[$k]);
                    if ($candidate === '') { $k++; continue; }
                    if (preg_match('/^\d+$/', $candidate)) {
                        $sums[] = $candidate;
                        $k++;
                    } else {
                        break;
                    }
                }
                if (count($sums) === 3) {
                    $merged[] = $line . ' | ' . $code . ' | ' . implode(' | ', $sums);
                    $i = $k;
                    continue;
                }
            }
        }

        // Ищем следующую непустую строку
        $j = $i + 1;
        while ($j < $total && trim($lines[$j]) === '') $j++;

        if ($j < $total) {
            $next = trim($lines[$j]);

            $isNextHeader = (strpos($next, '==') !== false);
            $isNextKey = (strlen($next) < 40 && !preg_match('/\d/', $next) && strpos($next, ':') === false && strpos($next, '|') === false);

            if (!$isNextHeader && strlen($line) < 50 && !$isNextKey && strlen($next) > 5) {
                $merged[] = $line . ' | ' . $next;
                $i = $j + 1;
                continue;
            }
        }

        if (strpos($line, ':') !== false && strpos($line, ':') < 50) {
            $idx = strpos($line, ':');
            $k = trim(substr($line, 0, $idx));
            $v = trim(substr($line, $idx + 1));
            if ($v !== '' && $k !== '') {
                $merged[] = $k . ' | ' . $v;
                $i++;
                continue;
            }
        }

        $merged[] = $line;
        $i++;
    }
    $fullText = implode("\n", $merged);

    // Разбивка по позициям заголовков разделов
    $headers = [
        'registration' => 'Регистрационные данные',
        'finance'      => 'Финансовый анализ',
        'balance'      => 'Бухгалтерский баланс',
        'results'      => 'Финансовые результаты',
        'courts'       => 'Суды',
        'contracts'    => 'Госконтракты',
        'taxes'        => 'Налоги',
        'licenses'     => 'Лицензии',
        'inspections'  => 'Выездные проверки',
        'reviews'      => 'Отзывы партнёров',
    ];

    $positions = [];
    foreach ($headers as $key => $title) {
        if (preg_match('/' . preg_quote($title, '/') . '/u', $fullText, $m, PREG_OFFSET_CAPTURE)) {
            $positions[$key] = $m[0][1];
        }
    }

    // Порядок разделов = порядок их появления в тексте
    asort($positions);

    // Отрезаем содержимое каждого раздела до следующего заголовка
    $sections = [];
    $keys = array_keys($positions);
    for ($i = 0; $i < count($keys); $i++) {
        $key = $keys[$i];
        $start = $positions[$key] + strlen($headers[$key]);
        $end = isset($keys[$i + 1]) ? $positions[$keys[$i + 1]] : strlen($fullText);
        $sections[$key] = trim(substr($fullText, $start, $end - $start));
    }

    // Уровни рисков ищем по подписи показателя в полном тексте
    $parsedIndicators = [];
    $indicatorMap = [
        'Риск блокировки'   => 'compliance',
        'Финансовый анализ' => 'finance',
        'Суды'              => 'courts',
        'Госконтракты'      => 'contracts',
        'Налоги'            => 'taxes',
        'Отзывы партнёров'  => 'reviews',
    ];

    foreach ($indicatorMap as $label => $key) {
        // Между подписью и уровнем не более 200 символов
        if (preg_match('/' . preg_quote($label, '/') . '.{0,200}?(Низкий риск|Средний риск|Высокий риск|Нет данных|Нет оценки)/su', $fullText, $mm)) {
            $parsedIndicators[$key] = [
                'label' => $label,
                'level' => trim($mm[1]),
            ];
        } else {
            $parsedIndicators[$key] = [
                'label' => $label,
                'level' => 'Нет данных',
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'full_text' => $fullText, // очищенный текст для отладки
        'indicators' => $parsedIndicators,
        'sections' => $sections,
        'pages' => count($pdf->getPages()),
    ]);

} catch (\Exception $e) {
    echo json_encode(['error' => Loc::getMessage('SBER_PDF_ERROR_PARSE') . $e->getMessage()]);
}