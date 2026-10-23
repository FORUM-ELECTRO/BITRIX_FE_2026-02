<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage('SBER_CONTENT_TITLE'));

if (!Loader::includeModule('crm')) {
    echo Loc::getMessage('SBER_CONTENT_ERROR_CRM');
    die();
}

\Bitrix\Main\UI\Extension::load([
    'main.core',
    'ui.buttons',
    'ui.forms',
    'ui.alerts',
    'ui.sidepanel',
    'ui.loader',
    'ui.design-tokens',
    'ui.fonts.opensans',
    'ui.fonts.roboto',
]);

$APPLICATION->SetAdditionalCSS('/bitrix/js/ui/buttons/dist/ui.buttons.bundle.min.css');
$APPLICATION->AddHeadScript('/bitrix/js/ui/buttons/dist/ui.buttons.bundle.min.js');
$APPLICATION->AddHeadScript('/bitrix/js/ui/loader/dist/loader.bundle.min.js');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <?php $APPLICATION->ShowHead(); ?>
    <style>
        body, .ui-card, button, input, pre, table, td, th {
            font-family: 'SB Sans Interface', 'SB Sans Text', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }
    </style>
</head>
<body>

<div style="padding:24px;max-width:840px;font-family:'SB Sans Interface', 'SB Sans Text', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <h2 style="margin:0 0 20px;font-size:22px;font-weight:600;"><?php echo Loc::getMessage('SBER_CONTENT_TITLE'); ?></h2>

    <!-- Поиск компании: название или ИНН -->
    <div style="position:relative;margin-bottom:20px;">
        <div class="ui-ctl ui-ctl-textbox ui-ctl-w100" style="position:relative;">
            <input type="text" id="company-search" class="ui-ctl-element"
                   placeholder="<?php echo htmlspecialcharsbx(Loc::getMessage('SBER_CONTENT_SEARCH_PLACEHOLDER')); ?>" autocomplete="off">
            <div id="search-spinner" style="display:none;position:absolute;right:12px;top:50%;transform:translateY(-50%);">
                <div class="ui-loader-circular"><span class="ui-loader-circular-txt">&#8203;</span></div>
            </div>
        </div>
        <div id="search-results" style="position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #d5d8dc;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.08);margin-top:4px;display:none;z-index:100;max-height:360px;overflow-y:auto;"></div>
    </div>

    <!-- Недавние компании -->
    <div id="quick-section" style="margin-bottom:24px;">
        <div id="recent-block" style="display:none;margin-bottom:16px;">
            <div style="font-size:12px;color:#828b95;text-transform:uppercase;margin-bottom:8px;"><?php echo Loc::getMessage('SBER_CONTENT_L_RECENT'); ?></div>
            <div id="recent-list" style="display:flex;flex-wrap:wrap;gap:8px;"></div>
        </div>
    </div>

    <!-- Информация о выбранной компании -->
<div id="selected-company" style="display:none;margin-bottom:20px;padding:16px 20px;background:var(--ui-color-background-secondary, #f5f7f9);border-radius:12px;">
        <div style="font-size:16px;font-weight:600;" id="selected-title"></div>
        <div style="font-size:13px;color:#525c69;margin-top:4px;">
            <?php echo htmlspecialcharsbx(Loc::getMessage('SBER_CONTENT_L_INN')); ?> <span id="selected-inn"></span> &nbsp;|&nbsp; <?php echo htmlspecialcharsbx(Loc::getMessage('SBER_CONTENT_L_KPP')); ?> <span id="selected-kpp"></span>
        </div>
    </div>

    <!-- Кнопка запуска анализа -->
    <div id="analyze-btn-container" style="margin-bottom:24px;display:none;"></div>

<!-- Результат анализа компании -->
<div id="analysis-result" style="display:none;">
    <div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
        <div id="analysis-content"></div>
    </div>
</div>

