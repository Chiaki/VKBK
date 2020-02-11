<?php

class attach {
	
	function __construct(){
		return true;
	}
	
	// Type - Photo
	/* IN:
	  av - array with attach data
	  attach_data -
	  v -
	   OUT: (string) $output
	*/
	function dlg_attach_photo($av,$attach_data,$v){
		$output = '';
		if((isset($av['type']) && $av['type'] == 'photo')){
			if($av['is_local'] == 0 && $av['path'] != ''){
				// Rewrite for Alias
				if($this->cfg['vhost_alias'] == true && substr($av['path'],0,4) != 'http'){
					$av['path'] = $this->func->windows_path_alias($av['path'],'photo');
				}
$output .= <<<E
    <div class="brick" style='max-width:350px;'><a class="fancybox" data-fancybox="images" rel="p{$v['msg_id']}" href="{$av['path']}"><img style="width:100%" src="{$av['path']}"></a></div>
E;
			}
			if($av['is_local'] == 1 && isset($attach_data[$v['msg_id']][$av['attach_id']])){
				// Rewrite for Alias
				if($this->cfg['vhost_alias'] == true && substr($attach_data[$v['msg_id']][$av['attach_id']]['path'],0,4) != 'http'){
					$attach_data[$v['msg_id']][$av['attach_id']]['path'] = $this->func->windows_path_alias($attach_data[$v['msg_id']][$av['attach_id']]['path'],'photo');
				}
$output .= <<<E
    <div class="brick" style='max-width:350px;'><a class="fancybox" data-fancybox="images" rel="p{$v['msg_id']}" href="{$attach_data[$v['msg_id']][$av['attach_id']]['path']}"><img style="width:100%" src="{$attach_data[$v['msg_id']][$av['attach_id']]['path']}"></a></div>
E;
			}
		}
		return $output;
	} // end of attach photo
	
	// Type = Link
	/* IN:
	  av - array with attach data
	   OUT: (string) $output
	*/
	function dlg_attach_link($av){
		$output = '';
		if(isset($av['type']) && $av['type'] == 'link'){
			// Rewrite for Alias
			if($this->cfg['vhost_alias'] == true && substr($av['path'],0,4) != 'http'){
				$av['path'] = $this->func->windows_path_alias($av['path'],'photo');
			}

			if($av['text'] != ''){ $av['text'] = nl2br($av['text']); }
			if($av['path'] != ''){
$output .= <<<E
<div>
	<div class="wall-link-img"><a rel="p{$av['attach_id']}" href="#"><img style="width:100%" src="{$av['path']}"></a><a href="{$av['link_url']}" class="wall-link-caption" rel="nofollow noreferrer" target="_blank"><i class="fa fa-link"></i>&nbsp;{$av['caption']}</a></div>
	<div class="col-sm-12" style="border:1px solid rgba(0,20,51,.12);">
		<h6 style="max-width:400px;">{$av['title']}</h6>
		<p class="wall-description">{$av['text']}</p>
	</div>
</div>
E;
			} else {
$output .= <<<E
<div class="col-sm-12">
	<h5><a href="{$av['link_url']}" rel="nofollow noreferrer" target="_blank"><i class="fas fa-share"></i> {$av['title']}</a></h5>
	<p class="wall-description">{$av['text']}</p>
</div>
E;
			}
		}
		return $output;
	} // end of attach link
	
	// Type - Document
	function dlg_attach_doc($av){
		$output = '';
		if((isset($av['type']) && $av['type'] == 'doc')){
			// Rewrite for Alias
			if($this->cfg['vhost_alias'] == true && substr($av['path'],0,4) != 'http'){
				$av['path'] = $this->func->windows_path_alias($av['path'],'docs');
			}
			// Attach
			if(isset($av['player'])){
				// Have preview
				if($av['path'] != ''){
					// Rewrite for Alias
					if($this->cfg['vhost_alias'] == true && substr($av['player'],0,4) != 'http'){
						$av['player'] = $this->func->windows_path_alias($av['player'],'docs');
					}
					$animated = '';
					if(strtolower(substr($av['player'],-3)) == "gif"){
						$animated = 'class="doc-gif" data-docsrc="'.$av['player'].'" data-docpre="'.$av['path'].'"';
					}
$output .= <<<E
    <div class="brick" style='width:100%;max-width:260px;'><a class="fancybox" data-fancybox="images" rel="p{$av['attach_id']}" href="{$av['player']}"><img {$animated} style="width:100%" src="{$av['path']}"></a></div>
E;
				} else {
					$av['duration'] = $this->func->human_filesize($av['duration']);
					$av['caption'] = strtoupper($av['caption']);
$output .= <<<E
<div class="col-sm-12">
	<h5><a href="{$av['player']}" rel="nofollow noreferrer" target="_blank"><i class="fas fa-share"></i> {$av['title']}</a></h5>
	<p class="wall-description"><span class="label label-default">{$av['caption']}</span> {$av['duration']}</p>
</div>
E;
				}
			}
		}
		return $output;
	} // end of attach document
	
