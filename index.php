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
  <div class="container">
    <div class="row">
	  <div class="col-sm-2 col-md-3 mt-4" id="auth-col">
	    <ul class="nav">
		  <li class="col-sm-6 col-md-12">
E;

// Include VK.API
require_once(ROOT.'classes/VK/VK.php');

// Check token
$q = $db->query("SELECT * FROM vk_session WHERE `vk_id` = 1");
$vk_session = $row = $db->return_row($q);
$token_valid = false;

// Empty counters
$counters_show = array(
	'albums' => 0,
	'photos' => 0,
	//'audios' => 0,
	'videos' => 0,
	'docs'   => 0,
	//'dialogs'=> 0
);

if($vk_session['vk_token']){
	$vk = new VK($cfg['vk_id'], $cfg['vk_secret'], $vk_session['vk_token']);
	// Set API version
	$vk->setApiVersion($cfg['vk_api_version']);
	$token_valid = $vk->checkAccessToken($vk_session['vk_token']);
} else {
	$vk = new VK($cfg['vk_id'], $cfg['vk_secret']);
	// Set API version
	$vk->setApiVersion($cfg['vk_api_version']);
}

if($vk_session['vk_token'] != '' && $token_valid == true){
	
	try {
		// We logged in, get personal info
		$user = $vk->api('users.get', array(
			'user_id' => $vk_session['vk_user'],
			'fields' => 'first_name,last_name,has_photo,photo_200_orig,counters,nickname'
		));
		
		$u = $user['response'][0];
		
		print '<div class="col text-center" style="display:flex;justify-content:space-between;flex-wrap:wrap">';
		if($u['has_photo']){
print <<<E
<div class="w-100"><img class="img-thumbnail" alt="200x200" style="width: 200px;" src="{$u['photo_200_orig']}" data-holder-rendered="true"></div>
E;
		}
print <<<E
			
			<div><a class="btn btn-link btn-sm" href="https://vk.com/id{$u['id']}" target="_blank" rel="noopener noreferrer">{$u['nickname']} <small><i class="fa fa-fw fa-external-link-alt"></i></small></a></div>
			<div><a class="btn btn-outline-secondary btn-sm" href="ajax/auth.php?do=logout" data-pjauth><i class="fa fa-sign-out-alt"></i></a></div>
			<div class="w-100">{$u['first_name']} {$u['last_name']}</div>
			</div>
		  </li>
		  <li class="col-sm-12">
		    <ul class="nav my-3 p-3 bg-white rounded box-shadow">
				<li style="width:100%;text-align:center;">Данные из ВК:</li>
E;
		
		// GET REAL ALBUMS
		$albums = $vk->api('photos.getAlbums', array(
			'owner_id' => $vk_session['vk_user'],
			'need_system' => '1'
		));
		
		$counters_show['albums'] = $albums['response']['count']-1;
		$counters_show['photos'] = 0;
		foreach($albums['response']['items'] as $k => $v){
			if($v['id'] > SYSTEM_ALBUM){
				$counters_show['photos'] += $v['size'];
			}
		}

		// GET AUDIO Count
		// Disabled because VK does not return data anymore
		/*$music = $vk->api('audio.getCount', array(
			'owner_id' => $vk_session['vk_user']
		));
		if(isset($music['response'])){
			$counters_show['audios'] = $music['response'];
		} else {
			$counters_show['audios'] = 0;
		}*/
		
		// GET VIDEO Count
		$video = $vk->api('video.get', array(
			'owner_id' => $vk_session['vk_user'],
			'count' => 0,
			'offset' => 0,
			'extended' => 0
		));
		if(isset($video['response']) && $video['response']['count']){
			$counters_show['videos'] = $video['response']['count'];
		} else {
			$counters_show['videos'] = 'n/a';
		}
		
		// GET DOCUMENTS Count
		$docs = $vk->api('docs.get', array(
			'owner_id' => $vk_session['vk_user'],
			'count' => 1,
			'offset' => 0
		));
		if(isset($docs['response'])){
			$counters_show['docs'] = $docs['response']['count'];
		} else {
			$counters_show['docs'] = 0;
		}
		
		// GET DIALOGS Count *** Disabled by API ***
		/*$dialogs = $vk->api('messages.getDialogs', array(
			'count' => 0,
			'offset' => 0
		));
		if(isset($dialogs['response'])){
			$counters_show['dialogs'] = $dialogs['response']['count'];
		} else {
			$counters_show['dialogs'] = 0;
		}*/

		foreach($counters_show as $k => $v){
			if($k == 'albums')  { $k = '<i class="fa fa-folder-open fa-fw"></i> Альбомы'; }
			if($k == 'photos')  { $k = '<i class="fa fa-image fa-fw"></i> Фото'; }
			//if($k == 'audios') { $k = '<i class="fa fa-music fa-fw"></i> Музыка'; }
			if($k == 'videos')  { $k = '<i class="fa fa-film fa-fw"></i> Видео'; }
			if($k == 'docs')    { $k = '<i class="fa fa-file fa-fw"></i> Документы'; }
			//if($k == 'dialogs') { $k = '<i class="far fa-comment-alt fa-fw"></i> Диалоги'; }
			print '<li class="w-100 btn btn-light text-left mb-1 d-flex justify-content-between align-items-center"><span>'.$k.':</span> <span class="badge badge-secondary">'.$v.'</span></li>';
		}
		
		print '</ul>';

	} catch (Exception $error) {
		echo $error->getMessage();
	}

} else {

	try {
    
		if (!isset($_REQUEST['code'])) {
			/**
	         * If you need switch the application in test mode,
			 * add another parameter "true". Default value "false".
	         * Ex. $vk->getAuthorizeURL($api_settings, $callback_url, true);
			*/
	        //$authorize_url = $vk->getAuthorizeURL('offline,status,photos,audio,video,docs',$cfg['vk_uri']);
			$authorize_url = $vk->getAuthorizeURL('offline,status,photos,audio,video,docs');//messages
print <<<E
<div class="text-center">
	<i class="fab fa-vk" style="font-size:3em;"></i>
	<p>1. Окно авторизации
	<a href="{$authorize_url}" target="_blank" class="btn btn-primary w-75" role="button">Открыть <i class="fa fa-fw fa-external-link-alt"></i></a></p>
</div>
<div class="text-center mt-2 p-2">
	<form action="" method="GET">
		<p>2. Скопируйте <b>x</b> из URL<br/><small>api.vk.com/blank.html#code=<b>x</b></small><br>в поле ниже:
		<input class="form-control w-75 mb-2 mx-auto" type="text" name="code" value="" />
		<button class="btn btn-success w-75 " type="submit">Войти <i class="fa fa-fw fa-sign-in-alt"></i></button></p>
	</form>
</div>
E;
		} else {
			$access_token = $vk->getAccessToken($_REQUEST['code']);//, $cfg['vk_uri']);

			// If we get token, save it!
			if($access_token['access_token']){
				$q = $db->query("REPLACE INTO vk_session (`vk_id`,`vk_token`, `vk_expire`, `vk_user`) VALUES (1,'{$access_token['access_token']}','{$access_token['expires_in']}','{$access_token['user_id']}')");
			}
		
			print '<div class="alert alert-success text-center"><h6><span style="white-space:inherit">Авторизация пройдена</span></h6></div>';
		}
	} catch (Exception $error) {
		echo '<div class="alert alert-danger text-center"><h6><span style="white-space:inherit">Ошибка: '.$error->getMessage().'</span></h6></div>';
	}
} // end if token else

