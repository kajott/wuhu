<?php
/*
Plugin name: File Editor
Description: Edit the HTML template and slideviewer CSS directly from the admin website.
*/
if (!defined("ADMIN_DIR")) exit();

function fileedit_addmenu( $data )
{
  $data["links"]["pluginoptions.php?plugin=fileedit"] = "File Editor";
}

add_hook("admin_menu","fileedit_addmenu");
?>
