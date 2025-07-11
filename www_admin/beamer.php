<?php
include_once("bootstrap.inc.php");
$DATAFILE = "beamer.data";
$beamerData = @unserialize(file_get_contents($DATAFILE));

$slidedir = get_setting("slidedir_show");
$slidedir = $slidedir ? (basename($slidedir) . "/") : "slides/";

if (@$_GET["format"])
{
  switch($_GET["format"])
  {
    case "jsonp":
      {
        header("Content-type: application/javascript");
        printf("%s(%s);",@$_GET["callback"]?:"wuhuJSONPCallback",json_encode($beamerData));
      }
      break;
    case "json":
    default:
      {
        header("Content-type: application/json");
        echo json_encode($beamerData);
      }
      break;
  }
  exit();
}

if (@$_POST["slidedir"] && @$_POST["timeout"])
{
  $slidedir = basename($_POST["slidedir"]);
  if ((substr($slidedir, 0, 6) == "slides") && is_dir($slidedir))
  {
    update_setting("slidedir_show", $slidedir);
  }
  $slidedir = $slidedir . "/";
  update_setting("slide_timeout", $_POST["timeout"]);
}
else if (@$_POST["mode"])
{
  $out = array();
  $out["success"] = true;
  $out["result"] = array();
  $out["result"]["mode"] = $_POST["mode"];

  switch ($_POST["mode"]) {
    case "announcement":
    {
      if ($_POST["isHTML"] == "on")
      {
        $out["result"]["announcementhtml"] = $_POST["announcement"];
      }
      else
      {
        $out["result"]["announcementtext"] = $_POST["announcement"];
      }
    } break;
    case "compocountdown":
    {
      if ($_POST["compo"])
      {
        $s = get_compo( $_POST["compo"] );
        $out["result"]["componame"] = $s->name;
        $out["result"]["compostart"] = $s->start;
      }
      if ($_POST["eventname"])
      {
        $out["result"]["eventname"] = $_POST["eventname"];
        $out["result"]["compostart"] = $_POST["eventtime"];
      }

    } break;
    case "compodisplay":
    {
      $compo = get_compo( $_POST["compo"] );
      $out["result"]["compoid"] = $compo->id;
      $out["result"]["componame"] = $compo->name;
      $out["result"]["callbacks"] = $_POST["callbacks"] ? @intval($_POST["cbdelay"]) : 0;

      $query = new SQLSelect();
      $query->AddTable("compoentries");
      $query->AddWhere(sprintf_esc("compoid=%d",$_POST["compo"]));
      $query->AddOrder("playingorder");
      run_hook("admin_beamer_generate_compodisplay_dbquery",array("query"=>&$query));
      $entries = SQLLib::selectRows( $query->GetQuery() );

      $playingorder = 1;
      $out["result"]["entries"] = array();
      foreach ($entries as $t)
      {
        $a = array(
          "number" => $playingorder++,
          "title" => $t->title,
          "comment" => $t->comment,
        );
        if ($compo->showauthor)
        {
          $a["author"] = $t->author;
        }
        if ($compo->hasplatform)
        {
          $a["platform"] = $t->platform;
        }
        $out["result"]["entries"][] = $a;
      }
    } break;
    case "prizegiving":
    {
      $voter = null;
      $compo = get_compo( $_POST["compo"] );
      $results = generate_results($voter, $compo->id, false);
      $compoResults = $results["compos"][0]["results"];
      run_hook("admin_beamer_prizegiving_rendervotes",array("results"=>&$compoResults,"compo"=>$compo));

      $out["result"]["componame"] = $compo->name;
      $out["result"]["results"] = array_reverse($compoResults);
    } break;
  }
  file_put_contents($DATAFILE,serialize($out));
  redirect("beamer.php");
}

include_once("header.inc.php");
printf("<h2>Change beamer setting</h2>\n");

$s = SQLLib::selectRows("select * from compos order by start");

