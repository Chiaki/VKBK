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

// Get Functions
require_once(ROOT.'classes/func.php');
$func = new func();

// Get Skin
require_once(ROOT.'classes/skin.php');
$skin = new skin();

// Get local counters for top menu
$lc = $db->query_row("SELECT * FROM vk_counters");

if(!$cfg['pj']){
	print $skin->header(array('extend'=>''));
	print $skin->navigation($lc);
}

$type_icons = array(
	'photo'		=> 'image',
	'audio'		=> 'music',
	'video'		=> 'film',
	'docs'		=> 'file',
	'attach'	=> 'paperclip',
	'mattach'	=> 'paperclip',
	'mwattach'	=> 'share',
	'cattach'	=> 'comment-alt'
);

$bar_total = $db->query_row("SELECT COUNT(*) as p FROM vk_photos WHERE album_id > ".SYSTEM_ALBUM." AND `skipthis` = 0");
$bar = $db->query_row("SELECT COUNT(*) as p FROM vk_photos WHERE album_id > ".SYSTEM_ALBUM." AND `in_queue` = 1 AND `skipthis` = 0");
$bar_queue['p'] = $bar['p'];
$per = $bar_total['p']/100;
if($bar_total['p'] > 0){
$done['pp'] = round(($bar_total['p'] - $bar_queue['p']) / $per, 2);
$done['p'] = ceil($done['pp']);
} else { $done['p'] = $done['pp'] = 0; }

$bar_total = $db->query_row("SELECT COUNT(*) as m FROM vk_music WHERE `deleted` = 0");
$bar = $db->query_row("SELECT COUNT(*) as m FROM vk_music WHERE `in_queue` = 1");
$bar_queue['m'] = $bar['m'];
$per = $bar_total['m']/100;
if($bar_total['m'] > 0){
$done['mm'] = round(($bar_total['m'] - $bar_queue['m']) / $per, 2);
$done['m'] = ceil($done['mm']);
} else { $done['m'] = $done['mm'] = 0; }

$bar_total = $db->query_row("SELECT COUNT(*) as v FROM vk_videos WHERE `deleted` = 0");
$bar = $db->query_row("SELECT COUNT(*) as v FROM vk_videos WHERE `in_queue` = 1");
$bar_queue['v'] = $bar['v'];
$per = $bar_total['v']/100;
if($bar_total['v'] > 0){
$done['vv'] = round(($bar_total['v'] - $bar_queue['v']) / $per, 2);
$done['v'] = ceil($done['vv']);
} else { $done['v'] = $done['vv'] = 0; }

$bar_total = $db->query_row("SELECT COUNT(*) as at FROM vk_attach WHERE `uri` != '' AND `is_local` = 0 AND `skipthis` = 0");
$bar = $db->query_row("SELECT COUNT(*) as at FROM vk_attach WHERE `path` = '' AND `uri` != '' AND `is_local` = 0 AND `skipthis` = 0");
$bar_queue['at'] = $bar['at'];
$per = $bar_total['at']/100;
if($bar_total['at'] > 0){
$done['att'] = round(($bar_total['at'] - $bar_queue['at']) / $per, 2);
$done['at'] = ceil($done['att']);
} else { $done['at'] = $done['att'] = 0; }

$bar_total = $db->query_row("SELECT COUNT(*) as dc FROM vk_docs WHERE `deleted` = 0 AND `skipthis` = 0");
$bar = $db->query_row("SELECT COUNT(*) as dc FROM vk_docs WHERE `in_queue` = 1 AND `skipthis` = 0");
$bar_queue['dc'] = $bar['dc'];
$per = $bar_total['dc']/100;
if($bar_total['dc'] > 0){
$done['dcc'] = round(($bar_total['dc'] - $bar_queue['dc']) / $per, 2);
$done['dc'] = ceil($done['dcc']);
} else { $done['dc'] = $done['dcc'] = 0; }

$bar_total = $db->query_row("SELECT COUNT(*) as msat FROM vk_messages_attach WHERE `uri` != '' AND `is_local` = 0 AND `skipthis` = 0");
$bar = $db->query_row("SELECT COUNT(*) as msat FROM vk_messages_attach WHERE `path` = '' AND `uri` != '' AND `is_local` = 0 AND `skipthis` = 0");
$bar_queue['msat']['total'] = $bar['msat'];
$per = $bar_total['msat']/100;
if($bar_total['msat'] > 0){
$done['msatt'] = round(($bar_total['msat'] - $bar_queue['msat']['total']) / $per, 2);
$done['msat'] = ceil($done['msatt']);
} else { $done['msat'] = $done['msatt'] = 0; }
if($bar_queue['msat']['total'] > 0){
	$msatq = $db->query("SELECT type, COUNT(*) as c FROM vk_messages_attach WHERE `path` = '' AND `uri` != '' AND `is_local` = 0 AND `skipthis` = 0 GROUP BY `type`");
	while($row = $db->return_row($msatq)){
		$bar_queue['msat'][$row['type']] = $row['c'];
	}
}

$bar_total = $db->query_row("SELECT COUNT(*) as mwsat FROM vk_messages_wall_attach WHERE `uri` != '' AND `is_local` = 0 AND `skipthis` = 0");
$bar = $db->query_row("SELECT COUNT(*) as mwsat FROM vk_messages_wall_attach WHERE `path` = '' AND `uri` != '' AND `is_local` = 0 AND `skipthis` = 0");
$bar_queue['mwsat']['total'] = $bar['mwsat'];
$per = $bar_total['mwsat']/100;
if($bar_total['mwsat'] > 0){
$done['mwsatt'] = round(($bar_total['mwsat'] - $bar_queue['mwsat']['total']) / $per, 2);
$done['mwsat'] = ceil($done['mwsatt']);
} else { $done['mwsat'] = $done['mwsatt'] = 0; }
if($bar_queue['mwsat']['total'] > 0){
	$mwsatq = $db->query("SELECT type, COUNT(*) as c FROM vk_messages_wall_attach WHERE `path` = '' AND `uri` != '' AND `is_local` = 0 AND `skipthis` = 0 GROUP BY `type`");
	while($row = $db->return_row($mwsatq)){
		$bar_queue['mwsat'][$row['type']] = $row['c'];
	}
}

$bar_total = $db->query_row("SELECT COUNT(*) as cattach FROM vk_wall_comments_attach WHERE `uri` != '' AND `is_local` = 0 AND `skipthis` = 0");
$bar = $db->query_row("SELECT COUNT(*) as cattach FROM vk_wall_comments_attach WHERE `path` = '' AND `uri` != '' AND `is_local` = 0 AND `skipthis` = 0");
$bar_queue['cattach']['total'] = $bar['cattach'];
$per = $bar_total['cattach']/100;
if($bar_total['cattach'] > 0){
$done['comatt'] = round(($bar_total['cattach'] - $bar_queue['cattach']['total']) / $per, 2);
$done['comat'] = ceil($done['comatt']);
} else { $done['cattach'] = $done['comatt'] = 0; }
if($bar_queue['cattach']['total'] > 0){
	$comatq = $db->query("SELECT type, COUNT(*) as c FROM vk_wall_comments_attach WHERE `path` = '' AND `uri` != '' AND `is_local` = 0 AND `skipthis` = 0 GROUP BY `type`");
	while($row = $db->return_row($comatq)){
		$bar_queue['cattach'][$row['type']] = $row['c'];
	}
}


