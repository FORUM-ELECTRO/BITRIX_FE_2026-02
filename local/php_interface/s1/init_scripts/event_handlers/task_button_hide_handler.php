<?php
// Файл: /local/php_interface/s1/init_scripts/event_handlers/task_button_hide_handler.php

AddEventHandler("main", "OnEndBufferContent", function(&$content) {
    // Проверяем, что это страница группы 553
    if (strpos($_SERVER['REQUEST_URI'], '/workgroups/group/553/') === false) {
        return;
    }
    
    // Получаем ID задачи из URL
    $taskId = 0;
    if (preg_match('/\/task\/view\/(\d+)\//', $_SERVER['REQUEST_URI'], $matches)) {
        $taskId = (int)$matches[1];
    }
    
    // Если нет ID - выходим (это не страница задачи)
    if (!$taskId) {
        return;
    }
    
    if (!\Bitrix\Main\Loader::includeModule('tasks')) {
        return;
    }
    
    global $USER;
    $userId = $USER->GetID();
    
    // Получаем задачу
    $task = \Bitrix\Tasks\Internals\TaskTable::getList([
        'select' => ['ID', 'GROUP_ID', 'RESPONSIBLE_ID'],
        'filter' => ['ID' => $taskId]
    ])->fetch();
    
    // Если не группа 553 - выходим
    if (!$task || $task['GROUP_ID'] != 553) {
        return;
    }
    
    // Проверяем, является ли пользователь исполнителем
    if ($userId == $task['RESPONSIBLE_ID']) {
        return; // Исполнитель - НЕ СКРЫВАЕМ
    }
    
    // Проверяем, является ли пользователь соисполнителем
    $isAccomplice = false;
    $res = \Bitrix\Tasks\Internals\Task\MemberTable::getList([
        'select' => ['USER_ID'],
        'filter' => ['TASK_ID' => $taskId, 'TYPE' => 'A']
    ]);
    while ($row = $res->fetch()) {
        if ($userId == $row['USER_ID']) {
            $isAccomplice = true;
            break;
        }
    }
    
    if ($isAccomplice) {
        return; // Соисполнитель - НЕ СКРЫВАЕМ
    }
    
    // Если дошли сюда - пользователь НЕ исполнитель и НЕ соисполнитель
    // СКРЫВАЕМ кнопку
    $html = <<<HTML
<style>
    button[data-task-button-id="secondary"] {
        display: none !important;
        visibility: hidden !important;
    }
</style>
<script>
(function() {
    function hideButton() {
        var buttons = document.querySelectorAll('button[data-task-button-id="secondary"]');
        for (var i = 0; i < buttons.length; i++) {
            var btn = buttons[i];
            if (btn.textContent && btn.textContent.trim() === 'Завершить') {
                btn.style.display = 'none';
                btn.style.visibility = 'hidden';
            }
        }
    }
    
    hideButton();
    
    if (window.MutationObserver) {
        var observer = new MutationObserver(hideButton);
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    setInterval(hideButton, 300);
})();
</script>
HTML;
    
    $content = str_replace('</body>', $html . '</body>', $content);
});
?>