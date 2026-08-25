<?

namespace Custom\Crm\Service;

\Bitrix\Main\Loader::includeModule('crm');
\Bitrix\Main\Loader::includeModule('iblock');

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Crm\Item;
use Bitrix\Crm\Service;
use Bitrix\Crm\Service\Operation;
use Bitrix\Crm\Service\Context;

define('CONTRACT_ENTITY_TYPE_ID', 1066);
define('CORRESPONDENCE_ENTITY_TYPE_ID', 1126);

// Действие для очистки дочерних полей другой зависимости
class ClearWrongDependencyFields extends Operation\Action
{
	private function getChildFieldCodesToClear($dependenciesList, $item)
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
			if ($parentFieldCode && $item->get($parentFieldCode) == $parentFieldExpectedValue) {
				if (!in_array($childFieldCode, $activeChildFieldCodes)) {
					$activeChildFieldCodes[] = $childFieldCode;
				}
			}
		}

		// Возвращаем разницу: все дочерние поля минус активные
		return array_diff($childFieldCodes, $activeChildFieldCodes);
	}

	private function clearFields(&$item, $fieldCodesToClear)
	{
		foreach ($fieldCodesToClear as $fieldCode) {
			$item->set($fieldCode, null);
		}
	}

	public function process(Item $item): Result
	{
		$result = new Result();
		try {
			$objectType = 'CRM';
			$objectId = $item->getEntityTypeId();
			$object = $objectType . '_' . $objectId;

			//$debugMessage = 'childFieldCodesToClear: ' . print_r($object, true);
			//\Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);
			// Получаем id дочерних полей для зависимостей, кроме текущей
			$arOrder = [];
			$arFilter = [
				'IBLOCK_ID' => FIELD_DEPENDENCY_IBLOCK_ID,
				'PROPERTY_OBJECT' => $object
			];
			$arSelect = ['PROPERTY_PARENT_FIELD', 'PROPERTY_PARENT_FIELD_VALUE', 'PROPERTY_CHILD_FIELDS'];
			$dependencies = \CIBlockElement::getList($arOrder, $arFilter, $arSelect);
			$dependenciesList = [];
			while ($dependency = $dependencies->getNext()) {
				$dependenciesList[] = $dependency;
			}

			// Вычисляем список дочерних полей, которые не входят в выбранную цепочку зависимостей
			$childFieldCodesToClear = $this->getChildFieldCodesToClear($dependenciesList, $item);

			// $debugMessage = 'childFieldCodesToClear: ' . print_r($childFieldCodesToClear, true);
			// \Bitrix\Main\Diag\Debug::writeToFile($debugMessage, __FUNCTION__, DEBUG_FILE_NAME);

			// Очищаем неактивные дочерние поля
			$this->clearFields($item, $childFieldCodesToClear);

		} catch (\Throwable $e) {
			$errorMessage = 'Ошибка: ' . print_r($e->getMessage(), true);
			$trace = print_r($e->getTraceAsString(), true);
			\Bitrix\Main\Diag\Debug::writeToFile($errorMessage, __FUNCTION__, DEBUG_FILE_NAME);
			\Bitrix\Main\Diag\Debug::writeToFile($trace, __FUNCTION__, DEBUG_FILE_NAME);
			$result->addError(new Error($errorMessage));
		}

		return $result;

	}
}

