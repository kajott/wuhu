<?php
if (!defined("ADMIN_DIR")) exit();

global $settings;
include_once(ADMIN_DIR . "/thumbnail.inc.php");

function perform(&$msg)
{
  global $settings;
  if (!is_user_logged_in()) 
  {
    $msg = "You got logged out :(";
    return 0;
  }
  $data = array();
  $meta = array("title","author","comment","orgacomment");
  foreach($meta as $m) $data[$m] = $_POST[$m];
  $data["compoID"] = $_POST["compo"];
  $data["userID"] = get_user_id();
  $data["localScreenshotFile"] = $_FILES['screenshot']['tmp_name'];
  $data["localFileName"] = $_FILES['entryfile']['tmp_name'];
  $data["originalFileName"] = $_FILES['entryfile']['name'];
  if (handleUploadedRelease($data,$out))
  {
    return $out["entryID"];
  }

  $msg = $out["error"] ?? "Unknown error";
  return 0;
}
if ($_POST) 
{
  $msg = "";
  $id = perform($msg);
  if ($id) 
  {
    redirect( build_url("EditEntries",array("id"=>(int)$id,"newUploadSuccess"=>time())) );
  } 
  else 
  {
    echo "<div class='failure'>".($msg ?? "There was an error!")."</div>";
  }
}

$s = SQLLib::selectRows("select * from compos where uploadopen>0 order by start");
if ($s) {
global $page;
?>
<form method="post" enctype="multipart/form-data" id='uploadEntryForm'>
<div id="entryform">
<div class='formrow'>
  <label for='compo'>Compo:</label>
  <select id='compo' name="compo" required='yes'>
    <option value=''>-- Please select a compo:</option>
<?php
foreach($s as $t)
  printf("  <option value='%d'%s>%s</option>\n",$t->id,$t->id==@$_POST["compo"] ? ' selected="selected"' : "",$t->name);
?>
  </select>
</div>
<div class='formrow' id='row_title'>
  <label for='title'>Product title:</label>
  <input id='title' name="title" type="text" value="<?=_html(@$_POST["title"])?>" required='yes'/>
</div>
<div class='formrow' id='row_author'>
  <label for='author'>Author:</label>
  <input id='author' name="author" type="text" value="<?=_html(@$_POST["author"])?>"/>
</div>
<div class='formrow' id='row_platform'>
  <label for='platform'>Platform / Options:</label>
  <input id='platform' name="platform" type="text" value="<?=_html(@$_POST["platform"])?>" list="platforms"/>
  <datalist id="platforms"></datalist>
</div>
<div class='formrow' id='row_comment'>
  <label for="comment">Comment: <small>(this will be shown on the compo slide)</small></label>
  <textarea name="comment"><?=_html(@$_POST["comment"])?></textarea>
</div>
<div class='formrow' id='row_orgacomment'>
  <label for='orgacomment'>Comment for the organizers: <small>(this will NOT be shown anywhere)</small></label>
  <textarea name="orgacomment" id="orgacomment"><?=_html(@$_POST["orgacomment"])?></textarea>
</div>
<div class='formrow' id='row_entryfile'>
  <label for='entryfile'>Uploaded file:
  <small>
  (max. <?=ini_get("upload_max_filesize")?> - if you want to upload
  a bigger file, just upload a dummy text file here and ask the organizers!)
  </small></label>
  <input id='entryfile' name="entryfile" type="file" required='yes' />
</div>
<div class='formrow' id='row_screenshot'>
  <label for='screenshot'>Screenshot: <small>(optional - JPG, GIF or PNG!)</small></label>
  <input id='screenshot' name="screenshot" type="file" accept="image/*" />
</div>
<div class='formrow'>
  <input type="submit" value="Go!" />
</div>
</div>
</form>
<script>
document.getElementById("compo").addEventListener("change",function(ev){
  var req=new XMLHttpRequest();
  req.addEventListener("load",function(){
    var s=req.responseText;
    if(!s)return;
    s=JSON.parse(s);
    document.getElementById("row_screenshot").style.visibility=((s.screenshot===undefined)||parseInt(s.screenshot))?"visible":"hidden";
    document.getElementById("row_platform").style.visibility=((s.hasplatform!==undefined)&&parseInt(s.hasplatform))?"visible":"hidden";
    var l=document.getElementById("platforms");
    l.replaceChildren();
    s.platforms.split("\n").forEach(function(p){
      p=p.trim();if(!p)return;
      l.appendChild(new Option(p,p));
    });
  });
  req.open("GET", "compoprops.php?id="+ev.target.value);
  req.send();
});
</script>
<?php
} else echo "Sorry, all deadlines are closed!";
?>