print <<<E
				</li>
			</ul>
        </div>
E;

// Get LOCAL counters for media
$counters = $db->query_row("SELECT * FROM vk_counters");
$music_albums = $db->query_row("SELECT count(id) as count FROM vk_music_albums");
$wall_attachments = $db->query_row("SELECT count(uid) as count FROM vk_attach");
$messages_count = $db->query_row("SELECT count(uid) as count FROM vk_messages");
$messages_attach = $db->query_row("SELECT count(uid) as count FROM vk_messages_attach");

print <<<E
        <div class="col-sm-10 col-md-9 mt-4">
          <div class="row placeholders pt-4 pb-2">
            <div class="col-sm-3 placeholder">
              <h2 class="display-4">{$f->human_thousand($counters['album'])}</h2>
              <span class="text-muted">Альбомы</span>
            </div>
            <div class="col-sm-3 mb-4">
              <h2 class="display-4">{$f->human_thousand($counters['photo'])}</h2>
              <span class="text-muted">Фотографии</span>
            </div>
			<div class="col-sm-3 mb-4">
              <h2 class="display-4">{$f->human_thousand($music_albums['count'])}</h2>
              <span class="text-muted">Альбомы музыки</span>
            </div>
            <div class="col-sm-3 mb-4">
              <h2 class="display-4">{$f->human_thousand($counters['music'])}</h2>
              <span class="text-muted">Музыка&nbsp;&nbsp;<a href="musicgrab.php"><i class="fas fa-skull-crossbones fa-fw"></i></a></span>
            </div>
            <div class="col-sm-3 mb-4">
              <h2 class="display-4">{$f->human_thousand($counters['video'])}</h2>
              <span class="text-muted">Видео</span>
            </div>
			<div class="col-sm-3 mb-4">
              <h2 class="display-4">{$f->human_thousand($counters['wall'])}</h2>
              <span class="text-muted">Стена</span>
            </div>
			<div class="col-sm-3 mb-4">
              <h2 class="display-4">{$f->human_thousand($counters['docs'])}</h2>
              <span class="text-muted">Документы</span>
            </div>
			<div class="col-sm-3 mb-4">
              <h2 class="display-4">{$f->human_thousand($counters['dialogs'])}</h2>
              <span class="text-muted">Диалоги<!--&nbsp;&nbsp;<a href="sync-messages.php"><i class="fa fa-sync fa-fw"></i></a>--></span>
            </div>
			<div class="col-sm-3 mb-4">
              <h2 class="display-4">{$f->human_thousand($wall_attachments['count'])}</h2>
              <span class="text-muted">Вложения (стена)</span>
            </div>
			<div class="col-sm-3 mb-4">
              <h2 class="display-4">{$f->human_thousand($messages_count['count'])}</h2>
              <span class="text-muted">Сообщения</span>
            </div>
			<div class="col-sm-3 mb-4">
              <h2 class="display-4">{$f->human_thousand($messages_attach['count'])}</h2>
              <span class="text-muted">Вложения (диалоги)</span>
            </div>
			<div class="col-sm-3 mb-4">
              <a href="sync.php"><h2 class="display-4"><i class="fa fa-sync fa-fw text-success"></i></h2>
              <span class="text-muted" style="text-decoration:underline;">Синхронизация</span></a>
            </div>
			
          </div>
          
		  <div class="row white-box">
			<div class="table-responsive pl-2 pr-2">
				<h6 class="p-2 mb-0 vkhead"><i class="fa fa-info-circle fa-fw"></i> Уведомления</h6>
	            <table class="table table-sm table-hover">
		          <tbody>
