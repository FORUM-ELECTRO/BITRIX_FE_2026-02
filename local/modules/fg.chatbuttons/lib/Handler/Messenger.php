<?php

/**
 * Подключение кнопок мессенджера на публичной части.
 *
 * @package Fg\ChatButtons
 */

namespace Fg\ChatButtons\Handler;

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;
use CJSCore;
use CUser;
use Fg\ChatButtons\Settings;

/**
 * Обработчик событий main для публичной части.
 */
final class Messenger
{
    /** @var string Идентификатор JS-расширения модуля. */
    public const EXTENSION = "fg.chatbuttons";

    /** @var string Путь config.php расширения относительно корня портала. */
    private const CONFIG_PATH = "modules/fg.chatbuttons/install/js/fg/chatbuttons/config.php";

    /**
     * Загружает JS-расширение на страницах портала.
     *
     * @return void
     */
    public static function onProlog(): void
    {
        global $USER;

        if (Context::getCurrent()->getRequest()->isAdminSection()) {
            return;
        }

        if (!$USER instanceof CUser || !$USER->IsAuthorized()) {
            return;
        }

        if (!Settings::isEnabled() || Settings::getJsItems() === []) {
            return;
        }

        $config = self::getExtensionConfig();

        if ($config === []) {
            return;
        }

        if (!CJSCore::IsExtRegistered(self::EXTENSION)) {
            Extension::registerAssets(self::EXTENSION, $config);
        }

        Extension::load(self::EXTENSION);
    }

    /**
     * Собирает конфигурацию расширения из config.php модуля.
     *
     * @return array
     */
    private static function getExtensionConfig(): array
    {
        $configFile = Loader::getLocal(self::CONFIG_PATH);

        if ($configFile === false) {
            return [];
        }

        $config = include $configFile;

        if (!is_array($config)) {
            return [];
        }

        $dir = substr($configFile, 0, -strlen("config.php"));
        $root = Application::getDocumentRoot();

        if ($root !== "" && str_starts_with($dir, $root)) {
            $dir = substr($dir, strlen($root));
        }

        foreach (["js", "css"] as $key) {
            if (isset($config[$key])) {
                $config[$key] = array_map(
                    static fn(string $asset): string => $dir . $asset,
                    array_map("strval", (array)$config[$key])
                );
            }
        }

        return $config;
    }
}
