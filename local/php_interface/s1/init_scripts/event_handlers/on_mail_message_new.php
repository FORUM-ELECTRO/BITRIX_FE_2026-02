<?

\Bitrix\Main\Loader::includeModule('tasks');
\Bitrix\Main\Loader::includeModule('mail');

AddEventHandler('mail', 'onMailMessageNew', 'onMailMessageNewHandler');

function onMailMessageNewHandler($event) {
	try {
		// Обработка писем в техническую поддержку
		/*
		if ($event['MAILBOX_ID'] == '193' && str_contains($event['FIELD_TO'], 'servicedesk@forumgroup.ru') && !isset($event['IN_REPLY_TO'])) {
			// Подготовить поля задачи
			$body = $event['BODY'] ?? 'Обработайте письмо, приложенное к задаче.';
			$body = trim(explode('С уважением,', $body)[0]);
			$arFields = Array(
				'TITLE' => $event['SUBJECT'] ?? 'Письмо на ящик технической поддержки',
				'DESCRIPTION' => $body,
				'CREATED_BY' => TECH_USER_ID,
				'RESPONSIBLE_ID' => TECH_USER_ID,
				'GROUP_ID' => TECH_SUPPORT_GROUP_ID,
				'UF_MAIL_MESSAGE' => $event['ID'],
			);
			*/
			// Попробовать найти отправителя и сделать его постановщиком
			/*
			$email_from = $event['FIELD_FROM'];
			$email_from_addr = mb_strtolower(CMailUtil::ExtractMailAddress($email_from));
			$resUser = CUser::GetList("LAST_LOGIN", "DESC", Array("ACTIVE" => "Y", "EMAIL" => $email_from_addr));
			if(($arUser = $resUser->Fetch()) && mb_strtolower(CMailUtil::ExtractMailAddress($arUser["EMAIL"])) == $email_from_addr) {
				$arFields['CREATED_BY'] = $arUser["ID"];
				$debugMessage = json_encode($arFields);
				\Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);
			}
			*/
			/*
			// Поставить задачу
			$obTask = new CTasks;
			$obTask->Add($arFields);
		}
		*/
	} catch (\Throwable $e) {
		// Обработка всех исключений
		$debugMessage = "Exception: " . print_r($e->getMessage(), true);
		\Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);
	}
}

?>