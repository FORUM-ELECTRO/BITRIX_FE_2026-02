<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Tasks\Access\ActionDictionary;
use Bitrix\Tasks\Access\TaskAccessController;
use Bitrix\Tasks\Integration\BizProc\NodeFilter\TargetDocumentResolverTrait;

\Bitrix\Main\Loader::includeModule('tasks');

/**
 * Робот «Изменить поток»: меняет поток у текущей задачи.
 */
class CBPTaskChangeFlowActivity extends CBPActivity
{
	use TargetDocumentResolverTrait;

	public function __construct($name)
	{
		parent::__construct($name);

		$this->arProperties = [
			'Title' => '',
			'FlowId' => null,
			// return
			'ErrorMessage' => null,
		];

		$this->SetPropertiesTypes([
			'ErrorMessage' => ['Type' => 'string'],
		]);
	}

	protected function ReInitialize()
	{
		parent::ReInitialize();

		$this->ErrorMessage = null;
	}

	public function Execute()
	{
		$flowId = (int)$this->FlowId;
		if ($flowId <= 0 || !CModule::IncludeModule('tasks'))
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$documentId = $this->resolveTargetDocumentId();
		if (($documentId[0] ?? null) !== 'tasks')
		{
			$this->writeError(Loc::getMessage('TASK_CHANGE_FLOW_ERROR_NO_TASK'));

			return CBPActivityExecutionStatus::Closed;
		}

		$taskId = (int)$documentId[2];

		$modifiedBy = method_exists($this, 'getModifiedByOrStarter')
			? $this->getModifiedByOrStarter()
			: $this->ModifiedBy;
		$userId = 0;
		if (is_string($modifiedBy) && preg_match('/^user_(\d+)$/', $modifiedBy, $m))
		{
			$userId = (int)$m[1];
		}
		$userId = $userId > 0 ? $userId : 1;

		if (!TaskAccessController::can($userId, ActionDictionary::ACTION_TASK_EDIT, $taskId))
		{
			$this->writeError(Loc::getMessage('TASK_CHANGE_FLOW_NO_PERMISSIONS'));

			return CBPActivityExecutionStatus::Closed;
		}

		try
		{
			$task = \CTaskItem::getInstance($taskId, $userId);
			$task->update(['FLOW_ID' => $flowId]);
		}
		catch (Exception $e)
		{
			$this->writeError($e->getMessage());
		}

		return CBPActivityExecutionStatus::Closed;
	}

	private function writeError(string $message): void
	{
		$this->WriteToTrackingService($message, 0, CBPTrackingType::Error);
		$this->ErrorMessage = $message;
	}

	public static function ValidateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$errors = [];

		if (empty($arTestProperties['FlowId']))
		{
			$errors[] = [
				'code' => 'NotExist',
				'parameter' => 'FlowId',
				'message' => Loc::getMessage('TASK_CHANGE_FLOW_EMPTY_PROP'),
			];
		}

		return array_merge($errors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = '', $popupWindow = null, $siteId = '')
	{
		if (!CModule::IncludeModule('tasks'))
		{
			return '';
		}

		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, [
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues,
			'formName' => $formName,
			'siteId' => $siteId,
		]);

		$dialog->setMap([
			'FlowId' => [
				'Name' => Loc::getMessage('TASK_CHANGE_FLOW_FLOW_ID'),
				'FieldName' => 'flow_id',
				'Type' => 'int',
				'Required' => true,
			],
		]);

		return $dialog;
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$errors)
	{
		$errors = [];

		$properties = [
			'FlowId' => (int)($arCurrentValues['flow_id'] ?? 0),
		];

		$errors = self::ValidateProperties($properties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($errors) > 0)
		{
			return false;
		}

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$currentActivity['Properties'] = $properties;

		return true;
	}
}
