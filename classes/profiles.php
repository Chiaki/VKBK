<?php

class profiles {
	
	function __construct(){
		return true;
	}
	
	// Insert new user profiles in DB
	// IN: (array) new_ids | (bool) debug
	function profile_user_add($new_ids,$debug){
		if(!$new_ids){ return false; } else {
			$profile_data = '';
			if(!empty($new_ids)){
				// Get Users info
				// Warning: API limit is 1000 users via query, there is no limit check
				// because most dialogs would be 1 on 1 and within limit of 100 dialogs
				// per query chance to get overhead is low. Maybe fix this later...
				$profile_api = $this->vk->api('users.get', array(
					'user_ids' => implode(',',$new_ids),
					'fields' => 'screen_name,first_name,last_name,sex,photo_100'
				));
				$in_ids = array();
				
				foreach($profile_api['response'] as $pk => $pv){
					if(in_array($pv['id'],$new_ids)){
						$in_ids[$pv['id']] = $pv;
					}
				}
				
				if(!empty($in_ids)){
					// Make import query string
					foreach($in_ids as $k => $v){
						if(!isset($v['screen_name'])){ $v['screen_name'] = 'id'.$v['id']; }
						$profile_data .= ($profile_data != '' ? ',' : '')."({$v['id']},'".$this->db->real_escape($v['first_name'])."','".$this->db->real_escape($v['last_name'])."',{$v['sex']},'{$v['screen_name']}','{$v['photo_100']}','')";
					}
				}
				
				// If we have data to import, do it!
				if($profile_data != '' && $debug === false){
					$q = $this->db->query("INSERT INTO vk_profiles (`id`,`first_name`,`last_name`,`sex`,`nick`,`photo_uri`,`photo_path`) VALUES ".$profile_data);
				} else {
					print '<div class="m-2 p-2">Profiles insert:'.$this->func->dbg_row(array(explode(',',$profile_data)),false,'primary').'</div>';
				}
			}
		} // else
	}
	
	// Insert new group profiles in DB
	// IN: (array) new_ids | (bool) debug
	function profile_group_add($new_ids,$debug){
		if(!$new_ids){ return false; } else {
			$group_data = '';
			if(!empty($new_ids)){
				// Get Groups info
				$group_api = $this->vk->api('groups.getById', array(
					'group_ids' => implode(',',$new_ids),
					'fields' => 'name,screen_name,photo_100'
				));
				$new_data = array();
				foreach($group_api['response'] as $pk => $pv){
					if(in_array($pv['id'],$new_ids)){
						$new_data[$pv['id']] = $pv;
					}
				}
				if(!empty($new_data)){
					// Make import query string
					foreach($new_data as $k => $v){
						$group_data .= ($group_data != '' ? ',' : '')."({$v['id']},'".$this->db->real_escape($v['name'])."','{$v['screen_name']}','{$v['photo_100']}','')";
					}
					
					// If we have data to import, do it!
					if($group_data != '' && $debug === false){
						$q = $this->db->query("INSERT INTO vk_groups (`id`,`name`,`nick`,`photo_uri`,`photo_path`) VALUES ".$group_data);
					} else {
						print '<div class="m-2 p-2">Group insert:'.$this->func->dbg_row(array(explode(',',$group_data)),false,'primary').'</div>';
					}
				}
			}
		} // else
	}
	
	// Get users\group array and return not existing in DB IDs
	// IN: (string) type [user|group] | (string) ids | (bool) debug
	// OUT: (array) ids
	function profile_get_new($type,$ids,$debug){
		if($type == 'user'){ $q = $this->db->query("SELECT * FROM vk_profiles WHERE id IN(".$ids.")"); }
		if($type == 'group'){ $q = $this->db->query("SELECT * FROM vk_groups WHERE id IN(".$ids.")"); }
		$ids = explode(',',$ids);
		while($row = $this->db->return_row($q)){
			if(in_array($row['id'],$ids)){
				// Existing profile. Check it for changes.
				
				// Remove profile id from known list
				$k = array_keys($ids,$row['id']);
				foreach($k as $knk => $knv){
					unset($ids[$knv]);
				}
			}
		}
		return $ids;
		if($debug === true){
			print '<div class="m-2 p-2 bg-white rounded shadow-sm">New profiles: <p>'.implode(',',$ids).'</p></div>';
		}
	}
}

?>