<script>
BX.message(<?php echo Json::encode([
    'SBER_CONTENT_NOT_FOUND' => Loc::getMessage('SBER_CONTENT_NOT_FOUND'),
    'SBER_CONTENT_BTN_ANALYZE' => Loc::getMessage('SBER_CONTENT_BTN_ANALYZE'),
    'SBER_CONTENT_LOADING' => Loc::getMessage('SBER_CONTENT_LOADING'),
    'SBER_CONTENT_PARSING' => Loc::getMessage('SBER_CONTENT_PARSING'),
    'SBER_CONTENT_ERROR_REQUEST' => Loc::getMessage('SBER_CONTENT_ERROR_REQUEST'),
    'SBER_CONTENT_ERROR_REQUEST_SERVER' => Loc::getMessage('SBER_CONTENT_ERROR_REQUEST_SERVER'),
    'SBER_CONTENT_ERROR_PREFIX' => Loc::getMessage('SBER_CONTENT_ERROR_PREFIX'),
    'SBER_CONTENT_COMPANY' => Loc::getMessage('SBER_CONTENT_COMPANY'),
    'SBER_CONTENT_INDICATOR' => Loc::getMessage('SBER_CONTENT_INDICATOR'),
    'SBER_CONTENT_COPY' => Loc::getMessage('SBER_CONTENT_COPY'),
    'SBER_CONTENT_REPORT_DETAILS' => Loc::getMessage('SBER_CONTENT_REPORT_DETAILS'),
    'SBER_CONTENT_REPORT_TEXT' => Loc::getMessage('SBER_CONTENT_REPORT_TEXT'),
    'SBER_CONTENT_DOWNLOAD_PDF' => Loc::getMessage('SBER_CONTENT_DOWNLOAD_PDF'),
    'SBER_CONTENT_RISK_NONE' => Loc::getMessage('SBER_CONTENT_RISK_NONE'),
    'SBER_CONTENT_RISK_LOW' => Loc::getMessage('SBER_CONTENT_RISK_LOW'),
    'SBER_CONTENT_RISK_MEDIUM' => Loc::getMessage('SBER_CONTENT_RISK_MEDIUM'),
    'SBER_CONTENT_RISK_HIGH' => Loc::getMessage('SBER_CONTENT_RISK_HIGH'),
    'SBER_CONTENT_TH_INDICATOR' => Loc::getMessage('SBER_CONTENT_TH_INDICATOR'),
    'SBER_CONTENT_TH_CODE' => Loc::getMessage('SBER_CONTENT_TH_CODE'),
    'SBER_CONTENT_SEC_REGISTRATION' => Loc::getMessage('SBER_CONTENT_SEC_REGISTRATION'),
    'SBER_CONTENT_SEC_FINANCE' => Loc::getMessage('SBER_CONTENT_SEC_FINANCE'),
    'SBER_CONTENT_SEC_BALANCE' => Loc::getMessage('SBER_CONTENT_SEC_BALANCE'),
    'SBER_CONTENT_SEC_COURTS' => Loc::getMessage('SBER_CONTENT_SEC_COURTS'),
    'SBER_CONTENT_SEC_CONTRACTS' => Loc::getMessage('SBER_CONTENT_SEC_CONTRACTS'),
    'SBER_CONTENT_SEC_TAXES' => Loc::getMessage('SBER_CONTENT_SEC_TAXES'),
    'SBER_CONTENT_SEC_LICENSES' => Loc::getMessage('SBER_CONTENT_SEC_LICENSES'),
    'SBER_CONTENT_SEC_INSPECTIONS' => Loc::getMessage('SBER_CONTENT_SEC_INSPECTIONS'),
    'SBER_CONTENT_SEC_REVIEWS' => Loc::getMessage('SBER_CONTENT_SEC_REVIEWS'),
]); ?>);

