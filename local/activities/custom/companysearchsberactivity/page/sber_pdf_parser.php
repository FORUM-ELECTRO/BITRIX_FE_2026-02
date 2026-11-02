<?php

/**
 * Разбор PDF-отчёта Сбера по координатам текста.
 *
 * Текстовый слой отчёта плоский: значения рвутся на строки, а порядок
 * строк в getText() не гарантирует колонок. Поэтому здесь строки
 * собираются по координатам: элементы с одинаковым y — одна строка,
 * положение по x определяет колонку.
 *
 * @package CompanySearchSberActivity
 */

final class SberPdfParser
{
    /** Заголовки разделов отчёта: ключ => как он напечатан в PDF. */
    private const SECTION_TITLES = [
        'registration' => 'Регистрационные данные',
        'finance'      => 'Финансовый анализ',
        'balance'      => 'Бухгалтерский баланс',
        'results'      => 'Финансовые результаты',
        'courts'       => 'Суды',
        'contracts'    => 'Госконтракты',
        'taxes'        => 'Налоги',
        'licenses'     => 'Лицензии',
        'inspections'  => 'Проверки',
        'reviews'      => 'Отзывы партнёров',
    ];

    /** Допуск по y, в пределах которого элементы считаются одной строкой. */
    private const LINE_TOLERANCE = 3.0;

    /** Граница левой колонки: всё левее — подпись показателя. */
    private const LABEL_X = 100.0;

    /**
     * Разбирает отчёт.
     *
     * @param \Smalot\PdfParser\Document $pdf Разобранный PDF.
     *
     * @return array Формат {indicators, sections, years, full_text}.
     */
    public function parse($pdf): array
    {
        $rows = $this->collectRows($pdf);
        $sections = $this->splitSections($rows);

        $buildSections = [];
        foreach ($sections as $key => $sectionRows) {
            $buildSections[$key] = $this->renderSection($key, $sectionRows['rows']);
        }

        $years = [];
        foreach ($sections['balance']['years'] ?? [] as $year) {
            if (!in_array($year, $years, true)) {
                $years[] = $year;
            }
        }

        return [
            'indicators' => $this->parseIndicators($rows),
            'sections' => $buildSections,
            'years' => $years,
            'full_text' => $this->renderFullText($rows),
        ];
    }

    /**
     * Собирает элементы страниц в строки по координатам.
     *
     * @param \Smalot\PdfParser\Document $pdf Разобранный PDF.
     *
     * @return array Список строк: [['cells' => [['x' => float, 'text' => string], ...]], ...].
     */
    private function collectRows($pdf): array
    {
        $method = new \ReflectionMethod($pdf->getPages()[0] ?? null ?: $pdf, 'getDataTm');
        $method->setAccessible(true);

        $rows = [];

        foreach ($pdf->getPages() as $page) {
            $items = [];

            foreach ($method->invoke($page) as $item) {
                $text = trim((string)($item[1] ?? ''));
                if ($text === '') {
                    continue;
                }

                // Колонтитулы повторяются на каждой странице и не являются данными.
                if (preg_match('/^Страница \d+ из \d+$/u', $text)
                    || mb_stripos($text, 'Отчёт о проверке контрагента') !== false
                ) {
                    continue;
                }

                $items[] = [
                    'x' => (float)$item[0][4],
                    'y' => (float)$item[0][5],
                    'text' => $text,
                ];
            }

            // Сверху вниз, внутри строки — слева направо.
            usort($items, static function (array $a, array $b): int {
                return $b['y'] <=> $a['y'] ?: $a['x'] <=> $b['x'];
            });

            $current = null;

            foreach ($items as $item) {
                if ($current === null || abs($current['y'] - $item['y']) > self::LINE_TOLERANCE) {
                    if ($current !== null) {
                        $rows[] = $this->finishRow($current);
                    }
                    $current = ['y' => $item['y'], 'cells' => []];
                }

                $current['cells'][] = ['x' => $item['x'], 'text' => $item['text']];
            }

            if ($current !== null) {
                $rows[] = $this->finishRow($current);
            }
        }

        return $rows;
    }

