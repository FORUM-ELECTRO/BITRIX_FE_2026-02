<?

AddEventHandler("im", "OnBeforeMessageNotifyAdd", "disable_notifications");

function disable_notifications($arFields)
{
	if ($arFields['MESSAGE_TYPE'] == 'S') {
		return false;
	}

}

?>