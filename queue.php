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

$bar_total = $db->query_row("SELECT COUNT(*) as p FROM vk_photos WHERE album_id > -9000");
$bar = $db->query_row("SELECT COUNT(*) as p FROM vk_photos WHERE album_id > -9000 AND `in_queue` = 1");
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

$bar_total = $db->query_row("SELECT COUNT(*) as dc FROM vk_docs WHERE `deleted` = 0");
$bar = $db->query_row("SELECT COUNT(*) as dc FROM vk_docs WHERE `in_queue` = 1");
$bar_queue['dc'] = $bar['dc'];
$per = $bar_total['dc']/100;
if($bar_total['dc'] > 0){
$done['dcc'] = round(($bar_total['dc'] - $bar_queue['dc']) / $per, 2);
$done['dc'] = ceil($done['dcc']);
} else { $done['dc'] = $done['dcc'] = 0; }

$bar_total = $db->query_row("SELECT COUNT(*) as msat FROM vk_messages_attach WHERE `uri` != '' AND `is_local` = 0 AND `skipthis` = 0");
$bar = $db->query_row("SELECT COUNT(*) as msat FROM vk_messages_attach WHERE `path` = '' AND `uri` != '' AND `is_local` = 0 AND `skipthis` = 0");
$bar_queue['msat'] = $bar['msat'];
$per = $bar_total['msat']/100;
if($bar_total['msat'] > 0){
$done['msatt'] = round(($bar_total['msat'] - $bar_queue['msat']) / $per, 2);
$done['msat'] = ceil($done['msatt']);
} else { $done['msat'] = $done['msatt'] = 0; }

$all_queue = $bar_queue['p'] + $bar_queue['m'] + $bar_queue['v'] + $bar_queue['at'] + $bar_queue['dc'] + $bar_queue['msat'];
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
	<div class="row white-box p-2 py-3 mb-4 mx-0" style="white-space:nowrap;">
E;

// Show last queue records
$show = 25;
$bar = array();

// Make a progress bars
// Photo
$bar[0] = array('fa' => 'image','name' => 'Фотографии','perx' => $done['pp'],'per' => $done['p'],'bar' => 'success');

// Audio
$bar[1] = array('fa' => 'music','name' => 'Аудиозаписи','perx' => $done['mm'],'per' => $done['m'],'bar' => 'warning');

// Video
$bar[2] = array('fa' => 'film','name' => 'Видеозаписи','perx' => $done['vv'],'per' => $done['v'],'bar' => 'info');

// Attachments
$bar[3] = array('fa' => 'paperclip','name' => 'Вложения','perx' => $done['att'],'per' => $done['at'],'bar' => 'primary');

// Documents
$bar[4] = array('fa' => 'file','name' => 'Документы','perx' => $done['dcc'],'per' => $done['dc'],'bar' => 'danger');

// MessagesAttachments
$bar[5] = array('fa' => 'paperclip','name' => 'Диалоги','perx' => $done['msatt'],'per' => $done['msat'],'bar' => 'secondary');

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
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Файл сохранен</div>
E;
					$q = $db->query("UPDATE vk_photos SET `in_queue` = 0, `date_done` = ".time().", `path` = '".$cfg['photo_path'].$f."/".$n[0]."', `saved` = 1, `hash` = '".md5_file($cfg['photo_path'].$f."/".$n[0])."' WHERE `id` = ".$queue_id."");
					
					if($_GET['auto'] == '1'){
						$nrow = $db->query_row("SELECT id FROM vk_photos WHERE album_id > -9000 AND `in_queue` = 1 ORDER BY date_added DESC");
						if($nrow['id'] > 0){
							print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_photo_next_cd']."</span> сек.","queue.php?t=p&id=".$nrow['id']."&auto=1",$cfg['sync_photo_next_cd']);
						}
					}
					
				} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Ошибка при сохранении файла</div>
E;
				}
			} else {
				// If error, let's try to see wtf is going on
					$error_code = false;
					if($func->is_html_response($out['content'])){
						$error_code = $skin->remote_server_error($out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 )));
				}
				// Something wrong with response or connection
					$skin->queue_no_data($error_code,false,false);
			}
			
		} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ID найден в очереди но ссылка на файл отсутствует.</div>
E;
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
			$q['uri'] = preg_replace("/\?extra\=.*/","",$q['uri']);
			preg_match("/[^\.]+$/",$q['uri'],$n);
			if(mb_strlen($q['title']) > 200){ $q['title'] = mb_substr($row['artist'],0,200); }
			$nam = $c->clean_name($q['artist'].' - '.$q['title'].' ['.$q['id'].'].'.$n[0]);
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
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Файл <b>{$nam}</b> сохранен</div>
E;
				
					$q = $db->query("UPDATE vk_music SET `in_queue` = 0, `date_done` = ".time().", `path` = '".$cfg['music_path'].$db->real_escape($cnam)."', `saved` = 1, `hash` = '".md5_file($cfg['music_path'].$fnam)."' WHERE `id` = ".$queue_id."");
					
					if($_GET['auto'] == '1'){
						$nrow = $db->query_row("SELECT id FROM vk_music WHERE `in_queue` = 1 ORDER BY date_added DESC");
						if($nrow['id'] > 0){
							print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_music_next_cd']."</span> сек.","queue.php?t=m&id=".$nrow['id']."&auto=1",$cfg['sync_music_next_cd']);
						}
					}
					
				} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Ошибка при сохранении файла {$nam}</div>
