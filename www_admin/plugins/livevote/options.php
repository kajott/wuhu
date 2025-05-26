<?php
if (!defined("ADMIN_DIR") || !defined("PLUGINOPTIONS"))
  exit();

if (isset($_POST["livevoteCompo"]))
{
  update_setting("livevote_compo", (int)$_POST["livevoteCompo"]);
  SQLLib::Query("update compoentries set livevote_enabled = 0 where compoid=".(int)$_POST["livevoteCompo"]);
}
if (isset($_POST["livevoteAuto"]))
{
  update_setting("livevote_auto", (int)$_POST["livevoteAuto"]);
}
if (isset($_POST["livevoteEntries"]))
{
  //update_setting("livevote_compo", (int)$_POST["livevoteCompo"]);
  foreach($_POST["livevoteEntries"] as $k=>$v)
  {
    SQLLib::updateRow("compoentries",array("livevote_enabled"=>(($v=="on")?1:0)),"id=".(int)$k);
  }
}

if (!check_menuitem("LiveVote"))
{
  printf("<div class='error'>The live voting menu is <a href='toc.php'>not added to the partynet menu</a>; if you don't do it, people won't find the page to use!</div>\n");
}
  
$opencompos = SQLLib::selectRows("select * from compos where uploadopen = 0 and updateopen = 0 order by start");

$compos = array(0=>"- none -");
foreach($opencompos as $v) $compos[$v->id] = $v->name;

$mode = @intval(get_setting("livevote_auto"));
echo "<form method='post'>";
echo "Live voting management mode:";
echo "<br/><input type='radio' name='livevoteAuto' value='0'" . (($mode == 0) ? " checked='checked'" : "") . "> ";
echo "<strong>manual:</strong> only this admin page sets up live voting";
echo "<br/><input type='radio' name='livevoteAuto' value='1'" . (($mode == 1) ? " checked='checked'" : "") . "> ";
echo "<strong>automatic:</strong> slideviewer callbacks from compo entry slides trigger live voting";
echo "<br/><input type='radio' name='livevoteAuto' value='2'" . (($mode == 2) ? " checked='checked'" : "") . "> ";
echo "<strong>fully automatic:</strong> as above, but callbacks from 'end of compo' slides trigger normal voting too";
echo "<br/><input type='submit' value='Apply'></form>\n";

echo "<form method='post'>";
echo "<label for='livevoteCompo'>Select compo for livevoting: (only compos with closed uploads are visible:)</label>";
echo "<select name='livevoteCompo'>";
foreach($compos as $k=>$v)
  echo "<option value='".$k."'".(get_setting("livevote_compo") == $k ? " selected='selected'" : "").">"._html($v)."</option>";
echo "</select>";
echo "<input type='submit' value='Set'/>";

$compo = get_compo( (int) get_setting("livevote_compo") );
if ($compo)
{
  $query = new SQLSelect();
  $query->AddTable("compoentries");
  $query->AddWhere(sprintf_esc("compoid=%d",get_setting("livevote_compo")));
  $query->AddOrder("playingorder");
  run_hook("admin_beamer_generate_compodisplay_dbquery",array("query"=>&$query));
  $entries = SQLLib::selectRows( $query->GetQuery() );

  echo "<h2>"._html($compo->name)."</h2>";
//  echo "<label>Select the entries to enable voting for them:</label>";
  echo "<ol id='entries'>";
  foreach($entries as $v)
  {
    echo "<li>";
    printf("<input type='checkbox' name='livevoteEntries[%d]' id='livevoteEntries[%d]'%s>\n",$v->id,$v->id,$v->livevote_enabled ? " checked='checked'" : "");
    printf("<label for='livevoteEntries[%d]'>%s</label>",$v->id,_html($v->title));
    echo "</li>";
  }
  echo "</ol>";
  echo "<p id='loading'></p>";
  echo "<input type='submit' value='Set' id='saveEntries'/>";
}

echo "</form>";
?>
<script type="text/javascript">
<!--
document.observe("dom:loaded",function(){
  $("saveEntries").hide();
  $$("#entries li").each(function(item){
    item.down("input").setStyle({margin:"5px"});
    item.down("input").observe("change",function(ev){
      var p = {};
      p[ ev.element().name ] = ev.element().checked ? "on" : "";
      $("loading").update("Saving...");
      new Ajax.Request("",{
        method:"post",
        parameters:p,
        onSuccess:function(){ $("loading").update("") },
      });
    });
  });
});
//-->
</script>
<?php if($compo && !$compo->votingopen) { ?>
<p>Click <a href='./compos.php?id=<?=$compo->id?>&change=votingopen'>here</a> to enable normal voting for this compo.</p>
<?php } ?>