BX.ready(function() {
    var searchInput = document.getElementById('company-search');
    var searchResults = document.getElementById('search-results');
    var selectedBlock = document.getElementById('selected-company');
    var analyzeBtnContainer = document.getElementById('analyze-btn-container');
    var resultBlock = document.getElementById('analysis-result');
    var resultContent = document.getElementById('analysis-content');
    var spinner = document.getElementById('search-spinner');

    var selectedCompany = null;
    var searchTimeout = null;
    var analyzeButton = null;

/**
 * Рисует чипы недавно проанализированных компаний из localStorage.
 */
function loadRecent() {
        var recent = JSON.parse(localStorage.getItem('sber_recent_companies') || '[]');
        if (recent.length === 0) return;
        var block = document.getElementById('recent-block');
        var list = document.getElementById('recent-list');
        block.style.display = 'block';
        list.innerHTML = '';
        recent.forEach(function(c) {
            var chip = document.createElement('span');
            chip.textContent = c.TITLE;
            chip.style.cssText = 'padding:6px 14px;background:#e8f4e8;color:#008000;border-radius:16px;cursor:pointer;font-size:13px;';
            chip.addEventListener('click', function() { selectCompany(c); });
            list.appendChild(chip);
        });
    }


    // Живой поиск по названию или ИНН с дебаунсом 300 мс
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        var query = this.value.trim();
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }
        spinner.style.display = 'block';
        searchTimeout = setTimeout(function() {
            BX.ajax({
                url: '/local/activities/custom/companysearchsberactivity/page/ajax.php',
                method: 'POST',
                dataType: 'json',
                data: {action: 'search', query: query},
                onsuccess: function(r) {
                    spinner.style.display = 'none';
                    if (r.companies && r.companies.length > 0) {
                        var html = '';
                        r.companies.forEach(function(c) {
                            html += '<div class="search-item" data-json=\'' + JSON.stringify(c).replace(/'/g, '&#39;') + '\' style="padding:12px 16px;cursor:pointer;border-bottom:1px solid #f0f2f4;">';
                            html += '<div style="font-weight:600;font-size:14px;">' + c.TITLE + '</div>';
                            if (c.INN) html += '<div style="font-size:12px;color:#828b95;">' + BX.message('SBER_CONTENT_L_INN') + c.INN + '</div>';
                            html += '</div>';
                        });
                        searchResults.innerHTML = html;
                        searchResults.style.display = 'block';
                        document.querySelectorAll('.search-item').forEach(function(item) {
                            item.addEventListener('click', function() {
                                var c = JSON.parse(this.getAttribute('data-json'));
                                selectCompany(c);
                            });
                        });
                    } else {
                        searchResults.innerHTML = '<div style="padding:16px;color:#828b95;font-size:13px;">' + BX.message('SBER_CONTENT_NOT_FOUND') + '</div>';
                        searchResults.style.display = 'block';
                    }
                },
                onfailure: function() {
                    spinner.style.display = 'none';
                }
            });
        }, 300);
    });

/**
 * Показывает выбранную компанию, сохраняет её в недавние и рисует кнопку анализа.
 *
 * @param {Object} c Компания в формате {ID, TITLE, INN, KPP}.
 */
function selectCompany(c) {
        selectedCompany = c;
        document.getElementById('selected-title').textContent = c.TITLE;
        document.getElementById('selected-inn').textContent = c.INN || '—';
        document.getElementById('selected-kpp').textContent = c.KPP || '—';
        selectedBlock.style.display = 'block';
        searchResults.style.display = 'none';
        searchInput.value = c.TITLE;
        resultBlock.style.display = 'none';

        // Перекладываем компанию в начало списка недавних
        var recent = JSON.parse(localStorage.getItem('sber_recent_companies') || '[]');
        recent = recent.filter(function(x) { return x.ID !== c.ID; });
        recent.unshift(c);
        recent = recent.slice(0, 5);
        localStorage.setItem('sber_recent_companies', JSON.stringify(recent));
        loadRecent();

        // Создаём кнопку анализа
        analyzeBtnContainer.style.display = 'block';
        analyzeBtnContainer.innerHTML = '';
        BX.loadExt('ui.buttons').then(function() {
            analyzeButton = new BX.UI.Button({
                text: BX.message('SBER_CONTENT_BTN_ANALYZE'),
                color: BX.UI.Button.Color.SUCCESS,
                size: BX.UI.Button.Size.MEDIUM,
                useAirDesign: true,
                onclick: function() { runAnalysis(); }
            });
            analyzeButton.renderTo(analyzeBtnContainer);
        });
    }

/**
 * Запрашивает анализ выбранной компании, при наличии PDF сначала парсит его.
 */
