<?php
if (!defined("PLUGINOPTIONS")) exit();

$TEXT_EXTENSIONS = array("txt", "md", "rst", "htm", "html", "xhtml", "xml", "php", "js", "mjs", "json", "yaml", "toml", "css", "svg", "csv", "ics", "sql", "gitignore");
function istextfile($fn)
{
  global $TEXT_EXTENSIONS;
  return in_array(strtolower(pathinfo($fn, PATHINFO_EXTENSION)), $TEXT_EXTENSIONS);
}

function put_absolute_path_picker(&$paths)
{
  if (!$paths || !count($paths)) { return; }
  sort($paths);

  // find common prefix (for the UI)
  $a = $paths[0];
  $b = $paths[count($paths) - 1];
  $prefix = 0;
  $len = min(strlen($a), strlen($b));
  for ($i = 0;  $i < $len;  $i++)
  {
    if ($a[$i] != $b[$i]) { break; }
    if ($a[$i] == "/") { $prefix = $i + 1; }
  }

  // render UI
  echo "<ul>\n";
  $root = explode("/", ADMIN_DIR);
  foreach ($paths as $fn)
  {
    // get relative path
    $path = explode("/", $fn);
    $len = min(count($root), count($path));
    $common = 0;
    for ($i = 0;  $i < $len;  $i++)
    {
      if ($path[$i] == $root[$i]) { $common = $i + 1; } else { break; }
    }
    $rel = str_repeat("../", count($root) - $common) . implode("/", array_slice($path, $common));
    $rel = $rel ? _html($rel) : ".";
    $disp = _html(substr($fn, $prefix));
    echo "<li><a href=\"pluginoptions.php?plugin=fileedit&path=$rel\">$disp</a></li>\n";
  }
  echo "</ul>\n";
}

$path = @$_GET["path"];
if ($path && is_dir($path))
{
  echo "<h2>Contents of " . _html($path) . "</h2>\n";
  echo "<ul>\n";
  foreach (scandir($path) as $fn)
  {
    if ($fn == ".") continue;
    if ($fn == "..")
    {
      $subpath = dirname($path);
      if (strlen($subpath) < 2) continue;
    }
    else
    {
      $subpath = $path . "/" . $fn;
    }
    $dispname = _html($fn) . (is_dir($subpath) ? "/" : "");
    $subpath = _html($subpath);
    echo "<li><a href=\"pluginoptions.php?plugin=fileedit&path=$subpath\">$dispname</a></li>\n";
  }
  echo "</ul>\n";
}
else if ($path && istextfile($path))
{
  // edit or update text file
  if (isset($_POST["text"]))
  {
    $contents = rtrim(str_replace("\r", "", $_POST["text"])) . "\n";
    file_put_contents($path, $contents);
  }
  else
  {
    $contents = @file_get_contents($path);
    if (!$contents)
    {
      $contents = @file_get_contents($path . ".dist");
    }
  }
  echo "<p>Editing: <strong class=\"filename\">" . _html($path) . "</strong></p>\n";
  echo "<form method=\"POST\" action=\"pluginoptions.php?plugin=fileedit&path=$path\">\n";
  echo "<textarea id=\"editor\" cols=\"80\" rows=\"30\" name=\"text\" style=\"width:100%; height:70vh;\">";
  echo _html($contents) . "</textarea>\n";
  echo "<input id=\"save\" type=\"submit\" value=\"Save\"/>\n";
  echo "</form>\n";
}
else if ($path)
{
  // download binary file
  ob_clean();
  $size = @filesize($path);
  if (!$size) { http_response_code(404); die; }
  $mime = @mime_content_type($path);
  if (!$mime) $mime = "application/octet-stream";
  header("Content-Type: $mime");
  header("Content-Length: $size");
  header("Content-Disposition: inline; filename=\"" . _html(basename($path)) . "\"");
  header("Vary:");
  readfile($path);
  exit;
}
else
{
  // no path specified -> generate root menu
  echo "<h2>Useful files to edit</h2>\n";
  $paths = array(
    ADMIN_DIR . "/results_header.txt",
    WWW_DIR . "/template.html"
  );
  array_push($paths, ... glob(ADMIN_DIR . "/slideviewer/custom.*"));
  array_push($paths, ... glob(WWW_DIR . "/*/*"));  // subdirectories of www_party
  $paths = array_filter($paths, 'istextfile');
  put_absolute_path_picker($paths);

  echo "<h2>Browse directories</h2>\n";
  $paths = array(
    ADMIN_DIR, WWW_DIR,
    $settings["private_ftp_dir"],
    $settings["public_ftp_dir"],
    $settings["screenshot_dir"]
  );
  put_absolute_path_picker($paths);
}

?>
