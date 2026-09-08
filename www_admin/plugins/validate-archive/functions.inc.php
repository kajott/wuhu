<?php
global $VALIDATEARCHIVE_COMMON;
$VALIDATEARCHIVE_COMMON = array(
  "demo.zip" => "^demo[_.]",
  "intro.zip" => "^intro[_.]",
  "entry.zip" => "^entry[_.]",
  "prod.zip" => "^prod[_.]",
  "release.zip" => "^release[_.]",
  "final.zip" => "^final\.",
  "000123.zip" => "^_*\d+\.",
  "(some_GUID).rar" => "^[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}\.",
  "DSCN0001.jpg" => "^_*d?sc[a-z0-9]\d+_*\.",
  "IMG_0001.jpg" => "^_*(img|pxl)[0-9_]+_*\.",
);
?>
