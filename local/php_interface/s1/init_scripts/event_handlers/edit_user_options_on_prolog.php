<?

AddEventHandler('main', 'OnProlog', 'editUserOptionsOnProlog');

function editUserOptionsOnProlog()
{
	global $USER;
	if ($USER->IsAdmin())
	{
		return;
	}

	$userId = $USER->GetID();
	$url = $_SERVER['REQUEST_URI'];

	$mainInterfaceGridCategory = 'main.interface.grid';

	$options = CUserOptions::GetList(
		$arOrder = ["ID" => "ASC"],
		$arFilter = [
			'USER_ID' => $userId,
			'CATEGORY' => $mainInterfaceGridCategory,
		]
	);

	while ($option = $options->GetNext())
	{
		$optionName = $option['NAME'];
	
		// Смарт-процесс Проекты
		if (str_contains($url, '/page/proekty/proekty_2/') && str_contains($optionName, 'crm-type-item-list-185') && !str_contains($optionName, 'parent'))
		{
			$optionValue = CUserOptions::GetOption($mainInterfaceGridCategory, $optionName, [], $userId);
			$optionValue['views']['default']['columns'] = 'UF_CRM_11_1696574333247,CREATED_TIME,ACTIVITY_BLOCK,UF_CRM_11_1698008329318,TITLE,UF_CRM_11_1695629592601,OPPORTUNITY_WITH_CURRENCY,UF_CRM_11_1700811514,UF_CRM_11_1700739474,UF_CRM_11_1715174463413,STAGE_ID,ASSIGNED_BY_ID,UF_CRM_11_1697008683,UF_CRM_11_1695815863928,UPDATED_TIME';
			$optionValue['views']['default']['custom_names'] = [
				'OPPORTUNITY_WITH_CURRENCY' => 'Сумма',
				'UPDATED_TIME' => 'Обновлён',
				'UF_CRM_11_1695629592601' => 'Адрес объекта',
				'UF_CRM_11_1695815863928' => 'Вер-ть %',
				'UF_CRM_11_1696574333247' => 'ID',
				'UF_CRM_11_1698008329318' => 'Отрасль',
			];
			CUserOptions::SetOption($mainInterfaceGridCategory, $optionName, $optionValue, false, $userId);
		}

	}
}

?>