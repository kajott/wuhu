<?php
/*
Plugin name: Entry download
Description: Allows users to download entries from the voting pages
*/

function entrydownload_ui($data)
{
  echo "<div class='entrydownload'>\n";
  $e = $data["entry"];
  $ext = _html(pathinfo($e->filename, PATHINFO_EXTENSION));
  echo "<a href='entrydownload.php?c={$e->compoid}&o={$e->playingorder}'>download (.$ext)</a>\n";
  echo "</div>\n";
}

add_hook("vote_entry_endform","entrydownload_ui");
?>
