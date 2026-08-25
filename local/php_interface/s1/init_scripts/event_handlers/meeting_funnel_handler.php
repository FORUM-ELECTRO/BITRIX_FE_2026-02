<?php

// Подключаем обработчик для кастомной воронки встреч и командировок
AddEventHandler('main', 'OnEpilog', 'OnEpilogInsertMeetingFunnel');

function OnEpilogInsertMeetingFunnel()
{
    global $APPLICATION;

    $request = \Bitrix\Main\Context::getCurrent()->getRequest();
    $uri = $request->getRequestUri();

    // Только на страницах потоков CRM
    if (strpos($uri, '/tasks/flow/') !== false) {
        $APPLICATION->AddHeadScript('/bitrix/js/main/utils.js');
        ?>
        <script>
        BX.ready(function() {
            const target = document.querySelector('.stream-section-container');
            if (target) {
                // Проверим, не вставлен ли уже компонент
                if (document.querySelector('#meeting-funnel-container')) {
                    return;
                }

                const container = document.createElement('div');
                container.id = 'meeting-funnel-container';
                container.style.padding = '10px';
                container.style.backgroundColor = '#f9f9f9';
                container.style.borderBottom = '1px solid #e0e0e0';
                container.style.fontSize = '13px';

                container.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 15px; font-weight: 500;">
                        <span style="color: #1890ff;"><i class="icon-meeting"></i> Встреча</span>
                        <span style="color: #52c41a;"><i class="icon-trip"></i> Командировка</span>
                        <span style="color: #faad14;"><i class="icon-vacation"></i> Отпуск</span>
                    </div>
                `;

                // Добавляем стили
                const style = document.createElement('style');
                style.textContent = `
                    #meeting-funnel-container .icon-meeting::before { content: '📅'; }
                    #meeting-funnel-container .icon-trip::before { content: '🚗'; }
                    #meeting-funnel-container .icon-vacation::before { content: '🌴'; }
                `;
                document.head.appendChild(style);

                target.parentNode.insertBefore(container, target);
            }
        });
        </script>
        <?php
    }
}