$all_queue = $bar_queue['p'] + $bar_queue['m'] + $bar_queue['v'] + $bar_queue['at'] + $bar_queue['dc'] + $bar_queue['msat']['total'] + $bar_queue['mwsat']['total'] + $bar_queue['cattach']['total'];
$no_queue = true;

// Profiles & Groups
$pr = $db->query_row("SELECT COUNT(*) as c FROM `vk_profiles` WHERE `photo_path` = ''");
$all_queue += $pr['c'];
$gr = $db->query_row("SELECT COUNT(*) as c FROM `vk_groups` WHERE `photo_path` = ''");
$all_queue += $gr['c'];

// Fix for counter if queue active
if($all_queue > 0 && isset($_GET['t'])){ $all_queue--; }

print <<<E
<div class="nav-scroller bg-white box-shadow mb-4" style="position:relative;">
    <nav class="nav nav-underline">
		<span class="nav-link active"><i class="fa fa-cloud-download-alt"></i> Очередь закачки {$all_queue}</span>
    </nav>
</div>
<div class="container">
	<div class="d-flex justify-content-center white-box p-2 py-3 mb-4 mx-0" style="white-space:nowrap;">
E;

// Show last queue records
$show = 25;
$bar = array();

// Make a progress bars
// Photo
$bar[0] = array('fa' => $type_icons['photo'],'name' => 'Фотографии','perx' => $done['pp'],'per' => $done['p'],'bar' => 'success');

// Audio
$bar[1] = array('fa' => $type_icons['audio'],'name' => 'Аудиозаписи','perx' => $done['mm'],'per' => $done['m'],'bar' => 'warning');

// Video
$bar[2] = array('fa' => $type_icons['video'],'name' => 'Видеозаписи','perx' => $done['vv'],'per' => $done['v'],'bar' => 'info');

// Attachments
$bar[3] = array('fa' => $type_icons['attach'],'name' => 'Вложения','perx' => $done['att'],'per' => $done['at'],'bar' => 'primary');

// Documents
$bar[4] = array('fa' => $type_icons['docs'],'name' => 'Документы','perx' => $done['dcc'],'per' => $done['dc'],'bar' => 'danger');

// MessagesAttachments
$bar[5] = array('fa' => $type_icons['mattach'],'name' => 'Диалоги','perx' => $done['msatt'],'per' => $done['msat'],'bar' => 'secondary');

// MessagesWallAttachments
$bar[6] = array('fa' => $type_icons['mwattach'],'name' => 'Репосты','perx' => $done['mwsatt'],'per' => $done['mwsat'],'bar' => 'secondary');

// WallCommentsAttachments
$bar[7] = array('fa' => $type_icons['cattach'],'name' => 'Коммент.','perx' => $done['comatt'],'per' => $done['comat'],'bar' => 'secondary');

foreach($bar as $bark => $barv){
	print $skin->queue_progress_bar($barv);
}

print <<<E
		<div class="clearfix"></div>
	</div>
<div class="table-responsive">
E;
unset($done);

$skip_list = isset($_GET['skip']) ? preg_replace("/[^0-9\,]/","",$_GET['skip']) : '';
if(!isset($_GET['auto'])){ $_GET['auto'] = false; } 

