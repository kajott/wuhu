<?php
if (!defined("PLUGINOPTIONS")) exit();

define('PIDFILE', "plugins/backup/backup.log");
define('LOGFILE', "plugins/backup/backup.log");

$ok = posix_access("plugins/backup", POSIX_W_OK);
if (!$ok) {
  echo "<p class=\"error\"><strong>Insufficient permissions</strong> on the www_admin/plugins/backup directory. The web server must be able to write there.</p>\n";
}

$pid = @intval(@file_get_contents(PIDFILE));
if (!is_dir("/proc/$pid")) { $pid = 0; }

if ($ok && !$pid && isset($_POST["create"])) {
  $dev = @$_POST["dev"];
  if (!$dev) {
    echo "<p class=\"error\">No device selected for backup.</p>\n";
  }
  else {
    $dir = @$_POST["dir"];
    if (@$_POST["date"]) {
      $dir = $dir . date("_md_Hi");
    }
    $pid = @intval(shell_exec("bash plugins/backup/wrapper.sh $dev $dir"));
  }
}

if ($ok && !$pid) {
  // parse the raw udisksctl dump
  $dump = shell_exec("udisksctl dump");
  $data = array();
  foreach (explode("\n\n", $dump) as $block) {
    $abc = explode(":", $block, 3);
    $item = array("_class" => trim($abc[1]));
    foreach (explode("\n", $abc[2]) as $line) {
      $kv = explode(":", $line, 2);
      if (count($kv) == 2) {
        $item[strtolower(trim($kv[0]))] = trim($kv[1], " \n\r'");
      }
    }
    $data[trim($abc[0])] = $item;
  }

  // create form, filter for suitable partitions
  echo "<form method=\"POST\">\n";
  echo "<h3>Create Backup</h3>\n";
  echo "<p>Device: <select name=\"dev\">\n";
  echo "<option value=\"\">(select device)</option>\n";
  foreach ($data as $obj => $item) {
    if (@$item['_class'] != "org.freedesktop.UDisks2.Block") { continue; }  // not a block device
    if (@$item['idusage'] != "filesystem") { continue; }  // not a filesystem
    if (@$item['readonly'] != "false") { continue; }  // read-only filesystem
    if (!isset($item['device'])) { continue; }  // no device set
    if (!isset($item['drive'])) { continue; }  // no parent drive
    if (!isset($data[$item['drive']])) { continue; }  // parent drive unknown
    $drive = $data[$item['drive']];
    if (@$drive['_class'] != "org.freedesktop.UDisks2.Drive") { continue; }  // parent drive isn't a drive
    if (@$drive['removable'] != "true") { continue; }  // parent drive isn't a removable drive
    $dev = $item['device'];
    $size = intval(intval(@$item['size']) / 1000000000);
    $name = @$item['hintname'];
    if (!$name) { $name = isset($item['number']) ? "partition ${item['number']}" : "unknown partition"; }
    $dname = @$drive['model'];
    if (!$dname) { $dname = str_replace("-", " ", $drive['id']); }
    $fstype = @$item['idtype'];
    echo "<option value=\"$dev\">$dev: $name on $dname (${size}G $fstype)</option>\n";
  }
  echo "</select></p>\n";
  $dir = strtolower(str_replace(" ", "", get_setting("party_name")));
  echo "<p>Directory: <input type=\"text\" name=\"dir\" value=\"$dir\">\n";
  echo "<input type=\"checkbox\" name=\"date\"> add date and time</p>";
  echo "<p><input type=\"submit\" name=\"create\" value=\"Create Backup\"></p>";
  echo "</form>\n";
}

if ($pid) { ?>
  <h3>CURRENTLY RUNNING</h3>
  <textarea cols="80" rows="30" id="log"></textarea>
  <script>
    var text = "";
    var nbytes = 0;
    function logRequestBlock() {
      var xhr = new XMLHttpRequest;
      xhr.addEventListener("load", function(e) {
        var res = xhr.responseText;
        text += res;
        nbytes += res.length;
        var log = document.getElementById("log");
        log.value = text;
        log.scrollTop = log.scrollHeight;
        if (xhr.status < 400) { window.setTimeout(logRequestBlock, 500); }
      });
      xhr.open('GET', "/plugins/backup/logtail.php?n=" + nbytes);
      xhr.send();
    }
    logRequestBlock();
  </script>
<? }

if (!$pid && file_exists(LOGFILE)) {
    echo "<h3>Log from previous run</h3>\n";
    echo "<textarea cols=\"80\" rows=\"24\">";
    echo htmlspecialchars(file_get_contents(LOGFILE));
    echo "</textarea>";
}

?>
