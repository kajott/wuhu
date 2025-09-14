<?php
/*
Plugin name: Custom link
Description: Adds a new page type that allows to provide custom HTML for the *link* instead of the resulting page.
*/

function customlink_activation() {
  $r = SQLLib::selectRow("SHOW COLUMNS FROM intranet_toc WHERE field='type'");
  $t = $r->Type;
  if (!strstr($t, "customlink") && (substr($t, 0, 5) == "enum(")) {
    $t = substr($t, 0, strlen($t) - 1) . ",'customlink')";
    SQLLib::Query("ALTER TABLE intranet_toc CHANGE type type $t DEFAULT NULL");
  }
}

add_activation_hook( __FILE__, "customlink_activation" );


function customlink_toc_formdata($data) {
  $data["formdata"]["fields"]["type"]["fields"]["customlink"] = "Custom Link";
}

add_hook("toc_formdata", "customlink_toc_formdata");


function customlink_index_menu_parse($data) {
  $links = [];
  foreach (SQLLib::selectRows("SELECT toc.title, toc.link, page.content FROM intranet_toc toc JOIN intranet_minuswiki_pages page ON toc.title=page.title WHERE toc.type='customlink'") as $l) {
    $links[rawurlencode($l->link)] = str_replace("{%TITLE%}", _html($l->title), $l->content);
  }
  foreach($data["menu"] as &$v) {
    $l = @$links[substr(strstr(strstr($v, "?page="), "'", true), 6)];
    if ($l) { $v = $l; }
  }
}

add_hook("index_menu_parse", "customlink_index_menu_parse");

?>
