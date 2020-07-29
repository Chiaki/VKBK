<?php

class queue {
	
	function __construct(){
		return true;
	}
	
	/*
		Save PRofile and GRoup
		IN:
		(string) queue_id - ID of element
		(string) type - short code of element type
		(string) auto - flag for auto queue; set `1` if ON
	*/
	function sprgr($queue_id,$type,$auto){
		$types = array('pr','gr');
		if(!in_array($type,$types) || $queue_id < 1){
			return false;
		}
		
		// Set values for types
		if($type == 'pr'){ $table = 'profiles'; $path = 'profiles'; }
		if($type == 'gr'){ $table = 'groups'; $path = 'groups'; }
		
		// Get photo info
		$q = $this->db->query_row("SELECT * FROM vk_{$table} WHERE `id` = {$queue_id}");
		if($q['photo_uri'] != ''){
			
			// Get file name
			preg_match("/[^\/]+\.(jpg|jpeg|png|gif|bmp)/",$q['photo_uri'],$n);
			
			// Check do we have this file already ( useful if you are developer and pucked up attachments DB :D )
			if(is_file(ROOT.'data/'.$path.'/'.$queue_id.'.'.$n[0])){
				print $this->skin->show_alert('info','far fa-file','Файл найден локально');
				
				$q = $this->db->query("UPDATE vk_{$table} SET `photo_path` = '".$queue_id.".".$n[0]."' WHERE `id` = ".$queue_id."");
				
				if($auto == '1'){
					$nrow = $this->db->query_row("SELECT id FROM vk_{$table} WHERE `photo_path` = ''");
					if($nrow['id'] > 0){
						print $this->skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$this->cfg['sync_found_local']."</span> сек.","queue.php?t={$type}&id=".$nrow['id']."&auto={$auto}",$this->cfg['sync_found_local']);
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
					$saved = $c->file_save(array('path'=>ROOT.'data/'.$path.'/','name'=>$queue_id.'.'.$n[0]),$out['content']);
					if($saved){
						print $this->skin->show_alert('success','far fa-file','Файл сохранен');
						
						$q = $this->db->query("UPDATE vk_{$table} SET `photo_path` = '".$queue_id.".".$n[0]."' WHERE `id` = ".$queue_id."");
						
						if($auto == '1'){
							$nrow = $this->db->query_row("SELECT id FROM vk_{$table} WHERE `photo_path` = ''");
							if($nrow['id'] > 0){
								print $this->skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$this->cfg['sync_photo_next_cd']."</span> сек.","queue.php?t={$type}&id=".$nrow['id']."&auto={$auto}",$this->cfg['sync_photo_next_cd']);
							}
						}
						
					} else {
						print $this->skin->show_alert('danger','fas fa-exclamation-triangle','Ошибка при сохранении файла');
					}
				} else {
					// If error, let's try to see wtf is going on
					$error_code = false;
					if($this->func->is_html_response($out['content'])){
						$error_code = $this->skin->remote_server_error($out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 )));
					}
					// Something wrong with response or connection
					$this->skin->queue_no_data($error_code,false,false);
				}
			} // end of local file check fail
		} else {
			print $this->skin->show_alert('danger','fas fa-exclamation-triangle','ID найден в очереди но ссылка на файл отсутствует.');
		}
		
	} // SPRGR end
	
	/*
		Save as Attach
		IN:
		(string) queue_id - ID of element
		(string|bool) queue_oid - owner_ID of element; `false` if not used
		(string) type - short code of element type
		(string) auto - flag for auto queue; set `1` if ON
	*/
	function save_as_attach($queue_id,$queue_oid,$type,$auto){
		// Default values
		$db_type = '';
		$table = '';
		$path = '';
		// Allowed types
		$types = array(
			'photo' => array('atph','matph','mwatph','catph'),
			'video' => array('atvi','matvi','mwatvi','catvi'),
			'link'  => array('atli','matli','mwatli','catli')
		);
		// Flag for work
		$type_allowed = false;
		
		// Check type and set basic values
		foreach($types as $k => $t){
			$found_type = '';
			foreach($t as $tt){
				if($type === $tt){ $type_allowed = true; $found_type = $k; }
			}
			if($found_type == 'photo'){
				$db_type = 'photo';
				$db_oid = 'owner_id';
				$countdown = $this->cfg['sync_photo_next_cd'];
				if($type == 'atph'){
					$table = 'attach'; $path = $this->cfg['photo_path'].'attach'; }
				if($type == 'matph'){
					$table = 'messages_attach'; $path = $this->cfg['photo_path'].'messages'; }
				if($type == 'mwatph'){
					$table = 'messages_wall_attach'; $path = $this->cfg['photo_path'].'messages_wall'; }
				if($type == 'catph'){
					$table = 'wall_comments_attach'; $path = $this->cfg['photo_path'].'wall_comments'; }
			}
			if($found_type == 'video'){
				$db_type = 'video';
				$db_oid = 'owner_id';
				$countdown = $this->cfg['sync_video_next_cd'];
				if($type == 'atvi'){
					$table = 'attach'; $path = $this->cfg['video_path'].'attach'; }
				if($type == 'matvi'){
					$table = 'messages_attach'; $path = $this->cfg['video_path'].'messages'; }
				if($type == 'mwatvi'){
					$table = 'messages_wall_attach'; $path = $this->cfg['video_path'].'messages_wall'; }
				if($type == 'catvi'){
					$table = 'wall_comments_attach'; $path = $this->cfg['video_path'].'wall_comments'; }
			}
			if($found_type == 'link'){
				$db_type = 'link';
				$db_oid = 'date';
				$countdown = $this->cfg['sync_photo_next_cd'];
				if($type == 'atli'){
					$table = 'attach'; $db_oid = 'owner_id'; $path = $this->cfg['photo_path'].'attach'; }
				if($type == 'matli'){
					$table = 'messages_attach'; $path = $this->cfg['photo_path'].'messages'; }
				if($type == 'mwatli'){
					$table = 'messages_wall_attach'; $path = $this->cfg['photo_path'].'messages_wall'; }
				if($type == 'catli'){
					$table = 'wall_comments_attach'; $path = $this->cfg['photo_path'].'wall_comments'; }
			}
		}
		
		if($type_allowed !== true || $queue_id < 1 || $queue_oid == 0 || $db_type == '' || $table == '' || $path == ''){
			return false;
		}
		
		// Get attach info
		$q = $this->db->query_row("SELECT * FROM vk_{$table} WHERE `type` = '{$db_type}' AND `attach_id` = {$queue_id} AND `{$db_oid}` = {$queue_oid}");
		if($q['uri'] != ''){
			
			// Get file name
			preg_match("/[^\/]+$/",$q['uri'],$n);
			$f = date("Y-m",$q['date']);
			
			// Check do we have this file already ( useful if you are developer and pucked up attachments DB :D )
			if(is_file($path.'/'.$f.'/'.$n[0])){
				print $this->skin->show_alert('info','far fa-file','Файл найден локально');
				
				$q = $this->db->query("UPDATE vk_{$table} SET `path` = '".$path."/".$f."/".$n[0]."' WHERE `type` = '".$db_type."' AND `attach_id` = ".$queue_id." AND `".$db_oid."` = ".$queue_oid."");
				
				if($auto == '1'){
					$nrow = $this->db->query_row("SELECT attach_id, {$db_oid} FROM vk_{$table} WHERE `path` = '' AND `type` = '{$db_type}' AND `is_local` = 0");
					if($nrow['attach_id'] > 0){
						print $this->skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$this->cfg['sync_found_local']."</span> сек.","queue.php?t={$type}&id=".$nrow['attach_id']."&oid=".$nrow[$db_oid]."&auto={$auto}",$this->cfg['sync_found_local']);
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
					$saved = $c->file_save(array('path'=>$path.'/'.$f.'/','name'=>$n[0]),$out['content']);
					if($saved){
						print $this->skin->show_alert('success','far fa-file','Файл сохранен');
						
						$q = $this->db->query("UPDATE vk_{$table} SET `path` = '".$path."/".$f."/".$n[0]."' WHERE `type` = '".$db_type."' AND `attach_id` = ".$queue_id." AND `".$db_oid."` = ".$queue_oid."");
						
						if($auto == '1'){
							$nrow = $this->db->query_row("SELECT attach_id, {$db_oid} FROM vk_{$table} WHERE `path` = '' AND `type` = '{$db_type}' AND `is_local` = 0");
							if($nrow['attach_id'] > 0){
								print $this->skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$countdown."</span> сек.","queue.php?t={$type}&id=".$nrow['attach_id']."&oid=".$nrow[$db_oid]."&auto={$auto}",$countdown);
							}
						}
					
					} else {
						print $this->skin->show_alert('danger','fas fa-exclamation-triangle','Ошибка при сохранении файла');
					}
				} else {
					// If error, let's try to see wtf is going on
					$error_code = false;
					if($this->func->is_html_response($out['content'])){
						$error_code = $this->skin->remote_server_error($out = $c->curl_req(array('uri' => $q['uri'], 'method'=>'', 'return'=>0 )));
					}
					// Something wrong with response or connection
					$this->skin->queue_no_data($error_code,"t=".$type."&id=".$queue_id."&oid=".$queue_oid,$queue_id);
				}
			} // end of local file check fail
		} else {
			print $this->skin->show_alert('danger','fas fa-exclamation-triangle','ID найден в очереди но ссылка на файл отсутствует.');
		}
		
	} // SaA end
	
	/*
		Save as Double Attach
		IN:
		(string) queue_id - ID of element
		(string|bool) queue_oid - owner_ID of element; `false` if not used
		(string) type - short code of element type
		(string) auto - flag for auto queue; set `1` if ON
	*/
	function save_as_double_attach($queue_id,$queue_oid,$type,$auto){
		// Default values
		$db_type = '';
		$table = '';
		$path = '';
		// Allowed types
		$types = array(
			'doc' => array('atdc','matdc','mwatdc','catdc')
		);
		// Flag for work
		$type_allowed = false;
		
		// Check type and set basic values
		foreach($types as $k => $t){
			$found_type = '';
			foreach($t as $tt){
				if($type === $tt){ $type_allowed = true; $found_type = $k; }
			}
			if($found_type == 'doc'){
				$db_type = 'doc';
				$countdown = $this->cfg['sync_docs_next_cd'];
				if($type == 'atdc'){
					$table = 'attach'; $path = $this->cfg['docs_path'].'attach'; }
				if($type == 'matdc'){
					$table = 'messages_attach'; $path = $this->cfg['docs_path'].'messages'; }
				if($type == 'mwatdc'){
					$table = 'messages_wall_attach'; $path = $this->cfg['docs_path'].'messages_wall'; }
				if($type == 'catdc'){
					$table = 'wall_comments_attach'; $path = $this->cfg['docs_path'].'wall_comments'; }
			}
		}
		
		if($type_allowed !== true || $queue_id < 1 || !isset($queue_oid) || $db_type == '' || $table == '' || $path == ''){
			return false;
		}
		
		// Get attach info
		$q = $this->db->query_row("SELECT * FROM vk_{$table} WHERE `type` = '{$db_type}' AND `attach_id` = {$queue_id} AND `owner_id` = {$queue_oid}");
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
				$saved = $c->file_save(array('path'=>$path.'/'.$f.'/','name'=>$q['attach_id'].'.'.$q['caption']),$out['content']);
				if($saved){
					print $this->skin->show_alert('success','far fa-file','Файл сохранен');
					
					$prev_q = '';
					if($q['uri'] != ''){
						$out_pre = $c->curl_req(array(
							'uri' => $q['uri'],
							'method'=>'',
							'return'=>1
						));
						if($out_pre['err'] == 0 && $out_pre['errmsg'] == '' && $out_pre['content'] != '' && substr($out_pre['content'],0,5) != '<html' && substr($out_pre['content'],0,9) != '<!DOCTYPE'){
							preg_match("/[^\.]+$/",$q['uri'],$np);
							$saved_pre = $c->file_save(array('path'=>$path.'/preview/','name'=>$q['attach_id'].'.'.$np['0']),$out_pre['content']);
							if($saved){
								print $this->skin->show_alert('success','far fa-file','Превью сохранено');
								
								$prev_q = ", `path` = '".$path."/preview/".$q['attach_id'].".".$np[0]."'";
							}
						}
					}
					
					$q = $this->db->query("UPDATE vk_{$table} SET `player` = '".$path.'/'.$f."/".$q['attach_id'].".".$q['caption']."'".$prev_q." WHERE `type` = '{$db_type}' AND `attach_id` = ".$queue_id." AND `owner_id` = ".$queue_oid."");
					
					if($auto == '1'){
						$nrow = $this->db->query_row("SELECT attach_id, owner_id FROM vk_{$table} WHERE `player` = '' AND `type` = '{$db_type}' AND `is_local` = 0");
						if($nrow['attach_id'] > 0){
							print $this->skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$countdown."</span> сек.","queue.php?t={$type}&id=".$nrow['attach_id']."&oid=".$nrow['owner_id']."&auto={$auto}",$countdown);
						}
					}
					
				} else {
					print $this->skin->show_alert('danger','fas fa-exclamation-triangle','Ошибка при сохранении файла');
				}
			} else {
				// If error, let's try to see wtf is going on
				$error_code = false;
				if($this->func->is_html_response($out['content'])){
					$error_code = $this->skin->remote_server_error($out = $c->curl_req(array('uri' => $q['link_url'], 'method'=>'', 'return'=>0 )));
				}
				// Something wrong with response or connection
				$this->skin->queue_no_data($error_code,"t=".$type."&id=".$queue_id."&oid=".$queue_oid,$queue_id);
			}
			
		} else {
			print $this->skin->show_alert('danger','fas fa-exclamation-triangle','ID найден в очереди но ссылка на файл отсутствует.');
		}
		
	} // SaDA end

}

?>