if(isset($_GET['id']) && isset($_GET['t'])){
	$queue_id = (isset($_GET['id']) && is_numeric($_GET['id'])) ? intval($_GET['id']) : 0;
	$queue_oid = (isset($_GET['oid']) && is_numeric($_GET['oid'])) ? intval($_GET['oid']) : 0;
	$don = false;
	$error_code = '';
	mb_internal_encoding("UTF-8");

	// Queue Functions
	require_once(ROOT.'classes/queue.php');
	$qe = new queue();
	$qe->cfg = $cfg;
	$qe->db = $db;
	$qe->func = $func;
	$qe->skin = $skin;
	
	// Pictures
	if($queue_id > 0 && $_GET['t']=='p'){
		$don = true;
		// Get photo info
		$q = $db->query_row("SELECT * FROM vk_photos WHERE `id` = {$queue_id}");//.($skip_list != '' ? "AND `id` NOT IN (".$skip_list.")" : "")
		if($q['uri'] != ''){
			
			// Are you reagy kids? YES Capitan Curl!
			require_once(ROOT.'classes/curl.php');
			$c = new cu();
			$c->curl_on();
			preg_match("/[^\/]+$/",$q['uri'],$n);
			$f = date("Y-m",$q['date_added']);
			
			$out = $c->curl_req(array(
					'uri' => $q['uri'],
					'method'=>'',
					'return'=>1
			));
			
			if($out['err'] == 0 && $out['errmsg'] == '' && $out['content'] != '' && substr($out['content'],0,5) != '<html' && substr($out['content'],0,9) != '<!DOCTYPE'){
				$saved = $c->file_save(array('path'=>$cfg['photo_path'].$f.'/','name'=>$n[0]),$out['content']);
				if($saved){
					print $skin->show_alert('success','far fa-file','Файл сохранен');
					
					$q = $db->query("UPDATE vk_photos SET `in_queue` = 0, `date_done` = ".time().", `path` = '".$cfg['photo_path'].$f."/".$n[0]."', `saved` = 1, `hash` = '".md5_file($cfg['photo_path'].$f."/".$n[0])."' WHERE `id` = ".$queue_id."");
					
					if($_GET['auto'] == '1'){
						$nrow = $db->query_row("SELECT id FROM vk_photos WHERE album_id > ".SYSTEM_ALBUM." AND `in_queue` = 1 ORDER BY date_added DESC");
						if($nrow['id'] > 0){
							print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_photo_next_cd']."</span> сек.","queue.php?t=p&id=".$nrow['id']."&auto=1",$cfg['sync_photo_next_cd']);
						}
					}
					
				} else {
					print $skin->show_alert('danger','fas fa-exclamation-triangle','Ошибка при сохранении файла');
				}
			} else {
					// If error, let's try to see wtf is going on
					$error_code = false;
					if($func->is_html_response($out['content'])){
						$error_code = $skin->remote_server_error($out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 )));
					}
					// Something wrong with response or connection
					$skin->queue_no_data($error_code,"t=p&id=".$queue_id."&oid=0",$queue_id);
			}
			
		} else {
			print $skin->show_alert('danger','fas fa-exclamation-triangle','ID найден в очереди но ссылка на файл отсутствует');
		}
	} // End of T = P
	
	// Music
	if($queue_id > 0 && $_GET['t']=='m'){
		$don = true;
		// Get audio info
		$q = $db->query_row("SELECT * FROM vk_music WHERE `id` = {$queue_id}");
		if($q['uri'] != ''){
			
			// Are you reagy kids? YES Capitan Curl!
			require_once(ROOT.'classes/curl.php');
			$c = new cu();
			$c->curl_on();
			//$q['uri'] = preg_replace("/\?extra\=.*/","",$q['uri']);
			preg_match("/[^\.]+$/",$q['uri'],$n);
			if(mb_strlen($q['title']) > 200){ $q['title'] = mb_substr($row['artist'],0,200); }
			$nam = $c->clean_name($q['artist'].' - '.$q['title'].' ['.$q['id'].'].mp3');
			$win = $c->win_name($nam);
			// Double check this f**kn' filename
			// If filename was converted for windows we need revert codepage to UTF-8 before DB insert
			// Or hell will be on earth... ><
			if($win[0] == true){
				$cnam = iconv("CP1251","UTF-8",$win[1]);
			} else {
				$cnam = $win[1];
			}
			$fnam = $win[1];
			
			$out = $c->curl_req(array(
				'uri' => $q['uri'],
				'method'=>'',
				'return'=>1
			));

			if($out['err'] == 0 && $out['errmsg'] == '' && $out['content'] != '' && substr($out['content'],0,5) != '<html' && substr($out['content'],0,9) != '<!DOCTYPE'){
				$saved = $c->file_save(array('path'=>$cfg['music_path'],'name'=>$fnam),$out['content']);
				if($saved){
					print $skin->show_alert('success','far fa-file','Файл <b>'.$nam.'</b> сохранен');
					
					$q = $db->query("UPDATE vk_music SET `in_queue` = 0, `date_done` = ".time().", `path` = '".$cfg['music_path'].$db->real_escape($cnam)."', `saved` = 1, `hash` = '".md5_file($cfg['music_path'].$fnam)."' WHERE `id` = ".$queue_id."");
					
					if($_GET['auto'] == '1'){
						$nrow = $db->query_row("SELECT id FROM vk_music WHERE `in_queue` = 1 ORDER BY date_added DESC");
						if($nrow['id'] > 0){
							print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_music_next_cd']."</span> сек.","queue.php?t=m&id=".$nrow['id']."&auto=1",$cfg['sync_music_next_cd']);
						}
					}
					
				} else {
					print $skin->show_alert('danger','fas fa-exclamation-triangle','Ошибка при сохранении файла '.$nam);
				}
			} else {
				// If error, let's try to see wtf is going on
				if((substr($out['content'],0,5) == '<html') || (substr($out['content'],0,9) == '<!DOCTYPE')){
					$out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 ));
					if(isset($out['header'])){ $error_code = "<br/>Ответ сервера: {$out['header']['http_code']}"; }
				}
				// Something wrong with response or connection
				print $skin->show_alert('danger','fas fa-exclamation-triangle','Невозможно получить данные с удаленного хоста для '.$nam.$error_code);
			}
			
		} else {
			print $skin->show_alert('danger','fas fa-exclamation-triangle','ID найден в очереди но ссылка на файл отсутствует');
		}
	} // End of T = M
	
	// Videos
	if($queue_id > 0 && $_GET['t']=='v'){
		$don = true;
		// Get video info
		$q = $db->query_row("SELECT * FROM vk_videos WHERE `id` = {$queue_id} AND `owner_id` = {$queue_oid}");
		if($q['preview_uri'] != ''){
			
			// Are you reagy kids? YES Capitan Curl!
			require_once(ROOT.'classes/curl.php');
			$c = new cu();
			$c->curl_on();
			preg_match("/[^\/]+$/",$q['preview_uri'],$n);
			$out = $c->curl_req(array(
				'uri' => $q['preview_uri'],
				'method'=>'',
				'return'=>1
			));
			
			if($out['err'] == 0 && $out['errmsg'] == '' && $out['content'] != '' && substr($out['content'],0,5) != '<html' && substr($out['content'],0,9) != '<!DOCTYPE'){
				$saved = $c->file_save(array('path'=>$cfg['video_path'],'name'=>$n[0]),$out['content']);
				if($saved){
					print $skin->show_alert('success','far fa-file','Файл сохранен');
					
					$q = $db->query("UPDATE vk_videos SET `in_queue` = 0, `date_done` = ".time().", `preview_path` = '".$cfg['video_path'].$n[0]."' WHERE `id` = ".$queue_id." AND `owner_id` =".$queue_oid);
					
					if($_GET['auto'] == '1'){
						$nrow = $db->query_row("SELECT id,owner_id FROM vk_videos WHERE `in_queue` = 1 ".($skip_list != '' ? "AND `id` NOT IN (".$skip_list.")" : "")." ORDER BY date_added DESC");
						if($nrow['id'] > 0){
							print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_video_next_cd']."</span> сек.","queue.php?t=v&id=".$nrow['id']."&oid=".$nrow['owner_id']."&auto=1".($skip_list != '' ? "&skip=".$skip_list : ""),$cfg['sync_video_next_cd']);
						}
					}
					
				} else {
					print $skin->show_alert('danger','fas fa-exclamation-triangle','Ошибка при сохранении превю файла');
				}
			} else {
				// If error, let's try to see wtf is going on
				if((substr($out['content'],0,5) == '<html') || (substr($out['content'],0,9) == '<!DOCTYPE')){
					$out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 ));
					if(isset($out['header'])){ $error_code = "<br/>Ответ сервера: {$out['header']['http_code']}"; }
				}
				// Something wrong with response or connection
				print $skin->show_alert('danger','fas fa-exclamation-triangle','Невозможно получить превью #'.$queue_id.' с удаленного хоста для '.$n[0].$error_code);
				
				// Move ID to skip list & continue if server response is contain html
				if($_GET['auto'] == '1' && substr($out['content'],0,5) == '<html'){
					$skip_row = ($_GET['skip'] != '') ? $_GET['skip'].','.$queue_id : $queue_id;
					$nrow = $db->query_row("SELECT id,owner_id FROM vk_videos WHERE `in_queue` = 1 && `id` < {$queue_id} ORDER BY date_added DESC");
					if($nrow['id'] > 0){
						print $skin->reload('info',"Пропускаем #".$queue_id." следующий #".$nrow['id'].". Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_music_error_cd']."</span> сек.","queue.php?t=v&id=".$nrow['id']."&oid=".$nrow['owner_id']."&auto=1&skip=".$skip_row."",$cfg['sync_music_error_cd']);
					}
				}
			}
			
		} else {
			print $skin->show_alert('danger','fas fa-exclamation-triangle','ID найден в очереди но ссылка на файл отсутствует');
		}
	} // End of T = V
	
	// Documents
	if($queue_id > 0 && $_GET['t']=='dc'){
		$don = true;
		// Get document info
		$q = $db->query_row("SELECT * FROM vk_docs WHERE `id` = {$queue_id}");
		if($q['uri'] != ''){
			
			// Are you reagy kids? YES Capitan Curl!
			require_once(ROOT.'classes/curl.php');
			$c = new cu();
			$c->curl_on();
			$f = date("Y-m",$q['date']);
			$out = $c->curl_req(array(
				'uri' => $q['uri'],
				'method'=>'',
				'return'=>1
			));
			
			if($out['err'] == 0 && $out['errmsg'] == '' && $out['content'] != '' && substr($out['content'],0,5) != '<html' && substr($out['content'],0,9) != '<!DOCTYPE'){
				$saved = $c->file_save(array('path'=>$cfg['docs_path'].$f.'/','name'=>$q['id'].'.'.$q['ext']),$out['content']);
				if($saved){
					print $skin->show_alert('success','far fa-file','Файл сохранен');

					$prev_q = '';
					if(($q['type'] == 3 || $q['type'] == 4) && $q['preview_uri'] != ''){
						$out_pre = $c->curl_req(array(
							'uri' => $q['preview_uri'],
							'method'=>'',
							'return'=>1
						));
						if($out_pre['err'] == 0 && $out_pre['errmsg'] == '' && $out_pre['content'] != '' && substr($out_pre['content'],0,5) != '<html' && substr($out_pre['content'],0,9) != '<!DOCTYPE'){
							preg_match("/[^\.]+$/",$q['preview_uri'],$np);
							$saved_pre = $c->file_save(array('path'=>$cfg['docs_path'].'preview/','name'=>$q['id'].'.'.$np['0']),$out_pre['content']);
							if($saved){
								print $skin->show_alert('success','far fa-file','Превью сохранено');
								
								$prev_q = ", `preview_path` = '".$cfg['docs_path']."preview/".$q['id'].".".$np[0]."'";
							}
						}
					}

					$q = $db->query("UPDATE vk_docs SET `in_queue` = 0, `local_path` = '".$cfg['docs_path'].$f."/".$q['id'].".".$q['ext']."'".$prev_q." WHERE `id` = ".$queue_id."");
					
					if($_GET['auto'] == '1'){
						$nrow = $db->query_row("SELECT id FROM vk_docs WHERE `in_queue` = 1 ORDER BY date DESC");
						if($nrow['id'] > 0){
							print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_docs_next_cd']."</span> сек.","queue.php?t=dc&id=".$nrow['id']."&auto=1",$cfg['sync_docs_next_cd']);
						}
					}
					
				} else {
					print $skin->show_alert('danger','fas fa-exclamation-triangle','Ошибка при сохранении файла');
				}
			} else {
					// If error, let's try to see wtf is going on
					$error_code = false;
					if($func->is_html_response($out['content'])){
						$error_code = $skin->remote_server_error($out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 )));
					}
					// Something wrong with response or connection
					$skin->queue_no_data($error_code,"t=dc&id=".$queue_id."&oid=0",$queue_id);
			}
			
		} else {
			print $skin->show_alert('danger','fas fa-exclamation-triangle','ID найден в очереди но ссылка на файл отсутствует');
		}
	} // End of T = DC
	
	// Profiles
	if($queue_id > 0 && $_GET['t']=='pr'){
		$don = true;
		
		// Call Mr.Queue to save us!
		$qe->sprgr($queue_id,'pr',$_GET['auto']);
		
	} // End of T = PR
	
	// Groups
	if($queue_id > 0 && $_GET['t']=='gr'){
		$don = true;
		
		// Call Mr.Queue to save us!
		$qe->sprgr($queue_id,'gr',$_GET['auto']);
		
	} // End of T = GR
	
	// Attach - Photo
	if($queue_id > 0 && $_GET['t']=='atph' && $queue_oid != 0){
		$don = true;
		
		// Call Mr.Queue to save us!
		$qe->save_as_attach($queue_id,$queue_oid,'atph',$_GET['auto']);
		
	} // End of T = ATPH
	
	// Attach - Video (preview)
	if($queue_id > 0 && $_GET['t']=='atvi' && $queue_oid != 0){
		$don = true;
		
		// Call Mr.Queue to save us!
		$qe->save_as_attach($queue_id,$queue_oid,'atvi',$_GET['auto']);
		
	} // End of T = ATVI
	
	// Attach - Link
	if($queue_id > 0 && $_GET['t']=='atli' && $queue_oid != 0){
		$don = true;
		
		// Call Mr.Queue to save us!
		$qe->save_as_attach($queue_id,$queue_oid,'atli',$_GET['auto']);
		
	} // End of T = ATLI
	
	// Attach - Music
	if($queue_id > 0 && $_GET['t']=='atau' && $queue_oid != 0){
		$don = true;
		// Get audio info
		$q = $db->query_row("SELECT * FROM vk_attach WHERE `type` = 'audio' AND `attach_id` = {$queue_id} AND `owner_id` = {$queue_oid}");
		if($q['uri'] != ''){
			
			// Are you reagy kids? YES Capitan Curl!
			require_once(ROOT.'classes/curl.php');
			$c = new cu();
			$c->curl_on();
			
			// Get file name
			$q['uri'] = preg_replace("/\?extra\=.*/","",$q['uri']);
			preg_match("/[^\.]+$/",$q['uri'],$n);
			if(mb_strlen($q['title']) > 200){ $q['title'] = mb_substr($row['caption'],0,200); }
			$nam = $c->clean_name($q['caption'].' - '.$q['title'].' ['.$q['attach_id'].'].'.$n[0]);
			$win = $c->win_name($nam);
			// Double check this f**kn' filename
			// If filename was converted for windows we need revert codepage to UTF-8 before DB insert
			// Or hell will be on earth... ><
			if($win[0] == true){
				$cnam = iconv("CP1251","UTF-8",$win[1]);
			} else {
				$cnam = $win[1];
			}
			$fnam = $win[1];
			
			// Check do we have this file already ( useful if you are developer and pucked up attachments DB :D )
			if(is_file($cfg['music_path'].'attach/'.$fnam)){
				print $skin->show_alert('info','far fa-file','Файл <b>'.$nam.'</b> найден локально');
				
				$q1 = $db->query("UPDATE vk_attach SET `path` = '".$cfg['music_path'].'attach/'.$db->real_escape($cnam)."' WHERE `type` = 'audio' AND `attach_id` = ".$queue_id." AND `owner_id` = ".$queue_oid."");
				
				if($_GET['auto'] == '1'){
					$nrow = $db->query_row("SELECT attach_id, owner_id FROM vk_attach WHERE `path` = '' AND `type` = 'audio' AND `uri` != '' AND `is_local` = 0");
					if($nrow['attach_id'] > 0){
						print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_found_local']."</span> сек.","queue.php?t=atau&id=".$nrow['attach_id']."&oid=".$nrow['owner_id']."&auto=1",$cfg['sync_found_local']);
					}
				}
			} else {
			
				$out = $c->curl_req(array(
						'uri' => $q['uri'],
						'method'=>'',
						'return'=>1
				));

				if($out['err'] == 0 && $out['errmsg'] == '' && $out['content'] != '' && substr($out['content'],0,5) != '<html' && substr($out['content'],0,9) != '<!DOCTYPE'){
					$saved = $c->file_save(array('path'=>$cfg['music_path'].'attach/','name'=>$fnam),$out['content']);
					if($saved){
						print $skin->show_alert('success','far fa-file','Файл <b>'.$nam.'</b> сохранен');
						
						$q1 = $db->query("UPDATE vk_attach SET `path` = '".$cfg['music_path'].'attach/'.$db->real_escape($cnam)."' WHERE `attach_id` = ".$queue_id." AND `owner_id` = ".$queue_oid."");
						
						if($_GET['auto'] == '1'){
							$nrow = $db->query_row("SELECT attach_id, owner_id FROM vk_attach WHERE `path` = '' AND `type` = 'audio' AND `uri` != '' AND `is_local` = 0");
							if($nrow['attach_id'] > 0){
								print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_music_next_cd']."</span> сек.","queue.php?t=atau&id=".$nrow['attach_id']."&oid=".$nrow['owner_id']."&auto=1",$cfg['sync_music_next_cd']);
							}
						}
					
					} else {
						print $skin->show_alert('danger','fas fa-exclamation-triangle','Ошибка при сохранении файла '.$nam);
					}
				} else {
					// If error, let's try to see wtf is going on
					if((substr($out['content'],0,5) == '<html') || (substr($out['content'],0,9) == '<!DOCTYPE')){
						$out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 ));
						if(isset($out['header'])){ $error_code = "<br/>Ответ сервера: {$out['header']['http_code']}"; }
					}
					// Something wrong with response or connection
					print $skin->show_alert('danger','fas fa-exclamation-triangle','Невозможно получить данные с удаленного хоста для '.$nam.$error_code);
				}
			} // end of local file check fail
		} else {
			print $skin->show_alert('danger','fas fa-exclamation-triangle','ID найден в очереди но ссылка на файл отсутствует.');
		}
	} // End of T = ATAU
	
	// Attach - Documents
	if($queue_id > 0 && $_GET['t']=='atdc'){
		$don = true;
		
		// Call Mr.Queue to save us!
		$qe->save_as_double_attach($queue_id,$queue_oid,'atdc',$_GET['auto']);
		
	} // End of T = ATDC
	
	// Message - Attach - Stickers
	if($queue_id > 0 && $_GET['t']=='matst'){
		$don = true;
		// Get sticker info
		$q = $db->query_row("SELECT * FROM vk_messages_attach WHERE `type` = 'sticker' AND `date` = {$queue_id}");
		if($q['uri'] != ''){
			
			// Get file name
			preg_match_all("/\/([0-9]+)\/[^\.]+\.([^\.]+)$/",$q['uri'],$n);
			
			if(!isset($n[1][0]) || empty($n[1][0]) || !isset($n[2][0]) || empty($n[1][0])){
				// Something wrong with url?... Who da fuck is did this?!
				// Ofc VK can change API without any notice, ass'oles =_=
				if(substr($q['uri'],15,14) == 'stickers_proxy'){
					preg_match_all("/sticker_id=([0-9]+)/",$q['uri'],$n);
					$n[2][0] = 'png';
				}
				// And again
				if(strpos($q['uri'],"vk.com/sticker/") !== false){
					preg_match_all("/[0-9]\-([0-9]+)\-512b?\-[0-9]/",$q['uri'],$n);
					// AND AGAIN! >_<
					if(empty($n[1][0])){
						preg_match_all("/[0-9]\-([0-9]+)\-512/",$q['uri'],$n);
					}
					$n[2][0] = 'png';
				}
			}
			
			// Check do we have this file already ( useful if you are developer and pucked up attachments DB :D )
			if(is_file(ROOT.'data/stickers/'.$n[1][0].'.'.$n[2][0])){
				print $skin->show_alert('info','far fa-file','Файл найден локально');
				
				$q = $db->query("UPDATE vk_messages_attach SET `is_local` = 1, `path` = '".$n[1][0].".".$n[2][0]."' WHERE `type` = 'sticker' AND `uri` = '".$q['uri']."'");
				
			} else {
				
				// Are you reagy kids? YES Capitan Curl!
				require_once(ROOT.'classes/curl.php');
				$c = new cu();
				$c->curl_on();
				
				$out = $c->curl_req(array(
						'uri' => $q['uri'],
						'method'=>'',
						'return'=>1
				));
				
				if($out['err'] == 0 && $out['errmsg'] == '' && $out['content'] != '' && substr($out['content'],0,5) != '<html' && substr($out['content'],0,9) != '<!DOCTYPE'){
					$saved = $c->file_save(array('path'=>ROOT.'data/stickers/','name'=>$n[1][0].'.'.$n[2][0]),$out['content']);
					if($saved){
						print $skin->show_alert('success','far fa-file','Файл сохранен');
						
						$q = $db->query("UPDATE vk_messages_attach SET `is_local` = 1, `path` = '".$n[1][0].".".$n[2][0]."' WHERE `type` = 'sticker' AND `uri` = '".$q['uri']."'");
						
					} else {
						print $skin->show_alert('danger','fas fa-exclamation-triangle','Ошибка при сохранении файла');
					}
				} else {
					// If error, let's try to see wtf is going on
					$error_code = false;
					if($func->is_html_response($out['content'])){
						$error_code = $skin->remote_server_error($out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 )));
					}
					// Something wrong with response or connection
					$skin->queue_no_data($error_code,"t=matst&id=".$queue_id."&oid=0",$queue_id);
				}
			} // end of local file check fail
		} else {
			print $skin->show_alert('danger','fas fa-exclamation-triangle','ID найден в очереди но ссылка на файл отсутствует.');
		}
	} // End of T = MATST
	
	// Message - Attach - Photo
	if($queue_id > 0 && $_GET['t']=='matph' && $queue_oid != 0){
		$don = true;
		$qe->save_as_attach($queue_id,$queue_oid,'matph',$_GET['auto']);
	} // End of T = MATPH
	
	// Message - Attach - Documents
	if($queue_id > 0 && $_GET['t']=='matdc'){
		$don = true;
		$qe->save_as_double_attach($queue_id,$queue_oid,'matdc',$_GET['auto']);
	} // End of T = MATDC
	
	// Message - Attach - Link
	if($queue_id > 0 && $_GET['t']=='matli' && $queue_oid != 0){
		$don = true;
		$qe->save_as_attach($queue_id,$queue_oid,'matli',$_GET['auto']);
	} // End of T = MATLI
	
	// Message - Attach - Video (preview)
	if($queue_id > 0 && $_GET['t']=='matvi' && $queue_oid != 0){
		$don = true;
		$qe->save_as_attach($queue_id,$queue_oid,'matvi',$_GET['auto']);
	} // End of T = MATVI
	
	// Message - Wall - Attach - Photo
	if($queue_id > 0 && $_GET['t']=='mwatph' && $queue_oid != 0){
		$don = true;
		$qe->save_as_attach($queue_id,$queue_oid,'mwatph',$_GET['auto']);
	} // End of T = MWATPH
	
	// Message - Wall - Attach - Video
	if($queue_id > 0 && $_GET['t']=='mwatvi' && $queue_oid != 0){
		$don = true;
		$qe->save_as_attach($queue_id,$queue_oid,'mwatvi',$_GET['auto']);
	} // End of T = MWATVI
	
	// Message - Wall - Attach - Link
	if($queue_id > 0 && $_GET['t']=='mwatli' && $queue_oid != 0){
		$don = true;
		$qe->save_as_attach($queue_id,$queue_oid,'mwatli',$_GET['auto']);
	} // End of T = MWATLI
	
	// Message - Wall - Attach - Document
	if($queue_id > 0 && $_GET['t']=='mwatdc'){
		$don = true;
		$qe->save_as_double_attach($queue_id,$queue_oid,'mwatdc',$_GET['auto']);
	} // End of T = MWATDC
	
	// Wall - Comments - Attach - Photo
	if($queue_id > 0 && $_GET['t']=='catph' && $queue_oid != 0){
		$don = true;
		$qe->save_as_attach($queue_id,$queue_oid,'catph',$_GET['auto']);
	} // End of T = CATPH
	
	// Wall - Comments - Attach - Video
	if($queue_id > 0 && $_GET['t']=='catvi' && $queue_oid != 0){
		$don = true;
		$qe->save_as_attach($queue_id,$queue_oid,'catvi',$_GET['auto']);
	} // End of T = CATVI
	
	// Wall - Comments - Attach - Link
	if($queue_id > 0 && $_GET['t']=='catli' && $queue_oid != 0){
		$don = true;
		$qe->save_as_attach($queue_id,$queue_oid,'catli',$_GET['auto']);
	} // End of T = CATLI
	
	// Wall - Comments - Attach - Document
	if($queue_id > 0 && $_GET['t']=='catdc'){
		$don = true;
		$qe->save_as_double_attach($queue_id,$queue_oid,'catdc',$_GET['auto']);		
	} // End of T = CATDC
	
	// Comment - Attach - Stickers
	if($queue_id > 0 && $_GET['t']=='catst'){
		$don = true;
		// Get sticker info
		$q = $db->query_row("SELECT * FROM vk_wall_comments_attach WHERE `type` = 'sticker' AND `date` = {$queue_id}");
		if($q['uri'] != ''){
			
			// Get file name
			preg_match_all("/\/([0-9]+)\/[^\.]+\.([^\.]+)$/",$q['uri'],$n);
			
			if(!isset($n[1][0]) || empty($n[1][0]) || !isset($n[2][0]) || empty($n[1][0])){
				// Something wrong with url?... Who da fuck is did this?!
				// Ofc VK can change API without any notice, ass'oles =_=
				if(substr($q['uri'],15,14) == 'stickers_proxy'){
					preg_match_all("/sticker_id=([0-9]+)/",$q['uri'],$n);
					$n[2][0] = 'png';
				}
				// And again
				if(strpos($q['uri'],"vk.com/sticker/") !== false){
					preg_match_all("/[0-9]\-([0-9]+)\-512b?\-[0-9]/",$q['uri'],$n);
					// AND AGAIN! >_<
					if(empty($n[1][0])){
						preg_match_all("/[0-9]\-([0-9]+)\-512/",$q['uri'],$n);
					}
					$n[2][0] = 'png';
				}
			}
			
			// Check do we have this file already ( useful if you are developer and pucked up attachments DB :D )
			if(is_file(ROOT.'data/stickers/'.$n[1][0].'.'.$n[2][0])){
				print $skin->show_alert('info','far fa-file','Файл найден локально');
				
				$q = $db->query("UPDATE vk_wall_comments_attach SET `is_local` = 1, `path` = '".$n[1][0].".".$n[2][0]."' WHERE `type` = 'sticker' AND `uri` = '".$q['uri']."'");
				
			} else {
				
				// Are you reagy kids? YES Capitan Curl!
				require_once(ROOT.'classes/curl.php');
				$c = new cu();
				$c->curl_on();
				
				$out = $c->curl_req(array(
						'uri' => $q['uri'],
						'method'=>'',
						'return'=>1
				));
				
				if($out['err'] == 0 && $out['errmsg'] == '' && $out['content'] != '' && substr($out['content'],0,5) != '<html' && substr($out['content'],0,9) != '<!DOCTYPE'){
					$saved = $c->file_save(array('path'=>ROOT.'data/stickers/','name'=>$n[1][0].'.'.$n[2][0]),$out['content']);
					if($saved){
						print $skin->show_alert('success','far fa-file','Файл сохранен');
						
						$q = $db->query("UPDATE vk_wall_comments_attach SET `is_local` = 1, `path` = '".$n[1][0].".".$n[2][0]."' WHERE `type` = 'sticker' AND `uri` = '".$q['uri']."'");
						
					} else {
						print $skin->show_alert('danger','fas fa-exclamation-triangle','Ошибка при сохранении файла');
					}
				} else {
					// If error, let's try to see wtf is going on
					$error_code = false;
					if($func->is_html_response($out['content'])){
						$error_code = $skin->remote_server_error($out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 )));
					}
					// Something wrong with response or connection
					$skin->queue_no_data($error_code,"t=catst&id=".$queue_id."&oid=0",$queue_id);
				}
			} // end of local file check fail
		} else {
			print $skin->show_alert('danger','fas fa-exclamation-triangle','ID найден в очереди но ссылка на файл отсутствует.');
		}
	} // End of T = СATST
	
	if($don == false) {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Неправильный тип или ID</div>
E;
	}
	
}

