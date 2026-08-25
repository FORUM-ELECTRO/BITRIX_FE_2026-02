<?

AddEventHandler('main', 'OnEpilog', 'companyRequisitesRequiredHandler');

function companyRequisitesRequiredHandler()
{
	try {
		$requestUri = $_SERVER['REQUEST_URI'];

		if (!str_contains($requestUri, '/details/')) {
			return;
		}

		$detailsType = '';
		if (str_contains($requestUri, '/crm/company/details/')) {
			$detailsType = 'company';
		}

		ob_start();
		?>
		<script>
			const detailsType = <? echo json_encode($detailsType); ?>;

			document.addEventListener('DOMContentLoaded', function() {

				// Функция для обработки логики, когда поле реквизитов появляется
				function handleRequisitesField(requisitesField) {
					const requisitesFieldSearchIcon = requisitesField.querySelector('.ui-ctl-icon-search');
					if (requisitesFieldSearchIcon === null) {
						return;
					}

					const saveButton = document.querySelector('.ui-entity-section-control-edit-mode > .ui-btn-success');
					if (saveButton === null) {
						return;
					}

					saveButton.title = '';

					const requisitesFieldErrorText = document.createElement('div');
					requisitesFieldErrorText.className = 'ui-entity-editor-field-error-text';
					requisitesFieldErrorText.textContent = 'Пожалуйста, введите реквизиты';
					requisitesFieldErrorText.style.display = 'none';

					requisitesField.append(requisitesFieldErrorText);
					if (requisitesFieldSearchIcon.style.display !== 'none') {
						saveButton.setAttribute('disabled', 'disabled');
						requisitesField.classList.add('ui-entity-editor-field-error');
						requisitesFieldErrorText.style.display = '';
					}

					let requisitesFieldSearchIconObserver = new MutationObserver(mutationRecords => {
						if (requisitesFieldSearchIcon.style.display === 'none') {
							saveButton.removeAttribute('disabled');
							requisitesField.classList.remove('ui-entity-editor-field-error');
							requisitesFieldErrorText.style.display = 'none';
						} else {
							saveButton.setAttribute('disabled', 'disabled');
							requisitesField.classList.add('ui-entity-editor-field-error');
							requisitesFieldErrorText.style.display = '';
						}
					});
					requisitesFieldSearchIconObserver.observe(requisitesFieldSearchIcon, {
						attributes: true
					});

					const requisitesFieldTitleText = requisitesField.querySelector('.ui-entity-editor-block-title-text');
					if (requisitesFieldTitleText === null) {
						return;
					}

					const requiredMarkHtml = '<span style="color: rgb(255, 0, 0);">*</span>';
					requisitesFieldTitleText.innerHTML = requisitesFieldTitleText.innerHTML + requiredMarkHtml;
				}

				// Периодическая проверка наличия поля реквизитов (каждые 500 миллисекунд)
				const intervalId = setInterval(() => {

					// Ищем в штатном поле Клиент в блоке Компании
					let requisitesField = document.querySelector('.ui-entity-editor-section-edit[data-cid*=COMPANY] .crm-entity-widget-content-block-field-requisites');

					// Ищем в карточке Компании
					if (requisitesField === null && detailsType === 'company') {
						requisitesField = document.querySelector('.crm-entity-widget-content-block-field-requisites');
					}

					if (requisitesField !== null) {
						handleRequisitesField(requisitesField);
						clearInterval(intervalId); // Останавливаем проверку после нахождения поля
					}

				}, 500);

			});
		</script>
		<?
		$html = ob_get_clean();
		\Bitrix\Main\Page\Asset::getInstance()->addString($html, true);
	} catch (\Throwable $e) {
		$debugMessage = 'Exception: ' . print_r($e->getMessage(), true);
		\Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);
	}
}

?>