function runAnalysis() {
        if (!selectedCompany) return;
        resultBlock.style.display = 'block';
        resultContent.innerHTML = '<div style="color:#828b95;">' + BX.message('SBER_CONTENT_LOADING') + '</div>';
        if (analyzeButton) analyzeButton.setDisabled(true);

        BX.ajax({
            url: '/local/activities/custom/companysearchsberactivity/page/ajax.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'analyze',
                company_id: selectedCompany.ID,
                inn: selectedCompany.INN,
                kpp: selectedCompany.KPP
            },
            onsuccess: function(r) {
                if (r.error) {
                    if (analyzeButton) analyzeButton.setDisabled(false);
                    resultContent.innerHTML = '<div class="ui-alert ui-alert-danger"><span class="ui-alert-message">' + r.error + '</span></div>';
                    return;
                }

                // Если есть PDF — парсим его, потом рисуем
                if (r.report_file) {
                    resultContent.innerHTML = '<div style="color:#828b95;">' + BX.message('SBER_CONTENT_PARSING') + '</div>';
                    
                    BX.ajax({
                        url: '/local/activities/custom/companysearchsberactivity/page/parse_pdf.php',
                        method: 'POST',
                        dataType: 'json',
                        data: {report_file: r.report_file},
                        onsuccess: function(pdfRes) {
                            if (analyzeButton) analyzeButton.setDisabled(false);
                            if (!pdfRes.error) {
                                r.pdfData = pdfRes;
                            }
                            renderResult(r);
                        },
                        onfailure: function() {
                            if (analyzeButton) analyzeButton.setDisabled(false);
                            renderResult(r);
                        }
                    });
                } else {
                    if (analyzeButton) analyzeButton.setDisabled(false);
                    renderResult(r);
                }
            },
            onfailure: function() {
                if (analyzeButton) analyzeButton.setDisabled(false);
                resultContent.innerHTML = '<div class="ui-alert ui-alert-danger"><span class="ui-alert-message">' + BX.message('SBER_CONTENT_ERROR_REQUEST') + '</span></div>';
            }
        });
    }

/**
 * Отрисовывает карточки показателей, разделы отчёта и кнопку скачивания PDF.
 *
 * @param {Object} r Ответ analyze: {indicators, report_file, pdfData?}.
 */
