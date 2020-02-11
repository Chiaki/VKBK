<?php
/*
	vkbk :: /ajax/sync.php
	since v0.8.9
*/

header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

define('SLF',basename(__DIR__).'/'.basename(__FILE__));
define('DEBUG_SYNC',false);

// Output > JSON container
$output = array(
	'response' => array(
		'error_msg' => '',
		'msg' => array(),
		'next_uri' => '',
		'done' => 0,
		'total' => 0,
		'timer' => 0
	),
	'error' => false
);

// Check do we have all needed GET data
$do = false;
$do_opts = array('albums','docs','photo','video');
$offset = false;
if(isset($_GET['do']) && in_array($_GET['do'],$do_opts)){
	$do = $_GET['do'];
}
if(isset($_GET['offset']) && is_numeric($_GET['offset'])){
	$offset = $_GET['offset'] >= 0 ? intval($_GET['offset']) : 0;
}
if($do === false){
$output['error'] = true;
$output['response']['error_msg'] = <<<E
    <div><i class="fas fa-fw fa-times-circle text-danger"></i> Неизвестный запрос</div>
E;
	print json_encode($output);
	die();
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
$func = new func();

if($do !== false){
	
	$don = false;
	
	// Include VK.API
	require_once(ROOT.'classes/VK/VK.php');
	
	// Check token
	$q = $db->query("SELECT * FROM vk_session WHERE `vk_id` = 1");
	$vk_session = $row = $db->return_row($q);
	$token_valid = false;
	
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
		
		// Albums sync
		if($do == 'albums'){
			$don = true;
			$output['response']['timer'] = $cfg['sync_photo_start_cd'];
			$to_delete = false;
			$items_list = array();
			$items_vk = array();
			$items_delete = '';
			$items_create = array();
			$items_renamed = array('count'=>0,'list'=>'');
			
			// Get local items
			$r = $db->query("SELECT id, name FROM vk_albums WHERE id > ".SYSTEM_ALBUM);
			while($row = $db->return_row($r)){
				$items_list['ids'][] = $row['id'];
				$items_list['names'][$row['id']] = $row['name'];
			}
			
			$local_items = sizeof($items_list['ids']);
			
			// Get VK items
			$api = $vk->api('photos.getAlbums', array(
				'owner_id' => $vk_session['vk_user'],
				'need_system' => 1
			));
			
			$items_vk = $api['response']['items'];
			$output['response']['total'] = $api['response']['count'];
			
			
			// Check local items for IDs and delete unknown
			if(!empty($items_vk[0]['id']) && !empty($local_items)){
				foreach($items_vk as $k => $v){
					
					// Если альбом есть в базе
					if(in_array($v['id'],$items_list['ids']) && $v['id'] > SYSTEM_ALBUM){
						// Если альбом есть локально, то убираем его из локального списка
						// Оставшиеся альбомы пойдут на удаление
						$key = array_search($v['id'], $items_list['ids']);
						unset($items_list['ids'][$key]);
						$to_delete = true;
						
						// Проверяем изменилось ли название
						if($v['title'] != $items_list['names'][$v['id']]){
							$q = $db->query("UPDATE vk_albums SET name = '".$v['title']."' WHERE id = ".$v['id']);
							$items_renamed['count']++;
							$items_renamed['list'] .= '&laquo;'.$items_list['names'][$v['id']].'&raquo; > &laquo;'.$v['title'].'&raquo;<br/>';
						}
						
					} else if($v['id'] > SYSTEM_ALBUM) {
						// Если альбом не найден локально, добавляем его в список импорта
						$items_create[] = $v;
					}
				}
			} else if(!empty($items_vk[0]['id']) && empty($local_items)) {
				foreach($items_vk as $k => $v){
					if($v['id'] > SYSTEM_ALBUM){
						$items_create[] = $v;
					}
				}
			}
			
			if($items_renamed['count'] > 0){
				$output['response']['msg'][] = "<div>Переименовано альбомов: <b>".$items_renamed['count']."</b>\r\n".$items_renamed['list']."</div>";
			}
			
			// Clean unused\deleted items
			$output['response']['msg'][] = '<div>Удалено альбомов: <b>'.sizeof($items_list['ids']).'</b></div>';
			if(!empty($items_list['ids']) && $to_delete == true){
				$items_delete = implode(',',$items_list['ids']);
				if($items_delete != ''){
					$q = $db->query("DELETE FROM vk_albums WHERE `id` IN({$items_delete})");
				}
			}
			
			// Update untouched items
			$q = $db->query("UPDATE vk_albums SET updated = ".time()." WHERE id > ".SYSTEM_ALBUM);
			
			// Import new albums
			$output['response']['msg'][] = '<div>Новых альбомов: <b>'.sizeof($items_create).'</b></div>';
			if(!empty($items_create)){
				$items_new = '';
				foreach($items_create as $k => $v){
					$items_new .= (($items_new!='') ? ',' : '').
					"({$v['id']},'{$v['title']}',".time().",".time().",{$v['size']},0)";
				}
				$q = $db->query("INSERT INTO vk_albums (`id`,`name`,`created`,`updated`,`img_total`,`img_done`) VALUES ".$items_new."");
			}
			
			// Update albums counter
			$q = $db->query("UPDATE vk_counters SET `album` = (SELECT COUNT(*) FROM vk_albums WHERE id > ".SYSTEM_ALBUM.")");
			
			$q = $db->query("SELECT COUNT(id) as total FROM vk_albums WHERE id > ".SYSTEM_ALBUM);
			$done_count = $db->return_row($q);
			$output['response']['done'] = $done_count['total'];
			
			$output['response']['msg'][] = '<div><i class="fa fa-fw fa-check-circle text-success"></i> <strong>УРА!</strong> Синхронизация завершена.</div>';
		
		} // DO Albums end
		
		// Photos sync
		if($do == 'photo'){
			$don = true;
			
			// Check do we have album ID in GET
			$album_id = (isset($_GET['album'])) ? intval($_GET['album']) : '';
			$albums_total = (isset($_GET['at'])) ? intval($_GET['at']) : 0;
			$albums_process = (isset($_GET['ap'])) ? intval($_GET['ap']) : 0;
			$fast_sync = (isset($_GET['fast']) && $_GET['fast'] == 1) ? 1 : 0;
			
			// No album? Let's start from the beginning
			if($album_id == ''){
				// Clean DB log before write something new
				$q4 = $db->query("UPDATE vk_status SET `val` = '' WHERE `key` = 'log_photo'");
				
				$log = array();
				
				// Set all local items to album SYSTEM_ALBUM
				// For fast sync move only 'system' albums
				$fsync_q1 = $fast_sync == 1 ? " WHERE `album_id` < 1" : "";
				$moved = 0;
				if(DEBUG_SYNC === false){
				$q = $db->query("UPDATE vk_photos SET `album_id` = ".SYSTEM_ALBUM." ".$fsync_q1);
				$moved = $db->affected_rows();
				}
				array_unshift($log,"Перемещаю фото в системный альбом - <b>".$moved."</b>\r\n");
				$output['response']['msg'][] = '<div><i class="fas fa-fw fa-info-circle text-info"></i> Перемещаю фото в системный альбом - <b>'.$moved.'</b></div>';
				unset($moved);
				
				// Save log
				array_unshift($log,"Начинаю синхронизацию фотографий...\r\n");
				
				$q = $db->query("UPDATE vk_status SET `val` = CONCAT('".implode("\r\n",$log)."',`val`) WHERE `key` = 'log_photo'");
				// Get first album ID
				// For fast sync get only 'system' albums
				$fsync_q2 = $fast_sync == 1 ? " AND `id` < 1" : "";
				$row = $db->query_row("SELECT id FROM vk_albums WHERE id > ".SYSTEM_ALBUM." ".$fsync_q2." LIMIT 1");
				// Get albums count
				$alb_c = $db->query_row("SELECT COUNT(*) as count FROM vk_albums WHERE id > ".SYSTEM_ALBUM." ".$fsync_q2);
				$albums_total = $alb_c['count'];
				// Reload page
				$output['response']['msg'][] = '<div><i class="fas fa-fw fa-play-circle text-info"></i> <b>Сейчас вылетит птичка!</b> Начинаю синхронизацию фотографий.</div>';
				$output['response']['next_uri'] = SLF."?do=photo&album=".$row['id']."&offset=0&at=".$albums_total."&ap=1&fast=".$fast_sync;
				$output['response']['timer'] = $cfg['sync_photo_start_cd'];
			} // if album is not found
			
			// Album ID found
			if($album_id != ''){
				// Logging
				$log = array();
				$album_name = $album_id;
				
				// Get album name
				$nrow = $db->query_row("SELECT name FROM vk_albums WHERE `id` = ".$album_id."");
				if($nrow['name'] != ''){
					$album_name = $nrow['name'];
				}
				
				array_unshift($log,"Синхронизация альбома <b>".$album_name."</b>\r\n");
				$output['response']['msg'][] = '<div><i class="fa fa-fw fa-circle text-info"></i> Синхронизация альбома <b>'.$album_name.'</b></div>';
				
				$items_vk_total = 0;
				
				$alb = $album_id;
				if($alb == -15){ $alb = 'saved';   }
				if($alb == -7) { $alb = 'wall';    }
				if($alb == -6) { $alb = 'profile'; }
				$count = 1000;
				$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
				
				// We logged in, get VK items
				$api = $vk->api('photos.get', array(
					'owner_id' => $vk_session['vk_user'],
					'album_id' => $alb,
					'rev' => 1,			// порядок сортировки (1 — антихронологический, 0 — хронологический)
					'extended' => 0,	// 1 — будут возвращены дополнительные поля likes, comments, tags, can_comment, reposts. Поля comments и tags содержат только количество объектов.
					'photo_sizes' => 0,	// параметр, указывающий нужно ли возвращать ли доступные размеры фотографии в специальном формате. 
					'offset' => $offset,
					'count' => $count
				));
				
				$items_vk = $api['response']['items'];
				$items_vk_total = $api['response']['count'];
				$output['response']['done'] = $count;
				$output['response']['total'] = $items_vk_total;
				
				$items_vk_list = array();
				// Get VK IDs
				foreach($items_vk as $k => $v){
					$items_vk_list[] = $v['id'];
				}
				
				// I want this logic in one line, but this blow my mind so...
				// Calculate FROM and TO values
				$from_to = $func->get_offset_range($offset,$count,$items_vk_total);
				
				array_unshift($log,"Получаем фото <b> ".$from_to['from']." - ".$from_to['to']." / ".$items_vk_total."</b>.\r\n");
				$output['response']['msg'][] = '<div><i class="far fa-fw fa-circle"></i> Получаем фото <b> '.$from_to['from'].' - '.$from_to['to'].' / '.$items_vk_total.'</b>.</div>';
				
				$items_list = array();
				// No items in list? Probably album is empty.
				if(sizeof($items_vk_list) > 0){
					// get local IDs
					$q = $db->query("SELECT id FROM vk_photos WHERE `id` IN(".implode(',',$items_vk_list).")");
					while($row = $db->return_row($q)){
						$items_list[] = $row['id'];
					}
				}
				
				// Get list of IDs which is NOT in local DB. So they are NEW.
				// Compare VK IDs with local IDs
				$items_create = array_diff($items_vk_list,$items_list);
				
				if(sizeof($items_list) > 0){
					// Update album for local IDs which was found
					$q = $db->query("UPDATE vk_photos SET `album_id` = ".$album_id." WHERE `id` IN(".implode(',',$items_list).") ");
					$moved = $db->affected_rows();
					array_unshift($log,"Найдены локально, перемещены обратно в альбом: <b>".$moved."</b>\r\n");
					$output['response']['msg'][] = '<div><i class="far fa-fw fa-circle"></i> Найдены локально, перемещены обратно в альбом: <b>'.$moved.'</b></div>';
					unset($moved);
				}
				
				// Put new items to queue
				$items_data = array();
				
				foreach($items_vk as $k => $v){
					if(in_array($v['id'],$items_create)){
						
						$photo_urx = $func->get_largest_photo($v);
						// Check do we have old or new type
						if(is_array($photo_urx)){
							$v['width'] = $photo_urx['width'];
							$v['height'] = $photo_urx['height'];
							$v['uri'] = $photo_urx['url'];
						} else { $photo_uri = $photo_urx; }
						
						$items_data[$v['id']] = array(
							'album_id' => $v['album_id'],
							'width' => (!is_numeric($v['width']) ? 0 : $v['width']),
							'height' => (!is_numeric($v['height']) ? 0 : $v['height']),
							'date' => $v['date'],
							'uri' => $v['uri']
						);
					}
				} // foreach end
				
				if(!empty($items_data) && (sizeof($items_create) == sizeof($items_data))){
					$data_sql = array(0=>'');
					$data_limit = 250;
					$data_i = 1;
					$data_k = 0;
					foreach($items_data as $k => $v){
						$data_sql[$data_k] .= ($data_sql[$data_k] != '' ? ',' : '')."({$k},{$v['album_id']},{$v['date']},'{$v['uri']}',{$v['width']},{$v['height']},0,0,'','',true,0)";
						$data_i++;
						if($data_i > $data_limit){
							$data_i = 1;
							$data_k++;
						}
					}
					
					foreach($data_sql as $k => $v){
						$q = $db->query("INSERT INTO vk_photos (`id`,`album_id`,`date_added`,`uri`,`width`,`height`,`date_done`,`saved`,`path`,`hash`,`in_queue`,`skipthis`) VALUES {$v}");
					}
					
					array_unshift($log,"Новые фото добавлены в очередь: <b>".sizeof($items_create)."</b>\r\n");
					$output['response']['msg'][] = '<div><i class="fa fa-fw fa-plus-circle text-info"></i> Новые фото добавлены в очередь: <b>'.sizeof($items_create).'</b></div>';
				}
				
				// Offset done
				// Now check do we need another run, or we can go next
				
				// If we done with all items in this album
				if(($offset+$count) >= $items_vk_total){
					array_unshift($log,"Обработка альбома завершена.\r\n");
					$output['response']['msg'][] = '<div><i class="far fa-fw fa-pause-circle"></i> Обработка альбома завершена.</div>';
				
					// Save log to the DB
					$q = $db->query("UPDATE vk_status SET `val` = CONCAT('".implode("\r\n",$log)."',`val`) WHERE `key` = 'log_photo'");
				
					// Get NEXT album id
					// For fast sync get only 'system' albums
					$fsync_q3 = $fast_sync == 1 ? " AND `id` < 1" : "";
					$row = $db->query_row("SELECT id FROM vk_albums WHERE id > ".$album_id.$fsync_q3." LIMIT 1");
					if(!empty($row['id']) && $row['id'] > $album_id){
						$album_next = $row['id'];
						$albums_process++;
						// Got next album, let's reload page
						$output['response']['next_uri'] = SLF.'?do=photo&album='.$album_next.'&offset=0&at='.$albums_total.'&ap='.$albums_process.'&fast='.$fast_sync;
						$output['response']['timer'] = $cfg['sync_photo_next_cd'];
					} else {
						// No unsynced items left and all abums was synced. This is the end...
						// Let's make recount
						$total = array('albums'=>0,'photos'=>0);
						$q = $db->query("SELECT id FROM vk_albums WHERE id > ".SYSTEM_ALBUM);
						while($row = $db->return_row($q)){
							$total['albums']++;
							$q2 = $db->query_row("SELECT COUNT(id) as photos FROM vk_photos WHERE `album_id` = ".$row['id']."");
							$total['photos'] += $q2['photos'];
							$q3 = $db->query("UPDATE vk_albums SET `img_total` = ".$q2['photos'].", `img_done` = ".$q2['photos']." WHERE `id` = ".$row['id']."");
							unset($q2);
						}
						
						// Update counters
						$q5 = $db->query("UPDATE vk_counters SET `photo` = (SELECT COUNT(*) FROM vk_photos)");
					
						array_unshift($log,"<strong>УРА!</strong> Синхронизация всех фотографий завершена.\r\nАльбомов - <b>".$total['albums']."</b>, фотографий - <b>".$total['photos']."</b>\r\n");
						$output['response']['msg'][] = '<div><i class="fa fa-fw fa-check-circle text-success"></i> <strong>УРА!</strong> Синхронизация фотографий завершена.<br/>Альбомов - <b>'.$total['albums'].'</b>, фотографий - <b>'.$total['photos'].'</b></div>';
						
					}
					
				} else {
					// Some items is not synced yed
					array_unshift($log,"Перехожу к следующей порции фото...\r\n");
					$output['response']['msg'][] = '<div><i class="far fa-fw fa-circle"></i> Перехожу к следующей порции фото...</div>';
					
					// Save log to the DB
					$q = $db->query("UPDATE vk_status SET `val` = CONCAT('".implode("\r\n",$log)."',`val`) WHERE `key` = 'log_photo'");
					
					// Calculate offset and reload page
					$offset_new = $offset+$count;
					$output['response']['next_uri'] = SLF.'?do=photo&album='.$album_id.'&offset='.$offset_new.'&at='.$albums_total.'&ap='.$albums_process.'&fast='.$fast_sync;
					$output['response']['timer'] = $cfg['sync_photo_next_cd'];
				}
				
			} // end if album
			// Save log into DB
			$q = $db->query("UPDATE vk_status SET `val` = CONCAT('".implode("\r\n",$log)."',`val`) WHERE `key` = 'log_photo'");
		} // DO Photos end
		
		// Video sync
		if($do == 'video'){
			$don = true;
			
			// Check do we have a PART in GET
			$part = (isset($_GET['part'])) ? intval($_GET['part']) : '';
			
			// No PART? Let's start from the beginning.
			if($part == ''){
				$log = array();
				// Clean DB log before write something new
				$q = $db->query("UPDATE vk_status SET `val` = '' WHERE `key` = 'log_video'");
				// Set all items to `deleted` state
				$q = $db->query("UPDATE vk_videos SET `deleted` = 1 WHERE `in_queue` = 0");
				
				array_unshift($log,"Начинаю синхронизацию...\r\n");
				
				$q = $db->query("UPDATE vk_status SET `val` = CONCAT('".implode("\r\n",$log)."',`val`) WHERE `key` = 'log_video'");
				
				// Reload
				$output['response']['msg'][] = '<div><i class="fas fa-fw fa-play-circle text-info"></i> <b>Свет, камера, мотор!</b> Начинаю синхронизацию...</div>';
				$output['response']['next_uri'] = SLF.'?do=video&part=1';
				$output['response']['timer'] = $cfg['sync_video_start_cd'];
			} // end if PART is not found
			
			// Hey, PART found!
			if($part >= 1){
				$log = array();
				
				array_unshift($log,"Синхронизация начата.\r\n");
				
				$items_vk_total = 0;
				$count = 200;
				$offset = ($part-1)*$count;
				
				// We logged in, get VK items
				$api = $vk->api('video.get', array(
					'owner_id' => $vk_session['vk_user'],
					'extended' => 0, // возвращать ли информацию о настройках приватности видео для текущего пользователя
					'offset' => $offset,
					'count' => $count
				));
				
				$items_vk = $api['response']['items'];
				$items_vk_total = $api['response']['count'];
				$output['response']['done'] = $count;
				$output['response']['total'] = $items_vk_total;
				
				$items_vk_list = array('id'=>array(),'uid'=>array());
				// Get VK IDs
				foreach($items_vk as $k => $v){
					$items_vk_list['id'][] = $v['id'];
					$items_vk_list['uid'][] = $v['id'].'_'.$v['adding_date'];
				}
				
				// Calculate FROM and TO values
				$from_to = $func->get_offset_range($offset,$count,$items_vk_total);
				
				array_unshift($log,"Синхронизация видеозаписей <b> ".$from_to['from']." - ".$from_to['to']." / ".$items_vk_total."</b>.\r\n");
				$output['response']['msg'][] = '<div><i class="far fa-fw fa-circle"></i> Синхронизация видеозаписей <b> '.$from_to['from'].' - '.$from_to['to'].' / '.$items_vk_total.'</b>.</div>';
				
				$items_list = array('id'=>array(),'uid'=>array());
				// Get local IDs
				$q = $db->query("SELECT id,date_added FROM vk_videos WHERE `id` IN(".implode(',',$items_vk_list['id']).")");
				while($row = $db->return_row($q)){
					$items_list['id'][] = $row['id'];
					$items_list['uid'][] = $row['id'].'_'.$row['date_added'];
				}
				
				// Get list of IDs which is NOT in local DB. So they are NEW
				// Compare VK IDs with local IDs
				$items_create = array_diff($items_vk_list['uid'],$items_list['uid']);
				
				if(sizeof($items_list['id']) > 0){
					// Update status for local IDs which was found
					$q = $db->query("UPDATE vk_videos SET `deleted` = 0 WHERE `id` IN(".implode(',',$items_list['id']).") AND `in_queue` = 0");
					$moved = $db->affected_rows();
					array_unshift($log,"Пропускаем сохраненные ранее видеозаписи: <b>".$moved."</b>\r\n");
					$output['response']['msg'][] = '<div><i class="far fa-fw fa-circle"></i> Пропускаем сохраненные ранее видеозаписи: <b>'.$moved.'</b></div>';
					unset($moved);
				}
				
				// Put new items to queue
				$items_data = array();
				
				// If we have new data
				if(sizeof($items_create) >= 1){
					$items_create_ids = array();
					foreach($items_create as $vck => $vcv){
						$vcv = explode("_",$vcv);
						$items_create_ids[$vcv[1]] = $vcv[0]; // Key: date_added, Value: id
					}
					
					foreach($items_vk as $k => $v){
						if(isset($items_create_ids[$v['adding_date']]) && $items_create_ids[$v['adding_date']] = $v['id'] && !isset($v['content_restricted'])){

						
							$items_data[$v['id']] = array(
								'owner_id' => (!is_numeric($v['owner_id']) ? 0 : $v['owner_id']),
								'title' => ($v['title'] == '' ? '- Unknown '.$v['id'].' -' : $v['title']),
								'desc' => ($v['description'] == '' ? '' : $v['description']),
								'duration' => (!is_numeric($v['duration']) ? 0 : $v['duration']),
								'preview_uri' => $func->get_video_image($v),
								'date' => $v['adding_date'],
								'player_uri' => $v['player'],
								'access_key' => (!isset($v['access_key']) ? '' : $v['access_key'])
							);
						}
						// Restricted content...
						if(isset($v['content_restricted']) && $v['content_restricted'] == 1){
							// Remove restricted item from creation list
							unset($items_create[$k]);
							
							array_unshift($log,"Skip: <b>".($v['title'] == '' ? 'ID '.$v['id'] : $v['title'])."</b> -> ".$v['content_restricted_message']."\r\n");
							$output['response']['msg'][] = '<div class="text-danger"><i class="fas fa-fw fa-exclamation-triangle"></i> Skip: <b>'.($v['title'] == '' ? 'ID '.$v['id'] : $v['title']).'</b> -> '.$v['content_restricted_message'].'</div>';
							
						}
					} // foreach end
				}
				
				if(!empty($items_data) && (sizeof($items_create) == sizeof($items_data))){
					$data_sql = array(0=>'');
					$data_limit = 250;
					$data_i = 1;
					$data_k = 0;
					foreach($items_data as $k => $v){
						$data_sql[$data_k] .= ($data_sql[$data_k] != '' ? ',' : '')."({$k},{$v['owner_id']},'".$db->real_escape($v['title'])."','".$db->real_escape($v['desc'])."',{$v['duration']},'{$v['preview_uri']}','','{$v['player_uri']}','{$v['access_key']}',{$v['date']},0,0,true,'',0,'',0,0)";
						$data_i++;
						if($data_i > $data_limit){
							$data_i = 1;
							$data_k++;
						}
					}
					
					foreach($data_sql as $k => $v){
						if(DEBUG_SYNC === false){
						$q = $db->query("INSERT INTO vk_videos (`id`,`owner_id`,`title`,`desc`,`duration`,`preview_uri`,`preview_path`,`player_uri`,`access_key`,`date_added`,`date_done`,`deleted`,`in_queue`,`local_path`,`local_size`,`local_format`,`local_w`,`local_h`) VALUES {$v}");
						} else {
							echo "INSERT INTO vk_videos (`id`,`owner_id`,`title`,`desc`,`duration`,`preview_uri`,`preview_path`,`player_uri`,`access_key`,`date_added`,`date_done`,`deleted`,`in_queue`,`local_path`,`local_size`,`local_format`,`local_w`,`local_h`) VALUES {$v}";
						}
					}
					
					array_unshift($log,"Новые видеозаписи добавлены в очередь: <b>".sizeof($items_create)."</b>\r\n");
					$output['response']['msg'][] = '<div><i class="far fa-fw fa-circle"></i> Новые видеозаписи добавлены в очередь: <b>'.sizeof($items_create).'</b></div>';
				}
				
				// Offset done.
				// Now check do we need an another run, or we done.
				
				// If we done with all items
				if(($offset+$count) >= $items_vk_total){
				
					// No unsynced items left. This is the end...
					// Let's make recount items
					$total = array('video'=>0,'deleted'=>0);
					
					$q1 = $db->query_row("SELECT COUNT(id) as v FROM vk_videos WHERE `deleted` = 0");
					$total['video'] = $q1['v'];
					
					$q2 = $db->query_row("SELECT COUNT(id) as v FROM vk_videos WHERE `deleted` = 1");
					$total['deleted'] = $q2['v'];
					
					// Update counters
					if(DEBUG_SYNC === false){
					$q5 = $db->query("UPDATE vk_counters SET `video` = (SELECT COUNT(*) FROM vk_videos WHERE `deleted` = 0)");
					}
					
					array_unshift($log,"Синхронизация видеозаписей завершена.\r\nВидео - <b>".$total['video']."</b>, на удаление - <b>".$total['deleted']."</b>\r\n");
					$output['response']['msg'][] = '<div><i class="fa fa-fw fa-check-circle text-success"></i> <strong>Снято!</strong> Синхронизация видеозаписей завершена.<br/>Видео - <b>'.$total['video'].'</b>, на удаление - <b>'.$total['deleted'].'</b></div>';
					
				} else {
					// Some items is not synced yed
					array_unshift($log,"Перехожу к следующей порции видеозаписей...\r\n");
					
					// Calculate offset and reload
					$part_new = $part+1;
					
					$output['response']['msg'][] = '<div><i class="far fa-fw fa-pause-circle"></i> Перехожу к следующей порции видеозаписей...</div>';
					$output['response']['next_uri'] = SLF.'?do=video&part='.$part_new;
					$output['response']['timer'] = $cfg['sync_video_next_cd'];
				}
				
			} // end if part
			// Save log to the DB
			if(DEBUG_SYNC === false){
			$q = $db->query("UPDATE vk_status SET `val` = CONCAT('".implode("\r\n",$log)."',`val`) WHERE `key` = 'log_video'");
			}
		} // DO Video end
		
		// Documents sync
		if($do == 'docs'){
			$don = true;
			
			// Check do we have a PART in GET
			$part = (isset($_GET['part'])) ? intval($_GET['part']) : '';
			
			// No PART? Let's start from the beginning.
			if($part == ''){
				$log = array();
				// Clean DB log before write something new
				$q = $db->query("UPDATE vk_status SET `val` = '' WHERE `key` = 'log_docs'");
				// Set all items to `deleted` state
				$q = $db->query("UPDATE vk_docs SET `deleted` = 1 WHERE `in_queue` = 0");
				
				array_unshift($log,"Начинаю синхронизацию...\r\n");
				
				$q = $db->query("UPDATE vk_status SET `val` = CONCAT('".implode("\r\n",$log)."',`val`) WHERE `key` = 'log_docs'");
				
				// Reload
				$output['response']['msg'][] = '<div><i class="fas fa-fw fa-play-circle text-info"></i> <b>Предъявите ваши документы!</b> Начинаю синхронизацию...</div>';
				$output['response']['next_uri'] = SLF.'?do=docs&part=1';
				$output['response']['timer'] = $cfg['sync_docs_start_cd'];
			} // end if PART is not found
			
			// Hey, PART found!
			if($part >= 1){
				$log = array();
				
				array_unshift($log,"Синхронизация начата.\r\n");
				
				$items_vk_total = 0;
				$count = 100;
				$offset = ($part-1)*$count;
				
				// We logged in, get VK items
				$api = $vk->api('docs.get', array(
					'owner_id' => $vk_session['vk_user'],
					'type' => 0, // фильтр по типу документа
					'offset' => $offset,
					'count' => $count
				));
				
				$items_vk = $api['response']['items'];
				$items_vk_total = $api['response']['count'];
				$output['response']['done'] = $count;
				$output['response']['total'] = $items_vk_total;
				
				$items_vk_list = array();
				// Get VK IDs
				foreach($items_vk as $k => $v){
					$items_vk_list[] = $v['id'];
				}
				
				// Calculate FROM and TO values
				$from_to = $func->get_offset_range($offset,$count,$items_vk_total);
				
				array_unshift($log,"Синхронизация документов <b> ".$from_to['from']." - ".$from_to['to']." / ".$items_vk_total."</b>.\r\n");
				$output['response']['msg'][] = '<div><i class="far fa-fw fa-circle"></i> Синхронизация документов <b> '.$from_to['from'].' - '.$from_to['to'].' / '.$items_vk_total.'</b>.</div>';
				
				$items_list = array();
				// Get local IDs
				$q = $db->query("SELECT id FROM vk_docs WHERE `id` IN(".implode(',',$items_vk_list).")");
				while($row = $db->return_row($q)){
					$items_list[] = $row['id'];
				}
				
				// Get list of IDs which is NOT in local DB. So they are NEW
				// Compare VK IDs with local IDs
				$items_create = array_diff($items_vk_list,$items_list);
				
				if(sizeof($items_list) > 0){
					// Update status for local IDs which was found
					$q = $db->query("UPDATE vk_docs SET `deleted` = 0 WHERE `id` IN(".implode(',',$items_list).") AND `in_queue` = 0");
					$moved = $db->affected_rows();
					array_unshift($log,"Пропускаем сохраненные ранее документы: <b>".$moved."</b>\r\n");
					$output['response']['msg'][] = '<div><i class="far fa-fw fa-circle"></i> Пропускаем сохраненные ранее документы: <b>'.$moved.'</b></div>';
					unset($moved);
				}
				
				// Put new items to queue
				$items_data = array();
				
				foreach($items_vk as $k => $v){
					if(in_array($v['id'],$items_create)){
						
						$v['pre'] = '';
						$v['prew'] = 0;
						$v['preh'] = 0;
						if(isset($v['preview'])){
							// Images
							if(isset($v['preview']['photo'])){
								// Get biggest preview
								foreach($v['preview']['photo']['sizes'] as $pk => $pv){
									if(    $pv['type'] == 's'){ $v['pre'] = $pv['src']; $v['prew'] = $pv['width']; $v['preh'] = $pv['height'];} // 75px
									elseif($pv['type'] == 'm'){ $v['pre'] = $pv['src']; $v['prew'] = $pv['width']; $v['preh'] = $pv['height'];} // 130 px
									elseif($pv['type'] == 'x'){ $v['pre'] = $pv['src']; $v['prew'] = $pv['width']; $v['preh'] = $pv['height'];} // 604 px
									elseif($pv['type'] == 'o'){ $v['pre'] = $pv['src']; $v['prew'] = $pv['width']; $v['preh'] = $pv['height'];} // 3:2 130 px
									elseif($pv['type'] == 'p'){ $v['pre'] = $pv['src']; $v['prew'] = $pv['width']; $v['preh'] = $pv['height'];} // 3:2 200 px
									elseif($pv['type'] == 'q'){ $v['pre'] = $pv['src']; $v['prew'] = $pv['width']; $v['preh'] = $pv['height'];} // 3:2 320 px
									elseif($pv['type'] == 'r'){ $v['pre'] = $pv['src']; $v['prew'] = $pv['width']; $v['preh'] = $pv['height'];} // 3:2 510 px
									elseif($pv['type'] == 'y'){ $v['pre'] = $pv['src']; $v['prew'] = $pv['width']; $v['preh'] = $pv['height'];} // 807 px
									elseif($pv['type'] == 'z'){ $v['pre'] = $pv['src']; $v['prew'] = $pv['width']; $v['preh'] = $pv['height'];} // 1082x1024
									elseif($pv['type'] == 'w'){ $v['pre'] = $pv['src']; $v['prew'] = $pv['width']; $v['preh'] = $pv['height'];} // 2560x2048
								}
							}
							// Audio MSG
							// no reason to do until VK disabled audio api
						} // Preview end
						
						$items_data[$v['id']] = array(
							'owner_id' => (!is_numeric($v['owner_id']) ? 0 : $v['owner_id']),
							'title' => ($v['title'] == '' ? 'Unknown '.$v['id'].'' : $v['title']),
							'size' => (!is_numeric($v['size']) ? 0 : $v['size']),
							'ext' => ($v['ext'] == '' ? 'unknown' : $v['ext']),
							'uri' => ($v['url'] == '' ? '' : $v['url']),
							'date' => $v['date'],
							'type' => $v['type'],
							'preview_uri' => $v['pre'],
							'width' => $v['prew'],
							'height' => $v['preh']
						);
					}
				} // foreach end
				
				if(!empty($items_data) && (sizeof($items_create) == sizeof($items_data))){
					$data_sql = array(0=>'');
					$data_limit = 250;
					$data_i = 1;
					$data_k = 0;
					foreach($items_data as $k => $v){
						$data_sql[$data_k] .= ($data_sql[$data_k] != '' ? ',' : '')."({$k},{$v['owner_id']},'".$db->real_escape($v['title'])."',{$v['size']},'".$db->real_escape($v['ext'])."','{$v['uri']}',{$v['date']},{$v['type']},'{$v['preview_uri']}','','{$v['width']}','{$v['height']}',0,1,'',0,0,0,0)";
						$data_i++;
						if($data_i > $data_limit){
							$data_i = 1;
							$data_k++;
						}
					}
					
					foreach($data_sql as $k => $v){
						$q = $db->query("INSERT INTO vk_docs (`id`,`owner_id`,`title`,`size`,`ext`,`uri`,`date`,`type`,`preview_uri`,`preview_path`,`width`,`height`,`deleted`,`in_queue`,`local_path`,`local_size`,`local_w`,`local_h`,`skipthis`) VALUES {$v}");
					}
					
					array_unshift($log,"Новые документы добавлены в очередь: <b>".sizeof($items_create)."</b>\r\n");
					$output['response']['msg'][] = '<div><i class="far fa-fw fa-circle"></i> Новые документы добавлены в очередь: <b>'.sizeof($items_create).'</b></div>';
				}
				
				// Offset done.
				// Now check do we need an another run, or we done.
				
				// If we done with all items
				if(($offset+$count) >= $items_vk_total){
				
					// No unsynced items left. This is the end...
					// Let's make recount items
					$total = array('docs'=>0,'deleted'=>0);
					
					$q1 = $db->query_row("SELECT COUNT(id) as v FROM vk_docs WHERE `deleted` = 0");
					$total['docs'] = $q1['v'];
					
					$q2 = $db->query_row("SELECT COUNT(id) as v FROM vk_docs WHERE `deleted` = 1");
					$total['deleted'] = $q2['v'];
					
					// Update counters
					$q5 = $db->query("UPDATE vk_counters SET `docs` = (SELECT COUNT(*) FROM vk_docs WHERE `deleted` = 0)");
					
					array_unshift($log,"Синхронизация документов завершена.\r\nДокументов - <b>".$total['docs']."</b>, на удаление - <b>".$total['deleted']."</b>\r\n");
					$output['response']['msg'][] = '<div><i class="fa fa-fw fa-check-circle text-success"></i> <strong>Распишитесь!</strong> Синхронизация документов завершена.<br/>Документов - <b>'.$total['docs'].'</b>, на удаление - <b>'.$total['deleted'].'</b></div>';
					
				} else {
					// Some items is not synced yed
					array_unshift($log,"Перехожу к следующей порции документов...\r\n");
					
					// Calculate offset and reload
					$part_new = $part+1;
					
					$output['response']['msg'][] = '<div><i class="far fa-fw fa-pause-circle"></i> Перехожу к следующей порции документов...</div>';
					$output['response']['next_uri'] = SLF.'?do=docs&part='.$part_new;
					$output['response']['timer'] = $cfg['sync_docs_next_cd'];
				}
				
			} // end if part
			// Save log to the DB
			$q = $db->query("UPDATE vk_status SET `val` = CONCAT('".implode("\r\n",$log)."',`val`) WHERE `key` = 'log_docs'");
		} // DO Documents end

	// END Of catch :: All DO methods should be INSIDE

	} catch (Exception $error) {
		$output['error'] = true;
		$output['response']['error_msg'] = '<div>'.$error->getMessage().'</div>';
	}
// end of Token Check
} else {
	// Token is NOT valid, re-auth?
$output['error'] = true;
$output['response']['error_msg'] = <<<E
    <div><i class="fas fa-fw fa-times-circle text-danger"></i> <span>Внимание!</span> Токен является недействительным. Необходимо авторизироваться.</div>
E;
}

if($don == false && $token_valid == true){
$output['error'] = true;
$output['response']['error_msg'] = <<<E
    <div><i class="fas fa-fw fa-exclamation-circle text-warning"></i> Нет заданий для синхронизации</div>
E;
}

// End of IF DO
} else {
$output['error'] = true;
$output['response']['error_msg'] = <<<E
    <div><i class="fas fa-fw fa-exclamation-circle text-warning"></i> Нет заданий для синхронизации</div>
E;
}

$db->close($res);

print json_encode($output);

?>