E;
				}
			} else {
				// If error, let's try to see wtf is going on
				if((substr($out['content'],0,5) == '<html') || (substr($out['content'],0,9) == '<!DOCTYPE')){
					$out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 ));
					if(isset($out['header'])){ $error_code = "<br/>Ответ сервера: {$out['header']['http_code']}"; }
				}
				// Something wrong with response or connection
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Невозможно получить данные с удаленного хоста для {$nam}{$error_code}</div>
E;
			}
			
		} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ID найден в очереди но ссылка на файл отсутствует.</div>
E;
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
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Файл превью сохранен</div>
E;
					$q = $db->query("UPDATE vk_videos SET `in_queue` = 0, `date_done` = ".time().", `preview_path` = '".$cfg['video_path'].$n[0]."' WHERE `id` = ".$queue_id." AND `owner_id` =".$queue_oid);
					
					if($_GET['auto'] == '1'){
						$nrow = $db->query_row("SELECT id,owner_id FROM vk_videos WHERE `in_queue` = 1 ".($skip_list != '' ? "AND `id` NOT IN (".$skip_list.")" : "")." ORDER BY date_added DESC");
						if($nrow['id'] > 0){
							print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_video_next_cd']."</span> сек.","queue.php?t=v&id=".$nrow['id']."&oid=".$nrow['oqner_id']."&auto=1".($skip_list != '' ? "&skip=".$skip_list : ""),$cfg['sync_video_next_cd']);
						}
					}
					
				} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Ошибка при сохранении превью файла</div>
E;
				}
			} else {
				// If error, let's try to see wtf is going on
				if((substr($out['content'],0,5) == '<html') || (substr($out['content'],0,9) == '<!DOCTYPE')){
					$out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 ));
					if(isset($out['header'])){ $error_code = "<br/>Ответ сервера: {$out['header']['http_code']}"; }
				}
				// Something wrong with response or connection
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Невозможно получить превью #{$queue_id} с удаленного хоста для {$n[0]}{$error_code}</div>
E;
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
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ID найден в очереди но ссылка на файл отсутствует.</div>
E;
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
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Файл сохранен</div>
E;

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
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Превью сохранено</div>
E;
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
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Ошибка при сохранении файла</div>
E;
				}
			} else {
				// If error, let's try to see wtf is going on
					$error_code = false;
					if($func->is_html_response($out['content'])){
						$error_code = $skin->remote_server_error($out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 )));
				}
				// Something wrong with response or connection
					$skin->queue_no_data($error_code,false,false);
			}
			
		} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ID найден в очереди но ссылка на файл отсутствует.</div>
E;
		}
	} // End of T = DC
	
	// Profiles
	if($queue_id > 0 && $_GET['t']=='pr'){
		$don = true;
		// Get photo info
		$q = $db->query_row("SELECT * FROM vk_profiles WHERE `id` = {$queue_id}");
		if($q['photo_uri'] != ''){
			
			// Get file name
			preg_match("/[^\/]+\.(jpg|jpeg|png|gif|bmp)/",$q['photo_uri'],$n);
			
			// Check do we have this file already ( useful if you are developer and pucked up attachments DB :D )
			if(is_file(ROOT.'data/profiles/'.$queue_id.'.'.$n[0])){
print <<<E
<div class="alert alert-info" role="alert"><i class="far fa-file"></i> Файл найден локально</div>
E;
				$q = $db->query("UPDATE vk_profiles SET `photo_path` = '".$queue_id.".".$n[0]."' WHERE `id` = ".$queue_id."");
				
				if($_GET['auto'] == '1'){
					$nrow = $db->query_row("SELECT id FROM vk_profiles WHERE `photo_path` = ''");
					if($nrow['id'] > 0){
						print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_found_local']."</span> сек.","queue.php?t=pr&id=".$nrow['id']."&auto=1",$cfg['sync_found_local']);
					}
				}
			} else {
				
				// Are you reagy kids? YES Capitan Curl!
				require_once(ROOT.'classes/curl.php');
				$c = new cu();
				$c->curl_on();
				
				$out = $c->curl_req(array(
						'uri' => $q['photo_uri'],
						'method'=>'',
						'return'=>1
				));
			
				if($out['err'] == 0 && $out['errmsg'] == '' && $out['content'] != '' && substr($out['content'],0,5) != '<html' && substr($out['content'],0,9) != '<!DOCTYPE'){
					$saved = $c->file_save(array('path'=>ROOT.'data/profiles/','name'=>$queue_id.'.'.$n[0]),$out['content']);
					if($saved){
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Файл сохранен</div>
E;
						$q = $db->query("UPDATE vk_profiles SET `photo_path` = '".$queue_id.".".$n[0]."' WHERE `id` = ".$queue_id."");
					
						if($_GET['auto'] == '1'){
							$nrow = $db->query_row("SELECT id FROM vk_profiles WHERE `photo_path` = ''");
							if($nrow['id'] > 0){
								print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_photo_next_cd']."</span> сек.","queue.php?t=pr&id=".$nrow['id']."&auto=1",$cfg['sync_photo_next_cd']);
							}
						}
						
					} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Ошибка при сохранении файла</div>
E;
					}
				} else {
					// If error, let's try to see wtf is going on
					$error_code = false;
					if($func->is_html_response($out['content'])){
						$error_code = $skin->remote_server_error($out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 )));
					}
					// Something wrong with response or connection
					$skin->queue_no_data($error_code,false,false);
				}
			} // end of local file check fail
		} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ID найден в очереди но ссылка на файл отсутствует.</div>
