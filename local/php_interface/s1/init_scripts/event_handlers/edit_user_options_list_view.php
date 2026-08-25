<?php
\Bitrix\Main\Loader::includeModule('iblock');

AddEventHandler('main', 'OnProlog', 'editUserOptionsOnPrologListView');

function editUserOptionsOnPrologListView()
{
    global $USER;


    if (!$USER->IsAuthorized() || $USER->IsAdmin()) {
        return;
    }

    $userId = $USER->GetID();
    $url = $_SERVER['REQUEST_URI'];


    $columnSettings = getColumnSettingsFromIBlock();

    if (empty($columnSettings)) {
        return;
    }

    $mainInterfaceGridCategory = 'main.interface.grid';

    $matchedSetting = null;
    foreach ($columnSettings as $setting) {
        $crmCode = $setting['CRM_CODE'];

        if (strpos($url, strtolower($crmCode)) !== false || strpos($url, 'type=' . $crmCode) !== false) {
            $matchedSetting = $setting;
            break;
        }
    }

    if (!$matchedSetting) {
        return;
    }

    $allOptions = CUserOptions::GetList([], [
        'USER_ID' => $userId,
        'CATEGORY' => $mainInterfaceGridCategory
    ]);

    $foundOption = false;

    $allOptions = CUserOptions::GetList([], [
		'USER_ID' => $userId,
		'CATEGORY' => $mainInterfaceGridCategory
	]);


	while ($option = $allOptions->Fetch()) {
		$optionName = $option['NAME'];

		if (strpos($optionName, $matchedSetting['CRM_CODE']) !== false) {

			
			$result = updateUserGridColumns($userId, $mainInterfaceGridCategory, $optionName, $matchedSetting['COLUMN_FIELDS']);

			
			$foundOption = true;
			break;
		}
	}

    if (!$foundOption) {

    }
 
}

function getColumnSettingsFromIBlock()
{
    $settings = [];
        
    $dbItems = \CIBlockElement::GetList(
        ['SORT' => 'ASC'],
        [
            'IBLOCK_ID' => 476,
            'ACTIVE' => 'Y'
        ],
        "PROPERTY_3451",
        false,
        [
            'ID',
            'NAME', 
            'IBLOCK_ID',
            'PROPERTY_3451',
            'PROPERTY_3452'
        ]
    );
    
    while ($item = $dbItems->GetNext()) {
        if (!empty($item['PROPERTY_3451_VALUE']) && !empty($item['PROPERTY_3452_VALUE'])) {
            $propertyValue = $item['PROPERTY_3451_VALUE'];
            $parts = explode('_', $propertyValue);
            
            $crmCode = $propertyValue;
            $optionNamePattern = null;

            if (count($parts) >= 2 && strtolower($parts[0]) === 'crm') {
                $crmCode = $parts[1];
                
                // СП crm-type-item-list-{число}
                if (is_numeric($parts[1])) {
                    $optionNamePattern = 'crm-type-item-list-' . $parts[1];
                } else {
                    // DEAL, CONTACT и т.д.  CRM_{TYPE}_LIST%
                    $optionNamePattern = 'CRM_' . strtoupper($parts[1]) . '_LIST%';
                }
            }

            if (!isset($settings[$crmCode])) {
                $settings[$crmCode] = [
                    'CRM_CODE' => $crmCode,
                    'COLUMN_FIELDS' => [],
                    'OPTION_NAME_PATTERN' => $optionNamePattern
                ];
            }

            $settings[$crmCode]['COLUMN_FIELDS'][] = $item['PROPERTY_3452_VALUE'];
        }
    }

    $settings = array_values($settings);

    return $settings;
}

function updateUserGridColumns($userId, $category, $optionName, $columnField)
{
    $optionValue = CUserOptions::GetOption($category, $optionName, [], $userId);

    if (is_array($columnField)) {
        $columnField = implode(',', $columnField);
    }

    if (empty($optionValue)) {
        $optionValue = [
            'views' => [
                'default' => [
                    'columns' => $columnField,
                    'page_size' => 20,
                    'sort_by' => 'ID',
                    'sort_order' => 'desc'
                ]
            ],
            'filters' => [],
            'current_view' => 'default'
        ];
    } else {

        $currentColumns = $optionValue['views']['default']['columns'] ?? '';
        if ($currentColumns === $columnField) {
            return true;
        }

        $optionValue['views']['default']['columns'] = $columnField;

    }

    $result = CUserOptions::SetOption($category, $optionName, $optionValue, false, $userId);
    
    return $result;
}
?>