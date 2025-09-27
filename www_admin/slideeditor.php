<?php
include_once("bootstrap.inc.php");

$slidedir = get_setting("slidedir_edit");
$slidedir = $slidedir ? (basename($slidedir) . "/") : "slides/";

$error = false;
if (@$_POST["slidedir_set"])
{
  $slidedir = basename($_POST["slidedir_set"]);
  if ((substr($slidedir, 0, 6) != "slides") || !is_dir($slidedir))
    $error = "Failed to change slide directory to $slidedir";
  else
  {
    update_setting("slidedir_edit", $slidedir);
    redirect();
  }
}
else if (@$_POST["slidedir_new"])
{
  $slidedir = "slides_" . basename($_POST["slidedir_new"]);
  $error = @file_exists($slidedir);
  @umask(0002);
  if (!$error)
    $error = @mkdir($slidedir, 02775) == false;
  if ($error)
    $error = "Failed to create slide directory $slidedir";
  else
  {
    update_setting("slidedir_edit", $slidedir);
    redirect();
  }
}
else if (is_uploaded_file(@$_FILES["newSlideFile"]["tmp_name"]?:""))
{
  $fn = $_FILES["newSlideFile"]["name"];
  sanitize_filename($fn);
  if ($fn != "index.php")
  {
    $error = @move_uploaded_file($_FILES["newSlideFile"]["tmp_name"],$slidedir.$fn) == false;
  }

  if (!$error)
    redirect();
  $error = "Failed to move uploaded file to ".$slidedir.$fn;
}
else if (@$_POST["newTextSlideContents"] && @$_POST["newTextSlideFilename"])
{
  $fn = $_POST["newTextSlideFilename"];
  sanitize_filename($fn);
  $error = @file_put_contents($slidedir.$fn,$_POST["newTextSlideContents"]) == false;

  if (!$error)
    redirect();
  $error = "Failed to write ".$slidedir.$fn;
}
else if (@$_POST["editSlideContents"] && @$_POST["editSlideFilename"])
{
  $error = @file_put_contents($slidedir.$_POST["editSlideFilename"],$_POST["editSlideContents"]) == false;

  if (!$error)
    redirect();
  $error = "Failed to write ".$slidedir.$_POST["editSlideFilename"];
}
else if (@$_POST["rename_from"] && @$_POST["rename_to"])
{
  $oldfn = basename($_POST["rename_from"]);
  $newfn = basename($_POST["rename_to"]);
  sanitize_filename($newfn);
  $error = rename($slidedir.$oldfn, $slidedir.$newfn) == false;
  if (!$error)
    redirect();
  $error = "Failed to rename " . $slidedir.$oldfn . " to " . $slidedir.$newfn;
}
else if (@$_GET["delete"])
{
  $error = @unlink($slidedir . basename($_GET["delete"])) == false;

  if (!$error)
    redirect("slideeditor.php");
  $error = "Failed to unlink ".$slidedir.$_GET["delete"];
}

include_once("header.inc.php");

if ($error)
  printf("<div class='error'>%s</div>",$error);
else if (@$_GET["edit"])
{
  $v = basename($_GET["edit"]);
  echo "<div id='slideedit'>";
  printf("<h3>%s</h3>\n",_html($v));
  switch(substr(strtolower($v),-4))
  {
    case ".png":
    case ".jpg":
    case "jpeg":
    case ".gif":
    case ".svg":
      printf("<img src='%s%s'/>",$slidedir,$v);
      break;
    case ".mp4":
    case ".ogv":
    case ".avi":
      printf("<video controls='yes'><source src='%s%s'/></video>",$slidedir,$v);
      break;
    case ".txt":
    case ".htm":
    case "html":
      echo "<form method='post' enctype='multipart/form-data'>\n";
      printf("<textarea name='editSlideContents'>%s</textarea>",_html(file_get_contents($slidedir.$v)));
      printf("<input type='hidden' name='editSlideFilename' value='%s' />",_html($v));
      echo "<input type='submit' value='Save' />";
      echo "</form>\n";
      break;
  }
  echo "</div>";
}
else
{
  $a = glob("slides*");
  echo "<h2>Slide Set Management</h2>";
  echo "<p><form method='post'>Current slide set to edit: <select name='slidedir_set'>\n";
  foreach ($a as $d) {
    if (!is_dir($d)) continue;
    $title = ($d == "slides") ? "(default rotation)" : trim(substr($d, 6), "_");
    echo "<option value='$d'" . (($d . "/" == $slidedir) ? " selected" : "") . ">$title</option>\n";
  }
  echo "</select><input type='submit' value='Switch Slide Set'></form></p>\n";
  echo "<p><form method='post'>Create new slide set: <input type='text' name='slidedir_new'>\n";
  echo "<input type='submit' value='Create Slide Set'></form></p>\n";

  $a = glob($slidedir . "*");
  echo "<h2>Current slides</h2>\n";
  echo "<ul id='slides'>\n";
  foreach($a as $v)
  {
    $v = basename($v);
    if ($v == ".") continue;
    if ($v == "..") continue;
    if ($v == "index.php") continue;
    $hv = _html($v);

    echo "<li><form method='post'>\n";
    echo "<input type='hidden' name='rename_from' value='$hv'/>\n";
    echo "<input type='text' name='rename_to' class='renameslide' size='30' value='$hv' title='change and press Enter to rename'/>\n";
    echo "<input type='submit' class='renamesubmit' value='Rename'/>\n";
    printf("<div class='contents'>\n");
    switch(substr(strtolower($v),-4))
    {
      case ".png":
      case ".jpg":
      case "jpeg":
      case ".gif":
      case ".svg":
        printf("<img src='%s%s'/>",$slidedir,$v);
        break;
      case ".mp4":
      case ".ogv":
      case ".avi":
        printf("<video><source src='%s%s'/></video>",$slidedir,$v);
        break;
      case ".txt":
      case ".htm":
      case "html":
        printf("<pre>%s</pre>",_html(file_get_contents($slidedir.$v)));
        break;
    }
    echo "</div>\n";
    printf("<a href='?edit=%s'>Edit</a> | ",rawurlencode($v));
    printf("<a href='?delete=%s' class='del'>Delete</a>",rawurlencode($v));
    echo "</form></li>";
  }
  echo "</ul>\n";

  echo "<h2>Add new slides</h2>\n";

  echo "<form method='post' enctype='multipart/form-data'>\n";
  printf("<h3>New text slide</h3>\n");
  echo "<label>Slide contents</label>\n";
  echo "<textarea name='newTextSlideContents' required='yes'></textarea>";
  echo "<p><b>Warning:</b> All text will be treated as HTML!</p>";
  echo "<label>Slide filename</label>\n";
  echo "<input name='newTextSlideFilename' required='yes' type='text' placeholder='<filename>.html'/>";
  echo "<input type='submit' value='Save file' />";
  echo "</form>\n";

  echo "<form method='post' enctype='multipart/form-data'>\n";
  printf("<h3>Upload new slide</h3>\n");
  echo "<input type='file' name='newSlideFile' required='yes' />";
  echo "<input type='submit' value='Start upload' />";

  echo "</form>\n";
  ?>
  <script type="text/javascript">
  <!--
  document.observe("dom:loaded",function(){
    $$(".del").invoke("observe","click",function(ev){
      if (!confirm("Are you sure?")) ev.stop();
    });
  });
  //-->
  </script>
  <?php
}
include_once("footer.inc.php");
?>
