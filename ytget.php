<?php

header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

define('SLF',basename(__FILE__));

require_once('./cfg.php');

// Get DB
require_once(ROOT.'classes/db.php');
$db = new db();
$res = $db->connect($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['base']);

// Get Skin
require_once(ROOT.'classes/skin.php');
$skin = new skin();

// Get local counters for top menu
$lc = $db->query_row("SELECT * FROM vk_counters");

print $skin->header(array('extend'=>''));
print $skin->navigation($lc);

// Video Key
$key = isset($_GET['key']) ? $_GET['key'] : '';
// DB id
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$oid = isset($_GET['oid']) ? intval($_GET['oid']) : 0;
// Service type
$s = isset($_GET['s']) ? preg_replace("/[^a-z]+/","",$_GET['s']) : '';
// Force authorization
$force_auth = (isset($_GET['force_auth']) && !empty($cfg['yt_dl_login']) && !empty($cfg['yt_dl_passw'])) ? true : false;
$force_dev = (isset($_GET['force_dev']) && !empty($cfg['yt_dl_login']) && !empty($cfg['yt_dl_passw'])) ? true : false;

$ytdl = $cfg['yt_dl_path'] != '' ? true : false;

print <<<E
<div class="nav-scroller bg-white box-shadow mb-4" style="position:relative;">
    <nav class="nav nav-underline">
		<span class="nav-link active"><i class="far fa-save"></i> Сохранение видео (youtube-dl)</span>
    </nav>
</div>
<div class="container">
	<div class="table-responsive">
		<table class="table">
E;

