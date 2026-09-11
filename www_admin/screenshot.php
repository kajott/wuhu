<?php
//error_reporting(E_ALL);
include_once("database.inc.php");
include_once(ADMIN_DIR . "/bootstrap.inc.php");

$MIMETYPES = array(
  "gif"  => "image/gif",
  "png"  => "image/png",
  "jpg"  => "image/jpeg",
  "jpeg" => "image/jpeg"
  // Wuhu doesn't accept other image formats, so we should be fine with these
);

// serve an image file, with caching
function serve_file($fn)
{
  global $MIMETYPES;
  if (!file_exists($fn)) { http_response_code(404); die; }

  // get local file statistics
  $mtime = filemtime($fn);
  $size  = filesize($fn);
  $etag  = '"' . dechex($mtime) . '-' . dechex($size) . '"';

  // get (and parse) server's cached statistics
  $sv_etag = @$_SERVER["HTTP_IF_NONE_MATCH"];
  if ($sv_etag) { $sv_etag = str_replace("W/", "", trim($sv_etag)); }
  $sv_time = @$_SERVER["HTTP_IF_MODIFIED_SINCE"];
  if ($sv_time) { $sv_time = strtotime($sv_time); }

  // do any of these match?
  $cached = ($sv_etag && (($sv_etag == $etag) || ($sv_etag == ("W/" . $etag))))
         || ($sv_time && ($sv_time == $mtime));

  // send caching headers
  if ($cached) { http_response_code(304); }
  header("ETag: " . $etag);
  header("Last-Modified: " . gmdate('D, d M Y H:i:s', $mtime) . " GMT");
  header("Cache-Control: public, max-age=3600");
  header_remove("Pragma");
  if ($cached) { exit; }

  // if we arrive here, the image hasn't been cached and needs to be sent in full
  $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
  $mime = @$MIMETYPES[$ext];
  if ($mime) { header("Content-Type: " . $mime); }
  header("Content-Length: " . $size);
  readfile($fn);
  exit;
}

$entry = intval(@$_GET["id"]);
$thumb = (@$_GET["show"] == "thumb");
if ($entry)
{
  // screenshot of specific entry requested
  $fn = $thumb ? get_compoentry_screenshot_thumb_path($entry) : get_compoentry_screenshot_path($entry);
  if ($fn && file_exists($fn)) {
    serve_file($fn);
  }
  else
  {
    // screenshot doesn't exist -> redirect to "no screenshot" image
    header("Location: " . $_SERVER['PHP_SELF'] . "?show=" . ($thumb ? "thumb" : "full"));
    exit;
  }
}
else
{
  // no or invalid entry ID -> serve "no screenshot" image
  $src = ADMIN_DIR . "/noscreenshot.png";
  if ($thumb)
  {
    $sx = intval($settings['screenshot_sizex']);
    $sy = intval($settings['screenshot_sizey']);
    $fn = ADMIN_DIR . "/noscreenshot-{$sx}x{$sy}.png";
    if (!file_exists($fn))
    {
      thumbnail($src, $fn, $sx, $sy);
    }
    serve_file($fn);
  }
  else
  {
    serve_file($src);
  }
}
?>
