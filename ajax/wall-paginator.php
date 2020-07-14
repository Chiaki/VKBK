<?php

header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check do we have all needed GET data
$page = 0;
if(isset($_GET['page']) && is_numeric($_GET['page'])){
	$p = intval($_GET['page']);
	if($p > 0){ $page = $p; }
}

require_once('../cfg.php');

// Get DB
require_once(ROOT.'classes/db.php');
$db = new db();
$res = $db->connect($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['base']);

// Get Skin
require_once(ROOT.'classes/skin.php');
$skin = new skin();

// Get session
$q = $db->query("SELECT * FROM vk_session WHERE `vk_id` = 1");
$vk_session = $row = $db->return_row($q);

// Get Functions
require_once(ROOT.'classes/func.php');
$f = new func();

// Filter Options
$options = '';
$date_a = (isset($_GET['date_a'])) ? intval($_GET['date_a']) : 0;
$date_b = (isset($_GET['date_b'])) ? intval($_GET['date_b']) : 0;
$qsearch = (isset($_GET['qsearch'])) ? $db->real_escape($_GET['qsearch']) : '';

// From date X
if($date_a > 0 && $date_b == 0){ $options .= " AND `date` >= ".$date_a; }
// Before date X
if($date_b > 0 && $date_a == 0){ $options .= " AND `date` <= ".$date_b; }
// Between
if($date_a > 0 && $date_b > 0){ $options .= " AND (`date` BETWEEN '".$date_a."' AND '".$date_b."')"; }

if($qsearch != ''){
	$options .= " AND `text` LIKE '%".$qsearch."%'";
}


$offset_page = ($page > 0) ? $cfg['perpage_wall']*$page : 0;
// Get 1 more post to see do we have something on the next page
$perpage = $cfg['perpage_wall']+1;
$next = 0;

$r = $db->query("SELECT * FROM vk_wall WHERE is_repost = 0 {$options} ORDER BY date DESC LIMIT {$offset_page},{$perpage}");
while($row = $db->return_row($r)){
	if($next < $cfg['perpage_wall']){
		$repost_body = '';
		$rrp_body = '';
		
		// Post have a repost?
		if($row['repost'] > 0){
			$rp = $db->query_row("SELECT * FROM vk_wall WHERE id = {$row['repost']} AND owner_id = {$row['repost_owner']}");
			// Post have a rerepost?
			if($rp['repost'] > 0){
				$rrp = $db->query_row("SELECT * FROM vk_wall WHERE id = {$rp['repost']} AND owner_id = {$rp['repost_owner']}");
				$rrp_body = $f->wall_show_post($rrp,true,'',$vk_session);
			}
			$repost_body = $f->wall_show_post($rp,true,$rrp_body,$vk_session);
			
		} // repost body end
		
		// Make post
		print $f->wall_show_post($row,false,$repost_body,$vk_session);
		
	} // End of while perpage body
	// Increase NEXT so if we load a full page we would have in the end NEXT = perpage+1
	// Otherwise if next would be lower or equal perpage there is no result for the next page
	$next++;
} // End of while

if($next > $cfg['perpage_wall']){
	$page++;
	print '<div class="paginator-next" style="display:none;"><span class="paginator-val">'.$page.'</span><a href="ajax/wall-paginator.php?page='.$page.'&date_a='.$date_a.'&date_b='.$date_b.'&qsearch='.$qsearch.'">следующая страница</a></div>';
}

$db->close($res);

?>