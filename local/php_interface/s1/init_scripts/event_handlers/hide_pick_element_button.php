<?

\Bitrix\Main\Loader::includeModule('iblock');

AddEventHandler("main", "OnEpilog", "hideRelatedButtonForSmartProcess");

function hideRelatedButtonForSmartProcess()
{
	// Получаем список исключаемых id сущностей из универсального списка
	$visibleOnEntities = [];
	$exclusionTypeIdField = 'PROPERTY_3251';

	$arOrder    = [];
	$arFilter   = [
		'IBLOCK_ID' => HIDE_PICK_ELEMENT_BUTTON_EXCLUSIONS_IBLOCK_ID,
	];
	$arSelect  = [$exclusionTypeIdField];

	$exclusions = CIBlockElement::getList($arOrder, $arFilter, false, false, $arSelect);
	while ($exclusion = $exclusions->getNext()) {
		$exclusionTypeId = (int)$exclusion[$exclusionTypeIdField . '_VALUE'];
		$visibleOnEntities[] = $exclusionTypeId;
	}
	$visibleOnEntitiesJs = json_encode($visibleOnEntities);

	\Bitrix\Main\Page\Asset::getInstance()->addString(<<<HTML
		<script>
		BX.ready(function() {
			// console.log('DEBUG: Запущен обработчик скрытия кнопок');

			setTimeout(function() {
				var visibleOnEntities = {$visibleOnEntitiesJs};
				// console.log('DEBUG: visibleOnEntities =', visibleOnEntities);
				// console.log('DEBUG: pathname = ' + window.location.pathname);

				// Определяем параметры сущности из URL
				var entityData = {};
				// Сначала проверяем универсальный вариант для смарт-процессов,
				// где URL содержит "/type/<число>/details/<число>/"
				// (это сработает и для случаев, когда смарт-процесс открыт через цифровое пространство)
				var smartMatch = window.location.pathname.match(/\/type\/(\d+)\/details\/(\d+)\//);
				if (smartMatch) {
					entityData.entityTypePrefix = smartMatch[1]; // например, "1054"
					entityData.entityId = smartMatch[2];           // например, "1"
				} else {
					// Для остальных CRM-сущностей, например: /crm/deal/details/23/
					var genericMatch = window.location.pathname.match(/^\/crm\/([^\/]+)\/details\/(\d+)\//);
					if (genericMatch) {
						var typeStr = genericMatch[1]; // например, "deal"
						var mapping = {
							'lead': '1',
							'deal': '2',
							'contact': '3',
							'company': '4'
						};
						entityData.entityTypePrefix = mapping[typeStr] ? mapping[typeStr] : typeStr;
						entityData.entityId = genericMatch[2];
					}
				}
				// console.log('DEBUG: entityData =', entityData);
				if (!entityData.entityTypePrefix || !entityData.entityId) {
					console.warn('Не удалось определить параметры сущности из URL', entityData.entityTypePrefix, entityData.entityId);
					return;
				}

				// Ищем все вкладки, используя селектор в одинарных кавычках с двойными внутри
				var tabs = document.querySelectorAll('[data-tab-id^="tab_relation_dynamic_"]');
				// console.log('DEBUG: Найдено вкладок:', tabs.length);

				var styleContent = '';

				tabs.forEach(function(tab) {
					var dataTabId = tab.getAttribute('data-tab-id');
					var parts = dataTabId.split('_');
					var entityTypeIdStr = parts.pop(); // например, "1050"
					var tabEntityTypeId = parseInt(entityTypeIdStr);
					// console.log('DEBUG: Извлечён tabEntityTypeId: ' + tabEntityTypeId);

					if (visibleOnEntities.indexOf(tabEntityTypeId) === -1) {
						// Формируем правило вида:
						// "#relation-button-[entityTypePrefix]-[entityId]-[tabEntityTypeId] { display: none !important; }"
						var cssRule = "#relation-button-" + entityData.entityTypePrefix + "-" +
									entityData.entityId + "-" + entityTypeIdStr +
									" { display: none !important; }";
						styleContent += cssRule + " ";
						// console.log('DEBUG: Добавляем правило:', cssRule);
					} else {
						// console.log('DEBUG: Сущность ' + tabEntityTypeId + ' входит в исключения');
					}
				});

				if (styleContent) {
					var styleTag = document.createElement('style');
					styleTag.textContent = styleContent;
					document.head.appendChild(styleTag);
					// console.log('DEBUG: CSS-инструкции добавлены:' + styleContent);
				}
			}, 0);
		});
		</script>
	HTML, true);
}

?>