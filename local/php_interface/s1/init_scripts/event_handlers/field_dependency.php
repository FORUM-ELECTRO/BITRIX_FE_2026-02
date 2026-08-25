<?

\Bitrix\Main\Loader::includeModule('iblock');

AddEventHandler('main', 'OnEpilog', 'showDependentFieldsHandler');

function showDependentFieldsHandler()
{
	try {
		$requestUri = $_SERVER['REQUEST_URI'];

		// Проверяем создание/редактирование элемента инфоблока через старую форму
		if (str_contains($requestUri, 'element/0')) {
			preg_match('/([^\/]+)\/element\/0/', $requestUri, $matches);
			$objectType = 'IBLOCK';
			$objectId = $matches[1];
		}

		// Проверяем создание элемента инфоблока через новую форму
		if (str_contains($requestUri, 'lists.element.creation_guide')) {
			preg_match('/iBlockId=([^&]+)/', $requestUri, $matches);
			$objectType = 'IBLOCK';
			$objectId = $matches[1];
		}

		// Проверяем создание элемента инфоблока через Мои процессы
		if (str_contains($requestUri, 'bizproc/processes') && str_contains($requestUri, 'element_id=')) {
			preg_match('/list_id=([^&]+)/', $requestUri, $matches);
			$objectType = 'IBLOCK';
			$objectId = $matches[1];
		}

		// Проверяем создание/редактирование элемента смарт-процесса
		if (str_contains($requestUri, '/type/') && str_contains($requestUri, '/details/')) {
			preg_match('/type\/([^&]+)\/details/', $requestUri, $matches);
			$objectType = 'CRM';
			$objectId = $matches[1];
		}

		if (!isset($objectType) || !isset($objectId)) {
			return;
		}

		$object = $objectType . '_' . $objectId;

		$parentFieldCodes = [];
		$childFields = [];
		$parentChildFieldMap = [];
		$parentChildFieldValueMap = [];

		$arOrder = [];
		$arFilter = [
			'IBLOCK_ID' => FIELD_DEPENDENCY_IBLOCK_ID,
			'PROPERTY_OBJECT' => $object,
		];
		$arSelect = ['PROPERTY_PARENT_FIELD', 'PROPERTY_PARENT_FIELD_VALUE', 'PROPERTY_CHILD_FIELDS', 'PROPERTY_CHILD_FIELDS_REQUIRED'];
		$dependencies = CIBlockElement::getList($arOrder, $arFilter, $arSelect);
		while ($dependency = $dependencies->getNext()) {
			$parentFieldCode = trim($dependency['PROPERTY_PARENT_FIELD_VALUE']);
			$parentFieldValue = $dependency['PROPERTY_PARENT_FIELD_VALUE_VALUE'];

			$childFieldCode = trim($dependency['PROPERTY_CHILD_FIELDS_VALUE']);
			$childFieldRequired = $dependency['PROPERTY_CHILD_FIELDS_REQUIRED_VALUE'] == 'Y';
			$childField = ['code' => $childFieldCode, 'required' => $childFieldRequired];

			if (!in_array($parentFieldCode, $parentFieldCodes)) {
				$parentFieldCodes[] = $parentFieldCode;
			}
			if (!in_array($childField, $childFields)) {
				$childFields[] = $childField;
			}

			$parentChildFieldMap[$parentFieldCode][] = $childField;
			$parentChildFieldValueMap[$parentFieldCode][$parentFieldValue][] = $childField;
		}

		if (empty($parentFieldCodes)) {
			return;
		}

		if ($objectType == 'IBLOCK') {
			ob_start();
			?>
			<script>
				function hideEmptyReadOnlyFields() {
					document.querySelectorAll('tr').forEach(function(row) {
						const fieldValueCell = row.querySelector('td.bx-field-value');
						if (fieldValueCell && fieldValueCell.textContent.trim() === 'Нет данных') {
							row.style.display = 'none';
						}
					});
				}
		
				function getFieldRow(fieldCode) {
					let inputElement = document.querySelector('[name*=PROPERTY_' + fieldCode + ']');
					if (inputElement === null) {
						inputElement = document.querySelector('#tag-selector-PROPERTY_' + fieldCode);
					}
					if (inputElement !== null) {
						return inputElement.closest('table.bx-edit-table > tbody > tr');
					}
					return null;
				}
		
				function hideFields(fields) {
					if (fields === undefined) {
						return; 
					}
					fields.forEach(function(field) {
						const fieldRow = getFieldRow(field.code);
						if (fieldRow !== null) {
							fieldRow.style.display = 'none';
						}
					});
				}
		
				function showFields(fields) {
					if (fields === undefined) {
						return;
					}
					fields.forEach(function(field) {
						const fieldRow = getFieldRow(field.code);
						if (fieldRow !== null) {
							fieldRow.style.display = '';
						}
					});
				}
		
				function addRequiredMarks(fields) {
					if (fields === undefined) {
						return;
					}
					fields.forEach(function(field) {
						if (field.required) {
							const fieldRow = getFieldRow(field.code);
							if (fieldRow !== null) {
								const fieldNameElement = fieldRow.querySelector('.bx-field-name');
								const requiredMarkElement = document.createElement('span');
								requiredMarkElement.className = 'required';
								requiredMarkElement.textContent = '*';
								fieldNameElement.prepend(requiredMarkElement);
							}
						}
					});
				}
		
				document.addEventListener('DOMContentLoaded', function() {
					const parentFieldCodes = <?= json_encode($parentFieldCodes); ?>;
					const childFields = <?= json_encode($childFields); ?>;
					const parentChildFieldMap = <?= json_encode($parentChildFieldMap); ?>;
					const parentChildFieldValueMap = <?= json_encode($parentChildFieldValueMap); ?>;
		
					hideEmptyReadOnlyFields();
					hideFields(childFields);
					addRequiredMarks(childFields);
					parentFieldCodes.forEach(function(fieldCode) {
						const fieldElement = document.querySelector('select[name*=PROPERTY_' + fieldCode + ']');
						showFields(parentChildFieldValueMap[fieldCode][fieldElement.value]);
						fieldElement.addEventListener('change', function() {
							hideFields(parentChildFieldMap[fieldCode]);
							showFields(parentChildFieldValueMap[fieldCode][this.value]);
						});
					});
				});
			</script>
			<?
			$html = ob_get_clean();
		}

		if ($objectType == 'CRM') {
			ob_start();
			?>
			<script>
				function getFieldRow(fieldCode) {
					let rowElement = document.querySelector('[data-cid=' + fieldCode + ']');
					return rowElement;
				}
		
				function hideFields(fields) {
					if (fields === undefined) {
						return; 
					}
					fields.forEach(function(field) {
						const fieldRow = getFieldRow(field.code);
						if (fieldRow !== null) {
							fieldRow.style.display = 'none';
						}
					});
				}
		
				function showFields(fields) {
					if (fields === undefined) {
						return;
					}
					fields.forEach(function(field) {
						const fieldRow = getFieldRow(field.code);
						if (fieldRow !== null) {
							fieldRow.style.display = '';
						}
					});
				}
		
				function addRequiredMarks(fields) {
					if (fields === undefined) {
						return;
					}
					fields.forEach(function(field) {
						if (field.required) {
							const fieldRow = getFieldRow(field.code);
							if (fieldRow !== null) {
								const fieldNameElement = fieldRow.querySelector('.ui-entity-editor-block-title-text');
								if (fieldNameElement !== null) {
									const requiredMarkElement = document.createElement('span');
									requiredMarkElement.style.color = 'rgb(255, 0, 0)';
									requiredMarkElement.textContent = '*';
									if (!fieldNameElement.innerHTML.includes(requiredMarkElement.outerHTML)) {
										fieldNameElement.innerHTML += requiredMarkElement.outerHTML;
									}
								}
							}
						}
					});
				}

				// Взято со StackOverflow: https://stackoverflow.com/a/61511955
				function waitForFieldElement(selector, callback) {
					const element = document.querySelector(selector);
					if (element) {
						waitForFieldElementOnRemove(element, selector, callback);
						callback(element);
						return;
					}

					const observer = new MutationObserver(mutations => {
						const element = document.querySelector(selector);
						if (element) {
							observer.disconnect();
							waitForFieldElementOnRemove(element, selector, callback);
							callback(element);
						}
					});

					observer.observe(document.body, {
						childList: true,
						subtree: true,
					});
				}

				// Взято со StackOverflow: https://stackoverflow.com/a/50397148
				function waitForFieldElementOnRemove(element, selector, callback) {
					const observer = new MutationObserver(mutations => {
						for (const mutation of mutations) {
							for (const removedElement of mutation.removedNodes) {
							if (removedElement === element || removedElement.contains(element)) {
									observer.disconnect();
									waitForFieldElement(selector, callback);
								}
							}
						}
					});

					observer.observe(document.body, {
						childList: true,
						subtree: true,
					});
				}

				window.onload = function() {
					const parentFieldCodes = <?= json_encode($parentFieldCodes); ?>;
					const childFields = <?= json_encode($childFields); ?>;
					const parentChildFieldMap = <?= json_encode($parentChildFieldMap); ?>;
					const parentChildFieldValueMap = <?= json_encode($parentChildFieldValueMap); ?>;

					parentFieldCodes.forEach(async function(fieldCode) {

						// Поля с представлением "Список" или "Выпадающий список"
						const listInputSelector = 'select[name=' + fieldCode + ']';
						waitForFieldElement(listInputSelector, (element) => {
							element.addEventListener('change', function() {
								const childFields = parentChildFieldMap[fieldCode];
								const childFieldsForValue = parentChildFieldValueMap[fieldCode][this.value];
								hideFields(childFields);
								showFields(childFieldsForValue);
								addRequiredMarks(childFieldsForValue);
							});
							element.dispatchEvent(new Event('change'));
						});

						// Поля с представлением "Флажок"
						const flagInputSelector = 'input[name=' + fieldCode + '][type=checkbox]';
						waitForFieldElement(flagInputSelector, (element) => {
							element.addEventListener('change', function() {
								const fieldValue = this.checked ? '1' : '0';
								const childFields = parentChildFieldMap[fieldCode];
								const childFieldsForValue = parentChildFieldValueMap[fieldCode][fieldValue];
								hideFields(childFields);
								showFields(childFieldsForValue);
								addRequiredMarks(childFieldsForValue);
							});
							element.dispatchEvent(new Event('change'));
						});

					});
				};
			</script>
			<?
			$html = ob_get_clean();
		}

		if (isset($html)) {
			\Bitrix\Main\Page\Asset::getInstance()->addString($html, true);
		}
	} catch (\Throwable $e) {
		$debugMessage = 'Exception: ' . print_r($e->getMessage(), true);
		\Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);
	}
}

