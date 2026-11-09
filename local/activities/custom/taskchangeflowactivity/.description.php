<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Bizproc\Activity\ActivityDescription;
use Bitrix\Bizproc\Activity\Enum\ActivityType;
use Bitrix\Main\Localization\Loc;

$description = (
	new ActivityDescription(
		name: Loc::getMessage('TASK_CHANGE_FLOW_NAME'),
		description: Loc::getMessage('TASK_CHANGE_FLOW_DESC'),
		type: [ ActivityType::ROBOT->value ],
	)
)
	->setClass('TaskChangeFlowActivity')
	->setJsClass(ActivityDescription::DEFAULT_ACTIVITY_JS_CLASS)
	->setCategory([
		'ID' => 'document',
		'OWN_ID' => 'tasks',
		'OWN_NAME' => Loc::getMessage('TASK_CHANGE_FLOW_CATEGORY'),
	])
	->setReturn([
		'ErrorMessage' => [
			'NAME' => Loc::getMessage('TASK_CHANGE_FLOW_ERROR_MESSAGE'),
			'TYPE' => 'string',
		],
	])
	->setFilter([
		'INCLUDE' => [
			['tasks'],
		],
	])
	->setRobotSettings([
		'CATEGORY' => 'employee',
		'GROUP' => ['taskManagement'],
	])
;

$arActivityDescription = $description->toArray();
