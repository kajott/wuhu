<?php
include_once("database.inc.php");
include_once(ADMIN_DIR . "/bootstrap.inc.php");

start_wuhu_session();

$eid = intval(@$_GET["id"]);
if ($eid)
{
  // access control checks
  $entry = SQLLib::selectRow(sprintf_esc("select * from compoentries where id = %d", $eid));
  if(!$entry) { http_response_code(404); exit; }
  $compo = SQLLib::selectRow(sprintf_esc("select * from compos where id = %d",$entry->compoid));
  if (get_user_id() != $entry->userid && $compo->votingopen==0) { http_response_code(401); exit; }
  if (get_user_id() == $entry->userid) define('SCREENSHOT_SAME_USER', 1);
}

include_once(ADMIN_DIR . "/screenshot.php");
?>