print <<<E
            <table class="table table-sm table-hover small white-box">
              <thead>
                <tr>
				  <th class="text-center">Тип</th>
                  <th>ID#</th>
				  <th>URL</th>
				  <th>Добавлено</th>
				  <th>Сохранить</th>
                </tr>
              </thead>
              <tbody>
E;

$btnclass = 'btn btn-sm btn-outline-primary';
$btnicon = 'fas fa-download fa-fw';
$btniconauto = 'fas fa-sync fa-fw';

$first['p'] = true;
if($bar_queue['p'] > 0){
	$r = $db->query("SELECT * FROM vk_photos WHERE `in_queue` = 1 AND `skipthis` = 0 ORDER BY date_added DESC LIMIT 0,{$show}");
	while($row = $db->return_row($r)){
		$row['date_added'] = date("Y-m-d H:i:s",$row['date_added']);
		// Add a autodownload for the first element in list
		if($first['p'] == true){
			$first['p'] = false;
			$auto = "&nbsp;&nbsp;<a href=\"queue.php?t=p&id={$row['id']}&auto=1\" class=\"{$btnclass}\" onClick=\"jQuery('#{$row['id']}').hide();return true;\" title=\"Скачать автоматически\"><b class=\"{$btniconauto}\"></b></a>";
		} else { $auto = ''; }
print <<<E
<tr id="{$row['id']}">
  <td class="text-center"><i class="fa fa-{$type_icons['photo']}"></i></td>
  <td>{$row['id']}</td>
  <td><a href="{$row['uri']}" class="fancybox" data-fancybox="images" >{$row['uri']}</a></td>
  <td>{$row['date_added']}</td>
  <td><a href="queue.php?t=p&id={$row['id']}" class="{$btnclass}" id="{$row['id']}" onClick="jQuery('#{$row['id']}').hide();return true;" title="Скачать"><b class="{$btnicon}"></b></a>{$auto}</td>
</tr>
E;
	}
}