function renderResult(r) {
    // indicators — из API (4 шт), pdfData.indicators — из PDF (6+ шт), приоритет у PDF.
    var ind = (r.pdfData && r.pdfData.indicators) ? r.pdfData.indicators : {};
    var apiInd = r.indicators || {};

    // Цвет и подпись уровня риска (сравнения идут с кодами API, не с текстами)
    /**
     * Возвращает цвет, иконку и подпись для уровня риска.
     *
     * @param {string} level Уровень из API или PDF.
     *
     * @returns {Object} Формат {color, icon, text}.
     */
    function getStatus(level) {
        if (!level || level === 'Нет данных' || level === 'Нет оценки' || level === 'BLANK') {
            return { color: '#828b95', icon: '⚪', text: BX.message('SBER_CONTENT_RISK_NONE') };
        }
        if (level.indexOf('Низкий') !== -1 || level === 'GREEN') {
            return { color: '#00a859', icon: '✅', text: BX.message('SBER_CONTENT_RISK_LOW') };
        }
        if (level.indexOf('Средний') !== -1 || level === 'YELLOW') {
            return { color: '#f5a623', icon: '⚠️', text: BX.message('SBER_CONTENT_RISK_MEDIUM') };
        }
        if (level.indexOf('Высокий') !== -1 || level === 'RED') {
            return { color: '#e53935', icon: '❌', text: BX.message('SBER_CONTENT_RISK_HIGH') };
        }
        return { color: '#525c69', icon: 'ℹ️', text: level };
    }

    // Список карточек: показатели из PDF, при их отсутствии — из API
    var cards = [];
    if (r.pdfData && r.pdfData.indicators) {
        for (var key in r.pdfData.indicators) {
            if (r.pdfData.indicators.hasOwnProperty(key)) {
                cards.push(r.pdfData.indicators[key]);
            }
        }
    } else {
        if (apiInd.compliance) cards.push({ label: BX.message('SBER_CONTENT_LABEL_COMPLIANCE'), level: apiInd.compliance.level });
        if (apiInd.state_sources) cards.push({ label: BX.message('SBER_CONTENT_LABEL_STATE_SOURCES'), level: apiInd.state_sources.level });
        if (apiInd.finance) cards.push({ label: BX.message('SBER_CONTENT_LABEL_FINANCE'), level: apiInd.finance.level });
        if (apiInd.sber_rating) cards.push({ label: BX.message('SBER_CONTENT_LABEL_RATING'), level: apiInd.sber_rating.level });
    }

    // Шапка результата
    var html = '<div style="margin-bottom:24px;">';
    html += '<h3 style="margin:0 0 8px;font-size:20px;font-weight:600;">' + (selectedCompany ? selectedCompany.TITLE : BX.message('SBER_CONTENT_COMPANY')) + '</h3>';
    html += '<div style="font-size:14px;color:#525c69;">' + BX.message('SBER_CONTENT_L_INN') + (selectedCompany ? selectedCompany.INN : '—') + ' | ' + BX.message('SBER_CONTENT_L_KPP') + (selectedCompany ? selectedCompany.KPP : '—') + '</div>';
    html += '</div>';

    // Карточки в две колонки
    html += '<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:24px;">';
    cards.forEach(function(item) {
        var status = getStatus(item.level);
        html += '<div style="padding:16px;border:1px solid #e8ecf0;border-radius:16px;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,0.04);">';
        html += '<div style="font-size:13px;color:#525c69;margin-bottom:8px;">' + (item.label || BX.message('SBER_CONTENT_INDICATOR')) + '</div>';
        html += '<div style="display:flex;align-items:center;gap:8px;">';
        html += '<span style="font-size:18px;">' + status.icon + '</span>';
        html += '<span style="font-weight:600;color:' + status.color + ';font-size:14px;">' + status.text + '</span>';
        html += '</div>';
        html += '</div>';
    });
    html += '</div>';

    // Разделы отчёта с копированием значений
    if (r.pdfData && r.pdfData.sections) {
        html += '<div style="margin-top:24px;">';
        html += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">';
        html += '<h4 style="margin:0;font-size:16px;">' + BX.message('SBER_CONTENT_REPORT_DETAILS') + '</h4>';
        if (r.report_file) {
            html += '<div id="download-btn-top"></div>';
        }
        html += '</div>';
        
        var sections = r.pdfData.sections;
        // Отображаемые названия разделов отчёта
        var sectionTitles = {
            'registration': BX.message('SBER_CONTENT_SEC_REGISTRATION'),
            'finance': BX.message('SBER_CONTENT_SEC_FINANCE'),
            'balance': BX.message('SBER_CONTENT_SEC_BALANCE'),
            'courts': BX.message('SBER_CONTENT_SEC_COURTS'),
            'contracts': BX.message('SBER_CONTENT_SEC_CONTRACTS'),
            'taxes': BX.message('SBER_CONTENT_SEC_TAXES'),
            'licenses': BX.message('SBER_CONTENT_SEC_LICENSES'),
            'inspections': BX.message('SBER_CONTENT_SEC_INSPECTIONS'),
            'reviews': BX.message('SBER_CONTENT_SEC_REVIEWS')
        };

        for (var key in sectionTitles) {
            if (!sections[key]) continue;
            
            html += '<div style="margin-bottom:16px;border:1px solid #e8ecf0;border-radius:16px;overflow:hidden;background:#fff;">';
            html += '<div style="padding:14px 18px;background:#fff;border-bottom:1px solid #f0f2f5;font-weight:600;font-size:14px;color:#2c3e50;">' + sectionTitles[key] + '</div>';
            html += '<div style="padding:16px;font-size:13px;line-height:1.6;background:#fff;">';

            if (key === 'balance') {
                html += '<table style="width:100%;border-collapse:collapse;font-size:13px;">';
                html += '<thead><tr style="background:#f8f9fa;">';
                html += '<th style="padding:10px 12px;text-align:left;font-weight:600;color:#525c69;font-size:12px;">' + BX.message('SBER_CONTENT_TH_INDICATOR') + '</th>';
                html += '<th style="padding:10px 12px;text-align:center;font-weight:600;color:#525c69;font-size:12px;">' + BX.message('SBER_CONTENT_TH_CODE') + '</th>';
                html += '<th style="padding:10px 12px;text-align:right;font-weight:600;color:#525c69;font-size:12px;">2023</th>';
                html += '<th style="padding:10px 12px;text-align:right;font-weight:600;color:#525c69;font-size:12px;">2024</th>';
                html += '<th style="padding:10px 12px;text-align:right;font-weight:600;color:#525c69;font-size:12px;">2025</th>';
                html += '</tr></thead><tbody>';
            }

            var lines = sections[key].split('\n');
            for (var li = 0; li < lines.length; li++) {
                var line = lines[li].trim();
                if (!line) continue;
                if (line.indexOf('==') === 0) continue;
                
                // Пропускаем мусорные строки
                if (line.indexOf('Банк не несет') !== -1) continue;
                if (line.indexOf('Информация, которая содержится') !== -1) continue;
                if (line.indexOf('ответственности за') !== -1) continue;
                if (line.indexOf('рекомендацией для принятия') !== -1) continue;
                if (line.indexOf('«Безопасный бизнес»') !== -1) continue;
                if (line.indexOf('Цветовых индикаторов') !== -1) continue;
                if (line.indexOf('упущенную выгоду') !== -1) continue;
                if (line.indexOf('третьим лицам') !== -1) continue;
                if (line.indexOf('официальных открытых источников') !== -1) continue;
                if (line.length < 2) continue;

                // Строка с " | " — таблица
                if (line.indexOf('|') !== -1) {
                    var parts = line.split('|');
                    var cells = parts.map(function(p) { return p.trim(); });
                    if (cells[0] === '' && cells.length > 1) cells.shift();

                    // Бухгалтерский баланс: 5 колонок (показатель | код | три года)
                    if (key === 'balance' && cells.length === 5) {
                        html += '<tr>';
                        html += '<td style="padding:8px 12px;border-bottom:1px solid #f0f2f4;font-size:13px;">' + cells[0] + '</td>';
                        html += '<td style="padding:8px 12px;border-bottom:1px solid #f0f2f4;text-align:center;font-size:12px;color:#828b95;">' + cells[1] + '</td>';
                        html += '<td style="padding:8px 12px;border-bottom:1px solid #f0f2f4;text-align:right;font-size:13px;">' + cells[2] + '</td>';
                        html += '<td style="padding:8px 12px;border-bottom:1px solid #f0f2f4;text-align:right;font-size:13px;">' + cells[3] + '</td>';
                        html += '<td style="padding:8px 12px;border-bottom:1px solid #f0f2f4;text-align:right;font-size:13px;">' + cells[4] + '</td>';
                        html += '</tr>';
                    }
                    // Обычная пара «ключ | значение»
                    else {
                        var nonEmpty = cells.filter(function(c) { return c !== ''; });
                        if (nonEmpty.length === 2) {
                            var k = nonEmpty[0];
                            var val = nonEmpty[1];
                            html += '<div style="display:flex;padding:8px 4px;border-bottom:1px solid #f5f7fa;">';
                            html += '<div style="flex:0 0 45%;color:#828b95;">' + k + '</div>';
                            html += '<div style="flex:1;display:flex;justify-content:space-between;align-items:center;gap:8px;">';
                            html += '<span style="font-weight:500;">' + val + '</span>';
                            html += '<button style="padding:4px 10px;font-size:11px;border:1px solid #e0e5ea;border-radius:8px;background:#fff;color:#525c69;cursor:pointer;" onclick="copyText(this, \'' + escapeHtml(val).replace(/'/g, '\\\'') + '\')">' + BX.message('SBER_CONTENT_COPY') + '</button>';
                            html += '</div>';
                            html += '</div>';
                        } else {
                            // Больше 2 ячеек — выводим строкой
                            html += '<div style="font-family:monospace;font-size:12px;padding:4px 0;border-bottom:1px solid #f5f5f5;white-space:pre-wrap;">' + line + '</div>';
                        }
                    }
                }
                // Строка "Ключ: Значение" (на случай, если склейка не сработала)
                else if (line.indexOf(':') !== -1 && line.indexOf(':') < 50) {
                    var idx = line.indexOf(':');
                    var k = line.substring(0, idx).trim();
                    var v = line.substring(idx + 1).trim();
                    if (v !== '') {
                        html += '<div style="display:flex;padding:8px 4px;border-bottom:1px solid #f5f7fa;border-radius:6px;">';
                        html += '<div style="flex:0 0 45%;color:#828b95;">' + k + '</div>';
                        html += '<div style="flex:1;display:flex;justify-content:space-between;align-items:center;gap:8px;">';
                        html += '<span style="font-weight:500;">' + v + '</span>';
                        html += '<button style="padding:4px 10px;font-size:11px;border:1px solid #e0e5ea;border-radius:8px;background:#fff;color:#525c69;cursor:pointer;" onclick="copyText(this, \'' + escapeHtml(v).replace(/'/g, '\\\'') + '\')">' + BX.message('SBER_CONTENT_COPY') + '</button>';
                        html += '</div>';
                        html += '</div>';
                    } else {
                        html += '<div style="padding:6px 0;color:#2c3e50;font-weight:600;">' + line + '</div>';
                    }
                }
                // Обычный текст (заголовок или пояснение)
                else {
                    html += '<div style="padding:6px 0;color:#525c69;font-weight:500;">' + line + '</div>';
                }
            }

            if (key === 'balance') {
                html += '</tbody></table>';
            }
            html += '</div></div>';
        }
        html += '</div>';
    }



    resultContent.innerHTML = html;

    // Рендер кнопки скачивания (справа от «Детали отчёта»)
    if (r.report_file && document.getElementById('download-btn-top')) {
        BX.loadExt('ui.buttons').then(function() {
            var btnDownload = new BX.UI.Button({
                text: BX.message('SBER_CONTENT_DOWNLOAD_PDF'),
                color: BX.UI.Button.Color.PRIMARY,
                size: BX.UI.Button.Size.SMALL,
                useAirDesign: true,
                tag: BX.UI.Button.Tag.LINK,
                link: r.report_file,
                props: {target: '_blank'}
            });
            btnDownload.renderTo(document.getElementById('download-btn-top'));
        });
    }
}

    // Закрытие результатов поиска при клике вне
    document.addEventListener('click', function(e) {
        if (!searchResults.contains(e.target) && e.target !== searchInput) {
            searchResults.style.display = 'none';
        }
    });

