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
		<span class="nav-link active"><i class="fa fa-music"></i> Музыка</span>
    </nav>
</div>

<div class="container">

<div id="jquery_jplayer_1" class="jp-jplayer"></div>
<div id="jp_container_1" class="jp-audio" role="application" aria-label="media player">
	<div class="jp-type-playlist">
		<div class="jp-gui jp-interface">
			<div class="jp-controls">
				<button class="jp-previous" role="button" tabindex="0"><i class="fa fa-fw fa-step-backward"></i></button>
				<button class="jp-play" role="button" tabindex="0"><i class="fa fa-fw fa-play jpi-play"></i><i class="fa fa-fw fa-pause jpi-pause" style="display:none;"></i></button>
				<button class="jp-next" role="button" tabindex="0"><i class="fa fa-fw fa-step-forward"></i></button>
				<button class="jp-stop" role="button" tabindex="0"><i class="fa fa-stop"></i></button>
				
				<div class="jp-toggles">
					<button class="jp-repeat tip" data-placement="top" data-toggle="tooltip" data-original-title="повторять" role="button" tabindex="0"><i class="fa fa-fw fa-retweet"></i></button>
					<button class="jp-shuffle tip" data-placement="top" data-toggle="tooltip" data-original-title="перемешать" role="button" tabindex="0"><i class="fa fa-fw fa-random"></i></button>
				</div>
				
				<div class="jp-volume-controls">
					<button class="jp-mute" role="button" tabindex="0"><i class="fa fa-fw fa-volume-off"></i></button>
					<button class="jp-volume-max" role="button" tabindex="0"><i class="fa fa-fw fa-volume-up"></i></button>
					<div class="jp-volume-bar">
						<div class="jp-volume-bar-value"></div>
					</div>
				</div>
			
			</div>
			<div class="jp-progress">
				<div class="jp-seek-bar">
					<div class="jp-play-bar"></div>
				</div>
			</div>
			<div class="jp-time-holder">
				<div class="jp-current-time" role="timer" aria-label="time">&nbsp;</div>
				<div class="jp-duration" role="timer" aria-label="duration">&nbsp;</div>
			</div>
			
			<div class="jp-filters">
				<label for="listsort">Сортировать по: </label>
				<select class="jp-sorter selectpicker show-tick" name="listsort">
					<option data-icon="fa-sort-amount-down" value="deflist"><i class="fa fa-reorder"></i> по умолчанию</option>
					<option data-icon="fa-sort-amount-up" value="reverse"><i class="fa fa-retweet"></i> в обратном порядке</option>
					<optgroup label="Длительность">
						<option data-icon="fa-sort-numeric-up" value="durasc"><i class="fa fa-retweet"></i> сначала короткие</option>
						<option data-icon="fa-sort-numeric-down" value="durdesc"><i class="fa fa-retweet"></i> сначала длинные</option>
					</optgroup>
					<optgroup label="Исполнитель">
						<option data-icon="fa-sort-alpha-up" value="arasc"><i class="fa fa-retweet"></i> Исполнитель A-Z</option>
						<option data-icon="fa-sort-alpha-down" value="ardesc"><i class="fa fa-retweet"></i> Исполнитель Z-A</option>
					</optgroup>
					<optgroup label="Трек">
						<option data-icon="fa-sort-alpha-up" value="ttlasc"><i class="fa fa-retweet"></i> Название A-Z</option>
						<option data-icon="fa-sort-alpha-down" value="ttldesc"><i class="fa fa-retweet"></i> Название Z-A</option>
					</optgroup>
				</select>
E;

// Show albums if they exist
$albums = '';
$r = $db->query("SELECT * FROM vk_music_albums ORDER BY id DESC");
while($row = $db->return_row($r)){
	mb_internal_encoding("UTF-8");
	if(mb_strlen($row['name']) > 25){ $row['name'] = mb_substr($row['name'],0,25).'...'; }
	$albums .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
}

if($albums != ''){
print <<<E
<label for="alblist">Альбом: </label>
<select class="jp-albums selectpicker show-tick" name="alblist">
<option value="0">Все аудиозаписи</option>
{$albums}
</select>
E;
}

print <<<E
			</div>
		</div>
		
		<div class="jp-playlist">
			<ul>
				<li>&nbsp;</li>
			</ul>
		</div>
		<div class="jp-no-solution">
			<span>Update Required</span>
			To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
		</div>
	</div>
</div>

E;

$playlist = <<<E
	new jPlayerPlaylist({
		jPlayer: "#jquery_jplayer_1",
		cssSelectorAncestor: "#jp_container_1"
	}, [
E;

$r = $db->query("SELECT * FROM vk_music WHERE saved = 1 ORDER BY date_added DESC");
while($list = $db->return_row($r)){
	// Rewrite if you plan to store content outside of web directory and will call it by Alias
	if($cfg['vhost_alias'] == true && substr($list['path'],0,4) != 'http'){
		$list['path'] = $f->windows_path_alias($list['path'],'audio');
	}
	$time = $list['duration'];
	$list['duration'] = $skin->seconds2human($list['duration']);
	$list['artist'] = trim(preg_replace('/\"/','\\"',$list['artist']));
	$list['title'] = trim(preg_replace('/\"/','\\"',$list['title']));
	mb_internal_encoding("UTF-8");
	$list['sartist'] = mb_substr(preg_replace('/\s[\\"]/','',$list['artist']),0,10);
	$list['stitle'] = mb_substr(preg_replace('/\s[\\"]/','',$list['title']),0,10);
	$playlist .= <<<E
{
	title:"{$list['title']}",
	artist:"{$list['artist']}",
	stitle:"{$list['stitle']}",
	sartist:"{$list['sartist']}",
	free:true,
	mp3:"{$list['path']}",
	duration:"{$list['duration']}",
	data_added:{$list['date_added']},
	data_dsync:{$list['date_done']},
	time:{$time},
	deleted: {$list['deleted']},
	album:{$list['album']}
},

E;
}

$playlist .= '],{';

$ex_bot = <<<E
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function(){

	{$playlist}
		playlistOptions : {
			addTime : 0,
			removeTime: 0,
			displayTime : 0,
			shuffleTime : 0,
		},
		
        cssSelectorAncestor: "#jp_container_1",
		backgroundColor: "#e8e8e8",
		solution: "html",
        supplied: "mp3",
		preload: "none", //"metadata",
		volume: 1,
		muted: false,
        useStateClassSkin: true,
        autoBlur: false,
        //smoothPlayBar: true,
        keyEnabled: true,
        remainingDuration: true,
        toggleDuration: true
    });
	
	$(".jp-play").click(function(){
		if($.jPlayer.event.play){ $(".jp-play .fa-play").hide();$(".jp-play .fa-pause").show(); }
		if($.jPlayer.event.pause){ $(".jp-play .fa-play").show();$(".jp-play .fa-pause").hide(); }
	});
	
	$(".tip").tooltip();
	
	// Bootstrip select
	$('.selectpicker').selectpicker({
		iconBase: 'fa',
		tickIcon: 'fa-check'
	});
});
//]]>
</script>

E;

if(!$cfg['pj']){
	print $skin->footer(array('extend'=> $ex_bot));
} else {
	print $ex_bot;
}

$db->close($res);

?>