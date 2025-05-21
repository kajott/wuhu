<?php
include_once("database.inc.php");
include_once(ADMIN_DIR . "/bootstrap.inc.php");

start_wuhu_session();

$s = SQLLib::selectRow(sprintf_esc("select * from compos where id = %d",$_GET["id"]));
if(!$s) exit;

header("Content-type: application/json");
echo json_encode($s);
?>
