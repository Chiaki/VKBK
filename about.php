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

// Get Skin
require_once(ROOT.'classes/skin.php');
$skin = new skin();

// Get local counters for top menu
$lc = $db->query_row("SELECT * FROM vk_counters");

if(!$cfg['pj']){
	print $skin->header(array('extend'=>''));
	print $skin->navigation($lc);
}

print <<<E
<div class="nav-scroller bg-white box-shadow mb-4" style="position:relative;">
    <nav class="nav nav-underline">
		<span class="nav-link active"><i class="fa fa-code-branch"></i> История версий</span>
    </nav>
</div>

<div class="container">
		  <div class="my-3 p-3 bg-white rounded box-shadow">
			<p><strong>VKBK</strong> - это инструмент для создания и синхронизации локального бэкапа вашего лампового профиля ВК.</p>
			<h6 class="border-bottom border-gray pb-2">Сторонние библиотеки:</h6>
			<div class="row">
			<div class="col-xs-6 col-sm-3 libs-row">
			<a href="http://getbootstrap.com/">Bootstrap</a>
				<span class="badge badge-light">4.0.0</span><br/>
			<a href="https://github.com/vladkens/VK">PHP класс для VK.API</a>
				<span class="badge badge-light">0.1.7</span><br/>
			<a href="https://github.com/kombai/freewall">Freewall.js</a>
				<span class="badge badge-light">1.05</span><br/>
			<a href="https://github.com/pklauzinski/jscroll">jScroll.js</a>
				<span class="badge badge-light">2.3.9a</span><br/>
			<a href="https://github.com/fancyapps/fancyBox">fancybox.js</a>
				<span class="badge badge-light">3.1.20</span>
			</div>
			<div class="col-xs-6 col-sm-3 libs-row">
			<a href="https://fortawesome.github.io/Font-Awesome/">Font Awesome</a>
				<span class="badge badge-light">5.0.8</span><br/>
			<a href="https://github.com/minhur/bootstrap-toggle">Bootstrap Toggle</a>
				<span class="badge badge-light">2.2.2</span><br/>
			<a href="https://habrahabr.ru/sandbox/57659/">hashnav.js</a>
				<span class="badge badge-light">6 May 2016</span><br/>
			<a href="https://github.com/noraesae/perfect-scrollbar">perfect-scrollbar.js</a>
				<span class="badge badge-light">1.3.0</span><br/>
			<a href="https://github.com/happyworm/jPlayer">jPlayer.js</a>
				<span class="badge badge-light">2.9.2</span>
			</div>
			<div class="col-xs-6 col-sm-3 libs-row">
			
			<a href="https://github.com/snapappointments/bootstrap-select">Bootstrap Select</a>
				<span class="badge badge-light">1.13.0</span><br/>
			<a href="https://github.com/defunkt/jquery-pjax">pjax</a>
				<span class="badge badge-light">2.0.1</span><br/>
			<a href="https://github.com/customd/jquery-visible/">jQuery Visible</a>
				<span class="badge badge-light">1.2.0</span><br/>
			<a href="http://benalman.com/projects/jquery-throttle-debounce-plugin/">Debounce plugin</a>
				<span class="badge badge-light">1.1</span><br/>
			<a href="https://github.com/js-cookie/js-cookie">js-cookie</a>
				<span class="badge badge-light">2.1.4</span>
			</div>
			<div class="col-xs-6 col-sm-3 libs-row">
			<a href="https://github.com/FezVrasta/popper.js">popper.js</a>
				<span class="badge badge-light">1.14.1</span><br/>
			<a href="https://github.com/miromannino/Justified-Gallery">Justified Gallery</a>
				<span class="badge badge-light">3.7.0</span><br/>
			</div>
			</div>
			
		  </div>
          <div class="col-sm-12 px-0 mb-5 pb-2">
E;