$first['m'] = true;
if($bar_queue['m'] > 0){
	// Set default encoding for correct filenames
	mb_internal_encoding("UTF-8");
	$r = $db->query("SELECT * FROM vk_music WHERE `in_queue` = 1 ORDER BY date_added DESC LIMIT 0,{$show}");
	while($row = $db->return_row($r)){
		$row['date_added'] = date("Y-m-d H:i:s",$row['date_added']);
		//$row['uri_a'] = preg_replace("/\?extra\=.*/","",$row['uri']);
		if(mb_strlen($row['title']) > 50){ $row['title'] = mb_substr($row['title'],0,50).'...'; }
		if(mb_strlen($row['artist']) > 50){ $row['artist'] = mb_substr($row['artist'],0,50).'...'; }
		$duration = $skin->seconds2human($row['duration']);
		
		// Add a autodownload for the first element in list
		if($first['m'] == true){
			$first['m'] = false;
			$auto = "&nbsp;&nbsp;<a href=\"queue.php?t=m&id={$row['id']}&auto=1\" class=\"{$btnclass}\" title=\"Скачать автоматически\"><b class=\"{$btniconauto}\" onClick=\"jQuery('#{$row['id']}').hide();return true;\"></b></a>";
		} else { $auto = ''; }
print <<<E
<tr id="{$row['id']}">
  <td class="text-center"><i class="fa fa-{$type_icons['audio']}"></i></td>
  <td>{$row['id']}</td>
  <td><a href="{$row['uri']}" target="_blank">[{$duration}] {$row['artist']} - {$row['title']}</a></td>
  <td>{$row['date_added']}</td>
  <td><a href="queue.php?t=m&id={$row['id']}" class="{$btnclass}" id="{$row['id']}" onClick="jQuery('#{$row['id']}').hide();return true;" title="Скачать"><b class="{$btnicon}"></b></a>{$auto}</td>
</tr>
E;
	}
}

