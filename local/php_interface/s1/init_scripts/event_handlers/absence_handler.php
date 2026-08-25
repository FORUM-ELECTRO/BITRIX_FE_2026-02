<?

AddEventHandler('main', 'OnEpilog', 'changeAbsenceButton');

function changeAbsenceButton()
{
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Только страница График отсутствий
    if ($path !== '/timeman/') {
        return;
    }
    
    ?>
    <style>
        .custom-absence-btn-original {
            background: #1cae6a !important;
            border-color: #1cae6a !important;
            color: white !important;
            position: relative;
            padding-left: 28px !important;
        }
        .custom-absence-btn-original::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 0V14M0 7H14" stroke="white" stroke-width="1.5"/></svg>') no-repeat center;
            background-size: contain;
        }
        .custom-absence-btn-original::after {
            content: '';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 12px;
            height: 12px;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M3 4.5L6 7.5L9 4.5" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>') no-repeat center;
            background-size: contain;
        }
        .custom-absence-btn-original .ui-btn-text-inner {
            margin-left: 4px;
            margin-right: 16px;
        }
    </style>
    <script>
    (function() {
        function init() {
            if (typeof jQuery === 'undefined') {
                setTimeout(init, 200);
                return;
            }
            
            var $ = jQuery;
            
            var $toolbar = $('.ui-toolbar-right-buttons');
            if (!$toolbar.length) {
                setTimeout(init, 500);
                return;
            }
            
            // Скрываем штатную кнопку
            $toolbar.find('.ui-btn-primary').each(function() {
                if ($(this).text().trim() === 'Добавить отсутствие') {
                    $(this).hide();
                }
            });
            
            // Не добавляем свою, если уже есть
            if ($('#custom-absence-container').length) {
                return;
            }
            
            // Создаём контейнер с выпадающим списком
            var $container = $('<div id="custom-absence-container" style="position:relative; display:inline-block; margin-left:8px;"></div>');
            
            // Кнопка с оригинальными плюсиком и треугольником
            var $button = $('<button class="ui-btn ui-btn-success ui-icon-set__scope --air ui-btn-no-caps ui-btn-sm custom-absence-btn-original" id="custom-absence-btn"><span class="ui-btn-text"><span class="ui-btn-text-inner">Добавить отсутствие</span></span></button>');
            
            var $dropdown = $('<div class="absence-dropdown" style="display:none; position:absolute; top:100%; right:0; background:white; border:1px solid #ccc; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.15); z-index:100000; min-width:220px;"></div>');
            
           // Список отсутствий
            var absences = [
                {name: 'отпуск ежегодный', type: 'stream', url: '/stream_vacation.php?run_stream=y&element_id=2655507&iblock_id=510'},
                {name: 'Встреча с клиентом', type: 'stream', url: '/stream_absence.php?run_stream=y&element_id=8&iblock_id=495'},
                {name: 'командировка', type: 'stream', url: '/stream_absence.php?run_stream=y&element_id=87447&iblock_id=194'},
                {name: 'больничный', type: 'smart', url: 'https://portal.forumgroup.ru/page/otsutstviya/bolnichnyy/type/1376/details/0/?categoryId=212&st%5Btool%5D=crm&st%5Bc_section%5D=custom_section&st%5Bc_sub_section%5D=list&st%5Bc_element%5D=create_button&st%5Bp1%5D=crmMode_classic&st%5Bcategory%5D=entity_operations&st%5Bevent%5D=entity_add_open&st%5Btype%5D=dynamic'},
                {name: 'отпуск декретный', type: 'stream', url: '/stream_absence.php?run_stream=y&element_id=90629&iblock_id=250'},
                {name: 'отгул за свой счет', type: 'stream', url: '/stream_absence.php?run_stream=y&element_id=90626&iblock_id=250'},
                {name: 'прогул', type: 'stream', url: '/stream_absence.php?run_stream=y&element_id=90631&iblock_id=250'},
                {name: 'другое', type: 'stream', url: '/stream_absence.php?run_stream=y&element_id=90633&iblock_id=250'}
            ];
            
            // Заполняем выпадающий список
            absences.forEach(function(a) {
                $dropdown.append(
                    $('<div style="padding:10px 15px; cursor:pointer; border-bottom:1px solid #eee;"></div>')
                        .text(a.name)
                        .data('url', a.url)
                        .data('type', a.type)
                        .hover(
                            function() { $(this).css('background', '#f5f7f9'); },
                            function() { $(this).css('background', 'white'); }
                        )
                );
            });
            
            // Обработчик клика по пункту меню
            $dropdown.on('click', 'div', function() {
                var url = $(this).data('url');
                var type = $(this).data('type');
                $dropdown.hide();
                
                if (window.BX && BX.SidePanel) {
                    if (type === 'smart') {
                        // Для смарт-процесса (больничный) открываем в слайдере полную ссылку
                        BX.SidePanel.Instance.open(url, {
                            width: 1200,
                            cacheable: false
                        });
                    } else {
                        // Для остальных процессов
                        BX.SidePanel.Instance.open(url);
                    }
                }
            });
            
            // Клик по кнопке - показать/скрыть список
            $button.on('click', function(e) {
                e.stopPropagation();
                $dropdown.toggle();
            });
            
            // Закрыть список при клике вне
            $(document).on('click', function(e) {
                if (!$container.is(e.target) && $container.has(e.target).length === 0) {
                    $dropdown.hide();
                }
            });
            
            $container.append($button).append($dropdown);
            $toolbar.append($container);
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
    </script>
    <?
}