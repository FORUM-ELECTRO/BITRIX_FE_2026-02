<?

//AddEventHandler('main', 'OnEndBufferContent', 'changeStylesOnEpilog');

function changeStylesOnEpilog()
{
\Bitrix\Main\Diag\Debug::writeToFile('OnBeforeEndBufferContent');
// Проверяем, открыты ли Потоки
	if (str_contains($requestUri, '/tasks/flow/')) {
		ob_start();
		?>         
		<script>
$('#tasks-flow-list-create-task-10').addClass('123123');
		$(document).ready(function() {
  			$('#tasks-flow-list-create-task-10').closest('.tasks-flow__list-cell_line.--start-line').append('<span class="start-workflow-cust ui-btn ui-btn-success ui-btn-xs ui-btn-no-caps ui-btn-round">Создать задачу</span>');
			$('#tasks-flow-list-create-task-10').remove();
			$('.start-workflow-cust').click(function() {
				//BX.SidePanel.Instance.open('https://portal.forumgroup.ru/bitrix/components/bitrix/bizproc.workflow.start/?#sessid_user_for_bp#&templateId=1861&signedDocumentType=[%22lists%22,%22BizprocDocument%22,%22iblock_420%22].f9d66e8b7e09efeac021543bc1fdfbbee19773a6e14e82194517212952af087d&signedDocumentId=[%22lists%22,%22BizprocDocument%22,%22120849%22].84837ad6b0f4e51238000ae879f4aa6d3b1842ad39b0742131e134920a119e1f');
				BX.SidePanel.Instance.open('/stream_zakupki.php?run_stream=y');
	 		});
		});
		</script>
		<?
		//$userSessid = bitrix_sessid_get();
		$html = ob_get_clean();
		//$html = str_replace('#sessid_user_for_bp#', $userSessid, $html);
		\Bitrix\Main\Page\Asset::getInstance()->addString($html, true);
	}

}

?>
