<?

AddEventHandler("im", "OnBeforeMessageNotifyAdd", "___OnBeforeSpecificMessageNotifyAdd");
function ___OnBeforeSpecificMessageNotifyAdd($arFields)
{
	if ($arFields['MESSAGE_TYPE'] == 'S' && $arFields['NOTIFY_MODULE'] != 'custom') {
		if (
			str_contains($arFields['NOTIFY_MESSAGE'],  "Добавил комментарий к бизнес-процессу") || 
			str_contains($arFields['NOTIFY_MESSAGE'],  "Добавила комментарий к бизнес-процессу") || 
			str_contains($arFields['NOTIFY_MESSAGE'],  "Упомянул вас в комментарии к процессу") ||
			str_contains($arFields['NOTIFY_MESSAGE'],  "Упомянула вас в комментарии к процессу")
		) {
			$debugMessage = 'Disabled notification. arFields: ' . print_r($arFields, true);
			\Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);
			return false;
		}
	}
}

?>