$changelog = array(
	'0.8.x' => array(
		'0.8.2' => array(
			array('n',"Добавлена возможность скрывать элементы в очереди закачки (пока для вложений)"),
			array('bf',"Исправлена ошибка при синхронизации видео, когда у видеозаписей совпадал ID"),
			array('bf',"Исправлены мелкие баги"),
		),
		'0.8.1' => array(
			array('bf',"Обновлено регулярное выражение для профилей и групп в связи с изменением url ВК."),
			array('bf',"Исправлены мелкие баги"),
		),
		'0.8.0' => array(
			array('n','Синхронизация личных сообщений (диалогов)'),
			array('u','perfect-scrollbar обновлен до версии 1.3.0'),
			array('u',"Исправлены мелкие недоработки в дизайне"),
			array('bf',"Исправлен баг с включением быстрой синхронизации для фотографий."),
			array('bf',"Исправлены мелкие баги"),
		),
	),
	'0.7.x' => array(
		'0.7.7' => array(
			array('u',"Исправлены мелкие недоработки в дизайне"),
			array('bf',"Исправлена ошибка при сохранении файлов в несуществующую директорию. Спасибо @phatal"),
		),
		'0.7.6' => array(
			array('i',"Для работы потребуется MySQL версии 5.5+ (с поддержкой utf8mb4)"),
			array('n',"Добавлен Justified Gallery"),
			array('n',"Добавлен popper.js"),
			array('u','jQuery обновлен до версии 3.3.1'),
			array('u','Bootstrap обновлен до версии 4.0.0'),
			array('u','pjax обновлен до версии 2.0.1'),
			array('u','jScroll обновлен до версии 2.3.9'),
			array('u','Bootstrap Select обновлен до версии 1.13.0-beta'),
		),
		'0.7.5' => array(
			array('u','Bootstrap-select обновлен до версии 1.12.4'),
			array('u','FontAwesome обновлен до версии 5.0.8'),
			array('i','Очередное обновление API Вконтакта.'),
		),
		'0.7.4' => array(
			array('bf','Исправлена ошибка в разделе Сообщения. Если в базе уже имеются данные, следуйте <a href="update/index.html" target="_blank">инструкции</a> на странице обновления.'),
		),
		'0.7.3' => array(
			array('bf',"Частично исправлен fancybox в разделе сообщений. Для некоторых типов сообщений могут остаться ошибки."),
			array('u',"Убраны абсолютные пути из радиректов."),
		),
		'0.7.2' => array(
			array('n',"Видео теперь можно минимизировать и при переходе в другой раздел оно не закроется."),
			array('n',"Добавлен быстрый поиск для видео."),
			array('n',"Настройки видео плеера теперь сохраняются в куки."),
			array('n',"Добавлен плагин js-cookie."),
			array('u',"jQuery обновлен до версии 1.12.4"),
			array('u',"Fancybox обновлен до версии 3.1.20"),
		),
		'0.7.1' => array(
			array('i',"При ошибках скачивания видео, обновите youtube-dl до версии от 2017.05.29 или выше."),
			array('u',"jScroll обновлен до версии 2.3.9"),
			array('u',"Bootstrap обновлен до версии 3.3.7"),
			array('i',"Запросы изменены на MySQLi."),
			array('bf',"Микрофикс ссылок аватаров групп и пользователей в очереди закачки."),
			array('b',"Проблема с отображением записи &laquo;пользователь обновил фото&raquo;."),
		),
		'0.7.0' => array(
			array('n',"Добавлено автоматическое проигрывание gif'ов на стене."),
			array('n',"Добавлена синхронизация документов на стене."),
			array('n',"Добавлена библиотека pjax."),
			array('u',"Bootstrap Select обновлен до версии 1.12.1."),
			array('bf',"Исправлен баг автоматической закачки для аттачей."),
			array('bf',"Сообщение со стены не сохранялось если начиналось с эмодзи."),
		),
	),
	'0.6.2' => array(
		'2017-03-04' => array(
			array('u',"Небольшие изменения в дизайне."),
			array('bf',"На стене могли неверно отображаться фото, видео, музыка групп и пользователей если их id совпадал с id материалами владельца."),
		),
	),
	'0.6.1' => array(
		'2017-01-24' => array(
			array('u',"Добавлена быстрая синхронизация для стены."),
		),
		'2017-01-23' => array(
			array('u',"Добавлены фильтры по дате и длительности для видео."),
			array('u',"Добавлена опция для автовоспроизведения локального видео."),
			array('u',"Переработан дизайн локального видео плеера."),
			array('b',"Полноэкранный режим не поддерживается в IE10 при проигрывании видео."),
		),
		'2017-01-15' => array(
			array('n',"Добавлена возможность скачивать видео из ВКонтакте с авторизацией через youtube-dl."),
		),
	),
	'0.6' => array(
		'2016-12-30' => array(
			array('n',"Добавлена синхронизация документов."),
			array('n',"Добавлена проверка версии структуры базы данных и инструкция по обновлению."),
		),
	),
	'0.5.6' => array(
		'2016-12-28' => array(
			array('d',"Добавлена проверка на отключенный API Вконтакте для музыки при синхронизации стены."),
		),
		'2016-12-19' => array(
			array('d',"API Вконтакте для музыки был отключен. Надеюсь, что через некоторое время он вновь будет доступен для всех."),
		),
	),
	'0.5.5' => array(
		'2016-11-21' => array(
			array('bf',"Поправлены пути для установки скрипта в суб-директорию. Спасибо Ивану за багрепорт."),
		),
		'2016-10-30' => array(
			array('n',"Добавлена быстрая синхронизация (только системные альбомы)."),
		),
		'2016-06-16' => array(
			array('u',"В плейлисте изменена иконка для активного трека."),
		),
		'2016-06-15' => array(
			array('bf',"Исправлен баг с упорядочиванием фото на последней странице."),
			array('bf','Исправлен баг с отображением кнопки "показать все альбомы" при ресайзе окна.'),
		),
		'2016-06-05' => array(
			array('u','Изменен урл загрузки роиков для ВКонтакте, чтобы можно было сохранять "приватные" видео.'),
			array('bf',"Исправлен баг в поиске видео когда при прокрутке следующая страница загружалась без фильтра."),
		),
	),
	'0.5' => array(
		'2016-06-01' => array(
			array('n',"Добавлена возможность поиска видео по типу, сервису и качеству."),
		),
		'2016-05-29' => array(
			array('u',"Добавлена настройка для проигрывания локального видео по умолчанию."),
			array('n',"Добавлен плеер для локальных видео."),
			array('bf',"Исправлено сохранение локального пути для видеофайла."),
		),
		'2016-05-28' => array(
			array('u',"Раздел видео обновлен."),
			array('n','Добавлена поддержка создания локальной копии видеофайлов с сервисов YouTube и VK.com при помощи <a href="https://github.com/rg3/youtube-dl" target="_blank"><i class="fa fa-link"></i> youtube-dl</a> (необходимо установить отдально!)'),
		),
		'2016-05-24' => array(
			array('u',"Улучшена сортировка плейлиста по исполнителю и названию трека."),
		),
		'2016-05-19' => array(
			array('n',"Добавлены красивые списки (Bootstrap Select)"),
			array('n',"Плеер audio.js заменен на более продвинутый jPlayer.js в связи с этим добавились новые опции а так же изменился дизайн"),
			array('u',"В аудиозаписях добавлено отображение удаленных треков"),
			array('n',"Добавлена синхронизация альбомов для аудиозаписей."),
		),
	),
	'0.4.5' => array(
		'2016-05-12' => array(
			array('n',"Добавлен perfect-scrollbar.js."),
			array('n',"Добавлен вывод альбомов (в которых есть хотя бы одно изображение) с превьюшками."),
			array('u',"В альбомах фото выводятся в порядке убывания."),
		),
		'2016-05-07' => array(
			array('u',"Для синхронизации аттачей добавлена проверка на наличие файла локально."),
			array('bf',"Исправлено сохранение ссылки для репоста."),
			array('bf',"Исправлено сохранение аудиозаписи для репоста."),
		),
	),
	'0.4' => array(
		'2016-05-06' => array(
			array('n',"Добавлена возможность смотреть отдельные посты со стены в лайтбоксе."),
			array('n',"Хэш навигация (hashnav.js)."),
			array('bf',"Исправлен баг с зависанием загрузки стены в хроме."),
		),
		'2016-05-05' => array(
			array('bf',"Добавлена дополнительная проверка на контент для закачек."),
			array('u',"Добавлен код ошибки если закачка не удалась."),
			array('u',"Добавлен интерактивный отсчет для таймеров."),
			array('bf',"Исправлено отображение чекбокса в настройках для IE 11."),
			array('n',"Синхронизация сообщений (стена): аудиозаписи."),
			array('n',"Синхронизация сообщений (стена): репосты (в том числе вложенные)."),
			array('n',"Синхронизация сообщений (стена): ссылки."),
		),
		'2016-05-04' => array(
			array('u',"Множественные изменения в коде."),
			array('u',"Опция для виртуальных хостов вынесена отдельно. Теперь для её включения необходимо отредактировать конфиг и одну функцию вместо множества файлов."),
			array('n',"Синхронизация сообщений (стена): добавлен парсинг видео аттачей, а так же обработка репостов (фото, видео)."),
		),
		'2016-04-30' => array(
			array('u',"Общий стиль изменен под новый дизайн ВК."),
			array('bf',"Не работал оптимизатор изображений в альбомах на FireFox."),
		),
		'2016-04-29' => array(
			array('n',"Добавлен раздел «Сообщения»."),
			array('n',"Синхронизация сообщений (стена). Альфа-версия."),
		),
		'2016-04-28' => array(
			array('n',"Добавлены настройки (Авто-редиректы)"),
			array('n',"Красивые чекбоксы (Bootstrap Toggle)"),
		),
	),
	'0.3' => array(
		'2016-04-24' => array(
			array('u',"Добавлена полоса прогресса при синхронизации фотографий."),
		),
		'2016-04-21' => array(
			array('u',"Обновление названий альбомов при синхронизации."),
		),
		'2016-03-27' => array(
			array('bf',"Исправлен баг при синхронизации если альбом пустой."),
		),
		'2016-03-21' => array(
			array('u',"Код немного приведен в порядок для работы на PHP 5.3.x."),
			array('bf',"Неправильные флаги при повторной синхронизации музыки."),
		),
		'2016-03-19' => array(
			array('u',"Настройки таймаутов перенесены в конфиг."),
			array('u',"Обновление количества фото в альбомах при синхронизации."),
			array('u',"Не отображалась кнопка автоматического скачивания."),
		),
		'2016-03-15' => array(
			array('n',"Просмотр видеозаписей"),
			array('u',"При ошибке синхронизации в автоматическом режиме и html ответе сервера (прим. ошибка Bad Gateway) добавлен пропуск данных ID."),
			array('n',"Добавлена синхронизация видеозаписей"),
			array('bf',"Исправлено отображение смешенного контента в очереди закачек."),
		),
	),
	'0.2' => array(
		'2016-03-14' => array(
			array('n',"Добавлен плеер в раздел музыки (audio.js)"),
			array('bf',"Добавлена проверка на содержимое ответа сервера."),
			array('bf',"Некоторые кириллические названия треков не сохранялись"),
		),
		'2016-03-13' => array(
			array('n',"Добавлены иконки (FontAwesome)"),
			array('i',"Имена аудиозаписей чистятся от не ASCII символов, так как PHP для Windows более чем точно будет скомпилирован без поддержки юникода."),
			array('b',"Непонятно почему ВК не отдает некоторые аудиозаписи через API, хотя они не являются заблокированными."),
			array('u',"Обновлен блок и страница очереди закачки. Объединены фотографии и аудиозаписи."),
			array('n',"Синхронизация аудиозаписей"),
		),
		'2016-03-12' => array(
			array('u',"Раздел Альбомы по-умолчанию отображают 25 последних добавленных фотографий"),
			array('bf',"Добавлена проверка на размер файла при сохранении"),
		),
	),
	'0.1' => array(
		'2016-03-09' => array(
			array('n',"Просмотр фотографий (Fancyfox)"),
			array('b',"Необходима проверка на размер файла при закачке"),
			array('u',"Ссылку на очередь отображается если есть хотя бы один элемент"),
			array('n',"Подгрузка фотографий при скроллинге (jscroll.js)"),
			array('n',"Responsive макет для фотографий (freewall.js)"),
			array('n',"Отображение фотографий в альбомах"),
		),
		'2016-03-08' => array(
			array('n',"Получение и сохранение фото"),
			array('n',"Очередь закачки"),
			array('u',"Блок очереди закачки в панели управления"),
		),
		'2016-03-07' => array(
			array('fx',"Синхронизация фотографий"),
			array('bf',"Исправлены баги в синхронизации альбомов"),
		),
		'2016-03-06' => array(
			array('fx',"Синхронизация альбомов"),
			array('fx',"Авторизация"),
			array('n',"Интерфейс, структура"),
			array('i',"Начало разработки"),
		),
	),
);

