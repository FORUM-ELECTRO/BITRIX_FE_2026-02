<?
AddEventHandler('main', 'OnEpilog', function() {
    global $APPLICATION;
    $APPLICATION->AddHeadScript('/local/php_interface/s1/init_scripts/crm_menu/menu_button.js');
});