$first['v'] = true;
if($bar_queue['v'] > 0){
	$r = $db->query("SELECT * FROM vk_videos WHERE `in_queue` = 1 ".($skip_list != '' ? "AND `id` NOT IN (".$skip_list.")" : "")." ORDER BY date_added DESC LIMIT 0,{$show}");
	while($row = $db->return_row($r)){
		$row['date_added'] = date("Y-m-d H:i:s",$row['date_added']);
		// Add a autodownload for the first element in list
		if($first['v'] == true){
			$first['v'] = false;
			$auto = "&nbsp;&nbsp;<a href=\"queue.php?t=v&id={$row['id']}&oid={$row['owner_id']}&auto=1\" class=\"{$btnclass}\" onClick=\"jQuery('#{$row['id']}').hide();return true;\" title=\"Скачать автоматически\"><b class=\"{$btniconauto}\"></b></a>";
		} else { $auto = ''; }
print <<<E
<tr id="{$row['id']}">
  <td class="text-center"><i class="fa fa-{$type_icons['video']}"></i></td>
  <td>{$row['id']}</td>
  <td><a href="{$row['preview_uri']}" class="fancybox" data-fancybox="images" >{$row['preview_uri']}</a></td>
  <td>{$row['date_added']}</td>
  <td><a href="queue.php?t=v&id={$row['id']}&oid={$row['owner_id']}" class="{$btnclass}" id="{$row['id']}" onClick="jQuery('#{$row['id']}').hide();return true;" title="Скачать"><b class="{$btnicon}"></b></a>{$auto}</td>
</tr>
E;
	}
}
$first['dc'] = true;
if($bar_queue['dc'] > 0){
	$r = $db->query("SELECT * FROM vk_docs WHERE `in_queue` = 1 AND `skipthis` = 0 ".($skip_list != '' ? "AND `id` NOT IN (".$skip_list.")" : "")." ORDER BY date DESC LIMIT 0,{$show}");
	while($row = $db->return_row($r)){
		$row['date'] = date("Y-m-d H:i:s",$row['date']);
		// Add a autodownload for the first element in list
		if($first['dc'] == true){
			$first['dc'] = false;
			$auto = "&nbsp;&nbsp;<a href=\"queue.php?t=dc&id={$row['id']}&auto=1\" class=\"{$btnclass}\" onClick=\"jQuery('#{$row['id']}').hide();return true;\" title=\"Скачать автоматически\"><b class=\"{$btniconauto}\"></b></a>";
		} else { $auto = ''; }
print <<<E
<tr id="{$row['id']}">
  <td class="text-center"><i class="fa fa-{$type_icons['docs']}"></i></td>
  <td>{$row['id']}</td>
  <td><a href="{$row['uri']}" target="_blank">{$row['title']}</a></td>
  <td>{$row['date']}</td>
  <td><a href="queue.php?t=dc&id={$row['id']}" class="{$btnclass}" id="{$row['id']}" onClick="jQuery('#{$row['id']}').hide();return true;" title="Скачать"><b class="{$btnicon}"></b></a>{$auto}</td>
</tr>
E;
	}
}