$r = 0;
foreach($changelog as $k => $v){
	print '<h4 '.($r > 0 ? 'data-toggle="collapse" data-target="#collapse'.$r.'" aria-expanded="false" aria-controls="collapse'.$r.'" style="cursor:pointer;">' : '>').'версия '.$k.'</h4>';
	print '<div class="wall-box collapse'.($r==0 ? "show" : "").'" id="collapse'.$r.'">';
	foreach($v as $d => $c){
		print '<ul class="list-group list-unstyled"><div>'.$d.'</div>';
		foreach($c as $t => $m){
			if($m[0] == 'u'){   $l = 'warning">обновлено'; }
			if($m[0] == 'b'){   $l = 'danger">баг'; }
			if($m[0] == 'bf'){  $l = 'danger">багфикс'; }
			if($m[0] == 'd'){   $l = 'danger">отключено'; }
			if($m[0] == 'n'){   $l = 'primary">новое'; }
			if($m[0] == 'fx'){  $l = 'success">функционал'; }
			if($m[0] == 'i'){   $l = 'info">инфо'; }
			print '<li><label><span class="badge badge-'.$l.'</span></label><p>'.$m[1].'</p></li>';
		}
		print '</ul>';
	}
	$r++;
	print '</div>';
}

print <<<E
          </div>
</div>
E;

if(!$cfg['pj']){
	print $skin->footer(array('extend'=>''));
}

$db->close($res);

?>