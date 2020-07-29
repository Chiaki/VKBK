<?php

class skin {
	
	private $twig;
	private $twig_data = array();
	
	function __construct(){
		return true;
	}
	
	function twig_init($config){
	    require ROOT . '/vendor/autoload.php';
	    $loader = new \Twig\Loader\FilesystemLoader($config->twi['template']);
	    // initialize Twig environment
	    $twig_cfg = array();
	    $twig_cfg['config'] = $config->twi['config'];
	    $this->twig = new \Twig\Environment($loader, $twig_cfg);
	}
	
	function twig_set($key, $val){
	    $this->twig_data[$key] = $val;
	}
	
	function twig_render($template){
	    try {
		return $this->twig->render($template.'.twig', $this->twig_data);
	    } catch (Exception $e) {
		trigger_error('Error: Could not load template ' .$template. '!');
		exit();	
	    }
	}
	
	/*
		Function: header
		Return header for page
		IN:
		s - extended HTML
	*/
	function header($s){
	    global $cfg;
	    $db_check = $this->check_db_ver() ? '' : '<div class="m-2 badge badge-danger db-expired"><i class="fas fa-exclamation-triangle"></i> Структура базы данных устарела. Обратитесь к <a href="update/index.html" target="_blank">инструкции</a> по обновлению перед синхронизацией. <i class="fas fa-exclamation-triangle"></i></div>';
		return <<<E
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>VKBK</title>
    <base href="./" />
    <link href="favicon.png" rel="shortcut icon">
    {$this->header_links()}
    {$s['extend']}
  </head>
  <body>
    {$db_check}
E;
	}
	
	/*
		Function: header_ajax
		Return header for AJAX page version
	*/
	function header_ajax(){
	    return <<<E
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>VKBK</title>
    <base href="../" />
    <link href="favicon.png" rel="shortcut icon">
    {$this->header_links()}
  </head>
  <body style="padding-top:0;">
E;
	}
	
	/*
		Function: header_links
		Return CSS includes list
	*/
	function header_links(){
	    return <<<E
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/jquery.fancybox3.min.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/perfect-scrollbar.css?v=1.3.0" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/bootstrap-select.min.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/bootstrap2-toggle.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="jplayer/skin/vkbk/css/jplayer.vkbk.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/justifiedGallery.min.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/plyr.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="css/datepicker.min.css" type="text/css" media="screen" />
    <!-- Custom styles for this template -->
    <link href="css/custom.css" rel="stylesheet">
    <link href="css/fontawesome-all.min.css" rel="stylesheet">
E;
	}
	
	/*
		Function: footer
		Footer for page
		IN:
		s - extended HTML
	*/
	function footer($s){
	    global $cfg;
		return <<<E
    </div> <!-- pj-content end -->
    <a class="gvplayer" data-fancybox="iframe" data-type="iframe" href="javascript:;"></a>
    <footer class="footer">
      <div class="container pt-2">
	<div class="row">
        <div class="col-8"><p class="text-muted mb-2"><a href="https://github.com/Chiaki/VKBK" target="_blank"><i class="fab fa-vk"></i>BK {$cfg['version']}</a> <small>&copy; 2016 - 2020 Megumin</small></p></div>
		<div class="col-4" style="text-align:right;"><a href="about.php" data-pjax><small><i class="fa fa-code-branch fa-fw"></i> История версий</a></small></div>
	</div>
      </div>
    </footer>
    {$this->footer_links()}
    {$s['extend']}
  </body>
</html>
E;
	}
	
	/*
		Function: footer_ajax
		Footer for AJAX page version
	*/
	function footer_ajax(){
	    return <<<E
    {$this->footer_links()}
  </body>
</html>
E;
	}
	
	/*
		Function: footer_links
		Return JS includes list
	*/
	function footer_links(){
	    return <<<E
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script type="text/javascript" src="js/popper.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/jquery.pjax.js"></script>
    <script type="text/javascript" src="js/freewall.js"></script>
    <script type="text/javascript" src="js/jquery.jscroll.js"></script>
    <script type="text/javascript" src="js/jquery.fancybox3.min.js"></script>
    <script type="text/javascript" src="js/perfect-scrollbar.min.js?v=1.3.0"></script>
    <script type="text/javascript" src="js/bootstrap-select.min.js"></script>
    <script type="text/javascript" src="js/bootstrap2-toggle.min.js"></script>
    <script type="text/javascript" src="js/jquery.visible.min.js"></script>
    <script type="text/javascript" src="js/jquery.debounce.min.js"></script>
    <script type="text/javascript" src="jplayer/jquery.jplayer.min.js"></script>
    <script type="text/javascript" src="jplayer/jplayer.playlist.js"></script>
    <script type="text/javascript" src="js/plyr.min.js"></script>
    <script type="text/javascript" src="js/hashnav.js"></script>
    <script type="text/javascript" src="js/js.cookie.min.js"></script>
    <script type="text/javascript" src="js/jquery.justifiedGallery.min.js"></script>
	<script type="text/javascript" src="js/datepicker.min.js"></script>
    <script type="text/javascript" src="js/vkbk.js"></script>
E;
	}
	
