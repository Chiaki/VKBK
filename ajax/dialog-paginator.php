<?php

header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Debug mode
define('DEBUG', false);

// Check do we have all needed GET data
$page = 0;
if(isset($_GET['page']) && is_numeric($_GET['page'])){
	$p = intval($_GET['page']);
	if($p > 0){ $page = $p; }
}
$dlgid = 0;
if(isset($_GET['dlgid']) && is_numeric($_GET['dlgid'])){
	$dlgid = $_GET['dlgid'];
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

// Attach Functions
require_once(ROOT.'classes/attach.php');
$atch = new attach();
$atch->cfg = $cfg;
$atch->db = $db;
$atch->func = $f;
$atch->skin = $skin;

// Get session
$q = $db->query("SELECT * FROM vk_session WHERE `vk_id` = 1");
$vk_session = $row = $db->return_row($q);

// Get skin if we in debug mode
if(DEBUG === true){
	echo $skin->header_ajax();
}

$offset_page = ($page > 0) ? $cfg['perpage_dlg_messages']*$page : 0;
// Get 1 more video to see do we have something on the next page
$perpage = $cfg['perpage_dlg_messages']+1;
$next = 0;

$messages = array();
$users = array();			// Users
$users_data = array();
$groups = array();			// Groups
$groups_data = array();
$attach = array();			// Attachments
$attach_data = array();
$fwd = array();				// Forwarded
$fwd_data = array();
$fwd_attach = array();		// Forwarded attachments
$fwd_attach_data = array();

// Get message data
$q = $db->query("SELECT * FROM vk_messages WHERE `msg_dialog` = {$dlgid} AND `msg_id` > 0 ORDER BY `msg_date` DESC LIMIT {$offset_page},{$perpage}");
while($row = $db->return_row($q)){
	if($next < $cfg['perpage_dlg_messages']){
		
		$messages[] = $row;
		if($row['msg_user'] < 1){
			$groups[$row['msg_user']] = abs($row['msg_user']);
		} else {
			$users[$row['msg_user']] = $row['msg_user'];
		}
		if($row['msg_attach'] == 1){
			$attach[] = $row['msg_id'];
		}
		if($row['msg_forwarded'] == 1){
			$fwd[] = -$row['msg_id'];
		}
	
	}
	// Increase NEXT so if we load a full page we would have in the end NEXT = perpage+1
	// Otherwise if next would be lower or equal perpage there is no result for the next page
	$next++;
}
// Get forwarded messages data
if(count($fwd) > 0){
	$q = $db->query("SELECT * FROM vk_messages WHERE `msg_id` IN(".implode(",",$fwd).") ORDER BY `msg_date` ASC");
	while($row = $db->return_row($q)){
		$fwd_data[abs($row['msg_id'])][] = $row;
		// Add users info to array
		if($row['msg_user'] < 1){
			$groups[$row['msg_user']] = abs($row['msg_user']);
		} else {
			$users[$row['msg_user']] = $row['msg_user'];
		}
		if($row['msg_attach'] == 1){
			$fwd_attach[] = $row['msg_id'];
		}
	}
}
// Get User information
if(count($users) > 0){
	$q = $db->query("SELECT id, first_name, last_name, nick, photo_path FROM vk_profiles WHERE `id` IN(".implode(",",$users).")");
	while($row = $db->return_row($q)){
		$users_data[$row['id']] = $row;
	}
}
// Get Groups information
if(count($groups) > 0){
	$q = $db->query("SELECT id, name, photo_path FROM vk_groups WHERE `id` IN(".implode(",",$groups).")");
	while($row = $db->return_row($q)){
		$groups_data[$row['id']] = $row;
	}
}

// Get Attachments
if(count($attach) > 0){
	$attachs_local = array();
	$skipped_local = array('sticker','link');
	$q = $db->query("SELECT * FROM vk_messages_attach WHERE `wall_id` IN(".implode(",",$attach).")");
	while($row = $db->return_row($q)){
		$attach_data[$row['wall_id']][] = $row;
		if($row['is_local'] == 1 && !in_array($row['type'],$skipped_local)){
			$attachs_local[$row['type']][$row['attach_id']] = $row['wall_id'];
		}
	}
	// If local attach exists
	if(count($attachs_local) > 0){
		// Set empty types for ID's
		$local = array(
			'photos' => array(),
			'videos' => array(),
			'docs'   => array()
		);
		// Go through locals and get ID's for types of attach
		// Structure of array: [type][attach_id] = wall_id
		foreach($attachs_local as $atlt => $atli){
			if($atlt == 'photo'){ foreach($atli as $lk => $lv){ $local['photos'][] = $lk; } }
			if($atlt == 'video'){ foreach($atli as $lk => $lv){ $local['videos'][] = $lk; } }
			if($atlt == 'doc'){   foreach($atli as $lk => $lv){ $local['docs'][] = $lk; } }
		}
		// If we have some local data... GET IT!
		// add local data to attach as [ID] => data so we could get it later
		if(!empty($local['photos'])){
			$qp = $db->query("SELECT * FROM vk_photos WHERE `id` IN(".implode(",",$local['photos']).")");
			while($row = $db->return_row($qp)){
				$attach_data[$attachs_local['photo'][$row['id']]][$row['id']] = $row;
			}
		}
		if(!empty($local['docs'])){
			$qp = $db->query("SELECT * FROM vk_docs WHERE `id` IN(".implode(",",$local['docs']).")");
			while($row = $db->return_row($qp)){
				$attach_data[$attachs_local['doc'][$row['id']]][$row['id']] = $row;
			}
		}
		if(!empty($local['videos'])){
			$qp = $db->query("SELECT * FROM vk_videos WHERE `id` IN(".implode(",",$local['videos']).")");
			while($row = $db->return_row($qp)){
				$attach_data[$attachs_local['video'][$row['id']]][$row['id']] = $row;
			}
		}
	}
}

// Get Forwarded Attachments
if(count($fwd_attach) > 0){
	$attachs_fwd_local = array();
	$skipped_fwd_local = array('sticker','link');
	$q = $db->query("SELECT * FROM vk_messages_attach WHERE `wall_id` IN(".implode(",",$fwd_attach).")");
	while($row = $db->return_row($q)){
		$fwd_attach_data[$row['wall_id']][] = $row;
		if($row['is_local'] == 1 && !in_array($row['type'],$skipped_fwd_local)){
			$attachs_fwd_local[$row['type']][$row['attach_id']] = $row['wall_id'];
		}
	}
	// If local attach exists
	if(count($attachs_fwd_local) > 0){
		// Set empty types for ID's
		$local = array(
			'photos' => array(),
			'videos' => array(),
			'docs'   => array()
		);
		// Go through locals and get ID's for types of attach
		// Structure of array: [type][attach_id] = wall_id
		foreach($attachs_fwd_local as $atlt => $atli){
			if($atlt == 'photo'){ foreach($atli as $lk => $lv){ $local['photos'][] = $lk; } }
			if($atlt == 'video'){ foreach($atli as $lk => $lv){ $local['videos'][] = $lk; } }
			if($atlt == 'doc'){   foreach($atli as $lk => $lv){ $local['docs'][] = $lk; } }
		}
		// If we have some local data... GET IT!
		// add local data to attach as [ID] => data so we could get it later
		if(!empty($local['photos'])){
			$qp = $db->query("SELECT * FROM vk_photos WHERE `id` IN(".implode(",",$local['photos']).")");
			while($row = $db->return_row($qp)){
				$fwd_attach_data[$attachs_fwd_local['photo'][$row['id']]][$row['id']] = $row;
			}
		}
		if(!empty($local['docs'])){
			$qp = $db->query("SELECT * FROM vk_docs WHERE `id` IN(".implode(",",$local['docs']).")");
			while($row = $db->return_row($qp)){
				$fwd_attach_data[$attachs_fwd_local['doc'][$row['id']]][$row['id']] = $row;
			}
		}
		if(!empty($local['videos'])){
			$qp = $db->query("SELECT * FROM vk_videos WHERE `id` IN(".implode(",",$local['videos']).")");
			while($row = $db->return_row($qp)){
				$fwd_attach_data[$attachs_fwd_local['video'][$row['id']]][$row['id']] = $row;
			}
		}
	}
}
//print_r($fwd_attach_data);

$messages = array_reverse($messages);
if(count($messages) > 0){
	print '<div id="mp'.$page.'">';
}
foreach($messages as $k => $v){
	if($v['msg_user'] < 1){
		$uplus = abs($v['msg_user']);
		$who = $groups_data[$uplus];
		$ava_path = "groups/".$who['photo_path'];
		$who['first_name'] = $who['name'];
	} else {
		// Check if user is a ghost (does not known by me)
		if(!isset($users_data[$v['msg_user']])){
			$who = array(
				'id' => $v['msg_user'],
				'first_name' => "VK# ".$v['msg_user'],
				'last_name' => "",
				'nick' => "Призрак",
				'photo_path' => ""
			);
			$ava_path = "#f44336";
		} else {
			$who = $users_data[$v['msg_user']];
			$ava_path = "profiles/".$who['photo_path'];
		}
	}
	
	$output_fwd = '';
	
	if(isset($fwd_data[$v['msg_id']])){
		foreach($fwd_data[$v['msg_id']] as $fwdk => $fwdv){
			if(!isset($users_data[$fwdv['msg_user']])){
				$fwd_name = array(
					'id' => $fwdv['msg_user'],
					'first_name' => "VK# ".$fwdv['msg_user'],
					'last_name' => "",
					'nick' => "Призрак",
					'photo_path' => ""
				);
				$fwd_ava_path = "#f44336";
			} else {
				$fwd_name = $users_data[$fwdv['msg_user']];
				$fwd_ava_path = "profiles/".$users_data[$fwdv['msg_user']]['photo_path'];
			}
			if(substr($fwd_ava_path,0,1) == "#"){
				$fwd_ava_path = '<span class="mb-1 mr-3" style="background:'.$fwd_ava_path.';display:block;min-width:35px;min-height:35px;float:left;border-radius:50%;"></span>';
			} else {
				$fwd_ava_path = '<img src="data/'.$fwd_ava_path.'" class="wall-ava dlg-ava mb-1 mr-2" />';
			}
			
			// Show forwarded messages and combine same user messages within 1 hour
			$output_fwd .= '<div class="ml-1 px-3 py-2" style="border-left: 2px solid #ccc;">';
			if($fwdk >= 1 && $fwd_data[$v['msg_id']][$fwdk-1]['msg_date']+3600 >= $fwdv['msg_date'] && $fwd_data[$v['msg_id']][$fwdk-1]['msg_user'] == $fwdv['msg_user']){
			} else {
$output_fwd .= <<<E
{$fwd_ava_path}
<strong>{$fwd_name['first_name']}</strong>&nbsp;&nbsp;{$f->dialog_date_format($fwdv['msg_date'])}<br/>
E;
			}
			
			
	// Forwarded message attachments
	$fwd_output_attach = '';
	
	if($fwdv['msg_attach'] == 1 && isset($fwd_attach_data[$fwdv['msg_id']])){
		$fwd_output_attach .= '<div class="p-2">';
		foreach($fwd_attach_data[$fwdv['msg_id']] as $fwdak => $fwdav){
			if($fwdav['date'] == $fwdv['msg_date']){
				// Type - Sticker
				$fwd_output_attach .= $atch->dlg_attach_sticker($fwdav);
				// Type - Photo or attach photo
				$fwd_output_attach .= $atch->dlg_attach_photo($fwdav,$fwd_attach_data,$v);
				// Remote Link Attach
				$fwd_output_attach .= $atch->dlg_attach_link($fwdav);
				// Type - Document or attach document
				$fwd_output_attach .= $atch->dlg_attach_doc($fwdav);
				// Remote Video Attach
				$fwd_output_attach .= $atch->dlg_attach_video($fwdav);
				// Type - Wall
				$fwd_output_attach .= $atch->dlg_attach_wall($fwdav,$vk_session);
			}
		}
		$fwd_output_attach .= '</div><div style="clear:both;"></div>';

	} // Output Attach end
			
			$output_fwd .= $fwdv['msg_body'].$fwd_output_attach.'</div>';
			// Forwarded message end
		}
	} // Output fwd end
	
	// Message attachments
	$output_attach = '';
	if(isset($attach_data[$v['msg_id']])){
		$output_attach .= '<div class="p-2">';
		foreach($attach_data[$v['msg_id']] as $ak => $av){
			// Type - Sticker
			$output_attach .= $atch->dlg_attach_sticker($av);
			// Type - Photo or attach photo
			$output_attach .= $atch->dlg_attach_photo($av,$attach_data,$v);
			// Remote Link Attach
			$output_attach .= $atch->dlg_attach_link($av);
			// Type - Document or attach document
			$output_attach .= $atch->dlg_attach_doc($av);
			// Remote Video Attach
			$output_attach .= $atch->dlg_attach_video($av);
		}
		$output_attach .= '</div><div style="clear:both;"></div>';

	} // Output Attach end
	
	if(substr($ava_path,0,1) == "#"){
		$ava_path = '<span class="mb-2 ml-2" style="background:'.$ava_path.';display:block;min-width:35px;min-height:35px;float:left;border-radius:50%;"></span>';
	} else {
		$ava_path = '<img src="data/'.$ava_path.'" class="wall-ava dlg-ava mb-2 ml-2" />';
	}
	
	$kk = $k+1;
	// Combine messages of same user within 1 hour
	if($k >= 1 && $messages[$k-1]['msg_user'] == $v['msg_user'] && $messages[$k-1]['msg_date']+3600 >= $v['msg_date']){
		// Check next message for same user
		if(isset($messages[$kk]) && $messages[$kk]['msg_user'] != $v['msg_user']){
			print '<div class="msg-body mb-3" style="min-height:auto;">';
		} else {
			print '<div class="msg-body mb-1" style="min-height:auto;">';
		}
print <<<E
	<div class="ml-5 pl-3">
		{$v['msg_body']}{$output_attach}{$output_fwd}
	</div>
E;
	} else {
		// Check next message for same user
		if(isset($messages[$kk]) && $messages[$kk]['msg_user'] != $v['msg_user']){
			print '<div class="msg-body mb-3">';
		} else {
			print '<div class="msg-body mb-1">';
		}
print <<<E
	{$ava_path}
	<div class="ml-5 pl-3">
		<strong>{$who['first_name']}</strong>&nbsp;&nbsp;{$f->dialog_date_format($v['msg_date'])}<br/>
		{$v['msg_body']}{$output_attach}{$output_fwd}
	</div>
E;
	}
	print '</div>';
	// Message end
}
if(count($messages) > 0){
	print '<div id="dlgp'.$page.'"></div></div>';
}

$db->close($res);

if(DEBUG === true){
	print $skin->footer_ajax();
}

?>