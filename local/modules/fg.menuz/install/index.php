<?php

/**
 * Установщик модуля fg.menuz.
 *
 * @package FG\Menuz
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

/**
 * Модуль «FG. Меню».
 */
class fg_menuz extends CModule
{
    /** @var string Идентификатор модуля. */
    public $MODULE_ID = "fg.menuz";
    /** @var string Версия модуля. */
    public $MODULE_VERSION;
    /** @var string Дата версии модуля. */
    public $MODULE_VERSION_DATE;
    /** @var string Название модуля. */
    public $MODULE_NAME;
    /** @var string Описание модуля. */
    public $MODULE_DESCRIPTION;
    /** @var string Права доступа. */
    public $MODULE_GROUP_RIGHTS = "N";

    public function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . "/version.php";
        $this->MODULE_VERSION      = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME         = Loc::getMessage("FG_MENUZ_MODULE_NAME") ?: "FG. Меню";
        $this->MODULE_DESCRIPTION  = Loc::getMessage("FG_MENUZ_MODULE_DESCRIPTION") ?: "Ручное управление разделами левого меню.";
    }

    /**
     * Устанавливает модуль.
     *
     * @return void
     */
    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallFiles();
        $this->InstallEvents();
    }

    // public function DoUninstall()
    // {
    //     global $APPLICATION;
    //     $this->UnInstallFiles();
    //     $this->UnInstallEvents();
    //     if (($_REQUEST["save_settings"] ?? "N") !== "Y") {
    //         \Bitrix\Main\Config\Option::delete($this->MODULE_ID);
    //     }
    //     ModuleManager::unRegisterModule($this->MODULE_ID);
    //     $APPLICATION->IncludeAdminFile(
    //         "Удаление модуля",
    //         __DIR__ . "/unstep.php"
    //     );
    // }

    /**
     * Удаляет модуль в два шага: подтверждение и снятие регистрации.
     *
     * Настройки удаляются, если в форме не отмечено «сохранить данные».
     *
     * @return void
     */
    public function DoUninstall()
    {
        global $APPLICATION, $step;
        $step = (int)$step;

        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("FG_MENUZ_UNINSTALL_TITLE") ?: "Удаление модуля FG. Меню",
                $_SERVER["DOCUMENT_ROOT"] . "/local/modules/" . $this->MODULE_ID . "/install/unstep1.php"
            );
            return;
        }

        // шаг 2 — только если нажата кнопка "nextstep"
        if ($step == 2 && array_key_exists("nextstep", $_REQUEST)) {
            $this->UnInstallFiles();
            $this->UnInstallEvents();

            // "savedata" = "Y" → сохраняем, "N" → удаляем
            if (($_REQUEST["savedata"] ?? "N") !== "Y") {
                \Bitrix\Main\Config\Option::delete($this->MODULE_ID);
            }

            ModuleManager::unRegisterModule($this->MODULE_ID);

            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("FG_MENUZ_UNINSTALL_DONE_TITLE") ?: "Модуль удалён",
                $_SERVER["DOCUMENT_ROOT"] . "/local/modules/" . $this->MODULE_ID . "/install/unstep.php"
            );
        }
    }

    /**
     * Подключает обработчики OnProlog и OnBuildGlobalMenu.
     *
     * @return void
     */
    public function InstallEvents()
    {
        RegisterModuleDependences("main", "OnProlog", $this->MODULE_ID, "\\FG\\Menuz\\Renderer", "onProlog");
        RegisterModuleDependences("main", "OnBuildGlobalMenu", $this->MODULE_ID, "\\FG\\Menuz\\AdminMenu", "onBuildGlobalMenu");
    }

    /**
     * Отключает обработчики OnProlog и OnBuildGlobalMenu.
     *
     * @return void
     */
    public function UnInstallEvents()
    {
        UnRegisterModuleDependences("main", "OnProlog", $this->MODULE_ID, "\\FG\\Menuz\\Renderer", "onProlog");
        UnRegisterModuleDependences("main", "OnBuildGlobalMenu", $this->MODULE_ID, "\\FG\\Menuz\\AdminMenu", "onBuildGlobalMenu");
    }

    /**
     * Копирует админ-скрипты в /bitrix/admin/.
     *
     * @return void
     */
    public function InstallFiles()
    {
        CopyDirFiles(
            __DIR__ . "/admin",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin",
            true, true
        );
    }

    /**
     * Удаляет скрипты из /bitrix/admin/.
     *
     * @return void
     */
    public function UnInstallFiles()
    {
        @unlink($_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/fg_menuz.php");
    }
}