AddEventHandler('iblock', 'OnBeforeIBlockElementAdd', 'saveDependentFieldsIBlockHandler');

function saveDependentFieldsIBlockHandler(&$arFields)
{
	try {
		$propValues = &$arFields['PROPERTY_VALUES'];

		$objectType = 'IBLOCK';
		$objectId = $arFields['IBLOCK_ID'];
		$object = $objectType . '_' . $objectId;

		$arOrder = [];
		$arFilter = [
			'IBLOCK_ID' => FIELD_DEPENDENCY_IBLOCK_ID,
			'PROPERTY_OBJECT' => $object,
		];
		$arSelect = ['PROPERTY_PARENT_FIELD', 'PROPERTY_PARENT_FIELD_VALUE', 'PROPERTY_CHILD_FIELDS', 'PROPERTY_CHILD_FIELDS_REQUIRED'];

		// Получаем список всех зависимостей
		$dependencies = CIBlockElement::getList($arOrder, $arFilter, $arSelect);
		$dependenciesList = [];
		while ($dependency = $dependencies->getNext()) {
			$dependenciesList[] = $dependency;
		}

		// Вычисляем список дочерних полей, которые не входят в выбранную цепочку зависимостей
		$childFieldCodesToClear = getChildFieldCodesToClear($dependenciesList, $propValues);

		// Очищаем неактивные дочерние поля
		clearFields($propValues, $childFieldCodesToClear);

		foreach ($dependenciesList as $dependency) {
			$parentFieldCode = trim($dependency['PROPERTY_PARENT_FIELD_VALUE']);
			$parentFieldValue = $dependency['PROPERTY_PARENT_FIELD_VALUE_VALUE'];

			if ($propValues[$parentFieldCode] != $parentFieldValue) {
				continue;
			}
			$childFieldRequired = $dependency['PROPERTY_CHILD_FIELDS_REQUIRED_VALUE'] == 'Y';
			if (!$childFieldRequired) {
				continue;
			}

			$childFieldCode = trim($dependency['PROPERTY_CHILD_FIELDS_VALUE']);
			$childFieldValue = $propValues[$childFieldCode];
			$childFieldEmpty = true;

			if (is_array($childFieldValue)) {
				foreach ($childFieldValue as $key => $valueObject) {
					if (is_array($valueObject)) {
						if (array_key_exists('VALUE', $valueObject)) {
							$value = $valueObject['VALUE'];
							if (is_array($value)) {
								if (array_key_exists('size', $value) && $value['size'] > 0) {
									$childFieldEmpty = false;
								}
								if (array_key_exists('TYPE', $value) && $value['TYPE'] == 'html') {
									if ($value['TEXT'] != null) {
										$childFieldEmpty = false;
									}
								}
							} else {
								if ($value != null) {
									$childFieldEmpty = false;
								}
							}
						}
					} else {
						if ($valueObject != null) {
							$childFieldEmpty = false;
						}
					}
				}
			} else {
				if ($childFieldValue != null) {
					$childFieldEmpty = false;
				}
			}

			if ($childFieldEmpty) {
				global $APPLICATION;
				$APPLICATION->throwException('Обязательное свойство не заполнено (код свойства: ' . $childFieldCode . ')');
				return false;
			}
		}

		return true;

	} catch (\Throwable $e) {
		$debugMessage = 'Exception: ' . print_r($e->getMessage(), true);
		\Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);
		return true;
	}
}

