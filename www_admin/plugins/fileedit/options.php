<?php
if (!defined("PLUGINOPTIONS")) exit();

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
  echo "<p>Select file to edit:</p><ul>\n";
  echo "<li><a href=\"pluginoptions.php?plugin=fileedit&file=../www_party/template.html\">visitor website template</a></li>\n";
  echo "<li><a href=\"pluginoptions.php?plugin=fileedit&file=slideviewer/custom.css\">slideviewer CSS</a></li>\n";
  echo "<li><a href=\"pluginoptions.php?plugin=fileedit&file=results_header.txt\">results file header</a></li>\n";
  echo "</ul>\n";
}

?>
