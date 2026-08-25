<?

AddEventHandler("im", "OnBeforeMessageNotifyAdd", "___OnBeforeSpecificMessageNotifyAdd");
function ___OnBeforeSpecificMessageNotifyAdd($arFields)
{
    if ($arFields['MESSAGE_TYPE'] == 'S') {
        if (str_contains($arFields['NOTIFY_MESSAGE'],  "комментарий к процессу") || 
            str_contains($arFields['NOTIFY_MESSAGE'], "комментарий в процессе")) {
            return false;
        }
    }

}

?>