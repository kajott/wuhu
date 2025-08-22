<?php

$offset = isset($_GET['n']) ? intval($_GET['n']) : 0;

$pid = @intval(@file_get_contents("backup.pid"));
if (!is_dir("/proc/$pid") && ($offset >= filesize("backup.log"))) {
    http_response_code(410);  // "Gone"
    die;
}

header("Content-Type: text/plain");
$fd = fopen("backup.log", "rb");
fseek($fd, $offset);
$res = fread($fd, 65536);
fclose($fd);
echo $res;

?>