E;

if($counters_show['albums'] != 0 && $counters_show['albums'] > $counters['album']){
print <<<E
<tr><td>Количество <b>альбомов</b> изменилось, необходима синхронизация. <a href="sync.php">Синхронизировать</a> сейчас?</td></tr>
E;
}
if($counters_show['photos'] != 0 && $counters_show['photos'] > $counters['photo']){
	$d = $counters_show['photos'] - $counters['photo'];
	if($d > 0){ $d = '(+<b>'.$d.'</b>)'; }
print <<<E
<tr><td>Количество <b>фотографий</b> изменилось {$d}, необходима синхронизация. <a href="sync.php">Синхронизировать</a> сейчас?</td></tr>
E;
}
// Disabled because VK does not return data anymore
/*
if($counters_show['audios'] != 0 && $counters_show['audios'] > $counters['music']){
	$d = $counters_show['audios'] - $counters['music'];
	if($d > 0){ $d = '(+<b>'.$d.'</b>)'; }
print <<<E
<tr><td>Количество <b>аудиозаписей</b> изменилось {$d}, необходима синхронизация. <a href="sync.php?do=music">Синхронизировать</a> сейчас?</td></tr>
E;
}
*/
if($counters_show['videos'] != 0 && $counters_show['videos'] > $counters['video']){
	$d = $counters_show['videos'] - $counters['video'];
	if($d > 0){ $d = '(+<b>'.$d.'</b>)'; }
print <<<E
<tr><td>Количество <b>видеозаписей</b> изменилось {$d}, необходима синхронизация. <a href="sync.php">Синхронизировать</a> сейчас?</td></tr>
E;
}
if($counters_show['docs'] != 0 && $counters_show['docs'] > $counters['docs']){
	$d = $counters_show['docs'] - $counters['docs'];
	if($d > 0){ $d = '(+<b>'.$d.'</b>)'; }
print <<<E
<tr><td>Количество <b>документов</b> изменилось {$d}, необходима синхронизация. <a href="sync.php">Синхронизировать</a> сейчас?</td></tr>
E;
}
/*if($counters_show['dialogs'] != 0 && $counters_show['dialogs'] > $counters['dialogs']){
	$d = $counters_show['dialogs'] - $counters['dialogs'];
	if($d > 0){ $d = '(+<b>'.$d.'</b>)'; }
print <<<E
<tr><td>Количество <b>диалогов</b> изменилось {$d}, необходима синхронизация. <a href="sync-messages.php">Синхронизировать</a> сейчас?</td></tr>
E;
}*/

