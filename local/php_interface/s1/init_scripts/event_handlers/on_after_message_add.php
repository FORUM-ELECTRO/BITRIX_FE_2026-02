<?

\Bitrix\Main\Loader::includeModule('forum');
\Bitrix\Main\Loader::includeModule('im');
\Bitrix\Main\Loader::includeModule('crm');
\Bitrix\Main\Loader::includeModule('bizproc');

AddEventHandler('forum', 'onAfterMessageAdd', 'onAfterMessageAddHandler');

function onAfterMessageAddHandler($messageId, $messageFields)
{
	try {
		$debugMessage = 'messageFields: ' . print_r($messageFields, true);
		\Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);

		$forumId = $messageFields['FORUM_ID'];
		$postMessage = $messageFields['POST_MESSAGE'];
		$topicId = $messageFields['TOPIC_ID'];
		$authorId = $messageFields['AUTHOR_ID'];
		$arAuthorInfo = CUser::getByID($authorId)->fetch();
		$authorFullName = $arAuthorInfo['NAME'] . ' ' . $arAuthorInfo['LAST_NAME'];
		$newTopic = $messageFields['NEW_TOPIC'];

		// Сообщение в форуме БП (комментарий к заданию)
		if ($forumId == WORKFLOW_FORUM_ID) {
			// Пропускаем системные сообщения о новом топике
			if ($newTopic == 'Y') {
				return;
			}

			$workflowTopic = \CForumTopic::GetByID($topicId);
			$workflowId = explode('WF_', $workflowTopic['TITLE'])[1];

			$debugMessage = 'workflowId: ' . $workflowId;
			\Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);

			if (!$workflowId) {
				return;
			}

			$mentions = preg_match_all('/\[USER=(\w+)\]/', $postMessage, $match_results);
			$mentionedUserIds = array_unique($match_results[1]);

			$workflowStates = \Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable::query()
				->setSelect(['ID', 'MODULE_ID', 'ENTITY', 'DOCUMENT_ID'])
				->where('ID', $workflowId)
				->exec();

			if ($workflowState = $workflowStates->fetch()) {
				$workflowDocumentId = $workflowState['DOCUMENT_ID'];
				$documentName = CBPDocument::getDocumentName([
					$workflowState['MODULE_ID'],
					$workflowState['ENTITY'],
					$workflowDocumentId,
				]);

				$debugMessage = 'documentName: ' . $documentName;
				\Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);

				if (!$documentName) {
					return;
				}

				$comment = $postMessage;
				$comment = str_replace('[QUOTE]', '"', $comment);
				$comment = str_replace('[/QUOTE]', '"', $comment);

				$arWorkflowDocumentId = explode('_', $workflowDocumentId);
				$typeId = $arWorkflowDocumentId[1];
				$itemId = $arWorkflowDocumentId[2];

				$dbTask = CBPTaskService::GetList(
					array(),
					array('WORKFLOW_ID' => $workflowId),
					false,
					false,
					array('USER_ID', "WORKFLOW_ID")
				);

				$arTaskUserIds = [];
				while ($arTaskRes = $dbTask->GetNext()) {
					$arTaskUserIds[] = $arTaskRes['USER_ID'];
				}

				// Добавление в наблюдателей элемента
				if ($typeId) {
					$container = \Bitrix\Crm\Service\Container::getInstance();
					$factory = $container->getFactory($typeId);
					if ($factory === null) {
						return;
					}

					$item = $factory->getItem($itemId);
					if ($item === null) {
						return;
					}

					$observerIds = $item->getObservers();
					if (empty($observerIds)) {
						$observerIds = [];
					}

					$arTaskUserIds = array_unique(array_merge($observerIds, $arTaskUserIds));

					if (!empty($mentionedUserIds)) {
						$observerIds = array_merge($observerIds, $mentionedUserIds);
						$item->setObservers($observerIds);
						$item->save();
					}
				}

				// Уведомление участников задания и наблюдателей
				foreach ($arTaskUserIds as $arTaskUserId) {
					if (!empty($mentionedUserIds) && in_array($arTaskUserId, $mentionedUserIds)) {
						continue;
					}

					$debugMessage = 'authorId: ' . json_encode($authorId) . ' arTaskUserId: ' . json_encode($arTaskUserId);
					\Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);

					if ($arTaskUserId == $authorId) {
						continue;
					}

					$taskLink = $_SERVER['HTTP_HOST'] . '/company/personal/bizproc/' . $workflowId . '/';

					if (stripos($workflowDocumentId, 'DYNAMIC') !== false) {
						$taskLink = $_SERVER['HTTP_HOST']  . '/crm/type/' . $typeId . '/details/' . $itemId . '/';
					}

					if ($authorId) {
						$messToSend = '<b>Пользователь ' . $authorFullName  . ' оставил новый комментарий в процессе <a href="' . $taskLink . '">' . $documentName . '</a></b><br><br>Комментарий: ' . $comment;
					} else {
						$messToSend = '<b>Оставлен новый комментарий в процессе <a href="' . $taskLink . '">' . $documentName . '</a></b><br><br>Комментарий: ' . $comment;
					}

					$arNotificationFields = array(
						'TO_USER_ID' => $arTaskUserId,
						'FROM_USER_ID' => $authorId,
						'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
						'NOTIFY_MODULE' => 'bizproc',
						'NOTIFY_EVENT' => 'activity',
						'NOTIFY_MESSAGE' => $messToSend,
					);

					$debugMessage = 'arTask. arNotificationFields: ' . print_r($arNotificationFields, true);
					\Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);

					CIMNotify::Add($arNotificationFields);
				}

				// Уведомление упоминаемых пользователей
				foreach ($mentionedUserIds as $mentionedUserId) {
					$debugMessage = 'authorId: ' . json_encode($authorId) . ' mentionedUserId: ' . json_encode($mentionedUserId);
					\Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);

					if ($mentionedUserId == $authorId) {
						continue;
					}

					$taskLink = $_SERVER['HTTP_HOST'] . '/company/personal/bizproc/' . $workflowId . '/';

					if (stripos($workflowDocumentId, 'DYNAMIC') !== false) {
						$taskLink = $_SERVER['HTTP_HOST']  . '/crm/type/' . $typeId . '/details/' . $itemId . '/';
					}

					$arNotificationFields = array(
						'TO_USER_ID' => $mentionedUserId,
						'FROM_USER_ID' => $authorId,
						'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
						'NOTIFY_MODULE' => 'bizproc',
						'NOTIFY_EVENT' => 'activity',
						'NOTIFY_MESSAGE' => '<b>Сотрудник упомянул вас в процессе <a href="' . $taskLink . '">' . $documentName . '</a></b><br><br>Комментарий: ' . $comment,
					);

					$debugMessage = 'mentioned. arNotificationFields: ' . print_r($arNotificationFields, true);
					\Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);

					CIMNotify::Add($arNotificationFields);
				}
			}
		}
	} catch (\Throwable $e) {
		$debugMessage = 'Exception: ' . print_r($e->getMessage(), true);
		\Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);
	}
}
