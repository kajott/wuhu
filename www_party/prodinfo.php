<?php  // this file is required for the prodinfo_popup plugin
include_once("database.inc.php");
include_once(ADMIN_DIR . "/bootstrap.inc.php");

start_wuhu_session();

$eid = intval(@$_GET["id"]);
if (!$eid) { http_response_code(400); exit; }

$entry = SQLLib::selectRow(sprintf_esc("select * from compoentries where id = %d", $eid));
if(!$entry) { http_response_code(404); exit; }

$compo = SQLLib::selectRow(sprintf_esc("select * from compos where id = %d",$entry->compoid));
if (get_user_id() != $entry->userid && $compo->votingopen == 0) { http_response_code(401); exit; }
?>

  <p class="compo_row">
    <span class="compo"><?=_html($compo->name);?></span>
    <span class="entry_label">Entry</span>
    <span class="entrynumber">#<?=$entry->playingorder;?></span>
  </p>
<? if ($compo->screenshot && get_compoentry_screenshot_path($eid) !== FALSE) { ?>
  <p class="screenshot_row">
    <a href="/screenshot.php?id=<?=$eid;?>" target="_blank">
      <img class="screenshot" src="/screenshot.php?id=<?=$eid;?>" alt="screenshot">
    </a>
  </p>
<? } ?>
  <p class="title_row">
    <strong class="title_label">Title:</strong>
    <span class="title"><?=_html($entry->title);?></span>
  </p>
  <p class="author_row">
    <strong class="author_label">Author:</strong>
    <span class="author"><?=_html($entry->author);?></span>
  </p>
<? if ($compo->hasplatform) { ?>
  <p class="platform_row">
    <strong class="platform_label">Platform:</strong>
    <span class="platform"><?=_html($entry->platform);?></span>
  </p>
<? } ?>
<? if ($entry->comment) { ?>
  <p class="comment_row">
    <strong class="comment_label">Comment:<br></strong>
    <span class="comment"><?=str_replace("\n","<br>",_html($entry->comment));?></span>
  </p>
<? } ?>
