<?php

/**
 * Установщик модуля fg.chatbuttons.
 *
 * @package Fg\ChatButtons
 */

use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

/**
 * Модуль «FG. Кнопки чатов в мессенджере».
 */
class fg_chatbuttons extends CModule
{
    /** @var string Идентификатор модуля. */
    public $MODULE_ID = "fg.chatbuttons";
    /** @var string Версия модуля. */
    public $MODULE_VERSION;
    /** @var string Дата версии модуля. */
    public $MODULE_VERSION_DATE;
    /** @var string Название модуля. */
    public $MODULE_NAME;
    /** @var string Описание модуля. */
    public $MODULE_DESCRIPTION;

    /**
     * Список обработчиков: [модуль, событие, класс, метод].
     *
     * @var array<int, array<int, string>>
     */
    private $handlers = [
        ["main", "OnProlog", "\\Fg\\ChatButtons\\Handler\\Messenger", "onProlog"],
        ["main", "OnBuildGlobalMenu", "\\Fg\\ChatButtons\\Handler\\AdminMenu", "onBuildGlobalMenu"],
    ];

    /**
     * Подтягивает версию из install/version.php и заполняет свойства.
     *
     * @return void
     */
    public function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . "/version.php";

        $this->MODULE_VERSION      = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME         = Loc::getMessage("FG_CB_MODULE_NAME") ?: "FG. Кнопки чатов в мессенджере";
        $this->MODULE_DESCRIPTION  = Loc::getMessage("FG_CB_MODULE_DESCRIPTION") ?: "Кнопки чатов задач и проектов в навигации мессенджера.";
    }

    /**
     * Устанавливает модуль.
     *
     * @return void
     */
    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);

        $this->InstallEvents();
        $this->InstallFiles();
    }

    /**
     * Снимает модуль с портала вместе с настройками.
     *
     * @return void
     */
    public function DoUninstall()
    {
        $this->UnInstallEvents();
        $this->UnInstallFiles();

        Option::delete($this->MODULE_ID);

        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    /**
     * Регистрирует обработчики OnProlog и OnBuildGlobalMenu.
     *
     * @return void
     */
    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();

        foreach ($this->handlers as [$module, $event, $class, $method]) {
            $eventManager->registerEventHandler($module, $event, $this->MODULE_ID, $class, $method);
        }
    }

    /**
     * Снимает обработчики OnProlog и OnBuildGlobalMenu.
     *
     * @return void
     */
    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();

        foreach ($this->handlers as [$module, $event, $class, $method]) {
            $eventManager->unRegisterEventHandler($module, $event, $this->MODULE_ID, $class, $method);
        }
    }

    /**
     * Копирует админ-скрипты в /bitrix/admin/.
     *
     * JS-ассеты регистрируется из каталога модуля,
     *
     * @return void
     */
    public function InstallFiles()
    {
        CopyDirFiles(
            __DIR__ . "/admin",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin",
            true,
            true
        );
    }

    /**
     * Удаляет админ-скрипты.
     *
     * @return void
     */
    public function UnInstallFiles()
    {
        DeleteDirFiles(
            __DIR__ . "/admin",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"
        );
    }
}