	/*
		Function: navigation
		Builds a nav menu for header
		IN:
		s - array of counters
	*/
	function navigation($s){
		return <<<E
    <nav class="navbar navbar-expand-md navbar-inverse fixed-top">

      <a class="navbar-brand" href="index.php"><i class="fab fa-vk"></i>BK</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"><i class="fa fa-fw fa-bars"></i></span>
      </button>

      <div class="collapse navbar-collapse" id="navbar">
        <ul class="navbar-nav ml-auto">
			{$this->navigation_item('index.php'	 ,'Главная'			,'fa fa-home'				,'')}
			{$this->navigation_item('albums.php' ,'Альбомы'			,'fa fa-camera-retro'		,$s['album'])}
			{$this->navigation_item('music.php'	 ,'Музыка'			,'fa fa-music'				,$s['music'])}
			{$this->navigation_item('videos.php' ,'Видео'			,'fa fa-film'				,$s['video'])}
			{$this->navigation_item('wall.php'	 ,'Стена'		,'far fa-newspaper'			,$s['wall'])}
			{$this->navigation_item('docs.php'	 ,'Документы'		,'far fa-file'				,$s['docs'])}
			{$this->navigation_item('dialogs.php','Диалоги'			,'far fa-comments'		,$s['dialogs'])}
			{$this->navigation_item('queue.php'	 ,'Очередь закачки'	,'fa fa-cloud-download-alt'	,'')}
			{$this->navigation_item('sync.php'	 ,'Синхронизация'	,'fa fa-sync'	,'')}
			
			<li class="nav-item tip" data-placement="bottom" data-toggle="tooltip" data-original-title="Настройки">
				<a class="nav-link" data-morphing id="morphing" data-src="ajax/settings.php" href="javascript:;"><i class="fa fa-sliders-h"></i><span class="xs-show">Настройки</span></a></li>
        </ul>
      </div>
    </nav>
    <div id="pj-content">
E;
	}
	
	/*
		Function navigation_item
		Returns LI element for navigation
	*/
	function navigation_item($link,$title,$icon,$counter){
		if($counter != ''){ $counter = ' - '.$counter; }
		return <<<E
	<li class="nav-item tip" data-placement="bottom" data-toggle="tooltip" data-original-title="{$title}{$counter}">
		<a class="nav-link" href="{$link}" data-pjax><i class="{$icon}"></i><span class="xs-show">{$title}</span></a></li>
E;
	}
	
	/*
	    Function: albums_header
	    Returns album name if specified or default
	    In:
	    header - album name
	*/
	function albums_header($header){
	    return '<span id="cur-alb" class="nav-link ">'.(!empty($header) ? '<i class="far fa-folder-open"></i> '.$header : '<i class="fa fa-image"></i> Последние фотографии').'</span>';
	}
	
	/*
	    Function: queue_progress_bar
	    Returns a progress bar for specific type of data
	    In:
	    bar - progress bar data (array)
	*/
	function queue_progress_bar($bar){
	    if(isset($bar['per'])  && $bar['per']  < 0){ $bar['per']  = 0; }
	    if(isset($bar['perx']) && $bar['perx'] < 0){ $bar['perx'] = 0; }
return <<<E
<div class="mx-auto text-center">
<i class="fa fa-{$bar['fa']} text-{$bar['bar']}" style="font-size:2em;"></i><br/>
<small>{$bar['name']}</small><br/>{$bar['perx']}%
</div>
E;
	}
	
