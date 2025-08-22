<?php
/*
Plugin name: Backup
Description: Create a backup of the entire Wuhu installation and its data on an external drive plugged into the server.
*/
if (!defined("ADMIN_DIR")) exit();

function backup_addmenu( $data )
{
  $data["links"]["pluginoptions.php?plugin=backup"] = "Backup";
}

add_hook("admin_menu","backup_addmenu");
?>
