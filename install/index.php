<?php

header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once('../cfg.php');

echo <<<E
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Установка - VKBK</title>
    <base href="./" />
    <link href="../favicon.png" rel="shortcut icon">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/fontawesome-all.min.css" rel="stylesheet">
    <style type="text/css">
	.welcome { margin: 20vh auto 5vh; padding: 2rem; max-width: 31.25rem; text-align: center; color: #fff; background-color: #597da3; border-radius: 0.25rem; box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, .05) }
	.welcome a { color: #fff }
	.git { margin: 0vh auto; text-align: center }
	.git a { color: #597da3 }
	.text-white a { color: rgba(255, 255, 255, .85); }
	.bg-header { background-color: #597da3; }
	.bg-header small { color: rgba(255, 255, 255, .85); }
	.border-bottom { border-bottom: 1px solid #e5e5e5; }
	.box-shadow { box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .05); }
	.lh-100 { line-height: 1; }
	.lh-125 { line-height: 1.25; }
	.lh-150 { line-height: 1.5; }
    </style>
  </head>
  <body>
E;

if(!empty($cfg['user']) && !empty($cfg['vk_id']) && file_exists(ROOT.'composer.lock')){
print <<<E
<div class="welcome"><h1>VKBK {$cfg['version']}</h1><p>Установка завершена<br/><a href="../">На главную</a></p></div>
E;
    exit;
} else {

print <<<E
<div class="container">
    <div class="d-flex align-items-center p-3 my-3 text-white1 bg-header rounded box-shadow">
	<div class="lh-100">
          <h6 class="mb-0 text-white lh-100">VKBK Установка</h6>
          <small>Версия {$cfg['version']}</small>
        </div>
    </div>
    <div class="my-3 p-3 bg-white rounded box-shadow">
E;

    /* 
	Шаг 0
    */
    if(!isset($_POST['step'])){
	// Текстовая аглушка
	if($cfg['vk_id'] == 0){ $cfg['vk_id'] = ''; }
	
print <<<E
        <h6 class="border-bottom border-gray pb-2 mb-0">Шаг 1 - Конфигурация</h6>
        <div class="text-muted pt-3">
          <div class="pb-0 mb-0 small lh-125">
	    <form method="post" action="./index.php">
		<input type="hidden" name="step" value="1" />
		<span class="d-block">
		    <h6>URL</h6>
		    <p>Адрес по которому расположен скрипт</p>
		</span>
		<div class="row">
		    <div class="col form-group">
			<label for="vkbk_url">URL</label>
			<input type="text" class="form-control" id="vkbk_url" name="vkbk_url" value="{$cfg['vkbk_url']}" placeholder="http://your.host/">
		    </div>
		</div>
		<hr/>
		<span class="d-block">
		    <h6>База данных</h6>
		    <p>Укажите данные для подключения</p>
		</span>
		<div class="row">
		    <div class="col form-group">
			<label for="user">Имя пользователя</label>
			<input type="text" class="form-control" id="user" name="user" value="{$cfg['user']}" placeholder="user">
		    </div>
		    <div class="col form-group">
			<label for="pass">Пароль</label>
			<input type="password" class="form-control" id="pass" name="pass" value="{$cfg['pass']}" placeholder="password">
		    </div>
		    <div class="col form-group">
			<label for="base">Имя базы</label>
			<input type="text" class="form-control" id="base" name="base" value="{$cfg['base']}" placeholder="database">
		    </div>
		</div>
		<hr/>
		<span class="d-block">
		    <h6>Данные Вашего standalone приложения</h6>
		    <p>Укажите необходимые данные для подключения к API ВК</p>
		</span>
		<div class="row">
		    <div class="col form-group">
			<label for="vk_id">ID приложения</label>
			<input type="text" class="form-control" id="vk_id" name="vk_id" value="{$cfg['vk_id']}" placeholder="1234567">
		    </div>
		    <div class="col form-group">
			<label for="vk_secret">Защищенный ключ</label>
			<input type="text" class="form-control" id="vk_secret" name="vk_secret" value="{$cfg['vk_secret']}" placeholder="Защищенный ключ">
		    </div>
		    <div class="col form-group">
			<label for="vk_uri">Open API URL</label>
			<input type="text" class="form-control" id="vk_uri" name="vk_uri" value="{$cfg['vk_uri']}" placeholder="http://your.host/">
		    </div>
		</div>
		<hr/>
		<span class="d-block">
		    <h6>Данные для "прямого" доступа (опционально)</h6>
		    <p>Вход через логин используется для синхронизации <b>диалогов</b>, <b>музыки</b> и для скачивания <b>видеозаписей</b> которые могут требовать авторизацию для просмотра. Все остальные функции работают через standalone приложение.</p>
		    <div class="alert alert-danger">
			<h5>ВНИМАНИЕ!</h5>
			<h6>Указывая свои логин и пароль вы можете потерять свой аккаунт в случае уязвимости в скрипте или на Вашем сервере! Если у Вас есть сомнения, лучше оставьте поля пустыми.</h6>
		    </div>
		</span>
		<div class="row">
		    <div class="col form-group">
			<label for="yt_dl_login">Логин ВК</label>
			<input type="text" class="form-control" id="yt_dl_login" name="yt_dl_login" value="{$cfg['yt_dl_login']}" placeholder="yourmail@mail.com">
		    </div>
		    <div class="col form-group">
			<label for="yt_dl_passw">Пароль ВК</label>
			<input type="password" class="form-control" id="yt_dl_passw" name="yt_dl_passw" value="{$cfg['yt_dl_passw']}" placeholder="пароль">
		    </div>
		</div>
		<button type="submit" class="btn btn-primary">Далее</button>
	    </form>
          </div>
        </div>
E;

    } // Step end
    
    /*
	Шаг 1
	Получение данных и запись в конфиг
    */
    if(isset($_POST['step']) && $_POST['step'] == 1){
	// Данные БД
	if(!isset($_POST['user']) || empty($_POST['user'])){
	    echo '<p>Имя пользователя не задано</p><p><a href="./">назад</a>'; exit; }
	if(!isset($_POST['pass']) || empty($_POST['pass'])){
	    echo '<p>Пароль не задан</p><p><a href="./">назад</a>'; exit; }
	if(!isset($_POST['base']) || empty($_POST['base'])){
	    echo '<p>Имя базы не задано</p><p><a href="./">назад</a>'; exit; }
	
	// URL скрипта
	if(!isset($_POST['vkbk_url']) || empty($_POST['vkbk_url'])){
	    echo '<p>URL не задан</p><p><a href="./">назад</a>'; exit; }
	
	// Данные приложения
	if(!isset($_POST['vk_id']) || empty($_POST['vk_id'])){
	    echo '<p>ID приложения не задано</p><p><a href="./">назад</a>'; exit; }
	if(!isset($_POST['vk_secret']) || empty($_POST['vk_secret'])){
	    echo '<p>Не задан защищенный ключ</p><p><a href="./">назад</a>'; exit; }
	if(!isset($_POST['vk_uri']) || empty($_POST['vk_uri'])){
	    echo '<p>Не задан Open API URL</p><p><a href="./">назад</a>'; exit; }
	
	// Тест соединения с БД
	require_once(ROOT.'classes/db.php');
	$db = new db();
	$res = $db->connect($cfg['host'],$_POST['user'],$_POST['pass'],$_POST['base']);
	
	if($res->connect_error == NULL){
print <<<E
        <h6 class="border-bottom border-gray pb-2 mb-0">Шаг 2 - Создание таблиц в БД</h6>
        <div class="text-muted pt-3">
          <div class="pb-0 mb-0 small lh-125">
          <span class="d-block">
		<h6>Сохранение данных подключения</h6>
E;
	    // Сохранение данных в cfg.php
	    $config = ROOT.'cfg.php';
	    
	    if(!is_writable($config)){
print <<<E
		<div class="alert alert-danger">Файл <b>/cfg.php</b> недоступен для записи!</div>
	    </span>
E;
	    } else {
		// Читаем конфиг
		$data = file_get_contents($config);
		
		// Меняем строки на строки с данными
		$data = str_replace("\$cfg['vkbk_url'] = '';","\$cfg['vkbk_url'] = '".$_POST['vkbk_url']."';",$data);
		$data = str_replace("\$cfg['user'] = '';","\$cfg['user'] = '".$_POST['user']."';",$data);
		$data = str_replace("\$cfg['pass'] = '';","\$cfg['pass'] = '".$_POST['pass']."';",$data);
		$data = str_replace("\$cfg['base'] = '';","\$cfg['base'] = '".$_POST['base']."';",$data);
		
		$data = str_replace("\$cfg['vk_id'] = 0;","\$cfg['vk_id'] = ".$_POST['vk_id'].";",$data);
		$data = str_replace("\$cfg['vk_secret'] = '';","\$cfg['vk_secret'] = '".$_POST['vk_secret']."';",$data);
		$data = str_replace("\$cfg['vk_uri'] = '';","\$cfg['vk_uri'] = '".$_POST['vk_uri']."';",$data);
		
		if(isset($_POST['yt_dl_login']) && !empty($_POST['yt_dl_login']) && isset($_POST['yt_dl_passw']) && !empty($_POST['yt_dl_passw'])){
		    $data = str_replace("\$cfg['yt_dl_login'] = '';","\$cfg['yt_dl_login'] = '".$_POST['yt_dl_login']."';",$data);
		    $data = str_replace("\$cfg['yt_dl_passw'] = '';","\$cfg['yt_dl_passw'] = '".$_POST['yt_dl_passw']."';",$data);
		}
		
		// Сохраняем в конфиг
		if(file_put_contents($config,$data) !== FALSE){
print <<<E
		<div class="alert alert-success">Данные успешно записаны в файл <b>/cfg.php</b></div>
	    </span>
            <span class="d-block">
		<h6>Подключение к БД - успешно</h6>
		<p>Необходимо создать таблицы в БД</p>
	    </span>
	    <form method="post" action="./index.php">
		<input type="hidden" name="step" value="2" />
		<button type="submit" class="btn btn-primary">Далее</button>
	    </form>
          </div>
        </div>
E;
		} else {
print <<<E
		<div class="alert alert-danger">Не удалось записать данные в файл <b>/cfg.php</b></div>
	    </span>
E;
		}
	    }
	}
	$db->close($res);
    } // Step end
    
    /*
	Шаг 2
	Загружаем дамп в БД
    */
    if(isset($_POST['step']) && $_POST['step'] == 2){
    
print <<<E
        <h6 class="border-bottom border-gray pb-2 mb-0">Шаг 3 - Создание таблиц</h6>
        <div class="text-muted pt-3">
          <div class="pb-0 mb-0 small lh-125">
            <span class="d-block">
		<h6>Создание таблиц</h6>
E;
	
	if(!empty($cfg['user']) && !empty($cfg['pass']) && !empty($cfg['base'])){
	    // SQL дамп
	    $sql = ROOT.'vkbk.sql';
	    
	    if(!file_exists($sql)){
print <<<E
		<div class="alert alert-danger">Файл структуры <b>/vkbk.sql</b> не найден</div>
	    </span>
E;
	    } else {
		// Коннектимся к БД
		require_once(ROOT.'classes/db.php');
		$db = new db();
		$res = $db->connect($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['base']);
		
		// Читаем дамп, формируем запрос и выполняем
		$dump = file($sql);
		if($dump){
		    $query = '';
		    foreach($dump as $line){
			if($line && (substr($line, 0, 2) != '--') && (substr($line, 0, 1) != '#')){
			    $query .= $line;
			    if(preg_match('/;\s*$/', $line)){
				$db->query($query);
				$query = '';
			    }
			}
		    }
		}
		
		$db->close($res);
		
print <<<E
		<div class="alert alert-success">Все необходисые таблицы созданы</div>
	    </span>
	    <form method="post" action="./index.php">
		<input type="hidden" name="step" value="3" />
		<button type="submit" class="btn btn-primary">Далее</button>
	    </form>
          </div>
        </div>
E;
	    }
	}
    } // Step end
    
    /*
	Шаг 3
	
    */
    if(isset($_POST['step']) && $_POST['step'] == 3){
print <<<E
        <h6 class="border-bottom border-gray pb-2 mb-0">Шаг 4 - Composer</h6>
        <div class="text-muted pt-3">
          <div class="pb-0 mb-0 small lh-125">
            <span class="d-block">
		<h6>Composer</h6>
		<p>Для корректной работы требуется установить зависимости.</p>
		<p>В терминале или командной строке перейдите в каталог скрипта и выполните команду: <b>composer install</b>. <i class="fa fa-fw fa-question-circle"></i> <a href="https://getcomposer.org/doc/00-intro.md" target="_blank" rel="noopener">Как установить composer?</a></p>
		<p>После выполения команды появится файл <b>composer.lock</b>. Нажмите "Завершть".</p>
	    </span>
	    <form method="post" action="./index.php">
		<input type="hidden" name="step" value="4" />
		<center><button type="submit" class="btn btn-success">Завершить</button></center>
	    </form>
          </div>
        </div>
E;
    } // Step end
    
    print '</div><!-- box end -->';

} // Check end

echo <<<E
    <script type="text/javascript" src="../js/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" src="../js/bootstrap.min.js"></script>
  </body>
</html>
E;

?>