// Profiles
$first['pr'] = true;
if($pr['c'] > 0){
	$no_queue = false;
	$r = $db->query("SELECT * FROM vk_profiles WHERE `photo_path` = '' LIMIT 0,{$show}");
	while($row = $db->return_row($r)){
		$row['type'] = 'profiles';
		$row['uri'] = $row['photo_uri'];
		print $skin->queue_list_attach($row,$first['pr']);
		if($first['pr'] == true){ $first['pr'] = false; }
	}
}

// Groups
$first['gr'] = true;
if($gr['c'] > 0){
	$no_queue = false;
	$r = $db->query("SELECT * FROM vk_groups WHERE `photo_path` = '' LIMIT 0,{$show}");
	while($row = $db->return_row($r)){
		$row['type'] = 'groups';
		$row['uri'] = $row['photo_uri'];
		print $skin->queue_list_attach($row,$first['gr']);
		if($first['gr'] == true){ $first['gr'] = false; }
	}
}

// Attach - Photo & Video (preview)
$first['atph'] = true;
$first['atvi'] = true;
$first['atli'] = true;
$first['atau'] = true;
$first['atdc'] = true;
$r = $db->query("SELECT * FROM vk_attach WHERE `path` = '' AND `uri` != '' AND `is_local` = 0 AND `skipthis` = 0 LIMIT 0,{$show}");
while($row = $db->return_row($r)){
	$no_queue = false;
	if($row['type'] == 'photo'){
		print $skin->queue_list_attach($row,$first['atph']);
		if($first['atph'] == true){ $first['atph'] = false; }
	}
	if($row['type'] == 'video'){
		print $skin->queue_list_attach($row,$first['atvi']);
		if($first['atvi'] == true){ $first['atvi'] = false; }
	}
	if($row['type'] == 'link'){
		print $skin->queue_list_attach($row,$first['atli']);
		if($first['atli'] == true){ $first['atli'] = false; }
	}
	if($row['type'] == 'audio'){
		print $skin->queue_list_attach($row,$first['atau']);
		if($first['atau'] == true){ $first['atau'] = false; }
	}
	if($row['type'] == 'doc'){
		print $skin->queue_list_attach($row,$first['atdc']);
		if($first['atdc'] == true){ $first['atdc'] = false; }
	}
}


