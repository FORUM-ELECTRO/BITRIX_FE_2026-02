<?

\Bitrix\Main\Loader::includeModule('forum');
\Bitrix\Main\Loader::includeModule('im');
\Bitrix\Main\Loader::includeModule('crm');
\Bitrix\Main\Loader::includeModule('bizproc');

AddEventHandler('forum', 'onAfterMessageAdd', 'onAfterMessageAddHandler');

function onAfterMessageAddHandler($messageId, $messageFields)
{
	try {
		$forumId = $messageFields['FORUM_ID'];
		$postMessage = $messageFields['POST_MESSAGE'];
		$topicId = $messageFields['TOPIC_ID'];
		$authorId = $messageFields['AUTHOR_ID'];
		$arAuthorInfo = CUser::getByID($authorId)->fetch();
		$authorFullName = $arAuthorInfo['NAME'] . ' ' . $arAuthorInfo['LAST_NAME'];

		// Сообщение в форуме БП (комментарий к заданию)
		if ($forumId == WORKFLOW_FORUM_ID) {
			$workflowTopic = \CForumTopic::GetByID($topicId);
			$workflowId = explode('WF_', $workflowTopic['TITLE'])[1];

			if ($workflowId) {
				$mentions = preg_match_all('/\[USER=(\w+)\]/', $postMessage, $match_results);
				$mentionedUserIds = array_unique($match_results[1]);

				if (true) {
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

						if ($documentName) {
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
								if (!empty($mentionedUserIds)) {
									$observerIds = array_merge($observerIds, $mentionedUserIds);
									$item->setObservers($observerIds);
									$item->save();
								}
	
								$arTaskUserIds = array_unique(array_merge($observerIds, $arTaskUserIds));
							} else {
								$arTaskUserIds = array_unique(array_merge($mentionedUserIds, $arTaskUserIds));
							}

							// Уведомление участников задания и наблюдателей
							foreach ($arTaskUserIds as $mentionedUserId) {
								if (!empty($mentionedUserIds) && in_array($mentionedUserId, $mentionedUserIds)) {
									continue;
								}
								$taskLink = $_SERVER['HTTP_HOST'] . '/company/personal/bizproc/' . $workflowId . '/';

								if (stripos($workflowDocumentId, 'DYNAMIC') !== false) {
									$taskLink = $_SERVER['HTTP_HOST']  . '/crm/type/' . $typeId . '/details/' . $itemId . '/';
								}

								if ($authorId) {
									$messToSend = '<b>Пользователь <a href="https://' . $_SERVER['HTTP_HOST'] . '/company/personal/user/' . $authorId . '/">' . $authorFullName  . '</a>  оставил новый комментарий в процессе <a href="' . $taskLink . '">' . $documentName . '</a></b><br><br>Комментарий: ' . $comment;
								} else {
									$messToSend = 'Оставлен новый комментарий в процессе <a href="' . $taskLink . '">' . $documentName . '</a></b><br><br>Комментарий: ' . $comment;
								}

								$arNotificationFields = array(
									'TO_USER_ID' => $mentionedUserId,
									'FROM_USER_ID' => $authorId,
									'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
									'NOTIFY_MODULE' => 'tasks',
									'NOTIFY_MESSAGE' => $messToSend,
								);
								if ($mentionedUserId != $authorId) {
									CIMNotify::Add($arNotificationFields);
								}
							}

							// Уведомление упоминаемых пользователей
							foreach ($mentionedUserIds as $mentionedUserId) {
								$taskLink = $_SERVER['HTTP_HOST'] . '/company/personal/bizproc/' . $workflowId . '/';

								if (stripos($workflowDocumentId, 'DYNAMIC') !== false) {
									$taskLink = $_SERVER['HTTP_HOST']  . '/crm/type/' . $typeId . '/details/' . $itemId . '/';
								}

								$arNotificationFields = array(
									'TO_USER_ID' => $mentionedUserId,
									'FROM_USER_ID' => $authorId,
									'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
									'NOTIFY_MODULE' => 'tasks',
									'NOTIFY_MESSAGE' => '<b>Сотрудник упомянул вас в процессе <a href="' . $taskLink . '">' . $documentName . '</a></b><br><br>Комментарий: ' . $comment,
								);
								if ($mentionedUserId != $authorId) {
									CIMNotify::Add($arNotificationFields);
								}
							}
						}
					}
				}
			}
		}
	} catch (\Throwable $e) {
		$debugMessage = 'Exception: ' . print_r($e->getMessage(), true);
		\Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);
	}
}
