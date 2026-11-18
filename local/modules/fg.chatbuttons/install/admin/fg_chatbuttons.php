<?php

/**
 * Страница настроек модуля fg.chatbuttons.
 *
 * @package Fg\ChatButtons
 */

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Fg\ChatButtons\Settings;

global $APPLICATION, $USER;

// Подключаем модуль и проверяем права.
if (!Loader::includeModule("fg.chatbuttons") || !$USER->IsAdmin()) {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

// Фразы берём из каталога модуля
$fgSelf = Loader::getLocal("modules/fg.chatbuttons/install/admin/fg_chatbuttons.php");
Loc::loadMessages($fgSelf ?: __FILE__);

$request = Application::getInstance()->getContext()->getRequest();

if ($request->isPost() && $request->getPost("save") !== null && check_bitrix_sessid()) {
    Settings::setEnabled($request->getPost("ACTIVE") === "Y");

    foreach (array_keys(Settings::getTypes()) as $type) {
        Settings::setTypeActive($type, $request->getPost("TYPE_" . $type) === "Y");
        Settings::setTypeIcon($type, (string)$request->getPost("ICON_" . $type));
    }

    LocalRedirect($APPLICATION->GetCurPageParam("saved=Y", ["saved"]));
}

// Нужен для превью иконок прямо в таблице настроек.
Extension::load("ui.icon-set.outline");

$APPLICATION->SetTitle(Loc::getMessage("FG_CB_TITLE"));

$tabControl = new CAdminTabControl("fg_chatbuttons_tabs", [
    [
        "DIV"   => "main",
        "TAB"   => Loc::getMessage("FG_CB_TAB"),
        "TITLE" => Loc::getMessage("FG_CB_TITLE"),
    ],
]);

require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php";

if ($request->get("saved") === "Y") {
    CAdminMessage::ShowNote(Loc::getMessage("FG_CB_SAVED"));
}
?>
<form method="post" action="<?= htmlspecialcharsbx($APPLICATION->GetCurPageParam("", ["saved"])) ?>">
    <?= bitrix_sessid_post() ?>
    <?php
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    ?>
    <tr>
        <td width="40%">
            <label for="fg_cb_active"><?= Loc::getMessage("FG_CB_ENABLED") ?></label>
        </td>
        <td width="60%">
            <input type="hidden" name="ACTIVE" value="N">
            <input type="checkbox" id="fg_cb_active" name="ACTIVE" value="Y"
                <?= Settings::isEnabled() ? "checked" : "" ?>>
            <div class="adm-desc"><?= Loc::getMessage("FG_CB_ENABLED_HINT") ?></div>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?= Loc::getMessage("FG_CB_TYPES_HEAD") ?></td>
    </tr>
    <?php
    foreach (Settings::getTypes() as $type => $definition) {
        $label = Settings::getTypeLabel($type);
        $icon = Settings::getTypeIcon($type);
        ?>
        <tr>
            <td width="40%">
                <label for="fg_cb_type_<?= htmlspecialcharsbx($type) ?>"><?= htmlspecialcharsbx($label) ?></label>
            </td>
            <td width="60%">
                <input type="hidden" name="TYPE_<?= htmlspecialcharsbx($type) ?>" value="N">
                <input type="checkbox" id="fg_cb_type_<?= htmlspecialcharsbx($type) ?>"
                    name="TYPE_<?= htmlspecialcharsbx($type) ?>" value="Y"
                    <?= Settings::isTypeActive($type) ? "checked" : "" ?>>
                &nbsp;
                <select name="ICON_<?= htmlspecialcharsbx($type) ?>">
                    <?php
                    foreach (Settings::getIcons() as $option) {
                        printf(
                            '<option value="%s"%s>%s</option>',
                            htmlspecialcharsbx($option),
                            $option === $icon ? " selected" : "",
                            htmlspecialcharsbx(ltrim($option, "-"))
                        );
                    }
                    ?>
                </select>
                &nbsp;<span class="ui-icon-set <?= htmlspecialcharsbx($icon) ?>"></span>
            </td>
        </tr>
        <?php
    }
    ?>
    <tr>
        <td colspan="2">
            <div class="adm-desc"><?= Loc::getMessage("FG_CB_TYPES_NOTE") ?></div>
        </td>
    </tr>
    <?php
    $tabControl->Buttons();
    ?>
    <input type="submit" name="save" value="<?= Loc::getMessage("FG_CB_SAVE") ?>" class="adm-btn-save">
    <?php
    $tabControl->End();
    ?>
</form>
<?php
require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php";