function getChildFieldCodesToClear(array $dependenciesList, $fields)
{
    // Коды всех дочерних полей
    $childFieldCodes = [];
    // Коды активных дочерних полей (те, что входят в выбранную цепочку)
    $activeChildFieldCodes = [];

    // Перебираем все зависимости
    foreach ($dependenciesList as $dependency) {
        // Извлекаем код родительского поля и его ожидаемое значение
        $parentFieldCode = trim($dependency['PROPERTY_PARENT_FIELD_VALUE']);
        $parentFieldExpectedValue = $dependency['PROPERTY_PARENT_FIELD_VALUE_VALUE'];
        // Извлекаем код дочернего поля
        $childFieldCode = trim($dependency['PROPERTY_CHILD_FIELDS_VALUE']);

        // Добавляем дочернее поле в общий список, если его там ещё нет
        if (!in_array($childFieldCode, $childFieldCodes)) {
            $childFieldCodes[] = $childFieldCode;
        }

        // Если значение родительского поля соответствует ожидаемому,
        // то дочернее поле считается активным
        if (isset($fields[$parentFieldCode]) && $fields[$parentFieldCode] == $parentFieldExpectedValue) {
            if (!in_array($childFieldCode, $activeChildFieldCodes)) {
                $activeChildFieldCodes[] = $childFieldCode;
            }
        }
    }

    // Возвращаем разницу: все дочерние поля минус активные
    return array_diff($childFieldCodes, $activeChildFieldCodes);
}

function clearFields(&$fields, array $fieldCodesToClear)
{
    foreach ($fieldCodesToClear as $fieldCode) {
        if (isset($fields[$fieldCode])) {
            $fieldValue = $fields[$fieldCode];
            if (is_array($fieldValue)) {
                // Если это файловое поле
                if (isset($fieldValue['name']) && isset($fieldValue['size'])) {
                    $fields[$fieldCode] = ["del" => "Y"];
                }
                // Если это HTML-редактор (проверяем наличие VALUE и TYPE, и TYPE равен "html")
                elseif (isset($fieldValue['VALUE'], $fieldValue['TYPE']) && $fieldValue['TYPE'] === "html") {
                    $fields[$fieldCode] = [
                        "VALUE" => ["TEXT" => "", "TYPE" => "html"]
                    ];
                }
                // Для множественных или иных полей – очищаем массив
                else {
                    $fields[$fieldCode] = [];
                }
            } else {
                // Простые свойства (число, строка) очищаем как null
                $fields[$fieldCode] = null;
            }
        }
    }
}

?>