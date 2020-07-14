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
		<span class="nav-link active"><i class="fa fa-film"></i> Видео</span>
<div class="input-group input-group-sm w-25 nav-item mt-2 ml-auto video-qsearch">
  <div class="input-group-prepend">
    <span class="input-group-text" id="qsearch-addon1"><i class="fas fa-search"></i></span>
  </div>
  <input type="text" value="" id="qsearch" class="form-control form-control-sm" placeholder="Быстрый поиск..." aria-label="Быстрый поиск..." aria-describedby="qsearch-addon1" />
</div>
		<button type="button" class="btn btn-link ml-auto mr-4 video-filter-btn"><i class="fa fa-filter"></i></button>
    </nav>

<div class="col-sm-4 white-box video-filter-box">
	<h6><i class="fa fa-filter"></i> Фильтр</h6>
	<div class="row">
	<label for="type">Тип</label>
	<select class="selectpicker show-tick" name="type" id="f-type">
		<option value="all">Любой</option>
		<option data-icon="fa-globe" value="online">Только онлайн</option>
		<option data-icon="fa-hdd" value="local">Только локальные</option>
	</select>
	</div>
	<div class="row">
	<label for="service">Сервис</label>
	<select class="selectpicker show-tick" name="service" id="f-service">
		<option value="any">Все видео</option>
		<option data-icon="fa-youtube" value="yt">Youtube</option>
		<option data-icon="fa-vk" value="vk">VK.com</option>
	</select>
	</div>
	<div class="row">
	<label for="quality">Качество</label>
	<select class="selectpicker show-tick" name="quality" id="f-quality">
		<option value="0">Любое</option>
		<option value="1080">1080p</option>
		<option value="720">720p</option>
		<option value="480">480p</option>
		<option value="360">360p</option>
		<option value="240">240p</option>
	</select>
	</div>
	<div class="row">
	<label for="length">Длительность</label>
	<select class="selectpicker show-tick" name="length" id="f-length">
		<option value="0">Любая</option>
		<option value="5">до 5 мин</option>
		<option value="15">15+ мин</option>
		<option value="30">30+ мин</option>
		<option value="60">60+ мин</option>
	</select>
	</div>
	<div class="row">
	<label for="date">Дата</label>
	<select class="selectpicker show-tick" name="date" id="f-date">
		<option value="new">Сначала новые</option>
		<option value="old">Сначала старые</option>
	</select>
	</div>
</div>

</div>

<div class="container">
          <div class="container" id="video-list">
E;

	$play = $db->query_row("SELECT val as local FROM vk_status WHERE `key` = 'play-local-video'");

	$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? intval($_GET['page']) : 0;
	$npage = $page+1;
	$offset_page = ($page > 0) ? $cfg['perpage_video']*$page : 0;

	mb_internal_encoding("UTF-8");
