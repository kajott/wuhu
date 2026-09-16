<?php
if (!defined("ADMIN_DIR")) exit();

global $settings;
function perform(&$msg)
{
  global $settings;
  if (!is_user_logged_in()) 
  {
    $msg = "You got logged out :(";
    return 0;
  }
  $data = array();
  $meta = array("title","author","platform","comment","orgacomment");
  foreach($meta as $m) $data[$m] = isset($_POST[$m]) ? $_POST[$m] : "";
  $data["id"] = $_POST["entryid"];
  $data["compoID"] = @$_POST["compo"];
  $data["userID"] = get_user_id();
  if (isset($_FILES['screenshot']))
  {
    $data["localScreenshotFile"] = $_FILES['screenshot']['tmp_name'];
  }
  $data["localFileName"] = $_FILES['entryfile']['tmp_name'];
  $data["originalFileName"] = $_FILES['entryfile']['name'];
  if (handleUploadedRelease($data,$out))
  {
    return $out["entryID"];
  }

  $msg = $out["error"];
  return 0;
}
if (@$_POST["entryid"]) 
{
  $msg = "";
  $id = perform($msg);
  if ($id) 
  {
    echo "<div class='success'>Update successful!</div>";
  } 
  else 
  {
    echo "<div class='failure'>Error: ".$msg."</div>";
  }
}
if (@$_GET["newUploadSuccess"])
{
  echo "<div class='success'>Upload successful! Your entry number is <b>".(int)$_GET["id"]."</b>. If you want to edit some of the details, you can do it below.</div>";
}