E;
		}
	} // End of T = PR
	
	// Groups
	if($queue_id > 0 && $_GET['t']=='gr'){
		$don = true;
		// Get photo info
		$q = $db->query_row("SELECT * FROM vk_groups WHERE `id` = {$queue_id}");
		if($q['photo_uri'] != ''){
			
			// Get file name
			preg_match("/[^\/]+\.(jpg|jpeg|png|gif|bmp)/",$q['photo_uri'],$n);
			
			// Check do we have this file already ( useful if you are developer and pucked up attachments DB :D )
			if(is_file(ROOT.'data/groups/'.$queue_id.'.'.$n[0])){
print <<<E
<div class="alert alert-info" role="alert"><i class="far fa-file"></i> Файл найден локально</div>
E;
				$q = $db->query("UPDATE vk_groups SET `photo_path` = '".$queue_id.".".$n[0]."' WHERE `id` = ".$queue_id."");
				
				if($_GET['auto'] == '1'){
					$nrow = $db->query_row("SELECT id FROM vk_groups WHERE `photo_path` = ''");
					if($nrow['id'] > 0){
						print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_found_local']."</span> сек.","queue.php?t=gr&id=".$nrow['id']."&auto=1",$cfg['sync_found_local']);
					}
				}
			} else {
				
				// Are you reagy kids? YES Capitan Curl!
				require_once(ROOT.'classes/curl.php');
				$c = new cu();
				$c->curl_on();
				
				$out = $c->curl_req(array(
						'uri' => $q['photo_uri'],
						'method'=>'',
						'return'=>1
				));
				
				if($out['err'] == 0 && $out['errmsg'] == '' && $out['content'] != '' && substr($out['content'],0,5) != '<html' && substr($out['content'],0,9) != '<!DOCTYPE'){
					$saved = $c->file_save(array('path'=>ROOT.'data/groups/','name'=>$queue_id.'.'.$n[0]),$out['content']);
					if($saved){
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Файл сохранен</div>
E;
						$q = $db->query("UPDATE vk_groups SET `photo_path` = '".$queue_id.".".$n[0]."' WHERE `id` = ".$queue_id."");
						
						if($_GET['auto'] == '1'){
							$nrow = $db->query_row("SELECT id FROM vk_groups WHERE `photo_path` = ''");
							if($nrow['id'] > 0){
								print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_photo_next_cd']."</span> сек.","queue.php?t=gr&id=".$nrow['id']."&auto=1",$cfg['sync_photo_next_cd']);
							}
						}
						
					} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Ошибка при сохранении файла</div>
E;
					}
				} else {
					// If error, let's try to see wtf is going on
					$error_code = false;
					if($func->is_html_response($out['content'])){
						$error_code = $skin->remote_server_error($out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 )));
					}
					// Something wrong with response or connection
					$skin->queue_no_data($error_code,false,false);
				}
			} // end of local file check fail
		} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ID найден в очереди но ссылка на файл отсутствует.</div>
E;
		}
	} // End of T = GR
	
	// Attach - Photo
	if($queue_id > 0 && $_GET['t']=='atph' && $queue_oid != 0){
		$don = true;
		// Get photo info
		$q = $db->query_row("SELECT * FROM vk_attach WHERE `attach_id` = {$queue_id} AND `owner_id` = {$queue_oid}");
		if($q['uri'] != ''){
			
			// Get file name
			preg_match("/[^\/]+$/",$q['uri'],$n);
			$f = date("Y-m",$q['date']);
			
			// Check do we have this file already ( useful if you are developer and pucked up attachments DB :D )
			if(is_file($cfg['photo_path'].'attach/'.$f.'/'.$n[0])){
print <<<E
<div class="alert alert-info" role="alert"><i class="far fa-file"></i> Файл найден локально</div>
E;
				$q = $db->query("UPDATE vk_attach SET `path` = '".$cfg['photo_path']."attach/".$f."/".$n[0]."' WHERE `attach_id` = ".$queue_id." AND `owner_id` = ".$queue_oid."");
				
				if($_GET['auto'] == '1'){
					$nrow = $db->query_row("SELECT attach_id, owner_id FROM vk_attach WHERE `path` = '' AND `type` = 'photo' AND `is_local` = 0");
					if($nrow['attach_id'] > 0){
						print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_found_local']."</span> сек.","queue.php?t=atph&id=".$nrow['attach_id']."&oid=".$nrow['owner_id']."&auto=1",$cfg['sync_found_local']);
					}
				}
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
					$saved = $c->file_save(array('path'=>$cfg['photo_path'].'attach/'.$f.'/','name'=>$n[0]),$out['content']);
					if($saved){
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Файл сохранен</div>
E;
						$q = $db->query("UPDATE vk_attach SET `path` = '".$cfg['photo_path']."attach/".$f."/".$n[0]."' WHERE `attach_id` = ".$queue_id." AND `owner_id` = ".$queue_oid."");
						
						if($_GET['auto'] == '1'){
							$nrow = $db->query_row("SELECT attach_id, owner_id FROM vk_attach WHERE `path` = '' AND `type` = 'photo' AND `is_local` = 0");
							if($nrow['attach_id'] > 0){
								print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_photo_next_cd']."</span> сек.","queue.php?t=atph&id=".$nrow['attach_id']."&oid=".$nrow['owner_id']."&auto=1",$cfg['sync_photo_next_cd']);
							}
						}
					
					} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Ошибка при сохранении файла</div>
E;
					}
				} else {
					// If error, let's try to see wtf is going on
					$error_code = false;
					if($func->is_html_response($out['content'])){
						$error_code = $skin->remote_server_error($out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 )));
					}
					// Something wrong with response or connection
					$skin->queue_no_data($error_code,"t=atph&id=".$queue_id."&oid=".$queue_oid,$queue_id);
				}
			} // end of local file check fail
		} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ID найден в очереди но ссылка на файл отсутствует.</div>