print <<<E
			      </tbody>
		        </table>
	          </div>
		  </div><br/>
		  <div class="row white-box">
E;

// Get LOCAL queue (photo,music,video,docs)
$queue_count = array('p'=>0,'m'=>0,'v'=>0,'dc'=>0);
$queue_photo = $db->query_row("SELECT COUNT(*) as count FROM vk_photos WHERE `in_queue` = 1");
$queue_count['p'] = $queue_photo['count'];
$queue_music = $db->query_row("SELECT COUNT(*) as count FROM vk_music WHERE `in_queue` = 1");
$queue_count['m'] = $queue_music['count'];
$queue_video = $db->query_row("SELECT COUNT(*) as count FROM vk_videos WHERE `in_queue` = 1");
$queue_count['v'] = $queue_video['count'];
$queue_docs = $db->query_row("SELECT COUNT(*) as count FROM vk_docs WHERE `in_queue` = 1");
$queue_count['dc'] = $queue_docs['count'];
$queue_total = $queue_count['p']+$queue_count['m']+$queue_count['v']+$queue_count['dc'];

print <<<E
          <div class="table-responsive pl-2 pr-2">
		  <h6 class="p-2 mb-0 vkhead"><i class="fa fa-cloud-download-alt fa-fw"></i> Очередь закачки - <b>{$queue_total}</b></h6>
            <table class="table table-sm table-hover">
              <thead>
                <tr>
                  <th>#</th>
				  <th>URL</th>
				  <th>Добавлено</th>
                </tr>
              </thead>
              <tbody>
E;

if($queue_count['p'] > 0){
	$r = $db->query("SELECT * FROM vk_photos WHERE `in_queue` = 1 ORDER BY date_added DESC LIMIT 0,5");
	while($row = $db->return_row($r)){
		$row['date_added'] = date("Y-m-d H:i:s",$row['date_added']);
print <<<E
<tr>
  <td>{$row['id']}</td>
  <td><i class="far fa-file-image"></i> <a href="{$row['uri']}" target="_blank">{$row['uri']}</a></td>
  <td>{$row['date_added']}</td>
</tr>
E;
	}
}

if($queue_count['m'] > 0) {
	$r = $db->query("SELECT * FROM vk_music WHERE `in_queue` = 1 ORDER BY date_added DESC LIMIT 0,5");
	while($row = $db->return_row($r)){
		$row['date_added'] = date("Y-m-d H:i:s",$row['date_added']);
		$row['uri_a'] = preg_replace("/\?extra\=.*/","",$row['uri']);
print <<<E
<tr>
  <td>{$row['id']}</td>
  <td><i class="far fa-file-audio"></i> <a href="{$row['uri']}" target="_blank">{$row['uri_a']}</a></td>
  <td>{$row['date_added']}</td>
</tr>
E;
	}
}

if($queue_count['v'] > 0) {
	$r = $db->query("SELECT * FROM vk_videos WHERE `in_queue` = 1 ORDER BY date_added DESC LIMIT 0,5");
	while($row = $db->return_row($r)){
		$row['date_added'] = date("Y-m-d H:i:s",$row['date_added']);
print <<<E
<tr>
  <td>{$row['id']}</td>
  <td><i class="far fa-file-video"></i> <a href="{$row['preview_uri']}" target="_blank">{$row['preview_uri']}</a></td>
  <td>{$row['date_added']}</td>
</tr>
E;
	}
}

if($queue_count['dc'] > 0) {
	$r = $db->query("SELECT * FROM vk_docs WHERE `in_queue` = 1 ORDER BY date DESC LIMIT 0,5");
	while($row = $db->return_row($r)){
		$row['date'] = date("Y-m-d H:i:s",$row['date']);
print <<<E
<tr>
  <td>{$row['id']}</td>
  <td><i class="far fa-file"></i> <a href="{$row['uri']}" target="_blank">{$row['title']}</a></td>
  <td>{$row['date']}</td>
</tr>
E;
	}
}

if($queue_total == 0) {
	print '<tr><td colspan="3" style="text-align:center;color:#bbb;">Очередь закачки пуста</td></tr>';
}

if($queue_total){
print <<<E
<tr>
  <td colspan="3" style="text-align:right;"><a href="queue.php">посмотреть всю очередь &raquo;</a></td>
</tr>
E;
}

print <<<E
              </tbody>
            </table>
          </div>
		  </div>
        </div>
      </div>
	  
    </div>

E;

if(!$cfg['pj']){
	print $skin->footer(array('extend'=>''));
}

$db->close($res);

?>