    /**
     * Упорядочивает ячейки строки по x.
     *
     * @param array $row Строка в процессе сборки.
     *
     * @return array Готовая строка.
     */
    private function finishRow(array $row): array
    {
        usort($row['cells'], static function (array $a, array $b): int {
            return $a['x'] <=> $b['x'];
        });

        return $row;
    }

    /**
     * Делит строки на разделы по заголовкам.
     *
     * @param array $rows Строки страниц.
     *
     * @return array Ключ раздела => ['rows' => [...], 'years' => [...]].
     */
    private function splitSections(array $rows): array
    {
        $sections = [];
        $current = null;
        $header = null;

        foreach ($rows as $row) {
            $first = $row['cells'][0] ?? null;

            // Заголовок раздела стоит в строке один: в блоке показателей
            // «Госконтракты» и «Суды» тоже подписи, но рядом с соседями.
            $sectionKey = null;
            if ($first !== null && $first['x'] < 24) {
                $sectionKey = $this->matchSectionTitle($first['text']);
            }

            // Повторяющийся колонтитул пропускаем.
            if ($first !== null && mb_stripos($first['text'], 'Отчёт о проверке контрагента') !== false) {
                continue;
            }

            if ($sectionKey !== null) {
                $current = $sectionKey;
                $sections[$current] = $sections[$current] ?? ['rows' => [], 'years' => []];
                $header = $first['text'];
                continue;
            }

            if ($current === null) {
                continue;
            }

            $sections[$current]['rows'][] = $row;

            if ($current === 'balance') {
                foreach ($row['cells'] as $cell) {
                    if (preg_match('/за\s+(\d{4})/u', $cell['text'], $m)) {
                        $sections[$current]['years'][] = $m[1];
                    }
                }
            }
        }

        return $sections;
    }

    /**
     * Определяет, является ли текст заголовком раздела.
     *
     * @param string $text Текст ячейки.
     *
     * @return string|null Ключ раздела или null.
     */
    private function matchSectionTitle(string $text): ?string
    {
        foreach (self::SECTION_TITLES as $key => $title) {
            if (mb_strtolower(trim($text)) === mb_strtolower($title)) {
                return $key;
            }
        }

        // В отчёте встречается «Выездные проверки» вместо «Проверки».
        if (mb_stripos($text, 'проверк') !== false && mb_strlen($text) < 40) {
            return 'inspections';
        }

        return null;
    }

    /**
     * Склеивает строки раздела в текст «подпись | значение».
     *
     * Продолжения значений (та же колонка ниже) присоединяются к предыдущей
     * строке, а не выводятся отдельно — иначе на странице появляются
     * обрывки вроде «"ЭЛЕКТРОРЕШЕНИЯ"» без подписи.
     *
     * @param string $sectionKey Ключ раздела.
     * @param array  $rows       Строки раздела.
     *
     * @return string Текст раздела.
     */
    private function renderSection(string $sectionKey, array $rows): string
    {
        $isBalance = $sectionKey === 'balance';
        $out = [];
        $lastIndex = null;

        foreach ($rows as $row) {
            $cells = $row['cells'];

            if ($cells === []) {
                continue;
            }

            // Шапка таблицы баланса — не данные.
            if ($isBalance && mb_stripos($cells[0]['text'], 'Показатель') === 0) {
                continue;
            }

            // Одна ячейка: либо продолжение значения, либо подпись без значения.
            if (count($cells) === 1) {
                $cell = $cells[0];

                if ($cell['x'] >= self::LABEL_X && $lastIndex !== null) {
                    $out[$lastIndex] .= ' ' . $cell['text'];
                    continue;
                }

                // В балансе названия показателей тоже переносятся на вторую строку.
                if ($isBalance && $cell['x'] < self::LABEL_X && $lastIndex !== null) {
                    $previous = explode(' | ', $out[$lastIndex]);
                    if (count($previous) >= 3) {
                        $previous[0] .= ' ' . $cell['text'];
                        $out[$lastIndex] = implode(' | ', $previous);
                        continue;
                    }
                }

                $out[] = $cell['text'];
                $lastIndex = count($out) - 1;
                continue;
            }

            $parts = array_map(static function (array $cell): string {
                return $cell['text'];
            }, $cells);

            // Дисклеймер банка идёт последним — дальше данных нет.
            if (mb_stripos(implode(' ', $parts), 'Информация, которая содержится') !== false) {
                break;
            }

            if ($isBalance && count($parts) <= 5) {
                // Баланс всегда пять колонок: показатель, код и три суммы.
                $parts = array_pad($parts, 5, '');
            }

            $out[] = implode(' | ', $parts);
            $lastIndex = count($out) - 1;
        }

        return implode("\n", $out);
    }