E;
		}
	} // End of T = ATPH
	
	// Attach - Video (preview)
	if($queue_id > 0 && $_GET['t']=='atvi' && $queue_oid != 0){
		$don = true;
		// Get video preview info
		$q = $db->query_row("SELECT * FROM vk_attach WHERE `attach_id` = {$queue_id} AND `owner_id` = {$queue_oid}");
		if($q['uri'] != ''){
			
			// Get file name
			preg_match("/[^\/]+$/",$q['uri'],$n);
			$f = date("Y-m",$q['date']);
			
			// Check do we have this file already ( useful if you are developer and pucked up attachments DB :D )
			if(is_file($cfg['video_path'].'attach/'.$f.'/'.$n[0])){
print <<<E
<div class="alert alert-info" role="alert"><i class="far fa-file"></i> Файл найден локально</div>
E;
				$q = $db->query("UPDATE vk_attach SET `path` = '".$cfg['video_path']."attach/".$f."/".$n[0]."' WHERE `attach_id` = ".$queue_id." AND `owner_id` = ".$queue_oid."");
				
				if($_GET['auto'] == '1'){
					$nrow = $db->query_row("SELECT attach_id, owner_id FROM vk_attach WHERE `path` = '' AND `type` = 'video' AND `is_local` = 0");
					if($nrow['attach_id'] > 0){
						print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_found_local']."</span> сек.","queue.php?t=atvi&id=".$nrow['attach_id']."&oid=".$nrow['owner_id']."&auto=1",$cfg['sync_found_local']);
					}
				}
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
					$saved = $c->file_save(array('path'=>$cfg['video_path'].'attach/'.$f.'/','name'=>$n[0]),$out['content']);
					if($saved){
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Файл сохранен</div>
E;
						$q = $db->query("UPDATE vk_attach SET `path` = '".$cfg['video_path']."attach/".$f."/".$n[0]."' WHERE `attach_id` = ".$queue_id." AND `owner_id` = ".$queue_oid."");
					
						if($_GET['auto'] == '1'){
							$nrow = $db->query_row("SELECT attach_id, owner_id FROM vk_attach WHERE `path` = '' AND `type` = 'video' AND `is_local` = 0");
							if($nrow['attach_id'] > 0){
								print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_photo_next_cd']."</span> сек.","queue.php?t=atvi&id=".$nrow['attach_id']."&oid=".$nrow['owner_id']."&auto=1",$cfg['sync_photo_next_cd']);
							}
						}
						
					} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Ошибка при сохранении файла</div>
E;
					}
				} else {
					// If error, let's try to see wtf is going on
					$error_code = false;
					if($func->is_html_response($out['content'])){
						$error_code = $skin->remote_server_error($out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 )));
					}
					// Something wrong with response or connection
					$skin->queue_no_data($error_code,"t=atvi&id=".$queue_id."&oid=".$queue_oid,$queue_id);
				}
			} // end of local file check fail
		} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ID найден в очереди но ссылка на файл отсутствует.</div>
E;
		}
	} // End of T = ATVI
	
	// Attach - Link
	if($queue_id > 0 && $_GET['t']=='atli' && $queue_oid != 0){
		$don = true;
		// Get photo info
		$q = $db->query_row("SELECT * FROM vk_attach WHERE `attach_id` = {$queue_id} AND `owner_id` = {$queue_oid}");
		if($q['uri'] != ''){
			
			// Get file name
			preg_match("/[^\/]+$/",$q['uri'],$n);
			$f = date("Y-m",$q['date']);
			
			// Check do we have this file already ( useful if you are developer and pucked up attachments DB :D )
			if(is_file($cfg['photo_path'].'attach/'.$f.'/'.$n[0])){
print <<<E
<div class="alert alert-info" role="alert"><i class="far fa-file"></i> Файл найден локально</div>
E;
				$q = $db->query("UPDATE vk_attach SET `path` = '".$cfg['photo_path']."attach/".$f."/".$n[0]."' WHERE `attach_id` = ".$queue_id." AND `owner_id` = ".$queue_oid."");
				
				if($_GET['auto'] == '1'){
					$nrow = $db->query_row("SELECT attach_id, owner_id FROM vk_attach WHERE `path` = '' AND `type` = 'link' AND `is_local` = 0");
					if($nrow['attach_id'] > 0){
						print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_found_local']."</span> сек.","queue.php?t=atli&id=".$nrow['attach_id']."&oid=".$nrow['owner_id']."&auto=1",$cfg['sync_found_local']);
					}
				}
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
					$saved = $c->file_save(array('path'=>$cfg['photo_path'].'attach/'.$f.'/','name'=>$n[0]),$out['content']);
					if($saved){
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Файл сохранен</div>
E;
						$q = $db->query("UPDATE vk_attach SET `path` = '".$cfg['photo_path']."attach/".$f."/".$n[0]."' WHERE `attach_id` = ".$queue_id." AND `owner_id` = ".$queue_oid."");
						
						if($_GET['auto'] == '1'){
							$nrow = $db->query_row("SELECT attach_id, owner_id FROM vk_attach WHERE `path` = '' AND `type` = 'link' AND `is_local` = 0");
							if($nrow['attach_id'] > 0){
								print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_photo_next_cd']."</span> сек.","queue.php?t=atli&id=".$nrow['attach_id']."&oid=".$nrow['owner_id']."&auto=1",$cfg['sync_photo_next_cd']);
							}
						}
						
					} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Ошибка при сохранении файла</div>
E;
					}
				} else {
					// If error, let's try to see wtf is going on
					$error_code = false;
					if($func->is_html_response($out['content'])){
						$error_code = $skin->remote_server_error($out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 )));
					}
					// Something wrong with response or connection
					$skin->queue_no_data($error_code,"t=atli&id=".$queue_id."&oid=".$queue_oid,$queue_id);
				}
			} // end of local file check fail
		} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ID найден в очереди но ссылка на файл отсутствует.</div>
