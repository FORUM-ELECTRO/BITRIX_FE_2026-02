<?
/**
 * Обработчик события OnProlog
 * Применяет настройки отображения колонок для списка смарт-процесса "Проекты"
 * 
 * @global CUser $USER
 * @return void
 */
AddEventHandler('main', 'OnProlog', 'editUserOptionsOnProlog');

/**
 * Устанавливает пользовательские настройки для списка проектов
 * Выполняется только на странице проектов и только один раз для каждого пользователя
 * 
 * @global CUser $USER Объект текущего пользователя
 * @return void
 */
function editUserOptionsOnProlog()
{
    global $USER;
    
    // Проверяем авторизацию и права администратора
    if (!$USER->IsAuthorized() || $USER->IsAdmin()) {
        return;
    }

    // Проверяем URL страницы
    $url = $_SERVER['REQUEST_URI'];
    if (!str_contains($url, '/page/proekty/proekty_2/')) {
        return;
    }

    $userId = (int)$USER->GetID();
    $mainInterfaceGridCategory = 'main.interface.grid';
    
    // Избежать повторного применения
    $flag = CUserOptions::GetOption('custom_settings', 'project_grid_done', false, $userId);
    if ($flag) {
        return;
    }

    // Получаем настройки списка проектов
    $options = CUserOptions::GetList(
        ['ID' => 'ASC'],
        [
            'USER_ID' => $userId,
            'CATEGORY' => $mainInterfaceGridCategory,
            'NAME' => 'crm-type-item-list-185%'
        ]
    );

    while ($option = $options->GetNext()) {
        $optionName = (string)$option['NAME'];
        
        // Пропускаем родительские настройки
        if (str_contains($optionName, 'parent')) {
            continue;
        }
        
        // Получаем текущие настройки
        $optionValue = CUserOptions::GetOption(
            $mainInterfaceGridCategory,
            $optionName,
            [],
            $userId
        );
        
        // Проверяем, не применены ли уже настройки
        if (!empty($optionValue['views']['default']['columns']) && 
            strpos($optionValue['views']['default']['columns'], 'UF_CRM_11_1695629592601') !== false) {
            CUserOptions::SetOption('custom_settings', 'project_grid_done', true, false, $userId);
            return;
        }
        
        // Применяем настройки колонок
        $optionValue['views']['default']['columns'] = implode(',', [
            'UF_CRM_11_1696574333247',
            'CREATED_TIME',
            'ACTIVITY_BLOCK',
            'UF_CRM_11_1698008329318',
            'TITLE',
            'UF_CRM_11_1695629592601',
            'OPPORTUNITY_WITH_CURRENCY',
            'UF_CRM_11_1700811514',
            'UF_CRM_11_1700739474',
            'UF_CRM_11_1715174463413',
            'STAGE_ID',
            'ASSIGNED_BY_ID',
            'UF_CRM_11_1697008683',
            'UF_CRM_11_1695815863928',
            'UPDATED_TIME'
        ]);
        
        // Устанавливаем пользовательские названия колонок
        $optionValue['views']['default']['custom_names'] = [
            'OPPORTUNITY_WITH_CURRENCY' => 'Сумма',
            'UPDATED_TIME' => 'Обновлён',
            'UF_CRM_11_1695629592601' => 'Адрес объекта',
            'UF_CRM_11_1695815863928' => 'Вер-ть %',
            'UF_CRM_11_1696574333247' => 'ID',
            'UF_CRM_11_1698008329318' => 'Отрасль',
        ];
        
        // Сохраняем настройки
        CUserOptions::SetOption(
            $mainInterfaceGridCategory,
            $optionName,
            $optionValue,
            false,
            $userId
        );
        
        // Устанавливаем флаг выполнения
        CUserOptions::SetOption(
            'custom_settings',
            'project_grid_done',
            true,
            false,
            $userId
        );
        
        break;
    }
}
?>