	// Type = Video
	function dlg_attach_video($av){
		
		$output = '';
		if(isset($av['type']) && $av['type'] == 'video'){
			// Rewrite for Alias
			if($this->cfg['vhost_alias'] == true && substr($av['path'],0,4) != 'http'){
				$av['path'] = $this->func->windows_path_alias($av['path'],'video');
			}
			
			// Clean player
			$av['player'] = $this->func->clean_player($av['player']);
			if($av['text'] != ''){
				$av['text'] = '<div style="margin-bottom:10px;">'.nl2br($av['text']).'</div>';
			}
			$av['duration'] = $this->skin->seconds2human($av['duration']);
$output .= <<<E
	<div class="msg-video-box">
	    <span class="label label-default msg-video-duration px-2 py-1">{$av['duration']}</span>
	    <a class="various fancybox" href="javascript:;" onclick="javascript:fbox_video_global('{$av['player']}',1);" data-title-id="title-{$av['attach_id']}" style="background-image:url('{$av['path']}');"></a>
	</div>
	<h6 class="msg-video-header" style="max-width:400px;">{$av['title']}</h6>
	<div id="title-{$av['attach_id']}" style="display:none;">
	    {$av['text']}
	    <div class="expander" onClick="expand_desc();">показать</div>
	</div>
E;
		}
		return $output;
	} // end of attach video
	
	// Type - Sticker
	function dlg_attach_sticker($av){
		$output = '';
		if((isset($av['type']) && $av['type'] == 'sticker')){
$output .= <<<E
    <div><img style="width:128px;height:128px;" src="data/stickers/{$av['path']}"></div>
E;
		} // end of STICKER
		return $output;
	}
	
	// Type = Wall
	function dlg_attach_wall($av,$vk_session){
		
		$output = '';
		if(isset($av['type']) && $av['type'] == 'wall' && !empty($av['attach_id'])){
			
			$aw = $this->db->query("SELECT * FROM vk_messages_wall WHERE id = ".$av['attach_id']);
			while($row = $this->db->return_row($aw)){
				$repost_body = '';
				$rrp_body = '';
				
				// Post have a repost?
				if($row['repost'] > 0){
					$rp = $this->db->query_row("SELECT * FROM vk_messages_wall WHERE id = {$row['repost']} AND owner_id = {$row['repost_owner']}");
					// Post have a rerepost?
					if($rp['repost'] > 0){
						$rrp = $this->db->query_row("SELECT * FROM vk_messages_wall WHERE id = {$rp['repost']} AND owner_id = {$rp['repost_owner']}");
						$rrp_body = $this->func->dlg_wall_show_post($rrp,true,'',$vk_session);
					}
					$repost_body = $this->func->dlg_wall_show_post($rp,true,$rrp_body,$vk_session);
				} // repost body end
				// Make post
				$output .= $this->func->dlg_wall_show_post($row,'single',$repost_body,$vk_session);
			} // End of while
			
		}
		return $output;
	} // end of attach wall
	
	// Parse functions
	