E;
		}
	} // End of T = ATLI
	
	// Attach - Music
	if($queue_id > 0 && $_GET['t']=='atau' && $queue_oid != 0){
		$don = true;
		// Get audio info
		$q = $db->query_row("SELECT * FROM vk_attach WHERE `attach_id` = {$queue_id} AND `owner_id` = {$queue_oid}");
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
print <<<E
<div class="alert alert-info" role="alert"><i class="far fa-file"></i> Файл <b>{$nam}</b> найден локально</div>
E;
				
				$q1 = $db->query("UPDATE vk_attach SET `path` = '".$cfg['music_path'].'attach/'.$db->real_escape($cnam)."' WHERE `attach_id` = ".$queue_id." AND `owner_id` = ".$queue_oid."");
				
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
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Файл <b>{$nam}</b> сохранен</div>
E;
				
						$q1 = $db->query("UPDATE vk_attach SET `path` = '".$cfg['music_path'].'attach/'.$db->real_escape($cnam)."' WHERE `attach_id` = ".$queue_id." AND `owner_id` = ".$queue_oid."");
						
						if($_GET['auto'] == '1'){
							$nrow = $db->query_row("SELECT attach_id, owner_id FROM vk_attach WHERE `path` = '' AND `type` = 'audio' AND `uri` != '' AND `is_local` = 0");
							if($nrow['attach_id'] > 0){
								print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_music_next_cd']."</span> сек.","queue.php?t=atau&id=".$nrow['attach_id']."&oid=".$nrow['owner_id']."&auto=1",$cfg['sync_music_next_cd']);
							}
						}
					
					} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Ошибка при сохранении файла {$nam}</div>
E;
					}
				} else {
					// If error, let's try to see wtf is going on
					if((substr($out['content'],0,5) == '<html') || (substr($out['content'],0,9) == '<!DOCTYPE')){
						$out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 ));
						if(isset($out['header'])){ $error_code = "<br/>Ответ сервера: {$out['header']['http_code']}"; }
					}
					// Something wrong with response or connection
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Невозможно получить данные с удаленного хоста для {$nam}{$error_code}</div>
E;
				}
			} // end of local file check fail
		} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ID найден в очереди но ссылка на файл отсутствует.</div>
E;
		}
	} // End of T = ATAU
	
	// Attach - Documents
	if($queue_id > 0 && $_GET['t']=='atdc'){
		$don = true;
		// Get document info
		$q = $db->query_row("SELECT * FROM vk_attach WHERE `attach_id` = {$queue_id} AND `owner_id` = {$queue_oid}");
		if($q['link_url'] != ''){
			
			// Are you reagy kids? YES Capitan Curl!
			require_once(ROOT.'classes/curl.php');
			$c = new cu();
			$c->curl_on();
			//preg_match("/[^\/]+$/",$q['uri'],$n);
			$f = date("Y-m",$q['date']);
			//print_r($cfg['docs_path'].$f.'/'.$n[0]);
			$out = $c->curl_req(array(
					'uri' => $q['link_url'],
					'method'=>'',
					'return'=>1
			));
			
			if($out['err'] == 0 && $out['errmsg'] == '' && $out['content'] != '' && substr($out['content'],0,5) != '<html' && substr($out['content'],0,9) != '<!DOCTYPE'){
				$saved = $c->file_save(array('path'=>$cfg['docs_path'].'attach/'.$f.'/','name'=>$q['attach_id'].'.'.$q['caption']),$out['content']);
				if($saved){
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Файл сохранен</div>
E;

					$prev_q = '';
					if($q['uri'] != ''){
						$out_pre = $c->curl_req(array(
							'uri' => $q['uri'],
							'method'=>'',
							'return'=>1
						));
						if($out_pre['err'] == 0 && $out_pre['errmsg'] == '' && $out_pre['content'] != '' && substr($out_pre['content'],0,5) != '<html' && substr($out_pre['content'],0,9) != '<!DOCTYPE'){
							preg_match("/[^\.]+$/",$q['uri'],$np);
							$saved_pre = $c->file_save(array('path'=>$cfg['docs_path'].'attach/preview/','name'=>$q['attach_id'].'.'.$np['0']),$out_pre['content']);
							if($saved){
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Превью сохранено</div>
E;
								$prev_q = ", `path` = '".$cfg['docs_path']."attach/preview/".$q['attach_id'].".".$np[0]."'";
							}
						}
					}

					$q = $db->query("UPDATE vk_attach SET `player` = '".$cfg['docs_path'].'attach/'.$f."/".$q['attach_id'].".".$q['caption']."'".$prev_q." WHERE `attach_id` = ".$queue_id." AND `owner_id` = ".$queue_oid."");
					
					if($_GET['auto'] == '1'){
						$nrow = $db->query_row("SELECT attach_id, owner_id FROM vk_attach WHERE `player` = '' AND `type` = 'doc' AND `is_local` = 0");
						if($nrow['attach_id'] > 0){
							print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_docs_next_cd']."</span> сек.","queue.php?t=atdc&id=".$nrow['attach_id']."&oid=".$nrow['owner_id']."&auto=1",$cfg['sync_docs_next_cd']);
						}
					}
					
				} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Ошибка при сохранении файла</div>
E;
				}
			} else {
				// If error, let's try to see wtf is going on
					$error_code = false;
					if($func->is_html_response($out['content'])){
						$error_code = $skin->remote_server_error($out = $c->curl_req(array('uri' => $q['link_url'], 'method'=>'', 'return'=>0 )));
				}
				// Something wrong with response or connection
					$skin->queue_no_data($error_code,"t=atdc&id=".$queue_id."&oid=".$queue_oid,$queue_id);
			}
			
		} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ID найден в очереди но ссылка на файл отсутствует.</div>
