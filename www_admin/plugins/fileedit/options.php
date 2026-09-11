<?php
if (!defined("PLUGINOPTIONS")) exit();

$TEXT_EXTENSIONS = array("txt", "md", "rst", "htm", "html", "xhtml", "xml", "js", "mjs", "json", "css", "svg", "csv", "ics");
function istextfile($fn)
{
  global $TEXT_EXTENSIONS;
  return in_array(strtolower(pathinfo($fn, PATHINFO_EXTENSION)), $TEXT_EXTENSIONS);
}

if (isset($_GET["file"]))
{
  $filename = $_GET["file"];
  if (isset($_POST["text"]))
  {
    $contents = rtrim(str_replace("\r", "", $_POST["text"])) . "\n";
    file_put_contents($filename, $contents);
  }
  else
  {
    $contents = @file_get_contents($filename);
    if (!$contents)
    {
      $contents = @file_get_contents($filename . ".dist");
    }
  }
  echo "<p>Editing: <strong class=\"filename\">" . _html($filename) . "</strong></p>\n";
  echo "<form method=\"POST\" action=\"pluginoptions.php?plugin=fileedit&file=$filename\">\n";
  echo "<textarea id=\"editor\" cols=\"80\" rows=\"30\" name=\"text\" style=\"width:100%; height:70vh;\">";
  echo _html($contents) . "</textarea>\n";
  echo "<input id=\"save\" type=\"submit\" value=\"Save\"/>\n";
  echo "</form>\n";
}
else
{
  // get list of files (with absolute paths, at this point)
  $files = array(
    ADMIN_DIR . "/results_header.txt",
    WWW_DIR . "/template.html"
  );
  array_push($files, ... glob(ADMIN_DIR . "/slideviewer/custom.*"));
  array_push($files, ... glob(WWW_DIR . "/*/*"));  // subdirectories of www_party
  $files = array_filter($files, 'istextfile');
  sort($files);

  // find common prefix (for the UI)
  $a = $files[0];
  $b = $files[count($files) - 1];
  $prefix = 0;
  $len = min(strlen($a), strlen($b));
  for ($i = 0;  $i < $len;  $i++)
  {
    if ($a[$i] != $b[$i]) { break; }
    if ($a[$i] == "/") { $prefix = $i + 1; }
  }

  // render UI
  echo "<p>Select file to edit:</p><ul>\n";
  $root = explode("/", ADMIN_DIR);
  foreach ($files as $fn)
  {
    // get relative path
    $path = explode("/", $fn);
    $len = min(count($root), count($path));
    $common = 0;
    for ($i = 0;  $i < $len;  $i++)
    {
      if ($path[$i] == $root[$i]) { $common = $i + 1; } else { break; }
    }
    $rel = _html(str_repeat("../", count($root) - $common) . implode("/", array_slice($path, $common)));
    $disp = _html(substr($fn, $prefix));
    echo "<li><a href=\"pluginoptions.php?plugin=fileedit&file=$rel\">$disp</a></li>\n";
  }
  echo "</ul>\n";
}

?>
