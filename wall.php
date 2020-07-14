<?php

header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once('./cfg.php');
if(isset($_GET['_pjax']) || isset($_POST['_pjax'])){ $cfg['pj'] = true; }

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

// Get local counters for top menu
$lc = $db->query_row("SELECT * FROM vk_counters");

if(!$cfg['pj']){
	print $skin->header(array('extend'=>''));
	print $skin->navigation($lc);
}

print <<<E
<div class="nav-scroller bg-white box-shadow mb-4" style="position:relative;">
    <nav class="nav nav-underline">
		<span class="nav-link active"><i class="far fa-newspaper"></i> Стена</span>
    </nav>

E;

$chrono = $db->query_row("SELECT min(date) as start, max(date) as end FROM vk_wall WHERE date > 0");

print <<<E
    </nav>
</div>

<div class="container wall-body">
	<div class="row">
		  <div class="col-sm-3">
			<div class="wall-box">
			<h5>Фильтр</h5>
<div class="input-group mb-2 wall-qsearch">
  <div class="input-group-prepend">
    <span class="input-group-text" id="qsearch-addon1"><i class="fas fa-search"></i></span>
  </div>
  <input type="text" value="" id="qsearch" class="form-control form-control-sm" placeholder="Быстрый поиск..." aria-label="Быстрый поиск..." aria-describedby="qsearch-addon1" />
</div>
<label>Дата:</label>
<div class="input-group mb-2">
  <div class="input-group-prepend">
    <span class="input-group-text" id="date_a-addon1"><i class="fas fa-calendar"></i></span>
  </div>
	<input type="text" value="" id="date_a" class="form-control form-control-sm" placeholder="от" aria-label="от" aria-describedby="date_a-addon1" />
</div>
<div class="input-group mb-2">
  <div class="input-group-prepend">
    <span class="input-group-text" id="date_b-addon1"><i class="fas fa-calendar"></i></span>
  </div>
	<input type="text" value="" id="date_b" class="form-control form-control-sm" placeholder="до" aria-label="до" aria-describedby="date_b-addon1" />
</div>

			</div>
		  </div>
          <div class="col-sm-9" id="wall-list">
E;

	$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? intval($_GET['page']) : 0;
	$npage = $page+1;
	$offset_page = ($page > 0) ? $cfg['perpage_wall']*$page : 0;

$r = $db->query("SELECT * FROM vk_wall WHERE is_repost = 0 ORDER BY date DESC LIMIT {$offset_page},{$cfg['perpage_wall']}");
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
	print $f->wall_show_post($row,false,$repost_body,$vk_session);
	
} // End of while

print <<<E
			<div class="paginator-next" style="display:none;"><span class="paginator-val">{$npage}</span><a href="ajax/wall-paginator.php?page={$npage}">следующая страница</a></div>
          </div>
</div>
E;

$ex_bot = <<<E
<script type="text/javascript">
$(document).ready(function() {
	var notload = false;
	var list = jQuery("#wall-list");

	// Default options
	var page = 1;
	var date_a = 'any';
	var date_b = 'any';
	var qsearch = '';
	var freewall_width = {$cfg['wall_layout_width']};

	// Hash URL commands
	urlCommands.bind('post', function(id) {
		if($.isNumeric(id)){
			$.fancybox.open({
				src : 'ajax/wall-post.php?p='+id+'',
				type : 'iframe',
				maxWidth	: 960,
				//maxHeight	: 720,
				fitToView	: false,
				width		: '90%',
				height		: '90%',
				autoSize	: true,
				closeClick	: false,
				openEffect	: 'none',
				closeEffect	: 'none',
				
				padding : 5,
				arrows : false,
				closeBtn : true,
				nextClick : false,
				loop : false,
				helpers : {
					overlay : {
						showEarly : false,
						css : {
							'background' : 'rgba(0, 0, 0, 0.85)'
						}
					},
					title: {
						type: 'inside'
					}
				}
			});
		}
	});
	
	urlCommands.bind('qsearch', function(id) { qsearch = id; jQuery("#qsearch").val(urldecode(id)); });
	urlCommands.bind('date_a', function(id) { date_a = id; /* set date back */ });
	
	// If not a default options -> reload
	if(date_a != 'any' || date_b != 'any' || qsearch != ''){
		console.log("Not default options -> reload");
		urlCommands.urlPush({page:0});
		ajax_page_reload('wall',new_vars(0));
	}
	
	jQuery("#qsearch").on('change', function(){
		urlCommands.urlPush({qsearch:this.value});
		if(qsearch != this.value){
			qsearch = this.value;
			console.log(this.value);
			urlCommands.urlPush({page:0});
			ajax_page_reload('wall',new_vars(0));
		}
	});
	
	// Datepicker
	$('#date_a').datepicker({
		dateFormat: 'yyyy-mm-dd',
		minDate: new Date({$chrono['start']} * 1000),
		maxDate: new Date({$chrono['end']} * 1000),
		startDate: new Date({$chrono['start']} * 1000),
		onSelect: function(formattedDate, date, inst){
			var utime = new Date(date) / 1000;
			urlCommands.urlPush({date_a:utime});
			if(date_a != utime){
				date_a = utime;
				console.log(utime);
				urlCommands.urlPush({page:0});
				ajax_page_reload('wall',new_vars(0));
			}
		}
	});
	$('#date_b').datepicker({
		dateFormat: 'yyyy-mm-dd',
		minDate: new Date({$chrono['start']} * 1000),
		maxDate: new Date({$chrono['end']} * 1000),
		onSelect: function(formattedDate, date, inst){
			var utime = new Date(date) / 1000;
			urlCommands.urlPush({date_b:utime});
			if(date_b != utime){
				date_b = utime;
				console.log(utime);
				urlCommands.urlPush({page:0});
				ajax_page_reload('wall',new_vars(0));
			}
		}
	});
	
	/*
		Returns a GET string for ajax request with current options
		Input: (int)page
	*/
	function new_vars(page){
		console.log("NewVars: "+"?page="+page+"&date_a="+date_a+"&date_b="+date_b+"&qsearch="+qsearch);
		return "?page="+page+"&date_a="+date_a+"&date_b="+date_b+"&qsearch="+qsearch;
	}
	
	/*
		Decode urlencoded value
	*/
	function urldecode(str) {
		//return decodeURIComponent((str+'').replace(/\+/g, '%20'));
			return decodeURIComponent((str + '').replace(/%(?![\da-f]{2})/gi, function () {
			// PHP tolerates poorly formed escape sequences
				return '%25'
			}).replace(/\+/g, '%20'))
	}
	
	if(notload == false){
		console.log("Fresh page call");
		
	}
	apr_jscroller('wall',list);
	
	$(".full-date").tooltip();
	
	// Autoplay gif's
	$(".doc-gif").each(
		function(){
			if($(this).visible()){
				$(this).attr("src",$(this).attr("data-docsrc"));
			} else {
				$(this).attr("src",$(this).attr("data-docpre"));
			}
		}
	);
	
});

// Autoplay gif's after scrolling
$(window).scroll($.debounce( 250, true, function(){
} ) );
$(window).scroll($.debounce( 250, function(){
	$(".doc-gif").each(
		function(){
			if($(this).visible()){
				$(this).attr("src",$(this).attr("data-docsrc"));
			} else {
				$(this).attr("src",$(this).attr("data-docpre"));
			}
		}
	);
} ) );

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
E;

if(!$cfg['pj']){
	print $skin->footer(array('extend'=> $ex_bot));
} else {
	print $ex_bot;
}

$db->close($res);

?>