E;
		}
	} // End of T = ATDC
	
	// Message - Attach - Stickers
	if($queue_id > 0 && $_GET['t']=='matst'){
		$don = true;
		// Get sticker info
		$q = $db->query_row("SELECT * FROM vk_messages_attach WHERE `date` = {$queue_id}");
		if($q['uri'] != ''){
			
			// Get file name
			preg_match_all("/\/([0-9]+)\/[^\.]+\.([^\.]+)$/",$q['uri'],$n);
			
			// Check do we have this file already ( useful if you are developer and pucked up attachments DB :D )
			if(is_file(ROOT.'data/stickers/'.$n[1][0].'.'.$n[2][0])){
print <<<E
<div class="alert alert-info" role="alert"><i class="far fa-file"></i> Файл найден локально</div>
E;
				$q = $db->query("UPDATE vk_messages_attach SET `is_local` = 1, `path` = '".$n[1][0].".".$n[2][0]."' WHERE `uri` = '".$q['uri']."'");
				
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
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Файл сохранен</div>
E;
						$q = $db->query("UPDATE vk_messages_attach SET `is_local` = 1, `path` = '".$n[1][0].".".$n[2][0]."' WHERE `uri` = '".$q['uri']."'");
					
					} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Ошибка при сохранении файла</div>
E;
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
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ID найден в очереди но ссылка на файл отсутствует.</div>
E;
		}
	} // End of T = MATST
	
	// Message - Attach - Photo
	if($queue_id > 0 && $_GET['t']=='matph' && $queue_oid != 0){
		$don = true;
		// Get photo info
		$q = $db->query_row("SELECT * FROM vk_messages_attach WHERE `attach_id` = {$queue_id} AND `owner_id` = {$queue_oid}");
		if($q['uri'] != ''){
			
			// Get file name
			preg_match("/[^\/]+$/",$q['uri'],$n);
			$f = date("Y-m",$q['date']);
			
			// Check do we have this file already ( useful if you are developer and pucked up attachments DB :D )
			if(is_file($cfg['photo_path'].'messages/'.$f.'/'.$n[0])){
print <<<E
<div class="alert alert-info" role="alert"><i class="far fa-file"></i> Файл найден локально</div>
E;
				$q = $db->query("UPDATE vk_messages_attach SET `path` = '".$cfg['photo_path']."messages/".$f."/".$n[0]."' WHERE `attach_id` = ".$queue_id." AND `owner_id` = ".$queue_oid."");
				
				if($_GET['auto'] == '1'){
					$nrow = $db->query_row("SELECT attach_id, owner_id FROM vk_messages_attach WHERE `path` = '' AND `type` = 'photo' AND `is_local` = 0");
					if($nrow['attach_id'] > 0){
						print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_found_local']."</span> сек.","queue.php?t=matph&id=".$nrow['attach_id']."&oid=".$nrow['owner_id']."&auto=1",$cfg['sync_found_local']);
					}
				}
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
					$saved = $c->file_save(array('path'=>$cfg['photo_path'].'messages/'.$f.'/','name'=>$n[0]),$out['content']);
					if($saved){
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Файл сохранен</div>
E;
						$q = $db->query("UPDATE vk_messages_attach SET `path` = '".$cfg['photo_path']."messages/".$f."/".$n[0]."' WHERE `attach_id` = ".$queue_id." AND `owner_id` = ".$queue_oid."");
						
						if($_GET['auto'] == '1'){
							$nrow = $db->query_row("SELECT attach_id, owner_id FROM vk_messages_attach WHERE `path` = '' AND `type` = 'photo' AND `is_local` = 0");
							if($nrow['attach_id'] > 0){
								print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_photo_next_cd']."</span> сек.","queue.php?t=matph&id=".$nrow['attach_id']."&oid=".$nrow['owner_id']."&auto=1",$cfg['sync_photo_next_cd']);
							}
						}
					
					} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Ошибка при сохранении файла</div>
E;
					}
				} else {
					// If error, let's try to see wtf is going on
					$error_code = false;
					if($func->is_html_response($out['content'])){
						$error_code = $skin->remote_server_error($out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 )));
					}
					// Something wrong with response or connection
					$skin->queue_no_data($error_code,"t=matph&id=".$queue_id."&oid=".$queue_oid,$queue_id);
				}
			} // end of local file check fail
		} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ID найден в очереди но ссылка на файл отсутствует.</div>