	/*
	    Function: queue_list_attach
	    Returns a list of attach items to download list
	    In:
	    row - data
	    first - auto button switch (bool)
	*/
	function queue_list_attach($row,$first){
	    global $skin;
	    
	    if(isset($row['date'])){ $row['fdate'] = date("Y-m-d H:i:s",$row['date']); } else { $row['fdate'] = ' -//- '; }
	    $uri_name = $row['uri'];
	    
	    $t = '';
	    $fancy = false;
		$fa_extra = '';
		
		// Allow fancy preview for some types
		$fancy_types = array('photo','video','link','groups','profiles','m-photo','m-video','m-link','m-doc','m-sticker','mw-photo','mw-video','mw-link','mw-doc','mw-sticker');
		if(in_array($row['type'],$fancy_types)){ $fancy = true; }
		
		// Icon for AttachTypes
		$attach_ico = 'paperclip';
		$attach_msg = array('m-photo','m-video','m-link','m-doc','m-sticker');
		$attach_wall = array('mw-photo','mw-video','mw-link','mw-doc','mw-sticker');
		if(in_array($row['type'],$attach_msg)){ $attach_ico = 'paperclip'; }
		if(in_array($row['type'],$attach_wall)){ $attach_ico = 'share'; }
		
	    if($row['type'] == 'photo'){    $t = 'atph';$row['id'] = $row['attach_id']; }
	    if($row['type'] == 'video'){    $t = 'atvi';$row['id'] = $row['attach_id']; }
	    if($row['type'] == 'link'){     $t = 'atli';$row['id'] = $row['attach_id']; }
	    if($row['type'] == 'audio'){
		$t = 'atau';$row['id'] = $row['attach_id'];
		mb_internal_encoding("UTF-8");
		if(mb_strlen($row['title'])   > 50){ $row['title']   = mb_substr($row['title'],0,50).'...'; }
		if(mb_strlen($row['caption']) > 50){ $row['caption'] = mb_substr($row['caption'],0,50).'...'; }
		$duration = $skin->seconds2human($row['duration']);
		$uri_name = "[{$duration}] {$row['caption']} - {$row['title']}";
	    }
	    if($row['type'] == 'groups'){
			$t = 'gr'; $row['uri'] = $row['photo_uri'];$fa_extra = 'users'; }
	    if($row['type'] == 'profiles'){
			$t = 'pr'; $row['uri'] = $row['photo_uri'];$fa_extra = 'user'; }
	    if($row['type'] == 'doc'){
			$t = 'atdc';$row['id'] = $row['attach_id']; }
	    
	    if($row['type'] == 'm-photo'){
			$t = 'matph';$row['id'] = $row['attach_id'];$fa_extra = 'image'; }
	    if($row['type'] == 'm-video'){
			$t = 'matvi';$row['id'] = $row['attach_id'];$fa_extra = 'film'; }
	    if($row['type'] == 'm-link'){
			$t = 'matli';$row['id'] = $row['attach_id'];$row['owner_id'] = $row['date'];$fa_extra = 'link'; }
	    if($row['type'] == 'm-doc'){
			$t = 'matdc';$row['id'] = $row['attach_id'];$fa_extra = 'file'; }
	    if($row['type'] == 'm-sticker'){
			$t = 'matst';$row['id'] = $row['date']; }
		
		if($row['type'] == 'mw-photo'){
			$t = 'mwatph';$row['id'] = $row['attach_id'];$fa_extra = 'image'; }
	    if($row['type'] == 'mw-video'){
			$t = 'mwatvi';$row['id'] = $row['attach_id'];$fa_extra = 'film'; }
	    if($row['type'] == 'mw-link'){
			$t = 'mwatli';$row['id'] = $row['attach_id'];$row['owner_id'] = $row['date'];$fa_extra = 'link'; }
	    if($row['type'] == 'mw-doc'){
			$t = 'mwatdc';$row['id'] = $row['attach_id'];$fa_extra = 'file'; }
	    if($row['type'] == 'mw-sticker'){
			$t = 'mwatst';$row['id'] = $row['date']; }
	    
	    // Fancybox preview for some types
	    if($fancy == true){
			$fbox = 'class="fancybox" data-fancybox="images"';
	    } else {
			$fbox = 'target="_blank"';
	    }
		
		if($fa_extra != ''){ $fa_extra = ' <i class="fa fa-'.$fa_extra.'"></i>'; }
	    
	    // Add a autodownload for the first element in list
	    if($first == true){
		$auto = "&nbsp;&nbsp;<a href=\"queue.php?t={$t}&id={$row['id']}".(isset($row['owner_id']) ? '&oid='.$row['owner_id'] : '' )."&auto=1\" class=\"btn btn-sm btn-outline-primary\" onClick=\"jQuery('#{$row['id']}').hide();return true;\" title=\"Скачать автоматически\"><b class=\"fa fa-sync fa-fw\"></b></a>";
	    } else { $auto = ''; }
	    $oid = isset($row['owner_id']) ? "&oid=".$row['owner_id'] : '';
return <<<E
<tr id="{$row['id']}">
  <td class="text-center"><i class="fa fa-{$attach_ico}"></i>{$fa_extra}</td>
  <td class="align-middle">{$row['id']}</td>
  <td class="align-middle"><a href="{$row['uri']}" {$fbox}>{$uri_name}</a></td>
  <td class="align-middle">{$row['fdate']}</td>
  <td class="align-middle"><a href="queue.php?t={$t}&id={$row['id']}{$oid}" class="btn btn-sm btn-outline-primary" id="{$row['id']}" onClick="jQuery('#{$row['id']}').hide();return true;" title="Скачать"><b class="fas fa-download fa-fw"></b></a>{$auto}</td>
</tr>
E;
	}
	
