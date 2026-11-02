<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

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
    'ui.alerts',
    'ui.design-tokens',
    'ui.fonts.opensans',
]);

$cssPath = '/local/activities/custom/companysearchsberactivity/page/assets/sber-report.css';
$cssVersion = @filemtime($_SERVER['DOCUMENT_ROOT'] . $cssPath) ?: 1;
$APPLICATION->SetAdditionalCSS($cssPath . '?v=' . $cssVersion);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $APPLICATION->ShowHead(); ?>
</head>
<body>

<div class="sber-page">

    <header class="sber-head">
        <h1 class="sber-head__title"><?php echo Loc::getMessage('SBER_CONTENT_TITLE'); ?></h1>
        <p class="sber-head__hint"><?php echo Loc::getMessage('SBER_CONTENT_HINT'); ?></p>
    </header>

    <div class="sber-search">
        <div class="sber-search__field">
            <svg class="sber-search__icon" viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                <circle cx="9" cy="9" r="6" fill="none" stroke="currentColor" stroke-width="1.7"></circle>
                <path d="M13.5 13.5L17 17" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"></path>
            </svg>
            <input type="text"
                   id="company-search"
                   class="sber-search__input"
                   placeholder="<?php echo htmlspecialcharsbx(Loc::getMessage('SBER_CONTENT_SEARCH_PLACEHOLDER')); ?>"
                   aria-label="<?php echo htmlspecialcharsbx(Loc::getMessage('SBER_CONTENT_SEARCH_ARIA')); ?>"
                   aria-controls="search-results"
                   aria-expanded="false"
                   autocomplete="off"
                   spellcheck="false">
            <span id="search-spinner" class="sber-search__spinner" hidden>
                <span class="sber-loading__spinner"></span>
            </span>
            <button type="button"
                    id="search-clear"
                    class="sber-search__clear"
                    aria-label="<?php echo htmlspecialcharsbx(Loc::getMessage('SBER_CONTENT_CLEAR')); ?>">&times;</button>
        </div>
        <div id="search-results" class="sber-dropdown" role="listbox"></div>
    </div>

    <div id="recent-block" class="sber-recent">
        <span class="sber-recent__label"><?php echo Loc::getMessage('SBER_CONTENT_L_RECENT'); ?></span>
        <div id="recent-list" class="sber-recent__list"></div>
    </div>

    <section id="selected-company" class="sber-company" aria-label="<?php echo htmlspecialcharsbx(Loc::getMessage('SBER_CONTENT_SELECTED_ARIA')); ?>">
        <div class="sber-company__main">
            <h2 class="sber-company__name" id="selected-title"></h2>
            <dl class="sber-company__meta">
                <div>
                    <dt><?php echo Loc::getMessage('SBER_CONTENT_L_INN'); ?></dt>
                    <dd id="selected-inn"></dd>
                </div>
                <div>
                    <dt><?php echo Loc::getMessage('SBER_CONTENT_L_KPP'); ?></dt>
                    <dd id="selected-kpp"></dd>
                </div>
            </dl>
        </div>
        <div class="sber-company__actions" id="analyze-btn-container"></div>
    </section>

    <section id="analysis-result" class="sber-result" aria-live="polite">
        <div class="sber-result__body" id="analysis-content"></div>
    </section>

</div>

<script>
BX.message(<?php echo Json::encode([
    'SBER_CONTENT_NOT_FOUND' => Loc::getMessage('SBER_CONTENT_NOT_FOUND'),
    'SBER_CONTENT_SEARCH_MIN' => Loc::getMessage('SBER_CONTENT_SEARCH_MIN'),
    'SBER_CONTENT_ALL_COMPANIES' => Loc::getMessage('SBER_CONTENT_ALL_COMPANIES'),
    'SBER_CONTENT_CHECK_BY_INN' => Loc::getMessage('SBER_CONTENT_CHECK_BY_INN'),
    'SBER_CONTENT_BTN_ANALYZE' => Loc::getMessage('SBER_CONTENT_BTN_ANALYZE'),
    'SBER_CONTENT_LOADING' => Loc::getMessage('SBER_CONTENT_LOADING'),
    'SBER_CONTENT_PARSING' => Loc::getMessage('SBER_CONTENT_PARSING'),
    'SBER_CONTENT_ERROR_REQUEST' => Loc::getMessage('SBER_CONTENT_ERROR_REQUEST'),
    'SBER_CONTENT_ERROR_REQUEST_SERVER' => Loc::getMessage('SBER_CONTENT_ERROR_REQUEST_SERVER'),
    'SBER_CONTENT_COMPANY' => Loc::getMessage('SBER_CONTENT_COMPANY'),
    'SBER_CONTENT_L_INN' => Loc::getMessage('SBER_CONTENT_L_INN'),
    'SBER_CONTENT_L_KPP' => Loc::getMessage('SBER_CONTENT_L_KPP'),
    'SBER_CONTENT_INDICATOR' => Loc::getMessage('SBER_CONTENT_INDICATOR'),
    'SBER_CONTENT_COPY' => Loc::getMessage('SBER_CONTENT_COPY'),
    'SBER_CONTENT_COPIED' => Loc::getMessage('SBER_CONTENT_COPIED'),
    'SBER_CONTENT_REPORT_DETAILS' => Loc::getMessage('SBER_CONTENT_REPORT_DETAILS'),
    'SBER_CONTENT_DOWNLOAD_PDF' => Loc::getMessage('SBER_CONTENT_DOWNLOAD_PDF'),
    'SBER_CONTENT_RISK_NONE' => Loc::getMessage('SBER_CONTENT_RISK_NONE'),
    'SBER_CONTENT_RISK_LOW' => Loc::getMessage('SBER_CONTENT_RISK_LOW'),
    'SBER_CONTENT_RISK_MEDIUM' => Loc::getMessage('SBER_CONTENT_RISK_MEDIUM'),
    'SBER_CONTENT_RISK_HIGH' => Loc::getMessage('SBER_CONTENT_RISK_HIGH'),
    'SBER_CONTENT_LABEL_COMPLIANCE' => Loc::getMessage('SBER_CONTENT_LABEL_COMPLIANCE'),
    'SBER_CONTENT_LABEL_STATE_SOURCES' => Loc::getMessage('SBER_CONTENT_LABEL_STATE_SOURCES'),
    'SBER_CONTENT_LABEL_FINANCE' => Loc::getMessage('SBER_CONTENT_LABEL_FINANCE'),
    'SBER_CONTENT_LABEL_RATING' => Loc::getMessage('SBER_CONTENT_LABEL_RATING'),
    'SBER_CONTENT_TH_INDICATOR' => Loc::getMessage('SBER_CONTENT_TH_INDICATOR'),
    'SBER_CONTENT_TH_CODE' => Loc::getMessage('SBER_CONTENT_TH_CODE'),
    'SBER_CONTENT_SUMMARY_LABEL' => Loc::getMessage('SBER_CONTENT_SUMMARY_LABEL'),
    'SBER_CONTENT_EXPAND_ALL' => Loc::getMessage('SBER_CONTENT_EXPAND_ALL'),
    'SBER_CONTENT_COLLAPSE_ALL' => Loc::getMessage('SBER_CONTENT_COLLAPSE_ALL'),
    'SBER_CONTENT_NO_DETAILS' => Loc::getMessage('SBER_CONTENT_NO_DETAILS'),
    'SBER_CONTENT_ROWS_1' => Loc::getMessage('SBER_CONTENT_ROWS_1'),
    'SBER_CONTENT_ROWS_2' => Loc::getMessage('SBER_CONTENT_ROWS_2'),
    'SBER_CONTENT_ROWS_5' => Loc::getMessage('SBER_CONTENT_ROWS_5'),
    'SBER_CONTENT_SEC_REGISTRATION' => Loc::getMessage('SBER_CONTENT_SEC_REGISTRATION'),
    'SBER_CONTENT_SEC_FINANCE' => Loc::getMessage('SBER_CONTENT_SEC_FINANCE'),
    'SBER_CONTENT_SEC_BALANCE' => Loc::getMessage('SBER_CONTENT_SEC_BALANCE'),
    'SBER_CONTENT_SEC_RESULTS' => Loc::getMessage('SBER_CONTENT_SEC_RESULTS'),
    'SBER_CONTENT_SEC_COURTS' => Loc::getMessage('SBER_CONTENT_SEC_COURTS'),
    'SBER_CONTENT_SEC_CONTRACTS' => Loc::getMessage('SBER_CONTENT_SEC_CONTRACTS'),
    'SBER_CONTENT_SEC_TAXES' => Loc::getMessage('SBER_CONTENT_SEC_TAXES'),
    'SBER_CONTENT_SEC_LICENSES' => Loc::getMessage('SBER_CONTENT_SEC_LICENSES'),
    'SBER_CONTENT_SEC_INSPECTIONS' => Loc::getMessage('SBER_CONTENT_SEC_INSPECTIONS'),
    'SBER_CONTENT_SEC_REVIEWS' => Loc::getMessage('SBER_CONTENT_SEC_REVIEWS'),
]); ?>);

