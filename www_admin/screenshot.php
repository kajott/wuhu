<?php
//error_reporting(E_ALL);
include_once("database.inc.php");
include_once(ADMIN_DIR . "/bootstrap.inc.php");

session_cache_limiter("");  // don't let PHP interfere with our caching headers

start_wuhu_session();

$s = SQLLib::selectRow(sprintf_esc("select * from compoentries where id = %d",$_GET["id"]));
if(!$s) exit;

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
  if ($cached) { exit; }

  // if we arrive here, the image hasn't been cached and needs to be sent in full
  $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
  $mime = @$MIMETYPES[$ext];
  if ($mime) { header("Content-Type: " . $mime); }
  header("Content-Length: " . $size);
  readfile($fn);
  exit;
}

$a = @$_GET["show"]=="thumb" ? get_compoentry_screenshot_thumb_path( $_GET["id"] ) : get_compoentry_screenshot_path( $_GET["id"] );
if ($a && file_exists($a))
{
  serve_file($a);
}
else
{
  if (@$_GET["show"]=="thumb")
  {
    $path = ADMIN_DIR . "/noscreenshot-".(int)$settings["screenshot_sizex"]."x".(int)$settings["screenshot_sizey"].".png";
    if (!file_exists($path))
    {
      thumbnail( ADMIN_DIR . "/noscreenshot.png",$path,$settings["screenshot_sizex"],$settings["screenshot_sizey"]);
    }
    serve_file($path);
  }
  else
  {
    serve_file(ADMIN_DIR . "/noscreenshot.png");
  }
}
?>