if($ytdl === true){

	if($key == ''){
		print '<tr><td><div class="alert alert-danger" role="alert">Не указан ключ для видео</div></td></tr>';
	}

	// Check video ID
	$vid = $db->query_row("SELECT id, owner_id, player_uri FROM `vk_videos` WHERE `id` = {$id} AND `owner_id` = {$oid}");
	if(!isset($vid['id']) || empty($vid['id'])){
		print '<tr><td><div class="alert alert-danger" role="alert">Не удалось найти видео с данным ID</div></td></tr>';
	}
	
	if($key != '' && isset($vid['id']) && $vid['id'] > 0 && $s != ''){

print <<<E
<tr>
	<td>Ключ: {$key}</td>
</tr>
<tr>	
	<td>
		<div style="width:100%;height:340px;overflow:hidden;position:relative;background-color:#000;border-radius:5px">
		<pre style="position:absolute;bottom:0;left:0;width:100%;color:green;padding:0.5rem 1rem;">
E;

/* youtube-dl command line options
  --no-mark-watched		- Do not mark videos watched (YouTube only)
  -4					- Use IPv4
  --restrict-filenames	- Restrict filenames to only ASCII characters, and avoid "&" and spaces in filenames
  -w					- Do not overwrite files
  --no-part				- Do not use .part files - write directly into output file
  -o					- File would be saved as `YouTubeKey.ext` Example: DijY9NkGSak.mp4
  -F					- List formats [FOR DEBUG ONLY]
  -f					- format
							Default YT: bestvideo[height<=720]+bestaudio - download max 720p or less
							Default VK: url720/url480/url360/url240      - download max 720p or less
							Example: bestvideo+bestaudio
  --merge-output-format - If a merge is required (e.g. bestvideo+bestaudio), output to given container format. One of mkv, mp4, ogg, webm, flv.
  --ffmpeg-location		- Path to ffmpeg binary
  
  optional:
  -v					- debug
  
  For more options you can read official readme
  https://github.com/rg3/youtube-dl/blob/master/README.md#readme
*/

		$youtubeDLlog = '';
		$local = array(
			'path' => "",
			'size' => 0,
			'format' => "",
			'w' => 0,
			'h' => 0
		);

		// YouTube
		if($s == 'yt'){
			$youtubeDLcmd = $cfg['yt_dl_path'].'youtube-dl.exe --no-mark-watched -4 --restrict-filenames -w -f "bestvideo[height<=720]+bestaudio" --merge-output-format mp4 --ffmpeg-location '.$cfg['ffmpeg'].' --no-part --write-info-json -o "'.$cfg['video_path'].'data/'.$key.'.%(ext)s" https://youtu.be/'.$key;
		}
		// VK.com
		if($s == 'vk'){
			// Default command
			$youtubeDLcmd = $cfg['yt_dl_path'].'youtube-dl.exe -4 --restrict-filenames -w -f "(mp4,webm,flv,3gp)url720/cache720/url480/cache480/url360/cache360/url240" --no-part --write-info-json -o "'.$cfg['video_path'].'data/vk-'.$vid['id'].'-'.$vid['owner_id'].'.%(ext)s" "'.$vid['player_uri'].'"';
			
			// Command with auth (public)
			if($force_auth === true){
				$youtubeDLcmd = $cfg['yt_dl_path'].'youtube-dl.exe -4 --restrict-filenames -w -f "(mp4,webm,flv,3gp)url720/cache720/url480/cache480/url360/cache360/url240" --no-part --write-info-json -u "'.$cfg['yt_dl_login'].'" -p "'.$cfg['yt_dl_passw'].'" -o "'.$cfg['video_path'].'data/vk-'.$vid['id'].'-'.$vid['owner_id'].'.%(ext)s" "'.$vid['player_uri'].'"';
			}
			
			// Command with auth (private)
			if($force_dev === true){
				// Get Functions
				require_once(ROOT.'classes/func.php');
				$func = new func();
				
				// Enable DEV.API
				require_once ROOT."/classes/vk-auth/wrapper.php";
				$vka = new \VKA\dev($cfg['yt_dl_login'],$cfg['yt_dl_passw'],$cfg['vk_api_version']);
				$vka->vka_init();
				
				// Get video
				$api = $vka->vka_method('video.get',array('param_owner_id'=>$vid['owner_id'],'param_videos'=>''.$vid['owner_id'].'_'.$vid['id'].'','param_count'=>1,'param_offset'=>0));
				
				if(isset($api['response']) && $api['response'] != ''){
					if(isset($api['response']['items'][0]) && is_array($api['response']['items'][0])){
						// Get option: best_video_quality
						$best_q = 0;
						$bq = $db->query_row("SELECT val FROM `vk_status` WHERE `key` = 'best_video_quality'");
						if(isset($bq['val']) && !empty($bq['val'])){ $best_q = $bq['val']; }
						
						// Get direct url for video
						$direct = $func->get_video_url($api['response']['items'][0],$best_q);
						
						if($direct['url'] != false){
							$youtubeDLcmd = $cfg['yt_dl_path'].'youtube-dl.exe -4 --restrict-filenames -w -f "(mp4,webm,flv,3gp)url720/cache720/url480/cache480/url360/cache360/url240/best" --no-part --write-info-json -o "'.$cfg['video_path'].'data/vk-'.$vid['id'].'-'.$vid['owner_id'].'.%(ext)s" "'.$direct['url'].'"';
						}
						
					} else {
						echo '<div class="alert alert-danger" role="alert">Не удалось получить ссылку на видео. ):</div>';
					}
				} else {
					echo '<div class="alert alert-danger" role="alert">Не удалось обработать запрос к API. ):</div>';
				}
			}
		}
		
		ob_implicit_flush(true);
		ob_end_flush();
		passthru($youtubeDLcmd);
		
		print '</pre></div></td></tr>';
		
		// Check info.json for... INFO! :D
		$info = '';
		if($s == 'yt'){	$info = $cfg['video_path'].'data/'.$key.'.info.json'; }
		if($s == 'vk'){ $info = $cfg['video_path'].'data/vk-'.$vid['id'].'-'.$vid['owner_id'].'.info.json'; }
		
if(file_exists($info)){
	$handle = fopen($info, "r");
	$content = fread($handle, filesize($info));
	fclose($handle);
	$youtubeDLlog = json_decode($content);
	
	if(isset($youtubeDLlog->_filename) && file_exists(preg_replace("@\\\@","/",$youtubeDLlog->_filename))){
		$local['path'] = preg_replace("@\\\@","/",$youtubeDLlog->_filename);
		if($s == 'yt'){ $local['size'] = filesize($cfg['video_path'].'data/'.$key.'.'.$youtubeDLlog->ext); }
		if($s == 'vk'){ $local['size'] = filesize($cfg['video_path'].'data/vk-'.$vid['id'].'-'.$vid['owner_id'].'.'.$youtubeDLlog->ext); }
		$local['format'] = $youtubeDLlog->ext;
		$local['w'] = (isset($youtubeDLlog->width)) ? $youtubeDLlog->width : 0;
		$local['h'] = (isset($youtubeDLlog->height)) ? $youtubeDLlog->height : 0;
		if($force_dev == true && isset($direct['quality'])){ $local['h'] = $direct['quality']; }
		
		$q = $db->query("UPDATE `vk_videos` SET `local_path` = '".$db->real_escape($local['path'])."', `local_size` = {$local['size']}, `local_format` = '{$local['format']}', `local_w` = {$local['w']}, `local_h` = {$local['h']} WHERE id = {$vid['id']} AND owner_id = {$vid['owner_id']}");
		if($q){
			print '<tr><td><div class="alert alert-success" role="alert">Видеофайл сохранен.</div></td></tr>';
		}
	}
	
} else {
	// No file?! We fail!
print <<<E
<tr>
  <td>
    <div class="alert alert-danger" role="alert">Не удалось получить или сохранить файл. ):</div>
E;

	// Try authorization for VK
	if($s == "vk" && !empty($cfg['yt_dl_login']) && !empty($cfg['yt_dl_passw'])){
print '<div class="alert alert-warning" role="alert">Попробовать скачать <a href="'.SLF.'?id='.$id.'&oid='.$oid.'&key='.$key.'&s=vk&force_auth=true">с авторизацией</a> или через <a href="'.SLF.'?id='.$id.'&oid='.$oid.'&key='.$key.'&s=vk&force_dev=true">DEV API</a>.</div>';
	}

print <<<E
  </td>
</tr>
E;
}

// End of IF KEY
}

} else {
print <<<E
<tr>
  <td>
    <div class="alert alert-danger" role="alert">youtube-dl не найден. Скачать можно по ссылке <a href="https://github.com/ytdl-org/youtube-dl/releases" target="_blank" rel="noopener noreferer" >https://github.com/ytdl-org/youtube-dl/releases</a></div>
  </td>
</tr>
E;
}

print <<<E
            </table>
          </div>
</div>
E;

print $skin->footer(array('extend'=>''));

$db->close($res);

?>