// MessageAttach - Photo & Video (preview)
$first['matph'] = true;
$first['matvi'] = true;
$first['matli'] = true;
$first['matdc'] = true;
$first['matst'] = false;
$r = $db->query("SELECT * FROM vk_messages_attach WHERE `path` = '' AND `uri` != '' AND `is_local` = 0 AND `skipthis` = 0 LIMIT 0,{$show}");
while($row = $db->return_row($r)){
	$no_queue = false;
	if($row['type'] == 'photo'){
		$row['type'] = 'm-photo';
		print $skin->queue_list_attach($row,$first['matph']);
		if($first['matph'] == true){ $first['matph'] = false; }
	}
	if($row['type'] == 'video'){
		$row['type'] = 'm-video';
		print $skin->queue_list_attach($row,$first['matvi']);
		if($first['matvi'] == true){ $first['matvi'] = false; }
	}
	if($row['type'] == 'link'){
		$row['type'] = 'm-link';
		print $skin->queue_list_attach($row,$first['matli']);
		if($first['matli'] == true){ $first['matli'] = false; }
	}
	if($row['type'] == 'doc'){
		$row['type'] = 'm-doc';
		print $skin->queue_list_attach($row,$first['matdc']);
		if($first['matdc'] == true){ $first['matdc'] = false; }
	}
	if($row['type'] == 'sticker'){
		$row['type'] = 'm-sticker';
		print $skin->queue_list_attach($row,$first['matst']);
	}
}

// MessageWallAttach - Photo & Video (preview)
$first['mwatph'] = true;
$first['mwatvi'] = true;
$first['mwatli'] = true;
$first['mwatdc'] = true;
$first['mwatst'] = false;
$r = $db->query("SELECT * FROM vk_messages_wall_attach WHERE `path` = '' AND `uri` != '' AND `is_local` = 0 AND `skipthis` = 0 LIMIT 0,{$show}");
while($row = $db->return_row($r)){
	$no_queue = false;
	if($row['type'] == 'photo'){
		$row['type'] = 'mw-photo';
		print $skin->queue_list_attach($row,$first['mwatph']);
		if($first['mwatph'] == true){ $first['mwatph'] = false; }
	}
	if($row['type'] == 'video'){
		$row['type'] = 'mw-video';
		print $skin->queue_list_attach($row,$first['mwatvi']);
		if($first['mwatvi'] == true){ $first['mwatvi'] = false; }
	}
	if($row['type'] == 'link'){
		$row['type'] = 'mw-link';
		print $skin->queue_list_attach($row,$first['mwatli']);
		if($first['mwatli'] == true){ $first['mwatli'] = false; }
	}
	if($row['type'] == 'doc'){
		$row['type'] = 'mw-doc';
		print $skin->queue_list_attach($row,$first['mwatdc']);
		if($first['mwatdc'] == true){ $first['mwatdc'] = false; }
	}
	if($row['type'] == 'sticker'){
		$row['type'] = 'mw-sticker';
		print $skin->queue_list_attach($row,$first['mwatst']);
	}
}

// WallCommentsAttach - Photo & Video (preview)
$first['catph'] = true;
$first['catvi'] = true;
$first['catli'] = true;
$first['catdc'] = true;
$first['catst'] = false;
$r = $db->query("SELECT * FROM vk_wall_comments_attach WHERE `path` = '' AND `uri` != '' AND `is_local` = 0 AND `skipthis` = 0 LIMIT 0,{$show}");
while($row = $db->return_row($r)){
	$no_queue = false;
	if($row['type'] == 'photo'){
		$row['type'] = 'com-photo';
		print $skin->queue_list_attach($row,$first['catph']);
		if($first['mwatph'] == true){ $first['mwatph'] = false; }
	}
	if($row['type'] == 'video'){
		$row['type'] = 'com-video';
		print $skin->queue_list_attach($row,$first['catvi']);
		if($first['mwatvi'] == true){ $first['mwatvi'] = false; }
	}
	if($row['type'] == 'link'){
		$row['type'] = 'com-link';
		print $skin->queue_list_attach($row,$first['catli']);
		if($first['mwatli'] == true){ $first['mwatli'] = false; }
	}
	if($row['type'] == 'doc'){
		$row['type'] = 'com-doc';
		print $skin->queue_list_attach($row,$first['catdc']);
		if($first['mwatdc'] == true){ $first['mwatdc'] = false; }
	}
	if($row['type'] == 'sticker'){
		$row['type'] = 'com-sticker';
		print $skin->queue_list_attach($row,$first['catst']);
	}
}

if($all_queue == 0 && $no_queue == true) {
	print '<tr><td colspan="5" style="text-align:center;color:#bbb;">Очередь закачки пуста</td></tr>';
}

$more = array();
foreach($bar_queue as $mrk => $mrv){
	if(!is_array($mrv)){
		if($mrv > $show){ $more[$mrk] = $mrv - $show; }
	} else {
		if($mrv['total'] > $show){ $more[$mrk] = $mrv['total'] - $show; }
		foreach($mrv as $rk => $rv){
			if($rk != 'total'){ $more[$mrk.'-'.$rk] = $rv; }
		}
	}
}
if(!empty($more)){
	$more_types = array(
		'p' => 'фотографий','m'=>'аудиозаписей','v'=>'видео','dc'=>'документов','at'=>'вложений','msat'=>'диалог вложений','mwsat'=>'репост вложений','cattach'=>'коммент вложений',
		'msat-photo'=>'фотографий','msat-link'=>'ссылок','msat-doc'=>'документов','msat-video'=>'видео','msat-sticker'=>'стикеров',
		'mwsat-photo'=>'фотографий','mwsat-link'=>'ссылок','mwsat-doc'=>'документов','mwsat-video'=>'видео','mwsat-sticker'=>'стикеров',
		'cattach-photo'=>'фотографий','cattach-link'=>'ссылок','cattach-doc'=>'документов','cattach-video'=>'видео','cattach-sticker'=>'стикеров'
	);
print <<<E
<tr>
  <td colspan="5">
	<div class="alert alert-info" role="alert">
	 И ещё
E;
	foreach($more as $mrk => $mrv){
		print ' <strong>'.($mrv).'</strong> '.$more_types[$mrk];
	}
print <<<E
    </div>
  </td>
</tr>
E;
} // More end

print <<<E
              </tbody>
            </table>
          </div>
</div>
E;

if(!$cfg['pj']){
	print $skin->footer(array('extend'=>''));
}

$db->close($res);

?>