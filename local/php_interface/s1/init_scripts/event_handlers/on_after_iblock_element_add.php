<?

\Bitrix\Main\Loader::includeModule('iblock');

AddEventHandler('iblock', 'OnAfterIBlockElementAdd', 'OnAfterIBlockElementAddHandler');

function OnAfterIBlockElementAddHandler(&$arFields)
{
	try {
		// История состояний
		if ($arFields['IBLOCK_ID'] == STATE_HISTORY_IBLOCK_ID) {
			$workflowTemplateId = 1213;
			$documentId = ['iblock', 'CIBlockDocument', $arFields['ID']];
			$arWorkflowParameters = [];
			$arErrorsTmp = [];

			CBPDocument::StartWorkflow(
				$workflowTemplateId,
				$documentId,
				$arWorkflowParameters,
				$arErrorsTmp
			);
		}
	} catch (\Throwable $e) {
		$debugMessage = 'Exception: ' . print_r($e->getMessage(), true);
		\Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);
	}
}

?>