E;
		}
	} // End of T = MATPH
	
	// Message - Attach - Documents
	if($queue_id > 0 && $_GET['t']=='matdc'){
		$don = true;
		// Get document info
		$q = $db->query_row("SELECT * FROM vk_messages_attach WHERE `attach_id` = {$queue_id} AND `owner_id` = {$queue_oid}");
		if($q['link_url'] != ''){
			
			// Are you reagy kids? YES Capitan Curl!
			require_once(ROOT.'classes/curl.php');
			$c = new cu();
			$c->curl_on();
			$f = date("Y-m",$q['date']);
			$out = $c->curl_req(array(
					'uri' => $q['link_url'],
					'method'=>'',
					'return'=>1
			));
			
			if($out['err'] == 0 && $out['errmsg'] == '' && $out['content'] != '' && substr($out['content'],0,5) != '<html' && substr($out['content'],0,9) != '<!DOCTYPE'){
				$saved = $c->file_save(array('path'=>$cfg['docs_path'].'messages/'.$f.'/','name'=>$q['attach_id'].'.'.$q['caption']),$out['content']);
				if($saved){
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Файл сохранен</div>
E;

					$prev_q = '';
					if($q['uri'] != ''){
						$out_pre = $c->curl_req(array(
							'uri' => $q['uri'],
							'method'=>'',
							'return'=>1
						));
						if($out_pre['err'] == 0 && $out_pre['errmsg'] == '' && $out_pre['content'] != '' && substr($out_pre['content'],0,5) != '<html' && substr($out_pre['content'],0,9) != '<!DOCTYPE'){
							preg_match("/[^\.]+$/",$q['uri'],$np);
							$saved_pre = $c->file_save(array('path'=>$cfg['docs_path'].'messages/preview/','name'=>$q['attach_id'].'.'.$np['0']),$out_pre['content']);
							if($saved){
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Превью сохранено</div>
E;
								$prev_q = ", `path` = '".$cfg['docs_path']."messages/preview/".$q['attach_id'].".".$np[0]."'";
							}
						}
					}

					$q = $db->query("UPDATE vk_messages_attach SET `player` = '".$cfg['docs_path'].'messages/'.$f."/".$q['attach_id'].".".$q['caption']."'".$prev_q." WHERE `attach_id` = ".$queue_id." AND `owner_id` = ".$queue_oid."");
					
					if($_GET['auto'] == '1'){
						$nrow = $db->query_row("SELECT attach_id, owner_id FROM vk_messages_attach WHERE `player` = '' AND `type` = 'doc' AND `is_local` = 0");
						if($nrow['attach_id'] > 0){
							print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_docs_next_cd']."</span> сек.","queue.php?t=matdc&id=".$nrow['attach_id']."&oid=".$nrow['owner_id']."&auto=1",$cfg['sync_docs_next_cd']);
						}
					}
					
				} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Ошибка при сохранении файла</div>
E;
				}
			} else {
				// If error, let's try to see wtf is going on
					$error_code = false;
					if($func->is_html_response($out['content'])){
						$error_code = $skin->remote_server_error($out = $c->curl_req(array('uri' => $q['link_url'], 'method'=>'', 'return'=>0 )));
				}
				// Something wrong with response or connection
					$skin->queue_no_data($error_code,"t=matdc&id=".$queue_id."&oid=".$queue_oid,$queue_id);
			}
			
		} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ID найден в очереди но ссылка на файл отсутствует.</div>
E;
		}
	} // End of T = MATDC
	
	// Message - Attach - Link
	if($queue_id > 0 && $_GET['t']=='matli' && $queue_oid != 0){
		$don = true;
		// Get photo info
		$q = $db->query_row("SELECT * FROM vk_messages_attach WHERE `attach_id` = {$queue_id} AND `date` = {$queue_oid}");
		if($q['uri'] != ''){
			
			// Get file name
			preg_match("/[^\/]+$/",$q['uri'],$n);
			$f = date("Y-m",$q['date']);
			
			// Check do we have this file already ( useful if you are developer and pucked up attachments DB :D )
			if(is_file($cfg['photo_path'].'messages/'.$f.'/'.$n[0])){
print <<<E
<div class="alert alert-info" role="alert"><i class="far fa-file"></i> Файл найден локально</div>
E;
				$q = $db->query("UPDATE vk_messages_attach SET `path` = '".$cfg['photo_path']."messages/".$f."/".$n[0]."' WHERE `attach_id` = ".$queue_id." AND `date` = ".$queue_oid."");
				
				if($_GET['auto'] == '1'){
					$nrow = $db->query_row("SELECT attach_id, date FROM vk_messages_attach WHERE `path` = '' AND `type` = 'link' AND `is_local` = 0");
					if($nrow['attach_id'] > 0){
						print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_found_local']."</span> сек.","queue.php?t=matli&id=".$nrow['attach_id']."&oid=".$nrow['date']."&auto=1",$cfg['sync_found_local']);
					}
				}
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
					$saved = $c->file_save(array('path'=>$cfg['photo_path'].'messages/'.$f.'/','name'=>$n[0]),$out['content']);
					if($saved){
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Файл сохранен</div>
E;
						$q = $db->query("UPDATE vk_messages_attach SET `path` = '".$cfg['photo_path']."messages/".$f."/".$n[0]."' WHERE `attach_id` = ".$queue_id." AND `date` = ".$queue_oid."");
						
						if($_GET['auto'] == '1'){
							$nrow = $db->query_row("SELECT attach_id, date FROM vk_messages_attach WHERE `path` = '' AND `type` = 'link' AND `is_local` = 0");
							if($nrow['attach_id'] > 0){
								print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_photo_next_cd']."</span> сек.","queue.php?t=matli&id=".$nrow['attach_id']."&oid=".$nrow['date']."&auto=1",$cfg['sync_photo_next_cd']);
							}
						}
						
					} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Ошибка при сохранении файла</div>
E;
					}
				} else {
					// If error, let's try to see wtf is going on
					$error_code = false;
					if($func->is_html_response($out['content'])){
						$error_code = $skin->remote_server_error($out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 )));
					}
					// Something wrong with response or connection
					$skin->queue_no_data($error_code,"t=matli&id=".$queue_id."&oid=".$queue_oid,$queue_id);
				}
			} // end of local file check fail
		} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ID найден в очереди но ссылка на файл отсутствует.</div>