printf("<p>URL to beamer data (for external / third party beam systems): <a href='beamer.php?format=json'>JSON</a> / <a href='beamer.php?format=jsonp'>JSONP</a></p>");
printf("<p>Current mode: <b>%s</b></p>",_html(@$beamerData["result"]["mode"]));

$a = glob("slides*");
echo "<h3>Rotation Slide Configuration</h2><form method='post'>";
echo "<p>Slide Set: <select name='slidedir'>\n";
foreach ($a as $d) {
  if (!is_dir($d)) continue;
  $title = ($d == "slides") ? "(default rotation)" : trim(substr($d, 6), "_");
  echo "<option value='$d'" . (($d . "/" == $slidedir) ? " selected" : "") . ">$title</option>\n";
}
echo "</select></p>\n";
echo "Auto-Advance Interval: <input style='display:inline; width:3.75em' type='number' name='timeout' min='1' max='9999' value='" . get_setting("slide_timeout") . "'/> seconds";
echo "</p>\n<p><input type='submit' value='Set Rotation Options'></p></form>\n";

?>

<div class='beamermode'>
<h3>Announcement</h3>
<form action="beamer.php" method="post" enctype="multipart/form-data">
  <textarea name="announcement"><?=_html(trim(@$beamerData["result"]["announcementhtml"]?:@$beamerData["result"]["announcementtext"]?:""))?></textarea><br/>
  <input type="checkbox" name="isHTML" id="isHTML" style='display:inline-block'<?=(@$beamerData["result"]["announcementhtml"]?" checked='checked'":"")?>/> <label for='isHTML'>Use HTML</label>
  <input type="hidden" name="mode" value="announcement"/>
  <input type="submit" value="Switch to Announcement mode."/>
</form>
</div>

<div class='beamermode'>
<h3>Compo countdown</h3>
<form action="beamer.php" method="post" enctype="multipart/form-data">
<select name="compo">
<?php
foreach($s as $t)
  printf("  <option value='%d'>%s</option>\n",$t->id,$t->name);
?>
</select><br/>
  <input type="hidden" name="mode" value="compocountdown"/>
  <input type="submit" value="Switch to Compo Countdown mode."/>
</form>
</div>

<div class='beamermode'>
<h3>Event countdown</h3>
<form action="beamer.php" method="post" enctype="multipart/form-data">
  <label for="eventname">Event name:</label>
  <input type="text" id="eventname" name="eventname" value="" required='yes'/>
  <label for="eventtime">Event time:</label>
  <input type="text" id="eventtime" name="eventtime" value="<?=date("Y-m-d H:i:s")?>"/>
  <input type="hidden" name="mode" value="compocountdown"/>
  <input type="submit" value="Switch to Event Countdown mode."/>
</form>
</div>

<div class='beamermode'>
<h3>Compo display</h3>
<form action="beamer.php" method="post" enctype="multipart/form-data">
<select name="compo">
<?php
foreach($s as $t)
  printf("  <option value='%d'>%s</option>\n",$t->id,$t->name);
?>
</select><br/>
  <input style="display:inline" type="checkbox" name="callbacks"/> trigger callbacks after
  <input style="display:inline; width:2.5em" type="number" max="99" name="cbdelay" value="20"/> seconds
<br/>
  <input type="hidden" name="mode" value="compodisplay"/>
  <input type="submit" value="Switch to Compo Display mode."/>
</form>
</div>

<div class='beamermode'>
<h3>Prizegiving</h3>
<form action="beamer.php" method="post" enctype="multipart/form-data">
<select name="compo">
<?php
foreach($s as $t)
  printf("  <option value='%d'>%s</option>\n",$t->id,$t->name);
?>
</select><br/>
  <input type="hidden" name="mode" value="prizegiving"/>
  <input type="submit" value="Switch to Prizegiving mode."/>
</form>
</div>
<?php
include_once("footer.inc.php");
?>