// Действие для проверки обязательного заполнения зависимых дочерних полей
class CheckRequiredDependencyChildFields extends Operation\Action
{
	public function process(Item $item): Result
	{
		$result = new Result();
		try {
			$fieldsCollection = $item->getFactory()->getFieldsCollection();
			$childFieldCodesRequiredEmpty = [];

			$objectType = 'CRM';
			$objectId = $item->getEntityTypeId();
			$object = $objectType . '_' . $objectId;

			$arOrder = [];
			$arFilter = [
				'IBLOCK_ID' => FIELD_DEPENDENCY_IBLOCK_ID,
				'PROPERTY_OBJECT' => $object,
			];
			$arSelect = ['PROPERTY_PARENT_FIELD', 'PROPERTY_PARENT_FIELD_VALUE', 'PROPERTY_CHILD_FIELDS', 'PROPERTY_CHILD_FIELDS_REQUIRED'];
			$dependencies = \CIBlockElement::getList($arOrder, $arFilter, $arSelect);
	
			while ($dependency = $dependencies->getNext()) {
				$parentFieldCode = trim($dependency['PROPERTY_PARENT_FIELD_VALUE']);
				$parentFieldValue = $dependency['PROPERTY_PARENT_FIELD_VALUE_VALUE'];
				$parentFieldValueItem = (string)$item->get($parentFieldCode);

				if ($parentFieldValueItem != $parentFieldValue) {
					continue;
				}

				$childFieldRequired = $dependency['PROPERTY_CHILD_FIELDS_REQUIRED_VALUE'] == 'Y';
				if (!$childFieldRequired) {
					continue;
				}

				$childFieldEmpty = false;
				$childFieldCode = trim($dependency['PROPERTY_CHILD_FIELDS_VALUE']);
				$childFieldType = $fieldsCollection->getField($childFieldCode)->getType();
				$childFieldValueItem = $item->get($childFieldCode);

				if (
					in_array($childFieldValueItem, [null, '', [], [null]], true) ||
					($childFieldValueItem === 0 && in_array($childFieldType, ['iblock_element', 'file']))
				) {
					$childFieldEmpty = true;
				}

				if ($childFieldEmpty) {
					$childFieldCodesRequiredEmpty[] = $childFieldCode;
				}
			}

			if (count($childFieldCodesRequiredEmpty) != 0) {
				$childFieldTitlesRequiredEmpty = [];
				foreach ($childFieldCodesRequiredEmpty as $fieldCode) {
					$fieldTitle = $fieldsCollection->getField($fieldCode)->getTitle();
					$childFieldTitlesRequiredEmpty[] = $fieldTitle;
				}
				$errorMessage = 'Обязательные поля не заполнены: ' . implode(', ', $childFieldTitlesRequiredEmpty);
				$result->addError(new Error($errorMessage));
			}

		} catch (\Throwable $e) {
			$errorMessage = 'Ошибка: ' . print_r($e->getMessage(), true);
			$trace = print_r($e->getTraceAsString(), true);
			\Bitrix\Main\Diag\Debug::writeToFile($errorMessage, __FUNCTION__, DEBUG_FILE_NAME);
			\Bitrix\Main\Diag\Debug::writeToFile($trace, __FUNCTION__, DEBUG_FILE_NAME);
			$result->addError(new Error($errorMessage));
		}

		return $result;
	}
}

// Фабрика для СП Договор
class ContractFactory extends Service\Factory\Dynamic {

	public function getAddOperation(Item $item, Context $context = null): Operation\Add
	{
		$operation = parent::getAddOperation($item, $context);

		$operation->addAction(
			Operation::ACTION_BEFORE_SAVE,
			new CheckRequiredDependencyChildFields()
		);

		$operation->addAction(
			Operation::ACTION_BEFORE_SAVE,
			new ClearWrongDependencyFields()
		);

		return $operation;
	}

	public function getUpdateOperation(Item $item, Context $context = null): Operation\Update
	{
		$operation = parent::getUpdateOperation($item, $context);
        
		$operation->addAction(
			Operation::ACTION_BEFORE_SAVE,
			new CheckRequiredDependencyChildFields()
		);

		//$operation->addAction(
		//	Operation::ACTION_BEFORE_SAVE,
		//	new ClearWrongDependencyFields()
		//);

		return $operation;
	}

};

// Подмена сервиса Crm\Container, в нём мы прописываем собственные фабрики для Смарт-процессов
class Container extends Service\Container
{
	public function getFactory(int $entityTypeId): ?Service\Factory
	{
		if (($entityTypeId == CONTRACT_ENTITY_TYPE_ID) || ($entityTypeId == CORRESPONDENCE_ENTITY_TYPE_ID))
		{
			$type = $this->getTypeByEntityTypeId($entityTypeId);
			$factory = new ContractFactory($type);
			return $factory;
		}

		return parent::getFactory($entityTypeId);
	}
};

\Bitrix\Main\DI\ServiceLocator::getInstance()->addInstance('crm.service.container', new Container);

?>