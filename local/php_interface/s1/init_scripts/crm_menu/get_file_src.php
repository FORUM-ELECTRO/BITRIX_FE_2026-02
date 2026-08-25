<?
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');


$prop_common = CFile::GetByID($_POST['file_id']);
$prop_common = $prop_common->Fetch();
echo $prop_common["SRC"];