/**
 * Загружает и показывает текстовую версию PDF-отчёта.
 *
 * @param {string} reportUrl Путь к сохранённому PDF-отчёту.
 */
function loadPdfText(reportUrl) {
        var block = document.getElementById('pdf-text-result');
        var content = document.getElementById('pdf-text-content');
        
        if (!block) {
            var container = document.createElement('div');
            container.id = 'pdf-text-result';
            container.style.marginTop = '20px';
            container.innerHTML = '<div class="ui-card" style="padding:24px;"><h3 style="margin:0 0 16px;font-size:18px;">' + BX.message('SBER_CONTENT_REPORT_TEXT') + '</h3><pre id="pdf-text-content" style="white-space:pre-wrap;max-height:500px;overflow-y:auto;font-size:13px;line-height:1.5;"></pre></div>';
            document.body.appendChild(container);
            block = document.getElementById('pdf-text-result');
            content = document.getElementById('pdf-text-content');
        }
        
        block.style.display = 'block';
        content.textContent = BX.message('SBER_CONTENT_LOADING');
        
        BX.ajax({
            url: '/local/activities/custom/companysearchsberactivity/page/parse_pdf.php',
            method: 'POST',
            dataType: 'json',
            data: {report_file: reportUrl},
            onsuccess: function(res) {
                if (res.error) {
                    content.textContent = BX.message('SBER_CONTENT_ERROR_PREFIX') + res.error;
                } else {
                    content.textContent = res.text;
                }
            },
            onfailure: function() {
                content.textContent = BX.message('SBER_CONTENT_ERROR_REQUEST_SERVER');
            }
        });
    }

    loadRecent();
});

/**
 * Копирует текст в буфер обмена и подсвечивает успех в кнопке.
 *
 * @param {HTMLElement} btn Кнопка-инициатор.
 * @param {string}      text Текст для копирования.
 */
function copyText(btn, text) {
    navigator.clipboard.writeText(text).then(function() {
        btn.textContent = '✓';
        setTimeout(function() { btn.textContent = BX.message('SBER_CONTENT_COPY'); }, 1500);
    });
}

/**
 * Экранирует HTML-спецсимволы строки.
 *
 * @param {string} str Исходное значение.
 *
 * @returns {string} Безопасное для вставки в HTML значение.
 */
function escapeHtml(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

</body>
</html>