$r = $db->query("SELECT * FROM vk_videos WHERE preview_path != '' ORDER BY date_added DESC LIMIT {$offset_page},{$cfg['perpage_video']}");
while($row = $db->return_row($r)){
	// Rewrite if you plan to store content outside of web directory and will call it by Alias
	if($cfg['vhost_alias'] == true && substr($row['preview_path'],0,4) != 'http'){
		$row['preview_path'] = $f->windows_path_alias($row['preview_path'],'video');
	}
	
	// Clean player
	$row['player_uri'] = $f->clean_player($row['player_uri']);
	
	$row['stitle'] = $row['title'];
	if(mb_strlen($row['title']) > 38){ $row['stitle'] = mb_substr($row['title'],0,38).'...'; }
	if($row['desc'] != ''){ $row['desc'] = nl2br($row['desc']); }
	$row['duration'] = $skin->seconds2human($row['duration']);
print <<<E
<div class="col-sm-4">
<div class="white-box">
	<div class="video-preview" style="background-image:url('{$row['preview_path']}');">
E;
	if($row['local_path'] != '' && $play['local'] == 1){
		// Local Player
print <<<E
		<a class="various-localz" href="javascript:;" onclick="javascript:fbox_video_global('ajax/local-video.php?id={$row['id']}&oid={$row['owner_id']}',1);"><span class="play-icon"><i class="fa fa-play"></i></span></a>
E;
	} else {
		// Remote Player
print <<<E
		<a class="various-localz" href="javascript:;" data-title-id="title-{$row['id']}-{$row['owner_id']}" onclick="javascript:fbox_video_global('{$row['player_uri']}',1);"><span class="play-icon"><i class="fa fa-play"></i></span></a>
E;
	}
print <<<E
		<span class="badge bg-light">{$row['duration']}</span>
	</div>
	<div class="video-info">
		<div class="video-title tip" data-placement="top" data-toggle="tooltip" data-original-title="{$row['title']}" onclick="javascript:show_details({$row['id']},{$row['owner_id']});"><i class="fa fa-info-circle"></i> | {$row['stitle']} </div>
		<div class="video-status">
E;

	// Show icon for known services
	$service = false;

	// Youtube
	if(strstr($row['player_uri'],'youtube.com') || strstr($row['player_uri'],'youtu.be')){
		$service = true;
		print '<i class="fab fa-youtube" style="color:red;"></i> ';
		if($row['local_path'] != ''){
			print ' | Копия: <i class="fa fa-check-square" style="color:#4caf50;"></i> ';
		} else {
			preg_match("/embed\/([^\?]+)\?/",$row['player_uri'],$pu);
			$key = $pu[1];
			print ' | Копия: <i class="fa fa-check-square"></i> <a href="ytget.php?id='.$row['id'].'&oid='.$row['owner_id'].'&key='.$key.'&s=yt" target="_blank">скачать?</a>';
		}
	}
	// Vkontakte
	if(strstr($row['player_uri'],'vk.com')) {
		$service = true;
		print '<i class="fab fa-vk" style="color:#517397;"></i> ';
		if($row['local_path'] != ''){
			print ' | Копия: <i class="fa fa-check-square" style="color:#4caf50;"></i> ';
		} else {
			preg_match("/oid\=([\-0-9]+)\&id\=([\-0-9]+)/",$row['player_uri'],$pu);
			$key = $pu[1].'_'.$pu[2];
			print ' | Копия: <i class="fa fa-check-square"></i> <a href="ytget.php?id='.$row['id'].'&oid='.$row['owner_id'].'&key='.$key.'&s=vk" target="_blank">скачать?</a>';
		}
	}
	
	if($service == false){
		print '<i class="fa fa-film"></i>';
	}

print <<<E
		</div>
E;

	if($play['local'] == 0){
print <<<E
		<div id="title-{$row['id']}-{$row['owner_id']}" style="display:none;">
			{$row['desc']}
			<div class="expander" onClick="expand_desc();">показать</div>
		</div>
E;
	}

print <<<E
	</div>
</div></div>
E;
}

print <<<E
			<div class="paginator-next" style="display:none;"><span class="paginator-val">{$npage}</span><a href="ajax/videos-paginator.php?page={$npage}">следующая страница</a></div>
          </div>
</div>
E;

// Fancybox Options
$fancybox_options = <<<E
		fitToView	: false,
		autoSize	: false,
		closeClick	: false,
		openEffect	: 'none',
		closeEffect	: 'none',
		
		padding : 0,
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
E;