E;
		}
	} // End of T = MATLI
	
	// Message - Attach - Video (preview)
	if($queue_id > 0 && $_GET['t']=='matvi' && $queue_oid != 0){
		$don = true;
		// Get video preview info
		$q = $db->query_row("SELECT * FROM vk_messages_attach WHERE `attach_id` = {$queue_id} AND `owner_id` = {$queue_oid}");
		if($q['uri'] != ''){
			
			// Get file name
			preg_match("/[^\/]+$/",$q['uri'],$n);
			$f = date("Y-m",$q['date']);
			
			// Check do we have this file already ( useful if you are developer and pucked up attachments DB :D )
			if(is_file($cfg['video_path'].'messages/'.$f.'/'.$n[0])){
print <<<E
<div class="alert alert-info" role="alert"><i class="far fa-file"></i> Файл найден локально</div>
E;
				$q = $db->query("UPDATE vk_messages_attach SET `path` = '".$cfg['video_path']."messages/".$f."/".$n[0]."' WHERE `attach_id` = ".$queue_id." AND `owner_id` = ".$queue_oid."");
				
				if($_GET['auto'] == '1'){
					$nrow = $db->query_row("SELECT attach_id, owner_id FROM vk_messages_attach WHERE `path` = '' AND `type` = 'video' AND `is_local` = 0");
					if($nrow['attach_id'] > 0){
						print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_found_local']."</span> сек.","queue.php?t=matvi&id=".$nrow['attach_id']."&oid=".$nrow['owner_id']."&auto=1",$cfg['sync_found_local']);
					}
				}
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
					$saved = $c->file_save(array('path'=>$cfg['video_path'].'messages/'.$f.'/','name'=>$n[0]),$out['content']);
					if($saved){
print <<<E
<div class="alert alert-success" role="alert"><i class="far fa-file"></i> Файл сохранен</div>
E;
						$q = $db->query("UPDATE vk_messages_attach SET `path` = '".$cfg['video_path']."messages/".$f."/".$n[0]."' WHERE `attach_id` = ".$queue_id." AND `owner_id` = ".$queue_oid."");
					
						if($_GET['auto'] == '1'){
							$nrow = $db->query_row("SELECT attach_id, owner_id FROM vk_messages_attach WHERE `path` = '' AND `type` = 'video' AND `is_local` = 0");
							if($nrow['attach_id'] > 0){
								print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_photo_next_cd']."</span> сек.","queue.php?t=matvi&id=".$nrow['attach_id']."&oid=".$nrow['owner_id']."&auto=1",$cfg['sync_photo_next_cd']);
							}
						}
						
					} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Ошибка при сохранении файла</div>
E;
					}
				} else {
					// If error, let's try to see wtf is going on
					$error_code = false;
					if($func->is_html_response($out['content'])){
						$error_code = $skin->remote_server_error($out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 )));
					}
					// Something wrong with response or connection
					$skin->queue_no_data($error_code,"t=matvi&id=".$queue_id."&oid=".$queue_oid,$queue_id);
				}
			} // end of local file check fail
		} else {
print <<<E
<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ID найден в очереди но ссылка на файл отсутствует.</div>
E;
		}
	} // End of T = MATVI
	
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
                  <th>#</th>
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
	$r = $db->query("SELECT * FROM vk_photos WHERE `in_queue` = 1 ORDER BY date_added DESC LIMIT 0,{$show}");
	while($row = $db->return_row($r)){
		$row['date_added'] = date("Y-m-d H:i:s",$row['date_added']);
		// Add a autodownload for the first element in list
		if($first['p'] == true){
			$first['p'] = false;
			$auto = "&nbsp;&nbsp;<a href=\"queue.php?t=p&id={$row['id']}&auto=1\" class=\"{$btnclass}\" onClick=\"jQuery('#{$row['id']}').hide();return true;\" title=\"Скачать автоматически\"><b class=\"{$btniconauto}\"></b></a>";
		} else { $auto = ''; }
print <<<E
<tr id="{$row['id']}">
  <td>{$row['id']}</td>
  <td><a href="{$row['uri']}" target="_blank">{$row['uri']}</a></td>
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
  <td>{$row['id']}</td>
  <td><a href="{$row['preview_uri']}" target="_blank">{$row['preview_uri']}</a></td>
  <td>{$row['date_added']}</td>
  <td><a href="queue.php?t=v&id={$row['id']}&oid={$row['owner_id']}" class="{$btnclass}" id="{$row['id']}" onClick="jQuery('#{$row['id']}').hide();return true;" title="Скачать"><b class="{$btnicon}"></b></a>{$auto}</td>
</tr>
E;
	}
}
$first['dc'] = true;
if($bar_queue['dc'] > 0){
	$r = $db->query("SELECT * FROM vk_docs WHERE `in_queue` = 1 ".($skip_list != '' ? "AND `id` NOT IN (".$skip_list.")" : "")." ORDER BY date DESC LIMIT 0,{$show}");
	while($row = $db->return_row($r)){
		$row['date'] = date("Y-m-d H:i:s",$row['date']);
		// Add a autodownload for the first element in list
		if($first['dc'] == true){
			$first['dc'] = false;
			$auto = "&nbsp;&nbsp;<a href=\"queue.php?t=dc&id={$row['id']}&auto=1\" class=\"{$btnclass}\" onClick=\"jQuery('#{$row['id']}').hide();return true;\" title=\"Скачать автоматически\"><b class=\"{$btniconauto}\"></b></a>";
		} else { $auto = ''; }
print <<<E
<tr id="{$row['id']}">
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


if($all_queue == 0 && $no_queue == true) {
	print '<tr><td colspan="4" style="text-align:center;color:#bbb;">Очередь закачки пуста</td></tr>';
}

if($bar_queue['p'] > $show || $bar_queue['m'] > $show){
print <<<E
<tr>
  <td colspan="4">
	<div class="alert alert-info" role="alert">
E;
print 'И ещё';
	if(($bar_queue['p']-$show) > 0){
		print ' <strong>'.($bar_queue['p']-$show).'</strong> фотографий;';
	}
	if(($bar_queue['m']-$show) > 0){
		print ' <strong>'.($bar_queue['m']-$show).'</strong> аудиозаписей;';
	}

print <<<E
    </div>
  </td>
</tr>
E;
}

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