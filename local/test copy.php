<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include.php';

if (str_contains($_SERVER['HTTP_USER_AGENT'], 'Firefox')) {
    echo 'Вы пользуетесь браузером Firefox.';
} else {
    echo 'Вы не пользуетесь браузером Firefox.';
}