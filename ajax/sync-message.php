<?php

header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Debug mode (if TRUE - no data would be saved)
define('SYNC_MSG_DEBUG', false);

// Output JSON container
$output = array(
	'response' => array(
		'error_msg' => '',
		'msg' => array(),
		'next_uri' => '',
		'done' => 0,
		'total' => 0
	),
	'error' => false
);

// Check do we have all needed GET data
$do = false;
$do_opts = array('dlg','msg','next');
$offset = false;
$dlg_id = 0;
$dlg_date = 0;
if(isset($_GET['offset']) && is_numeric($_GET['offset'])){
	$offset = $_GET['offset'] >= 0 ? intval($_GET['offset']) : 0;
}
if(isset($_GET['do']) && in_array($_GET['do'],$do_opts)){
	$do = $_GET['do'];
}
if(isset($_GET['dlg_id']) && is_numeric($_GET['dlg_id'])){
	$dlg_id = intval($_GET['dlg_id']);
}
if(isset($_GET['dlg_date']) && is_numeric($_GET['dlg_date'])){
	$dlg_date = $_GET['dlg_date'] >= 0 ? intval($_GET['dlg_date']) : 0;
}

if($offset === false || $do === false){
$output['error'] = true;
$output['response']['error_msg'] = <<<E
    <div><i class="fas fa-fw fa-times-circle text-danger"></i> Неизвестный запрос</div>
E;
	print json_encode($output);
	exit;
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
$f = new func();

// Get skin if we in debug mode
if(SYNC_MSG_DEBUG === true){
	echo $skin->header_ajax();
}

$don = false;

require_once ROOT ."/classes/vk-auth/wrapper.php";
$vka = new \VKA\dev($cfg['yt_dl_login'],$cfg['yt_dl_passw'],$cfg['vk_api_version']);
$vka->vka_init();

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

require_once(ROOT.'classes/profiles.php');
$prof = new profiles();
$prof->db = $db;
$prof->vk = $vk;
$prof->func = $f;

// Attach Functions
require_once(ROOT.'classes/attach.php');
$atch = new attach();
$atch->cfg = $cfg;
$atch->db = $db;
$atch->vk = $vk;
$atch->func = $f;
$atch->skin = $skin;

if($vk_session['vk_token'] != '' && $token_valid == true){
	try {
	
	// Do = Dialogs
	if($do == 'dlg'){
		$count = 100; // Maximum: 100
		$output['response']['done'] = $count;
		
	// We logged in, get VK dialog list
	$api = $vka->vka_method('messages.getConversations',array('param_offset'=>$offset,'param_count'=>$count));
	
	$api_dialogs = array();
	$vk_dialogs_total = 0;
	
	if(isset($api['response']) && $api['response'] != ''){
		
		$don = true;
		$api_dialogs = $api['response']['items'];
		$vk_dialogs_total = $api['response']['count'];
		
	} else {
		
		$output['error'] = true;
$output['response']['error_msg'] = <<<E
    <div class="alert alert-danger mb-0" role="alert">{$api['error']['error_msg']}</div>
E;
	}
	
	// Check & process
	if(!empty($api_dialogs)){
		
		$dialog_ids = '';
		$dialog_new_ids = array();
		$dialog_group_ids = '';
		$dialog_new_group_ids = array();
		$dialog_exist = array();
		
		$q = $db->query("SELECT id,in_read,chat_id,is_new,is_upd FROM vk_dialogs");
		while($row = $db->return_row($q)){
			$dialog_exist[$row['id']][$row['chat_id']] = array('read' => $row['in_read'], 'chat' => $row['chat_id'], 'new' => $row['is_new'], 'upd' => $row['is_upd']);
		}
		
		// Get returned IDs
		foreach($api_dialogs as $pk => $pv){
			//print_r($pv);
			if($pv['conversation']['peer']['type'] == 'user'){ // Filter Users from Groups and Chats
				$dialog_ids .= ($dialog_ids != '' ? ',' : '').$pv['conversation']['peer']['id'];
			}
			if($pv['conversation']['peer']['type'] == 'group'){
				$dialog_group_ids .= ($dialog_group_ids != '' ? ',' : '').abs($pv['conversation']['peer']['id']);
			}
			// Insert OR update dialog
			$multi = array('on' => 0, 'chat_id' => 0, 'users' => 0, 'admin' => 0);
			if($pv['conversation']['peer']['type'] == 'chat'){
				// Fix for members count not exist in new API
				if(!isset($pv['conversation']['chat_settings']['members_count'])){
					$pv['conversation']['chat_settings']['members_count'] = 0;
				}
				
				// Set last active user as 'admin'
				$admin_id = (isset($pv['last_message']['from_id']) && is_numeric($pv['last_message']['from_id'])) ? $pv['last_message']['from_id'] : 0;
				$multi = array(
					'on' => 1,
					'chat_id' => $pv['conversation']['peer']['local_id'],
					'users' => $pv['conversation']['chat_settings']['members_count'],
					'admin' => $admin_id);
				// Add 'admin' ID to dialogs users IDs
				if($admin_id > 0){ $dialog_ids .= ($dialog_ids != '' ? ',' : '').$admin_id; }
			}
			$f->dialog_insert($pv,$multi,$dialog_exist);
		}
		
		if($dialog_ids != ''){
			$dialog_ids = $prof->profile_get_new('user',$dialog_ids,SYNC_MSG_DEBUG);
			
			// Set last profiles as new and save em
			if(!empty($dialog_ids)){
				// Get data to new profiles array
				foreach($api_dialogs as $ak => $av){
					if(in_array($av['conversation']['peer']['id'],$dialog_ids)){
						$dialog_new_ids[$av['conversation']['peer']['id']] = $av['conversation']['peer']['id'];
					}
					// Check if we have multichat data
					if($av['conversation']['peer']['type'] == 'chat' && in_array($av['last_message']['from_id'],$dialog_ids)){
						$dialog_new_ids[$av['last_message']['from_id']] = $av['last_message']['from_id'];
					}
				}
				
				if(!empty($dialog_new_ids)){
					$prof->profile_user_add($dialog_new_ids,SYNC_MSG_DEBUG);
				}
			} // end new profiles
		}
		
		if($dialog_group_ids != ''){
			$dialog_group_ids = $prof->profile_get_new('group',$dialog_group_ids,SYNC_MSG_DEBUG);
			
			// Set last profiles as new and save em
			if(!empty($dialog_group_ids)){
				// Get data to new profiles array
				foreach($api_dialogs as $ak => $av){
					$gid = abs($av['conversation']['peer']['id']);
					if(in_array($gid,$dialog_group_ids)){
						$dialog_new_group_ids[$gid] = $gid;
					}
				}
				
				if(!empty($dialog_new_group_ids)){
					$prof->profile_group_add($dialog_new_group_ids,SYNC_MSG_DEBUG);
				}
			} // end new groups
		}
	} // Check & Process END
	
	// Calculate FROM and TO values
	$from_to = $f->get_offset_range($offset,$count,$vk_dialogs_total);
	if($output['error'] != true){
		
		$output['response']['msg'][] = '<div>Получаем диалоги <b> '.$from_to['from'].' - '.$from_to['to'].' / '.$vk_dialogs_total.'</b> из ВК.</div>';
	
		// Let's recount dialogs
		$q5 = $db->query("UPDATE vk_counters SET `dialogs` = (SELECT COUNT(*) FROM vk_dialogs)");
	
			// If we done with all dialogs
			if(($offset+$count) >= $vk_dialogs_total){
				// No unsynced dialogs left. This is the end...
				$output['response']['msg'][] = '<div class="alert alert-success mb-0" role="alert"><strong>Товарищ майор!</strong> Синхронизация диалогов завершена. Чтобы начать проверку сообщений снимите с паузы.</div>';
			} else {
				// Some dialogs is not synced yed
				$output['response']['msg'][] = '<div>Перехожу к следующей порции диалогов...</div>';
		
				// Calculate offset and reload page
				$offset_new = $offset+$count;
				$output['response']['next_uri'] = '/ajax/sync-message.php?do=dlg&offset='.$offset_new;
				$output['response']['total'] = $vk_dialogs_total;
			}
		}
	} // Do dialog END
	
		// Do = Next
		if($do == 'next'){
			$don = true;
			// Check do we need sync updated or new dialogs?
			$q0 = $db->query_row("SELECT id,date FROM vk_dialogs WHERE `is_new` = 1 OR `is_upd` = 1 ORDER BY `id` DESC LIMIT 1");
			if(!empty($q0['date'])){
				$output['response']['msg'][] = '<div>Найдены сообщения. Нажмите продолжить чтобы начать получение новых сообщений.</div>';
				$output['response']['next_uri'] = '/ajax/sync-message.php?do=msg&offset=0&dlg_id='.$q0['id'].'&dlg_date='.$q0['date'];
			} else {
				$output['response']['msg'][] = '<div>Сообщений требующих синхронизации не найдено.</div>';
			}
		} // Do next END
		
		
		// Do = Messages
		if($do == 'msg'){
			// Check Dialog ID
			$q = $db->query_row("SELECT * FROM vk_dialogs WHERE `id` = {$dlg_id} AND `date` = {$dlg_date}");
			if(!empty($q['date']) && !empty($q['in_read'])){
				
				$quick = ($q['is_new'] == 1) ? false : true;
				$quick_sync_stop = false;
				$count = 200; // Maximum: 200
				$output['response']['done'] = $count;
				
				$peer = $q['id'];
				if($q['chat_id'] != 0){ $peer = 2000000000 + $q['chat_id']; }	// Group Chat ID
				
				// We logged in, get VK dialog list
				$api = $vka->vka_method('messages.getHistory',array('param_offset'=>$offset,'param_count'=>$count,'param_peer_id'=>$peer));
	
				$api_msg = array();
				$vk_msg_total = 0;
				
				if($api['response'] != ''){
				
					$don = true;
					$api_msg = $api['response']['items'];
					$vk_msg_total = $api['response']['count'];
					
					if($offset == 0){
						// Send 'count' as total
						//if($quick == false){
							$output['response']['total'] = $api['response']['count'];
						//}
						// Send difference between saved & new messages
						//if($quick == true){
							//$output['response']['total'] = $api['response']['in_read'] - $q['in_read'];
						//}
					}
				
				}
				
				
				if(!empty($api_msg)){
				
					foreach($api_msg as $k => $v){
						$attach = 0;
						$forward = 0;
						$forward_attach = 0;
			
			// Check attachments
			if(!empty($v['attachments'])){
				$attach = 1;
				
				foreach($v['attachments'] as $atv => $atk){
					
					// Attach :: Parse
					$atch->attach_parse($v,$atk,$vk_session,array('forwarded'=>false),SYNC_MSG_DEBUG);
					
					
				} // Foreach end
			} // Attachments end
			
			// Check forwarded messages
			$fwd_profiles = array();
			if(!empty($v['fwd_messages'])){
				$forward = 1;
				
				foreach($v['fwd_messages'] as $fwdk => $fwdm){
					$fwd_attach = 0;
					
					// quick fix for API v5.9x
					if(!isset($fwdm['user_id'])){ $fwdm['user_id'] = $fwdm['from_id']; }
					
					// Insert user ID in array
					$fwd_profiles[$fwdm['user_id']] = $fwdm['user_id'];
					
					// Check the attachment first
					if(!empty($fwdm['attachments'])){
						$forward_attach = 1;
						$fwd_attach = 1;
						
						foreach($fwdm['attachments'] as $fatv => $fatk){
							
							// Set DATE same as forwarded message date, to bind it as key
							$fatk[$fatk['type']]['date'] = $fwdm['date'];
							
							// Insert attach user ID in array
							$possible_keys = array(
								0 => 'owner_id', 1 => 'from_id', 2 => 'to_id',
							);
							foreach($possible_keys as $psk => $psv){
								if(isset($fatk[$fatk['type']][$psv])){
									$fwd_profiles[$fatk[$fatk['type']][$psv]] = $fatk[$fatk['type']][$psv];
								}
							}
							// User IDs to array end
							
							// Attach :: Parse
							$atch->attach_parse($v,$fatk,$vk_session,array('forwarded'=>true),SYNC_MSG_DEBUG);
							
					
						} // Foreach end
					} // Forwarded Attachments end
						
					// Set forwarded message id as negative value of message id
					$fwdm['id'] = -abs($v['id']);
					$fwdm['chat_id'] = isset($v['chat_id']) ? $v['chat_id'] : 0;
					$fwdm['from_id'] = $fwdm['user_id'];
					
					// Insert OR update Forwarded message
					$f->dialog_message_insert($fwdm,$fwd_attach,0,SYNC_MSG_DEBUG);
					
				} // Foreach end
			} // Forwarded end
			
			// Check and insert new profiles from forwarded messages
			if(!empty($fwd_profiles)){
				// ADD: group parse and wall-> copy_history
				$fwd_pr = '';
				$fwd_gr = '';
				// Split Users and Groups
				foreach($fwd_profiles as $fpk => $fpv){
					if($fpv > 0){ $fwd_pr .= ($fwd_pr != '' ? ',' : '').$fpv; }
					if($fpv < 0){ $fwd_gr .= ($fwd_gr != '' ? ',' : '').abs($fpv); }
				}
				// If we have new users, add em
				if(!empty($fwd_pr)){
					$fwd_pr = $prof->profile_get_new('user',$fwd_pr,SYNC_MSG_DEBUG);
					if(!empty($fwd_pr)){ $prof->profile_user_add($fwd_pr,SYNC_MSG_DEBUG); }
				}
				// If we have new groups, add em
				if(!empty($fwd_gr)){
					$fwd_gr = $prof->profile_get_new('group',$fwd_gr,SYNC_MSG_DEBUG);
					if(!empty($fwd_gr)){ $prof->profile_group_add($fwd_gr,SYNC_MSG_DEBUG); }
				}
			}

						// Insert OR update message
						$f->dialog_message_insert($v,$attach,$forward,SYNC_MSG_DEBUG);
			
						// Fast sync option
						// Check the date of the last post to our posts. If found, stop sync.
						if($quick == true && $q['in_read'] > 0 && $v['id'] <= $q['in_read']){
							$quick_sync_stop = true;
						}
					}
		

				} // Check & Process END
				
				// Calculate FROM and TO values
				$from_to = $f->get_offset_range($offset,$count,$vk_msg_total);
				
				if(SYNC_MSG_DEBUG == true){
					echo '<p>Offset: '.$offset.' | Count: '.$count.' | Total: '.$vk_msg_total.'</p>';
				}
				
				$output['response']['msg'][] = '<div>Получаем сообщения <b> '.$from_to['from'].' - '.$from_to['to'].' / '.$vk_msg_total.'</b>'.($quick == true ? ' (быстрая синхронизация) ' : '').'</div>';
				
				if(SYNC_MSG_DEBUG == true){
					echo '<p>Quick: '.$quick.' | Quick sync stop: '.$quick_sync_stop.'</p>';
				}
				
				if($quick == true && $quick_sync_stop == true){
					if(SYNC_MSG_DEBUG == true){ echo 'Quick sync done. Task stopped.'; }
					if(SYNC_MSG_DEBUG == false){
						// Update current dialog status to done
						$q1 = $db->query("UPDATE vk_dialogs SET `is_new` = 0, `is_upd` = 0 WHERE `id` = ".$dlg_id." AND `date` = ".$dlg_date);
					}
					
					// Check do we need sync updated or new dialogs?
					$q2 = $db->query_row("SELECT id,date FROM vk_dialogs WHERE `is_new` = 1 OR `is_upd` = 1 ORDER BY `id` DESC LIMIT 1");
					if(!empty($q2['date'])){
						$output['response']['msg'][] = '<div>Найден диалог требующий синхронизации.</div>';
						$output['response']['next_uri'] = '/ajax/sync-message.php?do=msg&offset=0&dlg_id='.$q2['id'].'&dlg_date='.$q2['date'];
					} else {
						// No unsynced messages left. This is the end...
						$output['response']['msg'][] = '<div class="alert alert-success mb-0" role="alert"><strong>Все ходы записаны!</strong> Быстрая синхронизация сообщений завершена.</div>';
					}
				} else {
					if(SYNC_MSG_DEBUG == true){ echo 'Task continue. OffsetX: '.($offset+$count).' > '.$vk_msg_total; }
					// If we done with all messages
					if(($offset+$count) >= $vk_msg_total){
						if(SYNC_MSG_DEBUG == false){
							// Update current dialog status to done
							$q1 = $db->query("UPDATE vk_dialogs SET `is_new` = 0, `is_upd` = 0 WHERE `id` = ".$dlg_id." AND `date` = ".$dlg_date);
						}
					
						// Check do we need sync updated or new dialogs?
						$q2 = $db->query_row("SELECT id,date FROM vk_dialogs WHERE `is_new` = 1 OR `is_upd` = 1 ORDER BY `id` DESC LIMIT 1");
						if(!empty($q2['date'])){
							$output['response']['msg'][] = '<div>Найден следующий диалог требующий синхронизации.</div>';
							$output['response']['next_uri'] = '/ajax/sync-message.php?do=msg&offset=0&dlg_id='.$q2['id'].'&dlg_date='.$q2['date'];
						} else {
							// No unsynced messages left. This is the end...
							$output['response']['msg'][] = '<div class="alert alert-success mb-0" role="alert"><strong>Все ходы записаны!</strong> Быстрая синхронизация сообщений завершена.</div>';
						}
					} else {
						// Some messages on dialog is not synced yed
						$output['response']['msg'][] = '<div>Перехожу к следующей порции сообщений...</div>';
		
						// Calculate offset and reload page
						$offset_new = $offset+$count;
						$output['response']['next_uri'] = "/ajax/sync-message.php?do=msg&offset=".$offset_new."&dlg_id=".$dlg_id."&dlg_date=".$dlg_date;
					}
	
				} // Fast sync end
				
				
			} else {
				$output['error'] = true;
$output['response']['error_msg'] = <<<E
    <div class="alert alert-info mb-0" role="alert">Диалог {$dlg_id} не найден.</div>
E;
			}
		} // Do msg END
	
	
	// END Of catch
	} catch (Exception $error) {
		$output['error'] = true;
		$output['response']['error_msg'] = '<div>'.$error->getMessage().'</div>';
	}
// end of Token Check
} else {
	// Token is NOT valid, re-auth?
$output['error'] = true;
$output['response']['error_msg'] = <<<E
    <div class="alert alert-danger mb-0" role="alert"><span>Внимание!</span> Токен является недействительным. Необходимо авторизироваться.</div>
E;
}

if($don == false && $token_valid == true && $output['error'] != true){
$output['error'] = true;
$output['response']['error_msg'] = <<<E
    <div class="alert alert-info mb-0" role="alert">Нет заданий для синхронизации</div>
E;
}

$db->close($res);

if(SYNC_MSG_DEBUG === true){
	print $skin->footer_ajax();
} else {
	print json_encode($output);
}

?>