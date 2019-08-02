<?php

// Get VKBK config
require_once(__DIR__ . "/cfg.php");
if(isset($_GET['_pjax']) || isset($_POST['_pjax'])){ $cfg['pj'] = true; }

// Get DB
require_once(ROOT.'classes/db.php');
$db = new db();
$res = $db->connect($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['base']);

$offset = 0;
if(isset($_GET['offset']) && is_numeric($_GET['offset'])){
	$o = intval($_GET['offset']);
	if($o > 0){ $offset = $o; }
}
$email = $cfg['yt_dl_login']; //Login
$pass = $cfg['yt_dl_passw']; //Pass
$user = $db->query_row("SELECT vk_user FROM vk_session WHERE vk_id = 1");
$cfg['vk_user'] = $user['vk_user'];
$auth_url = "https://m.vk.com";

include_once(ROOT."/classes/music.php");

// Get Skin
require_once(ROOT.'classes/skin.php');
$skin = new skin();

// Get local counters for top menu
$lc = $db->query_row("SELECT * FROM vk_counters");

if(!$cfg['pj']){
	print $skin->header(array('extend'=>''));
	print $skin->navigation($lc);
}

print <<<E
<div class="nav-scroller bg-white box-shadow mb-4" style="position:relative;">
    <nav class="nav nav-underline">
		<span class="nav-link active"><i class="fa fa-skull"></i> Музыка, йо-хо-хо! <i class="fa fa-music"></i></span>
    </nav>
</div>

<div class="container" style="margin-bottom:50px;">
E;

if(empty($pass) || empty($email)){
print <<<E
<div class="alert alert-primary" role="alert">Для работы скрипта необходимо заполнить <strong>\$cfg['yt_dl_login']</strong> и <strong>\$cfg['yt_dl_passw']</strong> в конфигурационном файле.</div>
</div>
E;
if(!$cfg['pj']){
	print $skin->footer(array('extend'=> ''));
} else {
	print $ex_bot;
}
$db->close($res);
exit;
}

/* Auth .. Copied from stackoverflow */
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $auth_url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$login_page = curl_exec($ch);
curl_close($ch);

preg_match("/<form method=\"post\" action=\"([^\"]+)/i", $login_page, $login_url);
$login_url = $login_url[1];

if(empty($login_url)){
print <<<E
<div class="alert alert-primary" role="alert">Ссылка входа не найдена.</div>
</div>
E;
if(!$cfg['pj']){
	print $skin->footer(array('extend'=> ''));
} else {
	print $ex_bot;
}
$db->close($res);
exit;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $login_url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, ["email" => $email, "pass" => $pass]);
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_exec($ch);
curl_close($ch);

/* Get music */
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://m.vk.com/audio");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Requested-With: XMLHttpRequest']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, ["_ajax" => 1, 'offset' => $offset, 'q' => (isset($_GET['search']) ? $_GET['search'] : '')]);
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$page = curl_exec($ch);
curl_close($ch);

$html = json_decode($page, true);
$html = $html['data'][0];
	
	$titles = $artists = $extras = $tracks = array();
	$result = array();

	/*
		Array information
		0 - Artist Track
		1 - userid_trackid_pageurl
		2 - url
		3 - artist
		4 - track
		5 - duration
		6 - ?
		7 - ?
		8 - album cover
		9 - ?
		10 - ?
		11 - genre \ album name?
		12 - ?
	*/
	
	foreach($html as $hk => $hv){
		if(!empty($hv[0]) && !empty($hv[3]) && !empty($hv[4]) && !empty($hv[2])){
			preg_match_all('/https:\/\/m\.vk\.com\/mp3\/audio_api_unavailable\.mp3\?extra=([^"]+)/', $hv[2], $ex);
			preg_match("/^[0-9]+_([0-9]+)/",$hv[1],$aidi);
			
			$result[] = array(
				"id" => isset($aidi[1]) ? $aidi[1] : 0,
				"artist" => strip_tags($hv[3]),
				"track" => strip_tags($hv[4]),
				"duration" => $hv[5],
				"extra" => s("https://m.vk.com/mp3/audio_api_unavailable.mp3?extra=" . $ex[1][0]) //Decooooooode
			);
		}
	}

	// Check DB for possible matches
	$matches = array();
	foreach($result as $rk => $rv){
		$r = $db->query("SELECT id,artist,title,duration FROM vk_music WHERE `id` = {$rv['id']} OR `artist` LIKE '".$db->real_escape($rv['artist'])."' OR `title` LIKE '".$db->real_escape($rv['track'])."' ORDER BY id DESC");
		while($row = $db->return_row($r)){
			$matches[$rk][] = array('id'=>$row['id'], 'artist'=>$row['artist'], 'title'=>$row['title'], 'duration'=>$row['duration']);
		}
		
	}
	//print_r($matches);
	
