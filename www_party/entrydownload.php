<?php
include_once("database.inc.php");
include_once(ADMIN_DIR . "/bootstrap.inc.php");
start_wuhu_session();

$compoid = intval(@$_GET["c"]);
$order   = intval(@$_GET["o"]);
if (!$compoid || !$order) { http_response_code(400); die; }

$compo = SQLLib::selectRow("select * from compos where id = $compoid");
if (!$compo) { http_response_code(404); die; }
if (!$compo->votingopen) { http_response_code(403); die; }

$entry = SQLLib::selectRow("select * from compoentries where compoid = $compoid and playingorder = $order");
if (!$entry) { http_response_code(404); die; }

$path = get_compoentry_file_path($entry);
$fd = @fopen($path, 'rb');
if ($fd === false) { http_response_code(404); die; }
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"".basename($entry->filename)."\"");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
$stat = @fstat($fd);
if ($stat) { header("Content-Length: ".$stat['size']); }
fpassthru($fd);
fclose($fd);
exit();
?>
