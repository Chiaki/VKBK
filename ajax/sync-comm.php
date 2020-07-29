<?php
/*
	vkbk :: /ajax/sync-comm.php
	since v0.9.0
	example: do=check&offset=0
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
$do_opts = array('check','next','sync');
$offset = false;
$wall_id = 0;

if(isset($_GET['do']) && in_array($_GET['do'],$do_opts)){
	$do = $_GET['do'];
}
if(isset($_GET['offset']) && is_numeric($_GET['offset'])){
	$offset = $_GET['offset'] >= 0 ? intval($_GET['offset']) : -1;
}
if(isset($_GET['wall_id']) && is_numeric($_GET['wall_id'])){
	$wall_id = intval($_GET['wall_id']);
}

if($do === false || $offset === false){
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
if(DEBUG_SYNC === true){
	echo $skin->header_ajax();
}

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
	
	// Do = check
	if($do == 'check' && $offset >= 0){
		$count = 100;
		// Get wall posts for comment count
		$api = $vk->api('wall.get', array(
			'owner_id' => $vk_session['vk_user'],
			'offset' => $offset,
			'count' => $count,
			'filter' => 'all',
			'extended' => 0, // 1 — будут возвращены три массива items, profiles и groups.
		));
		
		$api_posts = array();
		$vk_post_total = 0;
		
		if($api['response'] != ''){
			$don = true;
			$api_posts = $api['response']['items'];
			$vk_post_total = $api['response']['count'];
			
			$output['response']['done'] = $count;
			$output['response']['total'] = $vk_post_total;
		}
		
		/* List of post => comment */
		$comm = array();
		
		// Check posts for comments
		if(!empty($api_posts)){
			foreach($api_posts as $k => $v){
				if(isset($v['comments']['count']) && $v['comments']['count']!=0){
					$comm[$v['id']] = $v['comments']['count'];
				}
			}
		} // endif
		
		// Comments change check
		if(!empty($comm)){
			// Get local data
			$q = $db->query("SELECT id, comments FROM vk_wall WHERE id IN(".implode(',',array_keys($comm)).")");
			while($row = $db->return_row($q)){
				if(isset($comm[$row['id']])){
					// Remove not changed ID's
					if($row['comments'] == $comm[$row['id']]){
						unset($comm[$row['id']]);
					}
				}
			}
		} // endif
		
		// Set update flag for comments
		if(!empty($comm)){
			if(DEBUG_SYNC == false){
				$q = $db->query("UPDATE vk_wall SET comm_upd = 1 WHERE id IN(".implode(',',array_keys($comm)).")");
			} else {
				echo 'Posts for update: '.implode(', ',array_keys($comm));
			}
		}
		
		// Calculate FROM and TO values
		$from_to = $f->get_offset_range($offset,$count,$vk_post_total);
		
		$output['response']['msg'][] = '<div><i class="far fa-fw fa-circle"></i> Получаем записи <b> '.$from_to['from'].' - '.$from_to['to'].' / '.$vk_post_total.'</b> со стены.</div>';
		
		// If we done with all posts
		if(($offset+$count) >= $vk_post_total){
			// No unsynced items left. This is the end...
			$output['response']['msg'][] = '<div class="alert alert-primary mb-0" role="alert"><strong>Another brick in the wall!</strong> Проверка завершена. Синхронизация.</div>';
			$output['response']['next_uri'] = SLF.'?do=next&offset=0';
		} else {
			// Some items is not synced yed
			// Calculate offset
			$offset_new = $offset+$count;
			//print_r($offset_new);
			$output['response']['msg'][] = '<div><i class="far fa-fw fa-pause-circle"></i> Перехожу к следующей порции постов...</div>';
			$output['response']['next_uri'] = SLF.'?do='.$do.'&offset='.$offset_new;
			$output['response']['timer'] = $cfg['sync_wall_next_cd'];
		}

	} // Do check END
	
	// Do = Next
	if($do == 'next'){
		$don = true;
		// Check do we need sync
		$q0 = $db->query_row("SELECT id FROM vk_wall WHERE `comm_upd` = 1 ORDER BY `id` DESC LIMIT 1");
		if(!empty($q0['id'])){
			$output['response']['msg'][] = '<div>Есть новые комментарии. Cинхронизирую.</div>';
			$output['response']['next_uri'] = SLF.'?do=sync&offset=0&wall_id='.$q0['id'];
		} else {
			$output['response']['msg'][] = '<div>Новых комментариев не найдено.</div>';
		}
	} // Do next END
	
	// Do = sync
	if($do == 'sync'){
		// Check Wall ID
		$q = $db->query_row("SELECT * FROM vk_wall WHERE `id` = {$wall_id}");
		if($q['comm_upd'] != 0 && $q['id'] != 0){
			
			// Attach Functions
			require_once(ROOT.'classes/attach.php');
			$atch = new attach();
			$atch->cfg = $cfg;
			$atch->db = $db;
			$atch->vk = $vk;
			$atch->func = $f;
			$atch->skin = $skin;
			
			$count = 100; // Maximum: 100
			// Get post comments
			$api = $vk->api('wall.getComments', array(
				'owner_id' => $vk_session['vk_user'],
				'post_id' => $wall_id,
				'need_likes' => 0,
				'offset' => $offset,
				'count' => $count,
				'sort' => 'asc',
				'extended' => 1, // 1 — будут возвращены три массива items, profiles и groups.
				'thread_items_count' => 10
			));
			
			if(isset($api['error'])){
$output['error'] = true;
$output['response']['error_msg'] = <<<E
    <div><i class="fas fa-fw fa-times-circle text-danger"></i> {$api['error']['error_code']}: {$api['error']['error_msg']}</div>
E;
	print json_encode($output);
	exit;
			} // Alarm! ><
			
			$api_comm = array();
			$api_profiles = array();
			$api_groups = array();
			$vk_comm_total = 0;
			
			if($api['response'] != ''){
				$don = true;
				$api_comm = $api['response']['items'];
				if(isset($api['response']['profiles'])){ $api_profiles = $api['response']['profiles']; }
				if(isset($api['response']['groups'])){ $api_groups = $api['response']['groups']; }
				$vk_comm_total = $api['response']['count'];
				if($offset == 0){
					// Send 'count' as total
					$output['response']['total'] = $api['response']['count'];
				}
			}
			
			// Check & process profiles
			if(!empty($api_profiles)){
			
				$profile_ids = '';
				$profile_new_ids = array();
				
				// Get returned IDs
				foreach($api_profiles as $pk => $pv){
					$profile_ids .= ($profile_ids != '' ? ',' : '').$pv['id'];
				}
				
				if($profile_ids != ''){
					$q = $db->query("SELECT * FROM vk_profiles WHERE id IN(".$profile_ids.")");
					$profile_ids = explode(',',$profile_ids);
					while($row = $db->return_row($q)){
						if(in_array($row['id'],$profile_ids)){
							// Existing profile. Check it for changes.
							
							// Remove profile id from known list
							$k = array_search($row['id'],$profile_ids);
							unset($profile_ids[$k]);
						}
					}
					
					// Set last profiles as new and save em
					if(!empty($profile_ids)){
						// Get data to new profiles array
						foreach($api_profiles as $ak => $av){
							if(in_array($av['id'],$profile_ids)){
								$profile_new_ids[$av['id']] = $av;
							}
						}
						
						$profile_data = '';
						if(!empty($profile_new_ids)){
							// Make import query string
							foreach($profile_new_ids as $k => $v){
								if(!isset($v['screen_name'])){ $v['screen_name']='id'.$v['id']; }
								$profile_data .= ($profile_data != '' ? ',' : '')."({$v['id']},'".$db->real_escape($v['first_name'])."','".$db->real_escape($v['last_name'])."',{$v['sex']},'{$v['screen_name']}','{$v['photo_100']}','')";
							}
							
							// If we have data to import, do it!
							if($profile_data != '' && DEBUG_SYNC == false){
								$q = $db->query("INSERT INTO vk_profiles (`id`,`first_name`,`last_name`,`sex`,`nick`,`photo_uri`,`photo_path`) VALUES ".$profile_data);
							}
						}
					} // end new profiles
				}
			} // Profiles END
			
			// Check & process group profiles
			if(!empty($api_groups)){
			
				$group_ids = '';
				$group_new_ids = array();
				
				// Get returned IDs
				foreach($api_groups as $gk => $gv){
					$group_ids .= ($group_ids != '' ? ',' : '').$gv['id'];
				}
				
				if($group_ids != ''){
					$q = $db->query("SELECT * FROM vk_groups WHERE id IN(".$group_ids.")");
					$group_ids = explode(',',$group_ids);
					while($row = $db->return_row($q)){
						if(in_array($row['id'],$group_ids)){
							// Existing group. Check it for changes.
							
							// Remove group id from known list
							$k = array_search($row['id'],$group_ids);
							unset($group_ids[$k]);
						}
					}
					
					// Set last groups as new and save em
					if(!empty($group_ids)){
						// Get data to new group profiles array
						foreach($api_groups as $gk => $gv){
							if(in_array($gv['id'],$group_ids)){
								$group_new_ids[$gv['id']] = $gv;
							}
						}
						
						$group_data = '';
						if(!empty($group_new_ids)){
							// Make import query string
							foreach($group_new_ids as $k => $v){
								$group_data .= ($group_data != '' ? ',' : '')."({$v['id']},'".$db->real_escape($v['name'])."','{$v['screen_name']}','{$v['photo_100']}','')";
							}
							
							// If we have data to import, do it!
							if($group_data != '' && DEBUG_SYNC == false){
								$q = $db->query("INSERT INTO vk_groups (`id`,`name`,`nick`,`photo_uri`,`photo_path`) VALUES ".$group_data);
							}
						}
					} // end new groups
				}
			} // Groups END
			
			if(!empty($api_comm)){
				foreach($api_comm as $k => $v){
					$attach = 0;
					// Check attachments
					if(!empty($v['attachments'])){
						$attach = 1;
						foreach($v['attachments'] as $atv => $atk){
							// Attach :: Parse
							$atch->attach_parse($v,$atk,$vk_session,array('table'=>'comm','forwarded'=>false),DEBUG_SYNC);
						} // Foreach end
					} // Attachments end
					
					// Prepare default values
					$v['p_stack'] = (isset($v['parents_stack']) ? implode(',',$v['parents_stack']) : '');
					$v['t_count'] = (isset($v['thread']['count']) ? $v['thread']['count'] : 0);
					$v['reply_u'] = (isset($v['reply_to_user']) ? $v['reply_to_user'] : 0);
					$v['reply_c'] = (isset($v['reply_to_comment']) ? $v['reply_to_comment'] : 0);
					
					// Insert comment
					$f->wall_comment_insert($v,$attach,DEBUG_SYNC);
					
					// So same for threads
					if(isset($v['thread']['items'])){
						foreach($v['thread']['items'] as $ck => $cv){
							$attach_r = 0;
							// Check attachments
							if(!empty($cv['attachments'])){
								$attach_r = 1;
								foreach($cv['attachments'] as $catv => $catk){
									// Attach :: Parse
									$atch->attach_parse($cv,$catk,$vk_session,array('table'=>'comm','forwarded'=>false),DEBUG_SYNC);
								} // Foreach end
							} // Attachments end
						
							// Prepare default values
							$cv['p_stack'] = (isset($cv['parents_stack']) ? implode(',',$cv['parents_stack']) : '');
							$cv['t_count'] = 0;
							$cv['reply_u'] = (isset($cv['reply_to_user']) ? $cv['reply_to_user'] : 0);
							$cv['reply_c'] = (isset($cv['reply_to_comment']) ? $cv['reply_to_comment'] : 0);
					
							// Insert comment
							$f->wall_comment_insert($cv,$attach_r,DEBUG_SYNC);
						}
					} // Items end
				} // Foreach end
			} // Comments end
			
			// Calculate FROM and TO values
			$from_to = $f->get_offset_range($offset,$count,$vk_comm_total);
				
			if(DEBUG_SYNC == true){
				echo '<p>Offset: '.$offset.' | Count: '.$count.' | Total: '.$vk_comm_total.'</p>';
			}
			
			$output['response']['msg'][] = '<div>Пост #<b>'.$wall_id.'</b> получаем комментарии <b> '.$from_to['from'].' - '.$from_to['to'].' / '.$vk_comm_total.'</b></div>';
			
			if(DEBUG_SYNC == true){ echo 'Task continue. OffsetX: '.($offset+$count).' > '.$vk_comm_total; }
			// If we done with all messages
			if(($offset+$count) >= $vk_comm_total){
				if(DEBUG_SYNC == false){
					// Update current item status to done
					$q1 = $db->query("UPDATE vk_wall SET `comm_upd` = 0, `comments` = ".$vk_comm_total." WHERE `id` = ".$wall_id);
				}
				
				// Check do we need sync again or new item
				$q2 = $db->query_row("SELECT id FROM vk_wall WHERE `comm_upd` = 1 ORDER BY `id` DESC LIMIT 1");
				if(!empty($q2['id'])){
					$output['response']['msg'][] = '<div>Найден пост требующий синхронизации.</div>';
					$output['response']['next_uri'] = SLF.'?do='.$do.'&offset=0&wall_id='.$q2['id'];
					$output['response']['timer'] = $cfg['sync_comm_next_cd'];
				} else {
					// No unsynced posts left. This is the end...
					$output['response']['msg'][] = '<div><i class="fa fa-fw fa-check-circle text-success"></i> <strong>Без комментариев!</strong> Синхронизация комментариев завершена.</div>';
					
					// Let's recount
					$q5 = $db->query("UPDATE vk_counters SET `comments` = (SELECT COUNT(*) FROM vk_wall_comments)");
				}
			} else {
				// Some comments on post is not synced yed
				$output['response']['msg'][] = '<div>Перехожу к следующей порции комментариев...</div>';
				
				// Calculate offset and reload page
				$offset_new = $offset+$count;
				//print_r($offset_new);
				$output['response']['next_uri'] = SLF.'?do='.$do.'&offset='.$offset_new.'&wall_id='.$wall_id;
			}
			
		} else {
			$output['error'] = true;
$output['response']['error_msg'] = <<<E
    <div class="alert alert-info mb-0" role="alert">Пост #{$wall_id} не найден.</div>
E;
		}
	}
	// Do sync END
	
	// END Of catch
	} catch (Exception $error) {
		echo '<tr><td>'.$error->getMessage().'</td></tr>';
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

// End of IF OFFSET
} else {
$output['error'] = true;
$output['response']['error_msg'] = <<<E
    <div><i class="fas fa-fw fa-exclamation-circle text-warning"></i> Нет заданий для синхронизации</div>
E;
}

$db->close($res);

if(DEBUG_SYNC === true){
	print $skin->footer_ajax();
} else {
	print json_encode($output);
}

?>