<?php
/*
Plugin name: Entry List
Description: Full list of all compo entries, sortable by various criteria.
*/
if (!defined("ADMIN_DIR")) exit();


function entrylist_addhead()
{
?>
<style type="text/css">
#orderoptions {
  margin-bottom: 1em;
}
#currentorder {
  color: white;
  font-weight: bold;
}
#entrylist td, #entrylist th {
  text-align: left;
  white-space: nowrap;
}
#entrylist tr.entry td:nth-child(1),
#entrylist tr.entry td:nth-child(5) {
  text-align: right;
}
#entrylist .author {
  font-weight: bold;
}
#entrylist .status {
  display: inline-block;
  padding: 0 .5em 0 .5em;
  border-radius: 5px;
  color: black;
}
#entrylist .status + .status {
  margin-left: .5em;
}
.status.orgacomment {
  background: #842;
}
.status.feedback {
  background: #482;
}
</style>
<?php
}
add_hook("admin_head","entrylist_addhead");


function entrylist_addmenu( $data )
{
  $data["links"]["pluginoptions.php?plugin=entrylist"] = "Entry List";
}

add_hook("admin_menu","entrylist_addmenu");
?>
