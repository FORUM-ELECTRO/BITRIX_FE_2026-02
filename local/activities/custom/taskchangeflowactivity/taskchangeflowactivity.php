<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Tasks\Access\ActionDictionary;
use Bitrix\Tasks\Access\TaskAccessController;
use Bitrix\Tasks\Flow\Control\Task\Field\FlowFieldHandler;
use Bitrix\Tasks\Flow\Internal\Link\FlowLink;
use Bitrix\Tasks\Integration\BizProc\NodeFilter\TargetDocumentResolverTrait;

\Bitrix\Main\Loader::includeModule('tasks');

/**
 * Робот «Изменить поток»
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
			'ModifiedBy' => null,
			'KeepDeadline' => true,
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

		// Действующее лицо: явный «изменить от имени» либо стартер процесса.
		$modifiedBy = method_exists($this, 'getModifiedByOrStarter')
			? $this->getModifiedByOrStarter()
			: $this->ModifiedBy;
		$userId = (int)\CBPHelper::ExtractUsers($modifiedBy, $documentId, true);
		$userId = $userId > 0 ? $userId : 1;

		if (!TaskAccessController::can($userId, ActionDictionary::ACTION_TASK_EDIT, $taskId))
		{
			$this->writeError(Loc::getMessage('TASK_CHANGE_FLOW_NO_PERMISSIONS'));

			return CBPActivityExecutionStatus::Closed;
		}

		// Текущий крайний срок — смена потока пересчитывает DEADLINE по настройкам потока.
		$runtime = CBPRuntime::GetRuntime();
		/** @var CBPDocumentService $ds */
		$ds = $runtime->GetService('DocumentService');

		if (defined('\CBPDocument::PARAM_USED_DOCUMENT_FIELDS'))
		{
			$document = $ds->GetDocument($documentId, select: ['DEADLINE', 'FLOW_ID']);
		}
		else
		{
			$document = $ds->GetDocument($documentId);
		}
		$oldDeadline = $document['DEADLINE'] ?? null;
		$oldFlowId = (int)($document['FLOW_ID'] ?? 0);

		$keepDeadline = CBPHelper::getBool($this->KeepDeadline);

		try
		{
			$taskData = \CTasks::GetByID($taskId, false)->fetch();
			if (!$taskData)
			{
				$this->writeError(Loc::getMessage('TASK_CHANGE_FLOW_ERROR_NO_TASK'));

				return CBPActivityExecutionStatus::Closed;
			}

			// Пересчитать поля потока: ответственный, группа, контроль, срок.
			$handler = new FlowFieldHandler($flowId, $userId);
			$skipTimeZoneFields = [];
			$fields = ['FLOW_ID' => $flowId];
			$handler->modify($fields, $skipTimeZoneFields, $taskData);

			if (!isset($fields['FLOW_ID']))
			{
				// Поток неактивен — менять нечего.
				$this->writeError(Loc::getMessage('TASK_CHANGE_FLOW_FLOW_NOT_ACTIVE'));

				return CBPActivityExecutionStatus::Closed;
			}

			unset($fields['FLOW_ID']);
			if ($keepDeadline)
			{
				unset($fields['DEADLINE']);
			}

			// Обновить поля задачи (без FLOW_ID — связь потока меняется отдельно).
			if (!empty($fields))
			{
				try
				{
					\Bitrix\Tasks\Integration\Bizproc\Document\Task::updateDocument(
						$taskId,
						$fields,
						$userId
					);
				}
				catch (Exception $e)
				{
					$this->writeError($e->getMessage());
				}
			}

			// Сменить связь потока (b_tasks_flow_task). Делается последней и всегда.
			if ($oldFlowId !== $flowId)
			{
				FlowLink::unlink($taskId);
				FlowLink::link($flowId, $taskId);
			}

			// Вернуть прежний крайний срок, если разрешено.
			if ($keepDeadline && !empty($oldDeadline))
			{
				try
				{
					\Bitrix\Tasks\Integration\Bizproc\Document\Task::updateDocument(
						$taskId,
						['DEADLINE' => $oldDeadline],
						$userId
					);
				}
				catch (Exception $e)
				{
					$this->writeError($e->getMessage());
				}
			}
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
			'ModifiedBy' => [
				'Name' => Loc::getMessage('TASK_CHANGE_FLOW_MODIFIED_BY'),
				'FieldName' => 'modified_by',
				'Type' => 'user',
			],
			'KeepDeadline' => [
				'Name' => Loc::getMessage('TASK_CHANGE_FLOW_KEEP_DEADLINE'),
				'FieldName' => 'keep_deadline',
				'Type' => 'bool',
				'Default' => 'Y',
			],
		]);

		return $dialog;
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$errors)
	{
		$errors = [];

		$properties = [
			'FlowId' => (int)($arCurrentValues['flow_id'] ?? 0),
			'ModifiedBy' => CBPHelper::UsersStringToArray($arCurrentValues['modified_by'], $documentType, $errors),
			'KeepDeadline' => ($arCurrentValues['keep_deadline'] ?? 'Y') === 'Y' ? 'Y' : 'N',
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
