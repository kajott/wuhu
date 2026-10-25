<?php
/*
Plugin name: Single page voting
Description: Separates the voting page by compos instead of one big list with all of them
*/
if (!defined("ADMIN_DIR")) exit();

function singlepagevoting_dbquery( $data )
{
  global $settings;
  if ($settings['voting_type'] == 'range')
  {
    $nvotes = array();
    $query = new SQLSelect();
    $query->AddTable("votes_range");
    $query->AddGroup("compoid");
    $query->AddField("compoid");
    $query->AddField("COUNT(*) AS count");
    $query->AddWhere(sprintf_esc("userid=%d", get_user_id()));
    $query->AddWhere("vote > 0");
    foreach (SQLLib::selectRows($query->GetQuery()) as $r)
      $nvotes[$r->compoid] = $r->count;

    $nentries = array();
    $query = new SQLSelect();
    $query->AddTable("compoentries");
    $query->AddGroup("compoid");
    $query->AddField("compoid");
    $query->AddField("COUNT(*) AS count");
    run_hook("vote_compo_dbquery",array("query"=>&$query));
    foreach (SQLLib::selectRows($query->GetQuery()) as $r)
      $nentries[$r->compoid] = $r->count;
  }

  $query = &$data["query"];
  $compos = SQLLib::selectRows( $query->GetQuery() );
  if ($compos)
  {
    printf("<ul id='votecompolist'>\n");
    foreach($compos as $compo)
    {
      $extra = "";
      if ($settings['voting_type'] == 'range')
      {
        $v = intval(@$nvotes[$compo->id]);
        $e = intval(@$nentries[$compo->id]);
        if      ($v < 1)   $extra = " <small class='none'>(no votes yet)</small>";
        else if ($v >= $e) $extra = " <small class='all'>(all entries voted)</small>";
        else               $extra = " <small class='some'>($v/$e entries voted)</small>";
      }
      printf("  <li><a href='%s'>%s</a>%s</li>\n",build_url("Vote",array("compo"=>$compo->id)),_html($compo->name), $extra);
    }
    printf("</ul>\n");
  }
  else
  {
    printf("No compos found open for voting!");
  }

  if (@$_GET["compo"])
    $query->AddWhere(sprintf_esc("id = %d",$_GET["compo"]));
  else
    $query->AddWhere("1=3");
}

add_hook("vote_prepare_dbquery","singlepagevoting_dbquery");

?>
