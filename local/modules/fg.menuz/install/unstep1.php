<?if(!check_bitrix_sessid()) return;?>
<form action="<?=$APPLICATION->GetCurPage(); ?>">
    <?=bitrix_sessid_post(); ?>
    <input type="hidden" name="step" value="2">
    <input type="hidden" name="id" value="fg.menuz">
    <input type="hidden" name="uninstall" value="Y">
    <p><b><?=GetMessage("FG_MENUZ_UNINSTALL_SAVE_TITLE")?></b><br>
        <input type="radio" name="savedata" value="Y" checked> <?=GetMessage("FG_MENUZ_UNINSTALL_SAVE_YES")?><br>
        <input type="radio" name="savedata" value="N"> <?=GetMessage("FG_MENUZ_UNINSTALL_SAVE_NO")?><br>
    </p>
    <input type="submit" name="nextstep" value="<?=GetMessage("FG_MENUZ_UNINSTALL_NEXT")?>">
    <input type="submit" name="cancel" value="<?=GetMessage("FG_MENUZ_UNINSTALL_CANCEL")?>">
</form>