BX.ready(function () {
    var input = document.getElementById('company-search');
    var clearBtn = document.getElementById('search-clear');
    var spinner = document.getElementById('search-spinner');
    var dropdown = document.getElementById('search-results');
    var recentBlock = document.getElementById('recent-block');
    var recentList = document.getElementById('recent-list');
    var companyCard = document.getElementById('selected-company');
    var selectedTitle = document.getElementById('selected-title');
    var selectedInn = document.getElementById('selected-inn');
    var selectedKpp = document.getElementById('selected-kpp');
    var analyzeBox = document.getElementById('analyze-btn-container');
    var resultSection = document.getElementById('analysis-result');
    var resultContent = document.getElementById('analysis-content');

    var state = {
        company: null,
        found: [],
        timeout: null,
        button: null,
        years: [],
        all: null
    };

    /** Коды уровней в порядке убывания серьёзности. */
    var LEVEL_ORDER = ['high', 'medium', 'low', 'none'];

    /** Названия уровней для сводки. */
    var LEVEL_TEXT = {
        high: 'SBER_CONTENT_RISK_HIGH',
        medium: 'SBER_CONTENT_RISK_MEDIUM',
        low: 'SBER_CONTENT_RISK_LOW',
        none: 'SBER_CONTENT_RISK_NONE'
    };

    /**
     * Экранирует HTML-спецсимволы.
     *
     * @param {*} value Исходное значение.
     *
     * @returns {string} Безопасная для вставки в HTML строка.
     */
    function esc(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    /**
     * Подбирает форму слова по числу.
     *
     * @param {number} n    Число.
     * @param {string} one  Фраза для 1.
     * @param {string} few  Фраза для 2-4.
     * @param {string} many Фраза для 5+.
     *
     * @returns {string} Готовая фраза с подставленным числом.
     */
    function plural(n, one, few, many) {
        var mod10 = n % 10;
        var mod100 = n % 100;
        var template = many;
        if (mod10 === 1 && mod100 !== 11) {
            template = one;
        } else if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) {
            template = few;
        }
        return template.replace('#COUNT#', n);
    }

    /**
     * Разбивает длинное целое число на разряды для читаемости.
     *
     * ИНН, КПП и ОГРН не трогаем: там разряды не нужны, а пробелы мешают
     * копированию реквизитов.
     *
     * @param {string} value Значение из отчёта.
     *
     * @returns {string} Значение с разделителями разрядов или исходное.
     */
    function formatNumber(value) {
        var text = String(value === null || value === undefined ? '' : value).trim();

        if (!/^-?\d{5,}$/.test(text)) {
            return text;
        }

        return text.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    /**
     * Определяет код, подпись и вес риска по уровню из API или PDF.
     *
     * @param {string} level Уровень: «Низкий риск», «GREEN» и т.п.
     *
     * @returns {Object} Формат {code, text, rank}.
     */
    function risk(level) {
        var raw = String(level || '').trim();

        if (!raw || raw === 'Нет данных' || raw === 'Нет оценки' || raw === 'BLANK') {
            return { code: 'none', text: BX.message('SBER_CONTENT_RISK_NONE'), rank: 0 };
        }
        if (raw.indexOf('Низкий') !== -1 || raw === 'GREEN') {
            return { code: 'low', text: BX.message('SBER_CONTENT_RISK_LOW'), rank: 1 };
        }
        if (raw.indexOf('Средний') !== -1 || raw === 'YELLOW') {
            return { code: 'medium', text: BX.message('SBER_CONTENT_RISK_MEDIUM'), rank: 2 };
        }
        if (raw.indexOf('Высокий') !== -1 || raw === 'RED') {
            return { code: 'high', text: BX.message('SBER_CONTENT_RISK_HIGH'), rank: 3 };
        }

        // Неизвестный уровень показываем как есть, не приписывая ему оценку.
        return { code: 'none', text: raw, rank: 0 };
    }

    /* ---------- Недавние компании ---------- */

    /**
     * Читает список недавних компаний из localStorage.
     *
     * @returns {Array} Список компаний.
     */
    function readRecent() {
        try {
            var stored = JSON.parse(localStorage.getItem('sber_recent_companies') || '[]');
            return Array.isArray(stored) ? stored : [];
        } catch (e) {
            return [];
        }
    }

    /** Рисует чипы недавно проанализированных компаний. */
    function loadRecent() {
        var recent = readRecent();
        recentList.innerHTML = '';

        if (!recent.length) {
            recentBlock.classList.remove('is-visible');
            return;
        }

        recentBlock.classList.add('is-visible');
        recent.forEach(function (company) {
            var chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'sber-recent__chip';
            chip.textContent = company.TITLE;
            chip.title = company.TITLE;
            chip.addEventListener('click', function () {
                selectCompany(company);
            });
            recentList.appendChild(chip);
        });
    }

    /* ---------- Поиск ---------- */

    /** Закрывает выпадающий список результатов поиска. */
    function closeDropdown() {
        dropdown.classList.remove('is-open');
        input.setAttribute('aria-expanded', 'false');
    }

    /** Рисует список найденных компаний. */
    /**
     * Возвращает ИНН, если в поле введён корректный номер (10 или 12 цифр).
     *
     * @returns {string} ИНН или пустая строка.
     */
    function manualCompany() {
        var query = input.value.trim();

        // «ИНН КПП» — для юрлица КПП обязателен, иначе Сбер не отдаёт отчёт.
        var pair = query.match(/^(\d{10})[\s,;]+(\d{9})$/);
        if (pair) {
            return {
                ID: 'inn' + pair[1],
                TITLE: 'ИНН ' + pair[1],
                INN: pair[1],
                KPP: pair[2]
            };
        }

        if (/^\d{12}$/.test(query)) {
            return {
                ID: 'inn' + query,
                TITLE: 'ИНН ' + query,
                INN: query,
                KPP: ''
            };
        }

        if (/^\d{10}$/.test(query)) {
            return {
                ID: 'inn' + query,
                TITLE: 'ИНН ' + query,
                INN: query,
                KPP: ''
            };
        }

        return null;
    }

    /**
     * Добавляет в список пункт «Проверить по ИНН» — для контрагента,
     * которого ещё нет в CRM.
     */
    function appendManualInn() {
        var manual = manualCompany();

        if (!manual) {
            return;
        }

        var item = document.createElement('button');
        item.type = 'button';
        item.className = 'sber-dropdown__item sber-dropdown__item--manual';
        item.innerHTML = '<span class="sber-dropdown__name">' + esc(manual.TITLE) + '</span>';

        item.addEventListener('click', function () {
            selectCompany(manual);
        });

        dropdown.appendChild(item);
    }

    function renderDropdown(title) {
        if (!state.found.length) {
            dropdown.innerHTML = '<div class="sber-dropdown__empty">'
                + esc(BX.message('SBER_CONTENT_NOT_FOUND')) + '</div>';
            appendManualInn();
            dropdown.classList.add('is-open');
            input.setAttribute('aria-expanded', 'true');
            return;
        }

        dropdown.innerHTML = title
            ? '<div class="sber-dropdown__title">' + esc(title) + '</div>'
            : '';

        state.found.forEach(function (company) {
            var item = document.createElement('button');
            item.type = 'button';
            item.className = 'sber-dropdown__item';
            item.setAttribute('role', 'option');

            var name = document.createElement('span');
            name.className = 'sber-dropdown__name';
            name.textContent = company.TITLE;
            item.appendChild(name);

            if (company.INN) {
                var meta = document.createElement('span');
                meta.className = 'sber-dropdown__meta';
                meta.textContent = BX.message('SBER_CONTENT_L_INN') + ' ' + company.INN;
                item.appendChild(meta);
            }

            item.addEventListener('click', function () {
                selectCompany(company);
            });
            dropdown.appendChild(item);
        });

        appendManualInn();

        dropdown.classList.add('is-open');
        input.setAttribute('aria-expanded', 'true');
    }

    /**
     * Запрашивает компании по строке поиска.
     *
     * @param {string} query Строка запроса.
     */
    function requestSearch(query) {
        BX.ajax({
            url: '/local/activities/custom/companysearchsberactivity/page/ajax.php',
            method: 'POST',
            dataType: 'json',
            data: { action: 'search', query: query },
            onsuccess: function (response) {
                spinner.hidden = true;
                state.found = (response && response.companies) || [];
                renderDropdown();
            },
            onfailure: function () {
                spinner.hidden = true;
                closeDropdown();
            }
        });
    }

    /**
     * Загружает и показывает компании по алфавиту, пока поиск пуст.
     */
    function requestCompanyList() {
        if (state.all !== null) {
            state.found = state.all;
            renderDropdown(BX.message('SBER_CONTENT_ALL_COMPANIES'));
            return;
        }

        spinner.hidden = false;

        BX.ajax({
            url: '/local/activities/custom/companysearchsberactivity/page/ajax.php',
            method: 'POST',
            dataType: 'json',
            data: { action: 'list' },
            onsuccess: function (response) {
                spinner.hidden = true;
                state.all = (response && response.companies) || [];
                state.found = state.all;

                if (state.all.length) {
                    renderDropdown(BX.message('SBER_CONTENT_ALL_COMPANIES'));
                }
            },
            onfailure: function () {
                spinner.hidden = true;
            }
        });
    }

    input.addEventListener('input', function () {
        clearTimeout(state.timeout);

        var query = input.value.trim();
        clearBtn.classList.toggle('is-visible', input.value.length > 0);

        if (query.length < 2) {
            spinner.hidden = true;
            state.found = [];

            // Один символ: подсказываем, что ещё нужно, вместо пустого списка.
            if (query.length === 1) {
                dropdown.innerHTML = '<div class="sber-dropdown__empty">'
                    + esc(BX.message('SBER_CONTENT_SEARCH_MIN')) + '</div>';
                dropdown.classList.add('is-open');
                input.setAttribute('aria-expanded', 'true');
            } else {
                closeDropdown();
            }

            return;
        }

        spinner.hidden = false;
        state.timeout = setTimeout(function () {
            requestSearch(query);
        }, 300);
    });

    clearBtn.addEventListener('click', function () {
        input.value = '';
        clearBtn.classList.remove('is-visible');
        spinner.hidden = true;
        state.found = [];
        closeDropdown();
        input.focus();
    });

    input.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDropdown();
            return;
        }

        if (event.key === 'Enter') {
            var first = dropdown.querySelector('.sber-dropdown__item');
            if (first) {
                event.preventDefault();
                first.click();
            }
        }
    });

    // Клик по пустому полю показывает все компании по алфавиту.
    input.addEventListener('focus', function () {
        if (input.value.trim() === '') {
            requestCompanyList();
            return;
        }

        if (state.found.length && dropdown.innerHTML !== '') {
            dropdown.classList.add('is-open');
            input.setAttribute('aria-expanded', 'true');
        }
    });

    document.addEventListener('click', function (event) {
        if (!dropdown.contains(event.target) && event.target !== input && !clearBtn.contains(event.target)) {
            closeDropdown();
        }
    });

    /* ---------- Выбор компании ---------- */

    /**
     * Показывает выбранную компанию, запоминает её и рисует кнопку запуска.
     *
     * @param {Object} company Компания в формате {ID, TITLE, INN, KPP}.
     */
    function selectCompany(company) {
        state.company = company;

        selectedTitle.textContent = company.TITLE;
        selectedInn.textContent = company.INN || '—';
        selectedKpp.textContent = company.KPP || '—';
        companyCard.classList.add('is-visible');
        resultSection.classList.remove('is-visible');

        closeDropdown();
        input.value = company.TITLE;
        clearBtn.classList.add('is-visible');

        var recent = readRecent().filter(function (item) {
            return item.ID !== company.ID;
        });
        recent.unshift(company);
        localStorage.setItem('sber_recent_companies', JSON.stringify(recent.slice(0, 5)));
        loadRecent();

        if (String(company.ID).indexOf('inn') === 0) {
            resolveManualName(company);
        }

        renderAnalyzeButton();
    }

    /**
     * Подставляет название организации по ИНН (Dadata) и обновляет историю.
     *
     * @param {Object} company Компания ручного ввода.
     */
    function resolveManualName(company) {
        BX.ajax({
            url: '/local/activities/custom/companysearchsberactivity/page/ajax.php',
            method: 'POST',
            dataType: 'json',
            data: { action: 'lookup', inn: company.INN },
            onsuccess: function (response) {
                if (!response || !response.name) {
                    return;
                }

                company.TITLE = response.name;

                if (state.company && state.company.ID === company.ID) {
                    selectedTitle.textContent = response.name;
                    input.value = response.name;
                }

                var recent = readRecent().map(function (item) {
                    return item.ID === company.ID ? { ID: item.ID, TITLE: response.name, INN: item.INN, KPP: item.KPP } : item;
                });

                localStorage.setItem('sber_recent_companies', JSON.stringify(recent));
                loadRecent();
            }
        });
    }

    /** Рисует кнопку «Проверить компанию» с откатом на обычную кнопку. */
    function renderAnalyzeButton() {
        analyzeBox.innerHTML = '';
        state.button = null;

        BX.loadExt('ui.buttons').then(function () {
            state.button = new BX.UI.Button({
                text: BX.message('SBER_CONTENT_BTN_ANALYZE'),
                color: BX.UI.Button.Color.SUCCESS,
                size: BX.UI.Button.Size.MEDIUM,
                useAirDesign: true,
                onclick: function () {
                    runAnalysis();
                }
            });
            state.button.renderTo(analyzeBox);
        }, renderFallbackAnalyzeButton);
    }

    /** Запасная кнопка анализа: нужна, если ui.buttons не загрузился. */
    function renderFallbackAnalyzeButton() {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'sber-btn sber-btn--primary';
        button.textContent = BX.message('SBER_CONTENT_BTN_ANALYZE');
        button.addEventListener('click', function () {
            runAnalysis();
        });
        analyzeBox.appendChild(button);

        // Повторяем интерфейс BX.UI.Button, который использует runAnalysis.
        state.button = {
            setDisabled: function (disabled) {
                button.disabled = Boolean(disabled);
            }
        };
    }

    /* ---------- Запуск анализа ---------- */

    /**
     * Показывает состояние загрузки внутри блока результата.
     *
     * @param {string} text Подпись состояния.
     */
    function showLoading(text) {
        // Скелетон повторяет структуру результата: сводка, плитки, строки.
        var html = '<div class="sber-skeleton" role="status">'
            + '<span class="sber-visually-hidden">' + esc(text) + '</span>'
            + '<div class="sber-skeleton__verdict"></div>'
            + '<div class="sber-skeleton__cards">';

        for (var i = 0; i < 6; i++) {
            html += '<div class="sber-skeleton__card"></div>';
        }

        html += '</div><div class="sber-skeleton__rows">';

        for (var j = 0; j < 5; j++) {
            html += '<div class="sber-skeleton__row"></div>';
        }

        html += '</div></div>';

        resultContent.innerHTML = html;
        resultSection.classList.add('is-visible');
        resultSection.setAttribute('aria-busy', 'true');
    }

    /**
     * Показывает ошибку анализа.
     *
     * @param {string} message Текст ошибки.
     */
    function showError(message) {
        resultSection.removeAttribute('aria-busy');
        resultContent.innerHTML = '<div class="ui-alert ui-alert-danger sber-alert" role="alert">'
            + '<span class="ui-alert-message">' + esc(message) + '</span>'
            + '</div>';
        resultSection.classList.add('is-visible');
    }

    /** Запрашивает анализ выбранной компании и при наличии PDF разбирает его. */
    function runAnalysis() {
        if (!state.company) {
            return;
        }

        showLoading(BX.message('SBER_CONTENT_LOADING'));
        if (state.button) {
            state.button.setDisabled(true);
        }

        BX.ajax({
            url: '/local/activities/custom/companysearchsberactivity/page/ajax.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'analyze',
                company_id: state.company.ID,
                inn: state.company.INN,
                kpp: state.company.KPP
            },
            onsuccess: function (response) {
                if (!response || response.error) {
                    if (state.button) {
                        state.button.setDisabled(false);
                    }
                    showError((response && response.error) || BX.message('SBER_CONTENT_ERROR_REQUEST'));
                    return;
                }

                if (!response.report_file) {
                    if (state.button) {
                        state.button.setDisabled(false);
                    }
                    renderResult(response);
                    return;
                }

                showLoading(BX.message('SBER_CONTENT_PARSING'));
                BX.ajax({
                    url: '/local/activities/custom/companysearchsberactivity/page/parse_pdf.php',
                    method: 'POST',
                    dataType: 'json',
                    data: { report_file: response.report_file },
                    onsuccess: function (pdfResponse) {
                        if (state.button) {
                            state.button.setDisabled(false);
                        }
                        if (pdfResponse && !pdfResponse.error) {
                            response.pdfData = pdfResponse;
                        }
                        renderResult(response);
                    },
                    onfailure: function () {
                        if (state.button) {
                            state.button.setDisabled(false);
                        }
                        renderResult(response);
                    }
                });
            },
            onfailure: function () {
                if (state.button) {
                    state.button.setDisabled(false);
                }
                showError(BX.message('SBER_CONTENT_ERROR_REQUEST'));
            }
        });
    }

    /* ---------- Рендер результата ---------- */

    /**
     * Собирает карточки индикаторов: приоритет у данных из PDF.
     *
     * @param {Object} response Ответ analyze с возможным pdfData.
     *
     * @returns {Array} Карточки в формате {label, level, desc}.
     */
    function collectCards(response) {
        var cards = [];
        var pdfIndicators = response.pdfData && response.pdfData.indicators;

        if (pdfIndicators) {
            Object.keys(pdfIndicators).forEach(function (key) {
                var item = pdfIndicators[key];
                if (!item) {
                    return;
                }
                cards.push({
                    label: item.label || BX.message('SBER_CONTENT_INDICATOR'),
                    level: item.level,
                    desc: item.desc || ''
                });
            });
            return cards;
        }

        var api = response.indicators || {};
        var apiMap = {
            compliance: 'SBER_CONTENT_LABEL_COMPLIANCE',
            state_sources: 'SBER_CONTENT_LABEL_STATE_SOURCES',
            finance: 'SBER_CONTENT_LABEL_FINANCE',
            sber_rating: 'SBER_CONTENT_LABEL_RATING'
        };

        Object.keys(apiMap).forEach(function (key) {
            var item = api[key];
            if (!item) {
                return;
            }
            var label = BX.message(apiMap[key]);

            cards.push({
                label: label,
                level: item.level,
                // В API поле desc часто совпадает с названием — не дублируем.
                desc: (item.desc && item.desc !== label) ? item.desc : ''
            });
        });

        return cards;
    }

    /**
     * Рисует сводку: общий уровень и распределение по уровням.
     *
     * @param {Array} cards Карточки индикаторов.
     *
     * @returns {string} HTML сводки.
     */
    function renderSummary(cards) {
        var counts = { high: 0, medium: 0, low: 0, none: 0 };
        var worst = 'none';

        cards.forEach(function (card) {
            var level = risk(card.level);
            counts[level.code]++;
            if (LEVEL_ORDER.indexOf(level.code) < LEVEL_ORDER.indexOf(worst)) {
                worst = level.code;
            }
        });

        var total = cards.length;
        var html = '<div class="sber-verdict">';
        html += '<div class="sber-verdict__main">';
        html += '<span class="sber-verdict__label">' + esc(BX.message('SBER_CONTENT_SUMMARY_LABEL')) + '</span>';
        html += '<span class="sber-verdict__value sber-verdict__value--' + worst + '">'
            + esc(BX.message(LEVEL_TEXT[worst])) + '</span>';
        html += '<p class="sber-verdict__meta">' + esc(plural(
            total,
            BX.message('SBER_CONTENT_ROWS_1'),
            BX.message('SBER_CONTENT_ROWS_2'),
            BX.message('SBER_CONTENT_ROWS_5')
        )) + '</p>';
        html += '</div>';

        html += '<div class="sber-bars">';

        LEVEL_ORDER.forEach(function (code) {
            if (!counts[code]) {
                return;
            }

            var width = Math.round((counts[code] / total) * 100);

            html += '<div class="sber-bars__row">';
            html += '<span class="sber-bars__name">' + esc(BX.message(LEVEL_TEXT[code])) + '</span>';
            html += '<span class="sber-bars__track"><span class="sber-bars__fill sber-bars__fill--' + code
                + '" style="width:' + width + '%"></span></span>';
            html += '<span class="sber-bars__count">' + counts[code] + '</span>';
            html += '</div>';
        });

        html += '</div></div>';

        return html;
    }

    /**
     * Рисует карточки индикаторов.
     *
     * @param {Array} cards Карточки индикаторов.
     *
     * @returns {string} HTML блока индикаторов.
     */
    function renderIndicators(cards) {
        var html = '<div class="sber-indicators">';

        cards.forEach(function (card) {
            var level = risk(card.level);
            html += '<div class="sber-indicator">';
            html += '<div class="sber-indicator__label">' + esc(card.label) + '</div>';
            html += '<span class="sber-indicator__level sber-indicator__level--' + level.code + '">'
                + esc(level.text) + '</span>';
            if (card.desc) {
                html += '<p class="sber-indicator__desc">' + esc(card.desc) + '</p>';
            }
            html += '</div>';
        });

        html += '</div>';

        return html;
    }

    /**
     * Отбрасывает служебные строки PDF: дисклеймеры и подписи банка.
     *
     * @param {string} line Строка отчёта.
     *
     * @returns {boolean} true, если строку нужно выводить.
     */
    function isMeaningfulLine(line) {
        var noise = [
            'Банк не несет',
            'Информация, которая содержится',
            'ответственности за',
            'рекомендацией для принятия',
            '«Безопасный бизнес»',
            'Цветовых индикаторов',
            'упущенную выгоду',
            'третьим лицам',
            'официальных открытых источников',
            'применения полученной информации',
            'в том числе, но не исключая',
            'использование и/или передачи информации',
            'в собственных целях',
            'не является'
        ];

        if (line.length < 2) {
            return false;
        }

        return !noise.some(function (fragment) {
            return line.indexOf(fragment) !== -1;
        });
    }

    /**
     * Проверяет, повторяет ли строка шапку таблицы отчёта.
     *
     * @param {string} line Строка отчёта.
     *
     * @returns {boolean} true, если это шапка «Показатель | Код | Сумма…».
     */
    function isTableHeader(line) {
        return line.indexOf('Показатель') === 0 && line.indexOf('Код') !== -1;
    }

    /**
     * Разбирает текст раздела на элементы: пары, таблицы и текстовые строки.
     *
     * Строки с тремя и более ячейками — это таблица (например, оценка
     * финансового состояния), иначе они сливались бы в одну строку через «|».
     *
     * @param {string} sectionKey Ключ раздела.
     * @param {string} raw        Текст раздела из PDF.
     *
     * @returns {Object} Формат {items, balance}.
     */
    function parseSection(sectionKey, raw) {
        var result = { items: [], balance: [] };
        var table = null;

        /**
         * Закрывает текущую таблицу.
         */
        function closeTable() {
            if (table) {
                result.items.push(table);
                table = null;
            }
        }

        String(raw || '').split('\n').forEach(function (rawLine) {
            var line = rawLine.trim();

            if (!line || line.indexOf('==') === 0 || !isMeaningfulLine(line)) {
                return;
            }

            if (line.indexOf('|') !== -1) {
                var cells = line.split('|').map(function (cell) {
                    return cell.trim();
                });

                if (cells[0] === '' && cells.length > 1) {
                    cells.shift();
                }

                if (sectionKey === 'balance' && cells.length === 5) {
                    result.balance.push({
                        name: cells[0],
                        code: cells[1],
                        values: [cells[2], cells[3], cells[4]]
                    });
                    return;
                }

                var filled = cells.filter(function (cell) {
                    return cell !== '';
                });

                // «Средняя оценка: | 0.0 | (0 отзывов)» — это пара, а не таблица.
                if (filled.length >= 3 && /:\s*$/.test(filled[0])) {
                    closeTable();
                    result.items.push({
                        type: 'kv',
                        key: filled[0].replace(/:\s*$/, '').trim(),
                        value: filled.slice(1).join(' ')
                    });
                    return;
                }

                if (filled.length >= 3) {
                    // Новая шапка внутри раздела начинает отдельную таблицу:
                    // в разделе их бывает две (показатели и ликвидность).
                    var looksLikeHeader = filled.every(function (cell) {
                        return cell.length < 70 && !/\d/.test(cell) && !/риск|данн|оценк/i.test(cell);
                    });

                    if (table && looksLikeHeader && table.head.join('|') === filled.join('|')) {
                        // Шапка повторяется на новой странице PDF — продолжаем таблицу.
                        return;
                    }

                    if (!table || (table.rows.length > 0 && looksLikeHeader)) {
                        closeTable();
                        table = { type: 'table', head: filled, rows: [] };
                        return;
                    }

                    // Колонок столько, сколько в самой широкой строке.
                    var cellsCount = filled.length;
                    while (table.head.length < cellsCount) {
                        table.head.push('');
                    }
                    while (filled.length < cellsCount) {
                        filled.push('');
                    }
                    if (filled.length > cellsCount) {
                        filled = filled.slice(0, cellsCount);
                    }

                    table.rows.push(filled);
                    return;
                }

                // Незакрытая таблица: любая строка не-шапка продолжает её,
                // даже если ячеек меньше — иначе таблица рвётся на куски.
                if (table && table.rows.length > 0) {
                    var padded = filled.slice();
                    while (padded.length < table.head.length) {
                        padded.push('');
                    }
                    table.rows.push(padded.slice(0, table.head.length));
                    return;
                }

                closeTable();

                if (filled.length === 2) {
                    result.items.push({ type: 'kv', key: filled[0], value: filled[1] });
                    return;
                }

                result.items.push({ type: 'text', text: line });
                return;
            }

            // «(активы)», «обязательств», «периода» — это продолжение названия
            // строки таблицы, а не отдельный текст.
            var looksLikeContinuation = /^[(a-zа-яё\-]/.test(line) && line.length < 90;

            if (table && table.rows.length > 0 && looksLikeContinuation) {
                var lastTableRow = table.rows[table.rows.length - 1];
                lastTableRow[0] = lastTableRow[0] ? lastTableRow[0] + ' ' + line : line;
                return;
            }

            closeTable();

            var colonAt = line.indexOf(':');
            if (colonAt !== -1 && colonAt < 50) {
                var key = line.substring(0, colonAt).trim();
                var value = line.substring(colonAt + 1).trim();

                if (value !== '' && key !== '') {
                    result.items.push({ type: 'kv', key: key, value: value });
                    return;
                }
            }

            var previousItem = result.items[result.items.length - 1];

            if (line.length <= 3 && previousItem && previousItem.type === 'text') {
                previousItem.text = previousItem.text + ' ' + line;
                return;
            }

            if (line.length <= 3 && previousItem && previousItem.type === 'table' && previousItem.rows.length) {
                var tail = previousItem.rows[previousItem.rows.length - 1];
                var lastFilled = tail.length - 1;

                while (lastFilled >= 0 && !tail[lastFilled]) {
                    lastFilled--;
                }

                // Если значение уже закончено предложением или процентом,
                // обрывок вроде «%» только дублирует его.
                if (lastFilled >= 0 && !/[\d%.,]$/.test(tail[lastFilled])) {
                    tail[lastFilled] += ' ' + line;
                    return;
                }
            }

            result.items.push({
                type: 'text',
                text: line,
                heading: line.length < 60
            });
        });

        closeTable();

        result.items = mergeValueRows(result.items);

        return result;
    }

    /**
     * Склеивает строку значений со строкой подписей под ней.
     *
     * В отчёте Сбера для лицензий и проверок значения напечатаны над
     * подписями: «0 | 0», ниже «всего | видов деятельности».
     *
     * @param {Array} items Элементы раздела.
     *
     * @returns {Array} Элементы с парами «подпись — значение».
     */
    function mergeValueRows(items) {
        var out = [];

        for (var i = 0; i < items.length; i++) {
            var current = items[i];
            var next = items[i + 1];

            if (current.type === 'kv' && next && next.type === 'kv'
                && isNumericCell(current.key) && isNumericCell(current.value)
                && !isNumericCell(next.key) && !isNumericCell(next.value)
            ) {
                out.push({ type: 'kv', key: next.key, value: current.key });
                out.push({ type: 'kv', key: next.value, value: current.value });
                i++;
                continue;
            }

            if (current.type === 'table' && current.rows.length === 2) {
                var values = current.rows[0];
                var labels = current.rows[1];
                var allNumeric = values.every(isNumericCell);
                var allLabels = labels.every(function (cell) {
                    return cell !== '' && !isNumericCell(cell);
                });

                if (allNumeric && allLabels && values.length === labels.length) {
                    for (var k = 0; k < labels.length; k++) {
                        out.push({ type: 'kv', key: labels[k], value: values[k] });
                    }
                    continue;
                }
            }

            out.push(current);
        }

        return out;
    }

    /**
     * Проверяет, является ли текст числом.
     *
     * @param {string} value Значение.
     *
     * @returns {boolean}
     */
    function isNumericCell(value) {
        var text = String(value === null || value === undefined ? '' : value).trim();
        return text !== '' && /^[\d\s.,-]+$/.test(text) && /\d/.test(text);
    }

    /**
     * Рисует таблицу бухгалтерского баланса.
     *
     * @param {Array} rows Строки баланса.
     *
     * @returns {string} HTML таблицы.
     */
    function renderBalance(rows) {
        // Годы берём из шапки таблицы в PDF, а не из константы.
        var years = (state.years && state.years.length === 3)
            ? state.years
            : ['2023', '2024', '2025'];

        var html = '<div class="sber-table-wrap">';
        html += '<table class="sber-table">';
        html += '<thead><tr>';
        html += '<th scope="col">' + esc(BX.message('SBER_CONTENT_TH_INDICATOR')) + '</th>';
        html += '<th scope="col">' + esc(BX.message('SBER_CONTENT_TH_CODE')) + '</th>';
        years.forEach(function (year) {
            html += '<th scope="col">' + esc(year) + '</th>';
        });
        html += '</tr></thead><tbody>';

        rows.forEach(function (row) {
            html += '<tr>';
            html += '<td>' + esc(row.name) + '</td>';
            html += '<td>' + esc(row.code) + '</td>';
            row.values.forEach(function (value) {
                html += '<td>' + esc(formatNumber(value)) + '</td>';
            });
            html += '</tr>';
        });

        html += '</tbody></table></div>';

        return html;
    }

    /**
     * Рисует строку «ключ — значение» с кнопкой копирования.
     *
     * @param {Object} row Строка раздела.
     *
     * @returns {string} HTML строки.
     */
    function renderRow(item) {
        if (item.type === 'text') {
            var modifier = item.heading ? ' sber-row--heading' : '';
            return '<li class="sber-row' + modifier + '"><span class="sber-row__text">'
                + esc(item.text) + '</span></li>';
        }

        return '<li class="sber-row">'
            + '<span class="sber-row__key">' + esc(item.key) + '</span>'
            + '<div class="sber-row__value">' + renderValue(item.value) + '</div>'
            + '<button type="button" class="sber-row__copy" data-copy="' + esc(item.value) + '"'
            + ' title="' + esc(BX.message('SBER_CONTENT_COPY')) + '"'
            + ' aria-label="' + esc(BX.message('SBER_CONTENT_COPY')) + '">'
            + '<svg viewBox="0 0 14 14" width="12" height="12" aria-hidden="true" focusable="false">'
            + '<rect x="4.5" y="4.5" width="8" height="8" rx="1.5" fill="none" stroke="currentColor" stroke-width="1.3"></rect>'
            + '<path d="M9.5 4.5V3a1.5 1.5 0 0 0-1.5-1.5H3A1.5 1.5 0 0 0 1.5 3v5A1.5 1.5 0 0 0 3 9.5h1.5" fill="none" stroke="currentColor" stroke-width="1.3"></path>'
            + '</svg>'
            + '</button></li>';
    }

    /**
     * Рисует значение строки: виды деятельности с ОКВЭД — списком,
     * остальное — как есть с разбивкой разрядов.
     *
     * @param {string} value Значение из отчёта.
     *
     * @returns {string} HTML значения.
     */
    function renderValue(value) {
        var text = String(value === null || value === undefined ? '' : value);

        if (text.indexOf('ОКВЭД') === -1) {
            return esc(formatNumber(text));
        }

        var items = [];
        var pattern = /(.*?)ОКВЭД\s*([\d.]+)/g;
        var match;

        while ((match = pattern.exec(text)) !== null) {
            var name = match[1].trim();

            if (name) {
                items.push({ name: name, code: match[2] });
            }
        }

        if (!items.length) {
            return esc(text);
        }

        var html = '<ul class="sber-okved">';

        items.forEach(function (item) {
            html += '<li class="sber-okved__item">'
                + '<span class="sber-okved__name">' + esc(item.name) + '</span>'
                + '<span class="sber-okved__code">' + esc(item.code) + '</span>'
                + '</li>';
        });

        return html + '</ul>';
    }

    /**
     * Рисует таблицу раздела (три и более колонок в PDF).
     *
     * @param {Object} table Таблица вида {head, rows}.
     *
     * @returns {string} HTML таблицы.
     */
    function renderTable(table) {
        var html = '<div class="sber-table-wrap"><table class="sber-table sber-table--data">';

        if (table.head && table.head.length) {
            html += '<thead><tr>';
            table.head.forEach(function (cell) {
                html += '<th scope="col">' + esc(cell) + '</th>';
            });
            html += '</tr></thead>';
        }

        html += '<tbody>';
        table.rows.forEach(function (row) {
            html += '<tr>';
            row.forEach(function (cell) {
                // Числа выравниваем по правому краю и держим табличные цифры.
                var numeric = /^[\d\s.,-]+$/.test(cell) && /\d/.test(cell);
                html += '<td' + (numeric ? ' class="is-num"' : '') + '>'
                    + esc(numeric ? formatNumber(cell.replace(/\s+/g, '')) : cell) + '</td>';
            });
            html += '</tr>';
        });

        return html + '</tbody></table></div>';
    }

    /**
     * Рисует элементы раздела в исходном порядке: пары, таблицы, текст.
     *
     * @param {Array} items Элементы из parseSection.
     *
     * @returns {string} HTML содержимого раздела.
     */
    function renderItems(items) {
        var html = '';
        var buffer = [];

        /**
         * Выводит накопленные строки списком.
         */
        function flush() {
            if (!buffer.length) {
                return;
            }

            html += '<ul class="sber-rows">' + buffer.map(renderRow).join('') + '</ul>';
            buffer = [];
        }

        items.forEach(function (item) {
            if (item.type === 'table') {
                flush();
                html += renderTable(item);
                return;
            }

            buffer.push(item);
        });

        flush();

        return html;
    }

    /**
     * Рисует раздел отчёта в виде раскрывающегося блока.
     *
     * @param {string} title      Название раздела.
     * @param {Object} parsed     Результат parseSection.
     * @param {boolean} openByDefault Раскрыть ли раздел по умолчанию.
     *
     * @returns {string} HTML секции.
     */
    function renderSection(title, parsed, openByDefault) {
        var count = parsed.balance.length;

        parsed.items.forEach(function (item) {
            if (item.type === 'kv') {
                count++;
            } else if (item.type === 'table') {
                count += item.rows.length;
            }
        });

        var countLabel = plural(
            count,
            BX.message('SBER_CONTENT_ROWS_1'),
            BX.message('SBER_CONTENT_ROWS_2'),
            BX.message('SBER_CONTENT_ROWS_5')
        );

        var html = '<details class="sber-section"' + (openByDefault ? ' open' : '') + '>';
        html += '<summary class="sber-section__summary">';
        html += '<svg class="sber-section__chevron" viewBox="0 0 10 10" aria-hidden="true" focusable="false">'
            + '<path d="M3 1l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.6"'
            + ' stroke-linecap="round" stroke-linejoin="round"></path></svg>';
        html += '<span class="sber-section__name">' + esc(title) + '</span>';
        html += '<span class="sber-section__count">' + esc(countLabel) + '</span>';
        html += '</summary>';
        html += '<div class="sber-section__body">';

        if (parsed.balance.length) {
            html += renderBalance(parsed.balance);
        }

        html += renderItems(parsed.items);

        if (!count) {
            html += '<div class="sber-empty">' + esc(BX.message('SBER_CONTENT_NO_DETAILS')) + '</div>';
        }

        html += '</div></details>';

        return html;
    }

    /**
     * Рисует блок разделов отчёта.
     *
     * @param {Object} sections Разделы из PDF-парсера.
     *
     * @returns {string} HTML блока разделов.
     */
    function renderSections(sections) {
        var titles = [
            ['registration', 'SBER_CONTENT_SEC_REGISTRATION'],
            ['finance', 'SBER_CONTENT_SEC_FINANCE'],
            ['balance', 'SBER_CONTENT_SEC_BALANCE'],
            ['results', 'SBER_CONTENT_SEC_RESULTS'],
            ['courts', 'SBER_CONTENT_SEC_COURTS'],
            ['contracts', 'SBER_CONTENT_SEC_CONTRACTS'],
            ['taxes', 'SBER_CONTENT_SEC_TAXES'],
            ['licenses', 'SBER_CONTENT_SEC_LICENSES'],
            ['inspections', 'SBER_CONTENT_SEC_INSPECTIONS'],
            ['reviews', 'SBER_CONTENT_SEC_REVIEWS']
        ];

        var blocks = '';
        var opened = 0;

        titles.forEach(function (pair) {
            var text = sections[pair[0]];
            if (!text) {
                return;
            }

            var parsed = parseSection(pair[0], text);

            var sectionCount = parsed.balance.length;

            parsed.items.forEach(function (item) {
                if (item.type === 'kv') {
                    sectionCount++;
                } else if (item.type === 'table') {
                    sectionCount += item.rows.length;
                }
            });

            if (!sectionCount) {
                return;
            }

            // Первые два раздела показываем раскрытыми: они чаще всего нужны.
            var openByDefault = opened < 2;
            if (openByDefault) {
                opened++;
            }

            blocks += renderSection(
                BX.message(pair[1]),
                parsed,
                openByDefault
            );
        });

        if (!blocks) {
            return '';
        }

        var html = '<div class="sber-sections">';
        html += '<div class="sber-sections__toolbar">';
        html += '<h3 class="sber-sections__title">' + esc(BX.message('SBER_CONTENT_REPORT_DETAILS')) + '</h3>';
        html += '<div class="sber-sections__actions">';
        html += '<div id="download-btn-slot"></div>';
        html += '<button type="button" class="sber-sections__toggle" data-toggle-all="expand">'
            + esc(BX.message('SBER_CONTENT_EXPAND_ALL')) + '</button>';
        html += '</div>';
        html += '</div>';
        html += blocks;
        html += '</div>';

        return html;
    }

    /**
     * Отрисовывает шапку результата, сводку, индикаторы и разделы отчёта.
     *
     * @param {Object} response Ответ analyze с добавленным pdfData.
     */
    function renderResult(response) {
        resultSection.removeAttribute('aria-busy');

        state.years = response.years || [];

        var cards = collectCards(response);
        var sections = (response.pdfData && response.pdfData.sections) || {};

        var html = '';

        if (cards.length) {
            html += renderSummary(cards);
            html += renderIndicators(cards);
        }

        var sectionsHtml = renderSections(sections);
        html += sectionsHtml;

        if (!cards.length && !sectionsHtml) {
            html += '<div class="sber-empty">' + esc(BX.message('SBER_CONTENT_NO_DETAILS')) + '</div>';
        }

        resultContent.innerHTML = html;
        resultSection.classList.add('is-visible');

        mountDownloadButton(response.report_file);
    }

    /**
     * Рисует кнопку скачивания PDF-отчёта.
     *
     * @param {string} reportFile Путь к сохранённому отчёту.
     */
    function mountDownloadButton(reportFile) {
        var slot = document.getElementById('download-btn-slot');

        if (!slot) {
            slot = document.createElement('div');
            slot.id = 'download-btn-slot';
            resultContent.insertBefore(slot, resultContent.firstChild);
        }

        if (!reportFile) {
            return;
        }

        BX.loadExt('ui.buttons').then(function () {
            var button = new BX.UI.Button({
                text: BX.message('SBER_CONTENT_DOWNLOAD_PDF'),
                color: BX.UI.Button.Color.PRIMARY,
                size: BX.UI.Button.Size.SMALL,
                useAirDesign: true,
                tag: BX.UI.Button.Tag.LINK,
                link: reportFile,
                props: { target: '_blank', rel: 'noopener' }
            });
            button.renderTo(slot);
        }, function () {
            var link = document.createElement('a');
            link.className = 'sber-btn';
            link.href = reportFile;
            link.target = '_blank';
            link.rel = 'noopener';
            link.textContent = BX.message('SBER_CONTENT_DOWNLOAD_PDF');
            slot.appendChild(link);
        });
    }

    /* ---------- Взаимодействие с результатом ---------- */

    /**
     * Копирует значение строки в буфер обмена.
     *
     * @param {HTMLElement} button Кнопка копирования.
     */
    function copyValue(button) {
        var text = button.getAttribute('data-copy') || '';

        function markDone() {
            button.classList.add('is-done');
            button.setAttribute('aria-label', BX.message('SBER_CONTENT_COPIED'));
            setTimeout(function () {
                button.classList.remove('is-done');
                button.setAttribute('aria-label', BX.message('SBER_CONTENT_COPY'));
            }, 1400);
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(markDone, function () {});
            return;
        }

        // Откат для окружений без Clipboard API (http, старые браузеры).
        var area = document.createElement('textarea');
        area.value = text;
        area.setAttribute('readonly', 'readonly');
        area.style.position = 'fixed';
        area.style.opacity = '0';
        document.body.appendChild(area);
        area.select();
        try {
            document.execCommand('copy');
            markDone();
        } finally {
            document.body.removeChild(area);
        }
    }

    resultContent.addEventListener('click', function (event) {
        var copyButton = event.target.closest('.sber-row__copy');
        if (copyButton) {
            copyValue(copyButton);
            return;
        }

        var toggle = event.target.closest('[data-toggle-all]');
        if (!toggle) {
            return;
        }

        var expand = toggle.getAttribute('data-toggle-all') === 'expand';
        resultContent.querySelectorAll('.sber-section').forEach(function (section) {
            section.open = expand;
        });

        toggle.setAttribute('data-toggle-all', expand ? 'collapse' : 'expand');
        toggle.textContent = BX.message(expand ? 'SBER_CONTENT_COLLAPSE_ALL' : 'SBER_CONTENT_EXPAND_ALL');
    });

    loadRecent();
});
</script>

</body>
</html>