global $page;
if (@$_GET["id"]) 
{
  $entry = SQLLib::selectRow(sprintf_esc("select * from compoentries where id=%d",$_GET["id"]));
  if ($entry->userid != $_SESSION["logindata"]->id)
  {
    die("nice try.");
  }

  $compo = get_compo($entry->compoid);

  $filedir = get_compoentry_dir_path( $entry );
  if (!$filedir)
  {
    die("Unable to find compo entry dir!");
  }

  if (@$_GET["select"]) 
  {
    $lock = new OpLock();
    $fn = basename($_GET["select"]);
    if (file_exists($filedir . $fn)) 
    {
      $upload = array(
        "filename" => $fn,
      );
      SQLLib::UpdateRow("compoentries",$upload,"id=".(int)$_GET["id"]);
      run_hook("editentries_selectfile",array("entryID"=>(int)$_GET["id"]));
      redirect( build_url($page,array("id"=>(int)$_GET["id"])) );
    }
  }

  if (@$_GET["delete"]) 
  {
    $lock = new OpLock();
    $fn = basename($_GET["delete"]);
    if (file_exists($filedir . $fn)) 
    {
      unlink($filedir . $fn);
      redirect( build_url($page,array("id"=>(int)$_GET["id"])) );
    }
  }

?>
<form method="post" enctype="multipart/form-data">
<div id="entryform">
<div class='formrow'>
  <label for='title'><?=cs('label_title', "Product title:")?></label>
  <input id="title" name="title" type="text" value="<?=_html($entry->title)?>" required='yes'/>
</div>
<div class='formrow'>
  <label for='author'><?=cs('label_author', "Author:")?></label>
  <input id="author" name="author" type="text" value="<?=_html($entry->author)?>"/>
</div>
<?php if ($compo->hasplatform) { ?>
<div class='formrow'>
  <label for='platform'><?=cs('label_platform', "Platform / Options:")?></label>
  <input id="platform" name="platform" type="text" value="<?=_html($entry->platform)?>" list="platforms"/>
  <datalist id="platforms">
  <?php
    foreach(explode("\n", $compo->platforms) as $p) {
      if (trim($p) != "") {
        echo "    <option value=\"" . _html(trim($p)) . "\"/>\n";
      }
    }
  ?>
  </datalist>
</div>
<?php } ?>
<div class='formrow'>
  <label for="comment"><?=cs('label_comment', "Comment: <small>(this will be shown on the compo slide)</small>")?></label>
  <textarea id="comment" name="comment"><?=_html($entry->comment)?></textarea>
</div>
<div class='formrow'>
  <label for='orgacomment'><?=cs('label_orgacomment', "Comment for the organizers: <small>(this will NOT be shown anywhere)</small>")?></label>
  <textarea name="orgacomment"><?=_html($entry->orgacomment)?></textarea>
</div>
<?php if ($compo->screenshot) { ?>
<div class='formrow'>
  <label for='screenshot'><?=cs('label_screenshot', "Screenshot: <small>(optional - JPG, GIF or PNG!)</small>")?></label>
  <input name="screenshot" type="file" accept="image/*" /><br>
  <img id='screenshot' src='screenshot.php?id=<?=(int)$_GET["id"]?>&amp;show=thumb' alt='thumb'/><br/>
</div>
<?php } ?>
<div class='formrow'>
  <label for="uploadedfiles"><?=cs('label_files', "Uploaded files:")?></label>
<table id='uploadedfiles'>
<?php
  $a = glob($filedir . "*");
  foreach ($a as $v)
  {
    $v = basename($v);
?>
<tr class='<?=($v == $entry->filename?"fileselected":"fileunselected")?>'>
  <td><?=$v?></td>
  <td><?php
  if ($v == $entry->filename) {
    echo "<i>Currently selected file</i>";
  } else {
    printf("<a href='%s&amp;select=%s'>Select this file</a>\n",$_SERVER["REQUEST_URI"],rawurlencode($v));
    printf("<a href='%s&amp;delete=%s' class='deletefile'>Delete this file</a>\n",$_SERVER["REQUEST_URI"],rawurlencode($v));
  }
  ?></td>
</tr>
<?php
  }
?>
</table>
</div>
<?php if (count($a)>1) { ?>
  <div id='multifilewarning'><?=cs('multifilewarning', "(Hint: having only <u>ONE</u> file decreases the chances of having the wrong version played!)")?></div>
<?php } ?>
<div class='formrow'>
  <label for='entryfile'><?=str_replace('MAXSIZE', ini_get('upload_max_filesize'), cs('label_file', "Upload new file: <small>(max. MAXSIZE - if you want to upload a bigger file, just upload a dummy text file here and ask the organizers!)</small>"))?></label>
  <input name="entryfile" type="file" />
</div>
<div class='formrow'>
  <input name="entryid" type='hidden' value="<?=(int)$_GET["id"]?>" />
  <input type="submit" value="Go!" />
</div>
<?php run_hook("editentry_endform"); ?>
</div>
</form>
<?php
} 
else 
{
  $entries = SQLLib::selectRows(sprintf_esc("select * from compoentries where userid=%d",get_user_id()));
  echo "<div class='entrylist' id='editmyentries'>\n";
  global $entry;
  foreach ($entries as $entry)
  {
    $compo = get_compo( $entry->compoid );
    echo "<div class='entry'>\n";
    if ($compo->screenshot)
    {
      printf("<div class='screenshot'><a href='screenshot.php?id=%d' target='_blank'><img src='screenshot.php?id=%d&amp;show=thumb'/></a></div>\n",$entry->id,$entry->id);
    }
    printf("<div class='compo'>%s</div>\n",_html($compo->name));
    printf("<div class='title'><b>%s</b> - %s</div>\n",_html($entry->title),_html($entry->author));

    if ($compo->uploadopen || $compo->updateopen)
    {
      printf("<div class='editlink'><a href='%s&amp;id=%d'>Edit entry</a></div>",$_SERVER["REQUEST_URI"],$entry->id );
    }

    run_hook("editentries_endrow",array("entry"=>$entry));

    echo "</div>\n";
  }
  echo "</div>";
}
?>
