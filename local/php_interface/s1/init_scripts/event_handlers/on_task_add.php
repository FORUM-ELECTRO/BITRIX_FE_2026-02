<?

//AddEventHandler("tasks", "OnTaskAdd", "MyOnTaskAdd");
function MyOnTaskAdd($idTask, $arTask) {

	if (!empty($arTask['FLOW_ID']) && $arTask['FLOW_ID'] == 10) {

		//LocalRedirect('/zakupki_task.php');
		//LocalRedirect("/company/personal/user/" .  $USER->GetID() . "/tasks/task/view/" . $idTask . '/');
	}
}