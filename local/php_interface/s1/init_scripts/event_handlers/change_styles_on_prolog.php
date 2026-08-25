<?
AddEventHandler('main', 'OnProlog', 'changeStylesOnProlog');

function changeStylesOnProlog()
{
	ob_start();
	?>         
	<style>
		.bx-field-value select[name*=PROPERTY] {
			width: 100% !important;
			min-width: 300px !important;
		}
	</style>
	<?
	$html = ob_get_clean();
	\Bitrix\Main\Page\Asset::getInstance()->addString($html, true);

	$requestUri = $_SERVER['REQUEST_URI'];

    // Проверяем, открыта ли карточка СП Проекты
	if (str_contains($requestUri, '/proekty_2/type/185/details/')) {
		ob_start();
		?>         
		<style>
			.crm-entity-section.crm-entity-section-tabs {
				margin: 0 0 5px 0 !important;
			}
			.crm-entity-section.crm-entity-section-tabs .main-buttons-inner-container {
				width: 75% !important;
			}
			.crm-entity-section.crm-entity-section-tabs .main-buttons-box {
				height: fit-content !important;
			}
			.crm-entity-section.crm-entity-section-tabs .main-buttons-item {
				margin-bottom: 5px !important;
			}
			.crm-entity-section.crm-entity-section-tabs .main-buttons-item.--hidden {
				display: inline-flex !important;
			}
		</style>
		<?
		$html = ob_get_clean();
		\Bitrix\Main\Page\Asset::getInstance()->addString($html, true);
	}


	// Проверяем, открыта ли карточка СП "Договоры", "Спецификации", "Доп. Соглашения" и "Протоколы разногласий"
	if (str_contains($requestUri, '/dokumentooborot_2/dogovory/type/1066/details/') ||
		str_contains($requestUri, '/dokumentooborot_2/spetsifikatsii/type/1192/details/') ||
		str_contains($requestUri, '/dokumentooborot_2/dop_soglasheniya/type/1172/details/') ||
		str_contains($requestUri, '/dokumentooborot_2/protokoly_raznoglasiy/type/1196/details/')) {
		ob_start();
		?>         
		<style>
			.crm-entity-stream-section.crm-entity-stream-section-live-im {
				display: none;
			}
		</style>
		<?
		$html = ob_get_clean();
		\Bitrix\Main\Page\Asset::getInstance()->addString($html, true);
	}

	// Проверяем, открыты ли Потоки
	if (str_contains($requestUri, '/tasks/flow/')) {
		ob_start();
		?>         
		<script>
		(function() {
			'use strict';
			
			// Конфигурация потоков с формой смарт-процесса
			var smartProcessFlows = {
				'17': { // КПП
					className: 'start-workflow-cust17',
					url: '/page/proekty/proektnye_raschyety/type/1406/details/0/?categoryId=218&st%5Btool%5D=crm&st%5Bc_section%5D=custom_section&st%5Bc_sub_section%5D=list&st%5Bc_element%5D=create_button&st%5Bp1%5D=crmMode_classic&st%5Bcategory%5D=entity_operations&st%5Bevent%5D=entity_add_open&st%5Btype%5D=dynamic'
				},
				'33': { // Шинопровод
					className: 'start-workflow-cust33',
					url: '/page/proekty/proektnye_raschyety/type/1406/details/0/?categoryId=219&st%5Btool%5D=crm&st%5Bc_section%5D=custom_section&st%5Bc_sub_section%5D=kanban&st%5Bc_element%5D=create_button&st%5Bp1%5D=crmMode_classic&st%5Bcategory%5D=entity_operations&st%5Bevent%5D=entity_add_open&st%5Btype%5D=dynamic'
				},
				'35': { // НВО
					className: 'start-workflow-cust35',
					url: '/page/proekty/proektnye_raschyety/type/1406/details/0/?categoryId=221&st%5Btool%5D=crm&st%5Bc_section%5D=custom_section&st%5Bc_sub_section%5D=kanban&st%5Bc_element%5D=create_button&st%5Bp1%5D=crmMode_classic&st%5Bcategory%5D=entity_operations&st%5Bevent%5D=entity_add_open&st%5Btype%5D=dynamic'
				},
				'40': { // Производство НКУ
					className: 'start-workflow-cust40',
					url: '/page/proekty/proektnye_raschyety/type/1406/details/0/?categoryId=222&st%5Btool%5D=crm&st%5Bc_section%5D=custom_section&st%5Bc_sub_section%5D=kanban&st%5Bc_element%5D=create_button&st%5Bp1%5D=crmMode_classic&st%5Bcategory%5D=entity_operations&st%5Bevent%5D=entity_add_open&st%5Btype%5D=dynamic'
				},
				'34': { // Светотехника и Светодизайн
					className: 'start-workflow-cust34',
					url: '/page/proekty/proektnye_raschyety/type/1406/details/0/?categoryId=220&st%5Btool%5D=crm&st%5Bc_section%5D=custom_section&st%5Bc_sub_section%5D=kanban&st%5Bc_element%5D=create_button&st%5Bp1%5D=crmMode_classic&st%5Bcategory%5D=entity_operations&st%5Bevent%5D=entity_add_open&st%5Btype%5D=dynamic'
				},
				'38': { // Корректировка документов 1С
					className: 'start-workflow-cust38',
					url: '/crm/type/1442/details/0/?categoryId=233&st%5Btool%5D=crm&st%5Bc_section%5D=dynamic_section&st%5Bc_sub_section%5D=list&st%5Bc_element%5D=create_button&st%5Bp1%5D=crmMode_classic&st%5Bcategory%5D=entity_operations&st%5Bevent%5D=entity_add_open&st%5Btype%5D=dynamic'
				}
			};
			
			// Конфигурация потоков со страницами в корне
			var pageFlows = {
				'10': { // Закупки
					className: 'start-workflow-cust10',
					url: '/stream_zakupki.php?run_stream=y'
				},
				'13': { // Рекламации
					className: 'start-workflow-cust1',
					url: '/stream_reklam.php?run_stream=y'
				},
				'26': { // Возврат денежных средств
					className: 'start-workflow-cust26',
					url: '/stream_refundsmoney.php?run_stream=y'
				}
			};
			
			// Объединенная конфигурация
			var flowConfig = Object.assign({}, smartProcessFlows, pageFlows);
			
			// Функция для полной замены нативной кнопки на кастомную
			function replaceNativeButtonWithCustom(nativeButton) {
				if (!nativeButton || !nativeButton.id) return;
				
				var flowId = nativeButton.id.replace('tasks-flow-list-create-task-', '');
				if (!flowConfig[flowId]) return;
				
				var config = flowConfig[flowId];
				var parentCell = nativeButton.closest('.tasks-flow__list-cell_line.--start-line');
				if (!parentCell) return;
				
				// Проверяем, не была ли уже заменена кнопка
				if (parentCell.querySelector('.' + config.className)) {
					// Удаляем старую нативную кнопку на всякий случай
					if (nativeButton.parentNode) {
						nativeButton.parentNode.removeChild(nativeButton);
					}
					return;
				}
				
				// Создаем кастомную кнопку
				var customButton = document.createElement('span');
				customButton.className = config.className + ' ui-btn ui-btn-success ui-btn-xs ui-btn-no-caps ui-btn-round';
				customButton.textContent = 'Создать задачу';
				customButton.setAttribute('data-flow-id', flowId);
				customButton.style.cursor = 'pointer';
				customButton.style.marginLeft = '5px';
				customButton.style.display = 'inline-block';
				
				// Обработчик клика
				customButton.addEventListener('click', function(e) {
					e.preventDefault();
					e.stopPropagation();
					
					if (smartProcessFlows[flowId]) {
						// Для форм смарт-процесса
						BX.SidePanel.Instance.open(config.url, {
							width: 1400,
							cacheable: false
						});
					} else {
						// Для страниц в корне
						BX.SidePanel.Instance.open(config.url);
					}
				});
				
				// Заменяем нативную кнопку кастомной
				nativeButton.parentNode.replaceChild(customButton, nativeButton);
			}
			
			// Функция для обработки всех нативных кнопок (включая disabled)
			function processAllNativeButtons() {
				// Ищем ВСЕ кнопки, включая disabled
				var nativeButtons = document.querySelectorAll('[id^="tasks-flow-list-create-task-"]');
				
				nativeButtons.forEach(function(button) {
					// Обрабатываем независимо от состояния disabled
					replaceNativeButtonWithCustom(button);
				});
				
				// Также ищем ячейки потоков, где может не быть нативной кнопки
				var flowCells = document.querySelectorAll('.tasks-flow__list-cell_line.--start-line');
				flowCells.forEach(function(cell) {
					var flowId = null;
					
					// Пробуем определить ID потока из различных источников
					var nativeButtonInCell = cell.querySelector('[id^="tasks-flow-list-create-task-"]');
					if (nativeButtonInCell) {
						flowId = nativeButtonInCell.id.replace('tasks-flow-list-create-task-', '');
					} else {
						// Пробуем из data-атрибутов или других элементов
						var flowElement = cell.closest('[data-flow-id]');
						if (flowElement && flowElement.getAttribute('data-flow-id')) {
							flowId = flowElement.getAttribute('data-flow-id');
						}
					}
					
					if (flowId && flowConfig[flowId] && !cell.querySelector('.' + flowConfig[flowId].className)) {
						// Создаем кастомную кнопку даже если нет нативной
						var config = flowConfig[flowId];
						var customButton = document.createElement('span');
						customButton.className = config.className + ' ui-btn ui-btn-success ui-btn-xs ui-btn-no-caps ui-btn-round';
						customButton.textContent = 'Создать задачу';
						customButton.setAttribute('data-flow-id', flowId);
						customButton.style.cursor = 'pointer';
						customButton.style.marginLeft = '5px';
						customButton.style.display = 'inline-block';
						
						customButton.addEventListener('click', function(e) {
							e.preventDefault();
							e.stopPropagation();
							
							if (smartProcessFlows[flowId]) {
								BX.SidePanel.Instance.open(config.url, {
									width: 1200,
									cacheable: false
								});
							} else {
								BX.SidePanel.Instance.open(config.url);
							}
						});
						
						cell.appendChild(customButton);
					}
				});
			}
			
			// Агрессивный обработчик изменений DOM
			function setupDOMObserver() {
				if (typeof MutationObserver === 'undefined') return;
				
				var observer = new MutationObserver(function(mutations) {
					var shouldProcess = false;
					
					mutations.forEach(function(mutation) {
						// Любое изменение в DOM
						if (mutation.type === 'childList') {
							// Проверяем добавленные узлы
							for (var i = 0; i < mutation.addedNodes.length; i++) {
								var node = mutation.addedNodes[i];
								if (node.nodeType === 1) {
									// Если добавлен элемент с классом потока или кнопкой
									if (node.classList && (
										node.classList.contains('tasks-flow__list-cell_line') ||
										node.classList.contains('tasks-flow-list__item') ||
										(node.id && node.id.startsWith('tasks-flow-list-create-task-'))
									)) {
										shouldProcess = true;
										break;
									}
									
									// Или если внутри есть такие элементы
									if (node.querySelectorAll) {
										if (node.querySelectorAll('.tasks-flow__list-cell_line, [id^="tasks-flow-list-create-task-"]').length > 0) {
											shouldProcess = true;
											break;
										}
									}
								}
							}
						}
					});
					
					if (shouldProcess) {
						// Множественные вызовы для надежности
						setTimeout(processAllNativeButtons, 0);
						setTimeout(processAllNativeButtons, 100);
						setTimeout(processAllNativeButtons, 500);
					}
				});
				
				// Наблюдаем за всем body
				observer.observe(document.body, {
					childList: true,
					subtree: true,
					attributes: false,
					characterData: false
				});
				
				return observer;
			}
			
			// Инициализация
			function init() {
				// Первичная обработка
				processAllNativeButtons();
				
				// Настройка наблюдателя
				setupDOMObserver();
				
				// Периодическая проверка
				setInterval(processAllNativeButtons, 3000);
				
				// Делегирование через jQuery для совместимости
				if (typeof jQuery !== 'undefined') {
					Object.keys(flowConfig).forEach(function(flowId) {
						var config = flowConfig[flowId];
						$(document).off('click', '.' + config.className).on('click', '.' + config.className, function(e) {
							e.preventDefault();
							e.stopPropagation();
							
							if (smartProcessFlows[flowId]) {
								BX.SidePanel.Instance.open(config.url, {
									width: 1200,
									cacheable: false
								});
							} else {
								BX.SidePanel.Instance.open(config.url);
							}
						});
					});
				}
			}
			
			// Запуск
			$(document).ready(function() {
				init();
				
				// Дополнительная инициализация
				setTimeout(init, 2000);
			});
			
			// AJAX интеграция
			if (typeof BX !== 'undefined') {
				BX.addCustomEvent('onAjaxSuccess', function() {
					setTimeout(processAllNativeButtons, 100);
					setTimeout(processAllNativeButtons, 500);
				});
			}
			
			if (typeof jQuery !== 'undefined') {
				$(document).ajaxComplete(function() {
					setTimeout(processAllNativeButtons, 100);
					setTimeout(processAllNativeButtons, 500);
				});
			}
			
		})();
		</script>
		<?
		$html = ob_get_clean();
		\Bitrix\Main\Page\Asset::getInstance()->addString($html, true);
	}
}
?>