print <<<E
	<table class="table table-sm table-hover small white-box pb-2 mb-2">
		<tr>
			<th>ID трека</th>
			<th>Исполнитель</th>
			<th>Название</th>
			<th>Время</th>
			<th>В очередь</th>
		</tr>
E;

	foreach ($result as $kal => $val) {
		
		$match_found = false;
		$match_opts = '';
		if(isset($matches[$kal])){
$match_opts .= <<<E
		<tr>
			<td colspan="5" style="background-color:#fcfcfc;"><strong>Возможные совпадения:</strong><br/>
E;
			foreach($matches[$kal] as $mk => $mv){
				$match_points = 0;
				if($mv['id'] == $val['id']){ $match_points++; }
				if($mv['artist'] == html_entity_decode($val['artist'], ENT_QUOTES | ENT_XML1, 'UTF-8')){ $match_points++; }
				if($mv['title'] == html_entity_decode($val['track'], ENT_QUOTES | ENT_XML1, 'UTF-8')){ $match_points++; }
				if($mv['duration'] == $val['duration']){ $match_points++; }
				
				if($match_points == 0){ $match_color = 'text-secondary'; }
				if($match_points == 1){ $match_color = ' '; }
				if($match_points == 2){ $match_color = 'text-primary'; }
				if($match_points == 3){ $match_color = 'text-info';  }
				if($match_points == 4){ $match_color = 'text-success'; $match_found = true; }
				$match_opts .= '<div class="'.$match_color.' px-2">'.$mv['id'].' => '.$mv['artist'].' &mdash; '.$mv['title'].' ['.$skin->seconds2human($mv['duration']).']</div>';
				
			}
$match_opts .= <<<E
			</td>
		</tr>
E;
		}
		
		// Match found, reset list of possible
		if($match_found == true){
			$match_opts = '';
			$grabit = 'Уже добавлен';
			$match_color = 'text-success';
		} else {
			$match_color = '';
			$grabit = '<input type="button" class="btn btn-sm btn-primary" onclick="grabit('.$val['id'].');" value="Добавить" />';
		}

print <<<E
		<tr id="{$val['id']}" class="{$match_color}">
			<td>{$val['id']}</td>
			<td>{$val['artist']}</td>
			<td>{$val['track']}</td>
			<td>{$skin->seconds2human($val['duration'])}</td>
			<td>{$grabit}
				<input type="hidden" id="{$val['id']}-artist" value="{$val['artist']}" />
				<input type="hidden" id="{$val['id']}-title" value="{$val['track']}" />
				<input type="hidden" id="{$val['id']}-duration" value="{$val['duration']}" />
				<input type="hidden" id="{$val['id']}-uri" value="{$val['extra']}" />
			</td>
		</tr>
		{$match_opts}
E;

	}

	$newoffset = $offset+50;
print <<<E
	</table>
	<div class="col-sm-12 p-2 m-2 text-center">
		<a class="btn btn-primary" role="button" href="musicgrab.php?offset={$newoffset}">Следующие 50 >>></a>
	</div>
</div>
E;
	
if(!$cfg['pj']){
	print $skin->footer(array('extend'=> ''));
} else {
	print $ex_bot;
}

$db->close($res);

?>