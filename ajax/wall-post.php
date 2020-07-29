<?php

header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check do we have all needed GET data
$post = 0;
if(isset($_GET['p']) && is_numeric($_GET['p'])){
	$p = intval($_GET['p']);
	if($p > 0){ $post = $p; }
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

$ex_top = <<<E
<link rel="stylesheet" href="css/jquery.fancybox3.min.css" type="text/css" media="screen" />
<style type="text/css">
body {padding-top:10px;margin-bottom:10px;}
</style>
E;

print $skin->header_ajax(array('extend'=>$ex_top));

print <<<E
<div class="wall-body">
    <div class="container" id="wall-list">
E;

$r = $db->query("SELECT * FROM vk_wall WHERE `id` = {$post}");
while($row = $db->return_row($r)){

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
		print $f->wall_show_post($row,'single',$repost_body,$vk_session);

} // End of while

print <<<E
          </div>
</div>
<script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/freewall.js"></script>
<script type="text/javascript" src="js/jquery.fancybox3.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {

	$(".free-wall").each(function(){
		var wall = new Freewall(this);
		
		wall.reset({
			selector: '.brick',
			animate: false,
			cellW: {$cfg['wall_layout_width']},
			cellH: 'auto',
			onResize: function() {
				wall.fitWidth();
			}
		});
		
		var images = wall.container.find('.brick');
		images.find('img').load(function() {
			wall.fitWidth();
		});
		
	});
	
	$(".fancybox").fancybox({
		padding : 5,
		arrows : false,
		closeBtn : false,
		nextClick : true,
		loop : false,
		keys : {
			toggle : [32], // space - toggle fullscreen
			play : [70]
		},
	    helpers : {
			overlay : {
				css : {
	               'background' : 'rgba(0, 0, 0, 0.85)'
	            }
			},
			buttons : {}
	    }
	});
	
	$(".various").fancybox({
		maxWidth	: 1280,
		maxHeight	: 720,
		fitToView	: false,
		width		: '90%',
		height		: '90%',
		autoSize	: false,
		closeClick	: false,
		openEffect	: 'none',
		closeEffect	: 'none',
		
		padding : 5,
		arrows : false,
		closeBtn : false,
		nextClick : false,
		loop : false,
	    helpers : {
			overlay : {
				css : {
	               'background' : 'rgba(0, 0, 0, 0.85)'
	            }
			},
			title: {
				type: 'inside'
			}
	    },
		beforeLoad: function() {
            var el, id = $(this.element).data('title-id');
	
            if (id) {
                el = $('#' + id);
            
                if (el.length) {
                    this.title = el.html();
                }
            }
        }
	});
	
	$(".full-date").tooltip();
});

	function expand_desc(){
		var el = jQuery(".fancybox-title-inside-wrap");
		if(el.css("height") == '40px'){
			el.css("height","auto");
			jQuery(".fancybox-title-inside-wrap > .expander").html("свернуть");
		} else {
			el.css("height","40px");
			jQuery(".fancybox-title-inside-wrap > .expander").html("показать");
		}
	}
</script>
</body>
</html>
E;

$db->close($res);

?>