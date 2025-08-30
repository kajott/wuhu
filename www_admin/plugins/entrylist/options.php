<?php
// try to set a download filename
@header('Content-Disposition: inline; filename="entrylist.html"');
if (!defined("PLUGINOPTIONS")) exit();

// check for entrystatus plugin
$plugins = unserialize(@file_get_contents(PLUGINREGISTRY));
if (!$plugins) $plugins = array();
$have_status_field = array_key_exists("entrystatus", $plugins) && $plugins["entrystatus"]["active"];

// define list of possible sort orders
$Orders = array(
  "id"     => array("Entry ID", "order by id desc"),
  "compo"  => array("Compo",    "order by compoid asc, playingorder asc"),
  "upload" => array("Upload",   "order by uploadtime desc"),
);
if ($have_status_field) {
  $Orders["status"] = array("Status", "order by status asc, id desc");
}

// load and check sort order
$prev = get_setting("entrylist_lastorder");
$order = isset($_GET["order"]) ? $_GET["order"] : $prev;
if (array_key_exists($order, $Orders))
{
  if ($order != $prev) update_setting("entrylist_lastorder", $order);
  $sortsql = $Orders[$order][1];
}
else $sortsql = "";

// render order menu
echo "<p id='orderoptions'>Order by:\n";
foreach ($Orders as $k => $o)
{
  if ($order == $k)
    echo "[<span id='currentorder'>{$o[0]}</span>]\n";
  else
    echo "[<a class='setorder' href='?plugin=entrylist&order=$k'>{$o[0]}</a>]\n";
}
echo "&bull; export as <a href=\"plugins/entrylist/json.php\">JSON</a></p>\n";

// query the DB
$compos = get_compos();
$entries = SQLLib::selectRows("select * from compoentries $sortsql");

// render the table header
echo "<table class='minuswiki' id='entrylist'>\n";
echo "<tr>\n";
echo "  <th title='entry ID'>ID</th>\n";
echo "  <th>Compo / Order</th>\n";
echo "  <th>Title / Author</th>\n";
echo "  <th>File name / Platform</th>\n";
echo "  <th>File size / date</th>\n";
echo "  <th>Status</th>\n";
echo "</tr>\n";

// render entries
foreach ($entries as $e)
{
  $c = $compos[$e->compoid];
  $fn = get_compoentry_file_path($e);
  $mtime = @filemtime($fn);
  if (!$mtime) { $mtime = strtotime($e->uploadtime); }
  $order = sprintf("%02d", $e->playingorder);
  echo "<tr class='entry'>\n";
  echo "<td class='id'>#{$e->id}</td>\n";
  echo "<td><a href='compos_entry_list.php?id={$e->compoid}' class='compo' data-dirname='{$c->dirname}'>" . _html($c->name) . "</a><br><span class='order'>$order</span></td>\n";
  echo "<td><a href='compos_entry_edit.php?id={$e->id}' class='title'>" . _html($e->title) . "</a><br>by <span class='author'>" . _html($e->author) . "</span></td>\n";
  echo "<td><a href='compos_entry_edit.php?download={$e->id}' class='download' data-filename='" . _html($fn) . "'>" . _html($e->filename) . "</a><br></span>" . _html($e->platform) . "</span></td>\n";
  echo "<td><span class='size'>" . number_format(filesize($fn)) . "</span>&nbsp;bytes<br><span class='date' data-mtime='$mtime'>" . date("d.m. H:i:s", $mtime) . "</span></td>\n";
  echo "<td>";
  if ($have_status_field)
    echo "<span class='status entrystatus_{$e->status}'>{$e->status}</span><br>";
  if ($e->orgacomment)
    echo "<span class='status orgacomment' title='has orga comment'>OC</span>";
  if (isset($e->organizerfeedback) && $e->organizerfeedback)
    echo "<span class='status feedback' title='has orga feedback'>FB</span>";
  echo "</td></tr>\n";
}

// done
echo "</table>\n";
?>
