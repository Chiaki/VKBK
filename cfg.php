<?php

// Defines
define('ROOT',dirname(__FILE__).'/');
define('SYSTEM_ALBUM', '-9000');
error_reporting(E_ALL & ~E_DEPRECATED);

// Time Configuration
date_default_timezone_set("Europe/Minsk");

// Necessary modules check
$needed_modules = array('curl','mbstring','mysqli','json');
foreach($needed_modules as $nm){
	if(!extension_loaded($nm)){ die('<b>Необходим модуль:</b> '.$nm); }
}

// VKBK Configuration
class VKBK {
	
    /**
     * VKBK twig config
     * @var array
     * template	- @var string >> template dir
     * config	- @var array >> twig configuration
     * cache	- @var bool
     */
	public $twi = array( 'template' => '', 'config' => array(), 'cache' => false );
	
	function __construct(){
		$this->twi = array(
			'template'	=> ROOT."template/",
			'config'	=> array('autoescape' => false),
			'cache'		=> false
		);
	}

}

$cfg = array();
require_once('version.php');

$cfg['pj'] = false;

/* Script URL
  Example: http://your.host/
*/
$cfg['vkbk_url'] = '';

/* MySQL Connection
  Import vkbk.sql in created DB
*/
$cfg['host'] = 'localhost';
$cfg['user'] = '';
$cfg['pass'] = '';
$cfg['base'] = '';

/* VK.API
  Script settings
*/
/* App ID
  ID приложения
*/
$cfg['vk_id'] = ;
/* Protected key
  Защищенный ключ
*/
$cfg['vk_secret'] = '';
/* Open API URL
  Example: http://your.host/
*/
$cfg['vk_uri'] = '';

/* Path for download

  For windows users: if you want to store files on different drives
  you should add an Alias for your Vhost configuration, enable option
  `vhost_alias` and edit function `windows_path_alias` in classes/func.php

  Vhost alias example:
  Alias "/vkbk-photo" "C:/VKBK/photo"
  Alias "/vkbk-music" "D:/VKBK/music"
  Alias "/vkbk-video" "E:/VKBK/video"
  Alias "/vkbk-docs"  "F:/VKBK/docs"
*/
// Enable this if you use Alias for windows
$cfg['vhost_alias'] = false;
/* Photos dir
  Example: C:/VKBK/photo/
*/
$cfg['photo_path'] = '';
/* Music dir
  Example: D:/VKBK/music/
*/
$cfg['music_path'] = '';
/* Videos dir
  Example: E:/VKBK/video/
*/
$cfg['video_path'] = '';
/* Documents dir
  Example: F:/VKBK/docs/
*/
$cfg['docs_path']  = '';


/* Albums
  Settings
  photo_layout_width - max width for photos in preview
  perpage_photo - how much photos load per page
*/
$cfg['photo_layout_width']   = 300;
$cfg['perpage_photo']        = 24;

/* Videos
  Settings
  perpage_video - how much videos load per page
*/
$cfg['perpage_video']        = 24;

/* Documents
  Settings
  perpage_docs - how much documents load per page
*/
$cfg['perpage_docs']         = 24;

/* Wall
  Settings
  wall_layout_width - max width for photos in preview
  perpage_wall - how much posts load per page
*/
$cfg['wall_layout_width']    = 200;
$cfg['perpage_wall'] = 20;

$cfg['perpage_dlg_messages'] = 250;

/* Sync
  Settings
  sync_*_start_cd - sec. before start sync
  sync_*_error_cd - sec. if error occurred before reload page
  sync_*_next_cd - sec. before next item would load
  sync_*_auto_cd - sec. before load queue after sync
  sync_found_local - sec. before load next item if current already exists
*/
$cfg['sync_photo_start_cd'] = 5;
$cfg['sync_photo_error_cd'] = 5;
$cfg['sync_photo_next_cd']  = 3;
$cfg['sync_photo_auto_cd']  = 10;

$cfg['sync_music_start_cd'] = 5;
$cfg['sync_music_error_cd'] = 5;
$cfg['sync_music_next_cd']  = 3;
$cfg['sync_music_auto_cd']  = 10;

$cfg['sync_docs_start_cd']  = 5;
$cfg['sync_docs_error_cd']  = 5;
$cfg['sync_docs_next_cd']   = 3;
$cfg['sync_docs_auto_cd']   = 10;

$cfg['sync_video_start_cd'] = 5;
$cfg['sync_video_next_cd']  = 3;

$cfg['sync_wall_next_cd']   = 10;

$cfg['sync_dialog_next_cd'] = 3;

$cfg['sync_found_local']    = 1;

/* Addon: youtube-dl
   Require: ffmpeg
  This path you should edit if you have a youtube-dl
  installed and you want use it for making local video backup
  Example: C:\Users\%USER%\AppData\Local\Programs\Python\Python35\
*/
$cfg['yt_dl_path'] = "";
$cfg['ffmpeg'] = "";
/* Addon: youtube-dl
   VK.com Authorization Details
   WARNING! YOUTUBE-DL AUTHORIZATION WORKS ONLY WITH LOGIN AND PASSWORD
   USE IT AT YOUR OWN RISK AND ONLY ON TRUSTED COMPUTER
   yt_dl_login - your VK.com email
   yt_dl_passw - your VK.com password
*/
$cfg['yt_dl_login'] = "";
$cfg['yt_dl_passw'] = "";
?>