	// Parse attachment [photo, video, doc, link, sticker, wall]
	// IN: (array) src | (array) atk | (array) vk_session | (array) opts | (bool) debug
	// OUT: --
	function attach_parse($src,$atk,$vk_session,$opts,$debug){

		// Forwarded attachments have negative ID
		if($opts['forwarded'] == true){ $id = -abs($src['id']); } else { $id = $src['id']; }
		$table = 'msg';
		if(isset($opts['table'])){ $table = $opts['table']; }
		
		// Attach - Photo
		if($atk['type'] == 'photo'){
			// Check do we have this attach already?
			$at = $this->db->query_row("SELECT id FROM vk_photos WHERE id = ".$atk['photo']['id']);
			// Attach found, make a link
			if(!empty($at['id']) && $atk['photo']['owner_id'] == $vk_session['vk_user']){
				// Insert OR update
				$this->func->msg_attach_update($id,$atk,$table,$debug);
			} else {
				
				$photo_urx = $this->func->get_largest_photo($atk['photo']);
				// Check do we have old or new type
				if(is_array($photo_urx)){
					$atk['photo']['width'] = $photo_urx['width'];
					$atk['photo']['height'] = $photo_urx['height'];
					$photo_uri = $photo_urx['url'];
				} else { $photo_uri = $photo_urx; }
				
				// Save information about attach
				$this->func->msg_attach_insert($id,$atk,$photo_uri,$table,$debug);
			}
		}
		
		// Attach - Video
		if($atk['type'] == 'video'){
			// Check do we have this attach already?
			$at = $this->db->query_row("SELECT id FROM vk_videos WHERE id = ".$atk['video']['id']);
			// Attach found, make a link
			if(!empty($at['id']) && $atk['video']['owner_id'] == $vk_session['vk_user']){
				// Insert OR update
				$this->func->msg_attach_update($id,$atk,$table,$debug);
			} else {
				$photo_uri = $this->func->get_largest_photo($atk['video']);
				$atk['video']['player'] = '';
				
				// Get video player code for external attach
				$v_api = $this->vk->api('video.get', array(
					'videos' => $atk['video']['owner_id'].'_'.$atk['video']['id'].($atk['video']['access_key'] != '' ? '_'.$atk['video']['access_key'] : ''),
					'extended' => 0, // возвращать ли информацию о настройках приватности видео для текущего пользователя
					'offset' => 0,
					'count' => 1
				));
				
				if(isset($v_api['response']['items'][0]['player']) && $v_api['response']['items'][0]['player'] != ''){
					$atk['video']['player'] = $v_api['response']['items'][0]['player'];
				}
				
				// Save information about attach
				$this->func->msg_attach_insert($id,$atk,$photo_uri,$table,$debug);
			}
		}
		
		// Attach - Link
		if($atk['type'] == 'link'){
			// For links we use a date as id because link type does not have id
			$atk['link']['id'] = $src['date'];
			$atk['link']['owner_id'] = 0;//$src['owner_id'];
			// Set owner for forwarded wall message
			if($table == 'wall'){
				$atk['link']['owner_id'] = $src['owner_id'];
			}
			// This is NOT needed for forwarded links, and WHY is needed for any other I need to remember >_<
			if($opts['forwarded'] != true){ $atk['link']['date'] = $src['date']; }
			$atk['link']['access_key'] = '';
			// Check do we have this attach already?
			$at = $this->db->query_row("SELECT attach_id FROM vk_messages_attach WHERE attach_id = ".$atk['link']['id']);
			// Attach found, just skip it ><
			if(!empty($at['attach_id'])){
				// Insert OR update
				$this->func->msg_attach_update($id,$atk,$table,$debug);
			} else {
				if(isset($atk['link']['photo'])){
					$atk['link']['width']  = (isset($atk['link']['photo']['width'])) ? $atk['link']['photo']['width'] : 0 ;
					$atk['link']['height'] = (isset($atk['link']['photo']['height'])) ? $atk['link']['photo']['height'] : 0;
					$photo_urx = $this->func->get_largest_photo($atk['link']['photo']);
					// Check do we have old or new type
					if(is_array($photo_urx)){
						$atk['link']['width'] = $photo_urx['width'];
						$atk['link']['height'] = $photo_urx['height'];
						$photo_uri = $photo_urx['url'];
					} else { $photo_uri = $photo_urx; }
				} else {
					$photo_uri = '';
				}
				
				// Save information about attach
				$this->func->msg_attach_insert($id,$atk,$photo_uri,$table,$debug);
			}
		}
		
		// Attach - Document
		/* DB fields comparison
		title -> title; size -> duration; ext -> text; url -> uri ( for local copy path ); date -> date; type -> not saved; preview ( photo -> link_url ( for local copy player ); width -> width; height -> height )
		*/
		if($atk['type'] == 'doc'){
			// Check do we have this attach already?
			$at = $this->db->query_row("SELECT id FROM vk_docs WHERE id = ".$atk['doc']['id']);
			$photo_uri = '';
			// Attach found, make a link
			if(!empty($at['id']) && $atk['doc']['owner_id'] == $vk_session['vk_user']){
				// Insert OR update
				$this->func->msg_attach_update($id,$atk,$table,$debug);
			} else {
				$atk['doc']['caption'] = $atk['doc']['ext'];
				$atk['doc']['width'] = 0;
				$atk['doc']['height'] = 0;
				$atk['doc']['duration'] = $atk['doc']['size'];
				$atk['doc']['text'] = $atk['doc']['ext'];
				
				if(isset($atk['doc']['preview'])){
					// Images
					if(isset($atk['doc']['preview']['photo'])){
						// Get biggest preview
						$sizes = $this->func->get_largest_doc_image($atk['doc']['preview']['photo']['sizes']);
						if($sizes['pre'] != ''){
							$photo_uri = $sizes['pre'];
							$atk['doc']['width'] = $sizes['prew'];
							$atk['doc']['height'] = $sizes['preh'];
						}
					}
				} // Preview end
				
				// Save information about attach
				$this->func->msg_attach_insert($id,$atk,$photo_uri,$table,$debug);
			}
		}
		
		// Attach - Sticker
		if($atk['type'] == 'sticker'){
			
			if(!isset($atk['sticker']['sticker_id'])){ $atk['sticker']['sticker_id'] = $atk['sticker']['id']; }
			// Check do we have this attach already?
			$at = $this->db->query_row("SELECT sticker FROM vk_stickers WHERE product = ".$atk['sticker']['product_id']." AND sticker = ".$atk['sticker']['sticker_id']);
			// Attach found, make a link
			if(!empty($at['sticker'])){
				// Insert OR update
				$this->func->msg_attach_update($id,$atk,$table,$debug);
			} else {
				//print_r($atk);
				$atk['sticker']['caption'] = '';
				$atk['sticker']['width'] = 0;
				$atk['sticker']['height'] = 0;
				$atk['sticker']['duration'] = 0;
				$atk['sticker']['text'] = '';
				$atk['sticker']['owner_id'] = 0;
				$atk['sticker']['date'] = $src['date'];
				$atk['sticker']['id'] = 0;
				
				$sticker = $this->func->get_sticker_image($atk['sticker'],true);
				
				if($sticker['pre'] != ''){
					$photo_uri = $sticker['pre'];
					$atk['sticker']['width'] = $sticker['prew'];
					$atk['sticker']['height'] = $sticker['preh'];
				}
				// Save information about attach
				$this->func->msg_attach_insert($id,$atk,$photo_uri,$table,$debug);
			}
		} // STICKER end
		
		// Attach - Wall
		if($atk['type'] == 'wall'){
			$atk['wall']['caption'] = '';
			$atk['wall']['owner_id'] = $atk['wall']['from_id'];
			$atk['wall']['width'] = 0;
			$atk['wall']['height'] = 0;
			$atk['wall']['duration'] = 0;
			$atk['wall']['title'] = '';
			$photo_uri = '';
			$this->func->msg_attach_insert($id,$atk,$photo_uri,$table,$debug);
			
			// Parse forwarded wall with possible reposts and attachments
			$attach = 0;
			$repost = 0;
			$repost_attach = 0;
			
			// Check wall attachments
			if(!empty($atk['wall']['attachments'])){
				$attach = 1;
				
				foreach($atk['wall']['attachments'] as $fw_atv => $fw_atk){
					
					// Attach :: Parse
					$this->attach_parse($atk['wall'],$fw_atk,$vk_session,array('table'=>'wall','forwarded'=>false),$debug);
					
				} // Forwarded foreach end
			} // Forwarded Wall Attachments end
			
			$origin = 0;
			$origin_owner = 0;
			// Forwarded Wall Repost parser
			if(!empty($atk['wall']['copy_history'])){
				$origin = $atk['wall']['copy_history'][0]['id'];
				$origin_owner = $atk['wall']['copy_history'][0]['owner_id'];
				foreach($atk['wall']['copy_history'] as $fw_chk => $fw_chv){
					$rp = $atk['wall']['copy_history'][$fw_chk];
					$repost = $rp['id'];
					
					// Check repost attachments
					if(!empty($rp['attachments'])){
						$repost_attach = 1;
						
						foreach($rp['attachments'] as $rp_atv => $rp_atk){
							
							// Attach :: Parse
							$this->attach_parse($rp,$rp_atk,$vk_session,array('table'=>'wall','forwarded'=>false),$debug);
							
						} // foreach end
					} // forwarded wall repost attachments end
					
					// If post have a repost inside, save it as another post
					if($repost > 0){
						// For multiple reposts let's check the next post id, if exists add it to current repost
						$ch_next = $fw_chk+1;
						
						if(isset($atk['wall']['copy_history'][$ch_next]['id']) && $atk['wall']['copy_history'][$ch_next]['id'] > 0){
							$rerepost = $atk['wall']['copy_history'][$ch_next]['id'];
							$rerepost_owner = ($fw_chk > 1) ? $atk['wall']['copy_history'][$ch_next-1]['owner_id'] : $atk['wall']['copy_history'][$ch_next]['owner_id'];
						} else {
							$rerepost = 0; $rerepost_owner = 0;
						}
						$this->func->wall_post_insert('msg',$rp,$repost_attach,$rerepost,$rerepost_owner,1,$debug);
					}
					
				} // Foreach end
			} // Forwarded Wall Reposts end
			
			// Insert OR update post
			$this->func->wall_post_insert('msg',$atk['wall'],$attach,$origin,$origin_owner,0,$debug);
			
			
			
		} // WALL end

	}
}

?>