    /**
     * Собирает уровни ключевых показателей по координатам.
     *
     * В блоке «Ключевые показатели» подпись стоит строкой выше значения,
     * а значения трёх колонок имеют близкий x к своей подписи. Поиск по
     * тексту здесь не работает: в одной строке лежат три разных показателя.
     *
     * @param array $rows Строки страниц.
     *
     * @return array Ключ показателя => ['label' => string, 'level' => string].
     */
    private function parseIndicators(array $rows): array
    {
        $known = [
            'риск блокировки'   => ['compliance', 'Риск блокировки'],
            'финансовый анализ' => ['finance', 'Финансовый анализ'],
            'суды'              => ['courts', 'Суды'],
            'госконтракты'      => ['contracts', 'Госконтракты'],
            'налоги'            => ['taxes', 'Налоги'],
            'отзывы партнёров'  => ['reviews', 'Отзывы партнёров'],
        ];

        // Блок показателей всегда стоит до первого заголовка раздела.
        $limit = count($rows);
        foreach ($rows as $index => $row) {
            $first = $row['cells'][0] ?? null;
            if ($first !== null
                && $first['x'] < 24
                && $this->matchSectionTitle($first['text']) !== null
            ) {
                $limit = $index;
                break;
            }
        }

        $levels = [];

        for ($i = 0; $i < $limit; $i++) {
            foreach ($rows[$i]['cells'] as $cell) {
                $name = mb_strtolower(trim($cell['text']));

                if (!isset($known[$name])) {
                    continue;
                }

                [$key, $label] = $known[$name];

                if (isset($levels[$key])) {
                    continue;
                }

                for ($j = $i + 1; $j <= min($i + 2, $limit - 1); $j++) {
                    foreach ($rows[$j]['cells'] as $value) {
                        if (abs($value['x'] - $cell['x']) < 60 && $this->isRiskLevel($value['text'])) {
                            $levels[$key] = ['label' => $label, 'level' => trim($value['text'])];
                            // Выходим только из поиска значения: в этой же строке
                            // могут стоять подписи других колонок.
                            break 2;
                        }
                    }
                }
            }
        }

        foreach ($known as [$key, $label]) {
            if (!isset($levels[$key])) {
                $levels[$key] = ['label' => $label, 'level' => 'Нет данных'];
            }
        }

        return $levels;
    }

    /**
     * Проверяет, похож ли текст на уровень риска.
     *
     * @param string $text Текст ячейки.
     *
     * @return bool
     */
    private function isRiskLevel(string $text): bool
    {
        $text = trim($text);

        if (preg_match('/^(Низкий риск|Средний риск|Высокий риск|Нет данных|Нет оценки)$/u', $text)) {
            return true;
        }

        // Рейтинг отзывов: «5.0 (6 отзывов)».
        return (bool)preg_match('/^\d+(?:[.,]\d+)?\s*\(/u', $text);
    }

    /**
     * Собирает полный текст отчёта из строк — для отладки и поиска.
     *
     * @param array $rows Строки страниц.
     *
     * @return string Текст отчёта.
     */
    private function renderFullText(array $rows): string
    {
        $lines = [];

        foreach ($rows as $row) {
            $text = implode(' ', array_map(static function (array $cell): string {
                return $cell['text'];
            }, $row['cells']));

            if ($text !== '') {
                $lines[] = $text;
            }
        }

        return implode("\n", $lines);
    }
}