	/*
	    Function: Reload
	    Add's an element with countdown. After countdown redirect to specified url.
	    In:
	    class   - name of bootstrap class (string)
	    msg     - message to display      (string)
	    uri     - URL to redirect         (string)
	    timeout - time in ms              (int)
	*/
	function reload($class,$msg,$uri,$timeout){
	    if($timeout <= 0){ $timeout = 10000; } else { $timeout = $timeout * 1000; } // Default 10 sec
	    if($class=='info'){    $c = 'class="alert alert-info" role="alert"';    }
	    if($class=='warning'){ $c = 'class="alert alert-warning" role="alert"'; }
return <<<E
<tr>
  <td>
    <div {$c}><i class="fa fa-sync fa-spin"></i> {$msg}</div>
	<script type="text/javascript">
	var count = {$timeout}/1000;
	var counter = setInterval(timer, 1000);
	function timer() {
	    count=count-1;
	    if(count <= 0) {
		clearInterval(counter);
		//return;
		window.location = "{$uri}";
	    }
	    document.getElementById("gcd").innerHTML = count;
	}
	</script>
  </td>
</tr>
E;
	}
	
	/*
	    Function: details_row
	    Returns a row for video details
	    In:
	    left - data
	    right - data
	*/
	function details_row($left,$right){
return <<<E
<div class="row">
    <div class="col-sm-8">{$left}</div>
    <div class="col-sm-4">{$right}</div>
</div>
E;
	}
	
	/*
	    Function: remote_server_error
	    In:
	    response - cURL response array
	*/
	function remote_server_error($response){
	    if(isset($response['header'])){
		return "<br/>Ответ сервера: {$response['header']['http_code']}";
	    } else {
		return false;
	    }
	}
	
	/*
	    Function: queue_no_data
	    In:
	    error - remote_server_error() output
	    exclurl - url params to exclusion function
	*/
	function queue_no_data($error,$exclurl = false,$exclid = false){
		$exclude = '';
	    if($exclurl != false){
		$exclude = <<<E
<div class="float-right"><input type="button" class="btn btn-outline-danger" onclick="skipit('{$exclurl}',{$exclid});" value="Исключить из очереди" /></div>
E;
	    }
print <<<E
<div class="alert alert-danger hosterror" role="alert">{$exclude}<div><i class="fas fa-exclamation-triangle"></i> Невозможно получить данные с удаленного хоста.{$error}</div></div>
E;
	}
	
	function reload_manual($class,$msg,$uri,$timeout){
	    if($class=='info'){ $c = 'class="alert alert-info" role="alert"'; }
	    if($class=='warning'){ $c = 'class="alert alert-warning" role="alert"'; }
return <<<E
<tr>
  <td>
    <div {$c}>{$msg}<br/><a href="{$uri}">Далее...</a></div>
  </td>
</tr>
E;
	}
	
	/*
		Function: Show Alert
		IN:
		(string) type - bootstrap color style of alert
		(string) icon - FontAwesome icon name
		(string) msg - message of alert
	*/
	function show_alert($type,$icon,$msg){
		$ic = '';
		if($icon != ''){ $ic = '<i class="'.$icon.'"></i> '; }
		return <<<E
<div class="alert alert-{$type}" role="alert">{$ic}{$msg}</div>
E;
	}
	
	/*
		Function: seconds2human
		Parse seconds and return H:M:S
	*/
	function seconds2human($ss) {
	    $s = $ss%60;
	    $s = str_pad($s, 2, '0', STR_PAD_LEFT);
	    $m = floor(($ss%3600)/60);
	    $m = str_pad($m, 2, '0', STR_PAD_LEFT);
	    $h = floor(($ss%86400)/3600);
	    //$h = str_pad($h, 2, '0', STR_PAD_LEFT); // Double zeroes for hours? No, tnx
	    if($h > 0){
		return "$h:$m:$s";
	    } else {
		return "$m:$s";
	    }
	}
	
	/*
		Function: check_db_ver
		Checks DB version and return true or false
	*/
	function check_db_ver(){
	    global $db, $cfg;
	    $row = $db->query_row("SELECT val as version FROM vk_status WHERE `key` = 'version'");
	    return ($cfg['version_db'] != $row['version']) ? false : true;
	}
}

?>