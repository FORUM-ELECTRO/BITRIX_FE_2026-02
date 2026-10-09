<?php
if (!check_bitrix_sessid()) return;
global $APPLICATION;
echo CAdminMessage::ShowNote(GetMessage("FG_MENUZ_INSTALL_DONE"));
?>
<form action="<?= $APPLICATION->GetCurPage() ?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="submit" value="<?= GetMessage("FG_MENUZ_INSTALL_BACK") ?>">
</form>
<p><a href="/local/admin/fg_menuz.php"><?= GetMessage("FG_MENUZ_INSTALL_SETTINGS_LINK") ?></a></p>