$ex_bot = <<<E
<script type="text/javascript" src="js/jquery.jscroll.js"></script>
<script type="text/javascript" src="js/bootstrap-select.min.js"></script>
<script type="text/javascript" src="js/hashnav.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	var notload = false;
	var list = jQuery("#video-list");
	
	// Default options
	var page = 1;
	var type = 'all';
	var service = 'any';
	var quality = 0;
	var length = 0;
	var date = 'new';
	var qsearch = '';
	
	// Bootstrip select
	$('.selectpicker').selectpicker({
		iconBase: 'fa',
		tickIcon: 'fa-check'
	});
	
	jQuery('.video-filter-btn').click(function(){ jQuery('.video-filter-box').show(); });
	
	// Hash URL commands
	urlCommands.bind('type', function(id) { type = id; jQuery("#f-type").selectpicker('val',id); });
	urlCommands.bind('service', function(id) { service = id; jQuery("#f-service").selectpicker('val',id); });
	urlCommands.bind('quality', function(id) { quality = id; jQuery("#f-quality").selectpicker('val',id); });
	urlCommands.bind('length', function(id) { length = id; jQuery("#f-length").selectpicker('val',id); });
	urlCommands.bind('date', function(id) { date = id; jQuery("#f-date").selectpicker('val',id); });
	urlCommands.bind('qsearch', function(id) { qsearch = id; jQuery("#qsearch").val(urldecode(id)); });
	
	// Not default options -> reload
	if(type != 'all' || service != 'any' || quality != 0 || length != 0 || date != 'new' || qsearch != ''){
		urlCommands.urlPush({page:0});
		ajax_page_reload('video',video_vars(0));
	}
	
	urlCommands.bind('page', function(id) {
		if($.isNumeric(id) && id >= 2){
			notload = true;
			for(i=2;i<=id;i++){
				jQuery.ajax({
					async : false,
					method : "GET",
					url : paginator_video+video_vars(i)
				}).done( function(data){
					jQuery(".paginator-next").remove();
					list.append(data);
				});
			}
			notload = false;
		}
	});
	
	// If filter command changed, update url and reload data with new filter
	jQuery("#f-type").on('change', function(){
		urlCommands.urlPush({type:this.value});
		if(type != this.value){
			type = this.value;
			urlCommands.urlPush({page:0});
			ajax_page_reload('video',video_vars(0));
		}
	});
	jQuery("#f-service").on('change', function(){
		urlCommands.urlPush({service:this.value});
		if(service != this.value){
			service = this.value;
			urlCommands.urlPush({page:0});
			ajax_page_reload('video',video_vars(0));
		}
	});
	jQuery("#f-quality").on('change', function(){
		urlCommands.urlPush({quality:this.value});
		if(quality != this.value){
			quality = this.value;
			urlCommands.urlPush({page:0});
			ajax_page_reload('video',video_vars(0));
		}
	});
	jQuery("#f-length").on('change', function(){
		urlCommands.urlPush({length:this.value});
		if(length != this.value){
			length = this.value;
			urlCommands.urlPush({page:0});
			ajax_page_reload('video',video_vars(0));
		}
	});
	jQuery("#f-date").on('change', function(){
		urlCommands.urlPush({date:this.value});
		if(date != this.value){
			date = this.value;
			urlCommands.urlPush({page:0});
			ajax_page_reload('video',video_vars(0));
		}
	});
	jQuery("#qsearch").on('change', function(){
		urlCommands.urlPush({qsearch:this.value});
		if(qsearch != this.value){
			qsearch = this.value;
			console.log(this.value);
			urlCommands.urlPush({page:0});
			ajax_page_reload('video',video_vars(0));
		}
	});
	
	function video_vars(page){
		return "?page="+page+"&type="+type+"&service="+service+"&quality="+quality+"&length="+length+"&date="+date+"&qsearch="+qsearch;
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
		apr_jscroller('video',list);
	}
	
	$(".tip").tooltip();
});

$(document).mouseup(function (e){
	var container = $(".video-filter-box");
	if (!container.is(e.target) // if the target of the click isn't the container...
    && container.has(e.target).length === 0) // ... nor a descendant of the container
	{
		container.hide();
		container.unbind( 'click' );
	}
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
	
	// Load details of video
	function show_details(id,oid){
		jQuery.ajax({
			async : false,
			method : "GET",
			url : "ajax/video-details.php?id="+id+"&oid="+oid
		}).done( function(data){
			jQuery("#pj-content").append(data);
		});
	}
	// Kill details
	function hide_details(){
		jQuery("#video-details").remove();
		jQuery("#video-details-bg").remove();
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