<?php
use Bitrix\Main\ModuleManager;

if(!check_bitrix_sessid()) return;
IncludeModuleLangFile(__FILE__);

global $APPLICATION;

// Шаг 2 - выполняем удаление
if($_REQUEST['step'] == 2):
    
    // Создаем экземпляр модуля и вызываем удаление
    $module = new b2b_integration();
    $module->UnInstallDB();
    $module->UnInstallFiles();
    ModuleManager::unRegisterModule($module->MODULE_ID);
    
    // Выводим сообщение об успешном удалении
    if($ex = $APPLICATION->GetException())
        echo CAdminMessage::ShowMessage(array(
            "TYPE" => "ERROR",
            "MESSAGE" => $ex->GetString(),
            "HTML" => true
        ));
    else
        echo CAdminMessage::ShowNote('Модуль успешно удален');
    ?>
    <form action="<?=$APPLICATION->GetCurPage()?>">
        <input type="submit" value="Вернуться к списку модулей">
    </form>
    <?

// Шаг 1 - показываем форму с чек-боксом
else:
    ?>
    <form action="<?=$APPLICATION->GetCurPage()?>" method="post">
        <?=bitrix_sessid_post()?>
        <input type="hidden" name="lang" value="<?=LANG?>">
        <input type="hidden" name="id" value="b2b.integration">
        <input type="hidden" name="uninstall" value="Y">
        <input type="hidden" name="step" value="2">
        
        <p><strong>Удаление модуля "Интеграция с B2B"</strong></p>
        <p>Что делать с таблицами модуля?</p>
        <p>
            <input type="checkbox" name="savedata" id="savedata" value="Y" checked>
            <label for="savedata">Сохранить таблицы</label>
        </p>
        <p><small>Если снимете галочку — таблица <b>b2b_sync_state</b> будет удалена</small></p>
        
        <input type="submit" value="Удалить модуль">
    </form>
<?php
endif;
?>