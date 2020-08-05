<?php

header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check do we have all needed GET data
$video = 0;
if(isset($_GET['id']) && is_numeric($_GET['id'])){
	$id = intval($_GET['id']);
	if($id > 0){ $video = $id; }
}
$oid = 0;
if(isset($_GET['oid']) && is_numeric($_GET['oid'])){
	$oid  = intval($_GET['oid']);
}

require_once('../cfg.php');

// Get DB
require_once(ROOT.'classes/db.php');
$db = new db();
$res = $db->connect($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['base']);

// Get Skin
require_once(ROOT.'classes/skin.php');
$skin = new skin();

// Get Functions
require_once(ROOT.'classes/func.php');
$f = new func();

$ex_top = <<<E
<link rel="stylesheet" href="css/plyr.css" type="text/css" media="screen" />
<style type="text/css">
body {padding:0px;margin:0px;overflow:hidden;}
.wall-box{margin-bottom:0;}
</style>
E;

print $skin->header_ajax(array('extend'=>$ex_top));

print <<<E
<div class="col-sm-12 wall-body" style="padding:0;">
    <div class="col-sm-12" id="wall-list" style="padding:0;">
E;

$list = $db->query_row("SELECT * FROM vk_videos WHERE `id` = {$video} AND `owner_id` = {$oid}");

// Rewrite if you plan to store content outside of web directory and will call it by Alias
if($cfg['vhost_alias'] == true && substr($list['local_path'],0,4) != 'http'){
	$list['local_path'] = preg_replace("/^\//","",$f->windows_path_alias($list['local_path'],'video'));
}
if($cfg['vhost_alias'] == true && substr($list['preview_path'],0,4) != 'http'){
	$list['preview_path'] = preg_replace("/^\//","",$f->windows_path_alias($list['preview_path'],'video'));
}
$list['title'] = trim(preg_replace('/\"/','\\"',$list['title']));
$plyr_src = '';
if($list['local_format'] == 'flv') {
	$plyr_src = '<source src="/'.$list['local_path'].'" type="video/x-flv">'; }
if($list['local_format'] == 'webm'){
	$plyr_src = '<source src="/'.$list['local_path'].'" type="video/webm">'; }
if($list['local_format'] == 'mp4') {
	$plyr_src = '<source src="/'.$list['local_path'].'" type="video/mp4">'; }

$plyr_opt_autoplay = 'false';
$opt = $db->query_row("SELECT val FROM vk_status WHERE `key` = 'start-local-video'");
if($opt['val'] == 1){
	$plyr_opt_autoplay = 'true';
}

print <<<E

<video poster="/{$list['preview_path']}" id="vkbk-player" playsinline controls>
	{$plyr_src}
</video>

          </div>
</div>
<script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
<script type="text/javascript" src="js/plyr.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	
	const player = new Plyr('#vkbk-player', {
		debug: false,
		controls : ['play-large', 'restart', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'fullscreen'], //'rewind','fast-forward',
		settings : ['speed', 'loop'], 
		autoplay: {$plyr_opt_autoplay},
		clickToPlay: false,
		volume: 1,
		muted: false
	});
	
});
</script>
</body>
</html>
E;

$db->close($res);

?>