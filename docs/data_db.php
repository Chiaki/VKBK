<?php

$db_prefix = 'vk_';
$db_attr = array(
  'i11'  => 'int(11)',
  'i10'  => 'int(10)',
  'mi8'  => 'mediumint(8)',
  'si6'  => 'smallint(6)',
  'si5'  => 'smallint(5)',
  'ti4'  => 'tinyint(4)',
  'v255' => 'varchar(255)',
  'v50'  => 'varchar(50)',
  'v40'  => 'varchar(40)',
  'v25'  => 'varchar(25)',
  'tx'   => 'text',
  'b'    => 'bool'
);

/*
	'table_name' => array(
		'description' = '',
		'columns' => array (
			'field_name' => array(
				'type' => '',
				'desc' => ''
			)
		}
	)
*/
$db = array(
'albums' => array(
	'desc' => 'Таблица с данными о фото альбомах пользователя',
	'cols' => array(
		'id' => array(
			'type' => 'i10',
			'desc' => 'ID альбома'
		),
		'name' => array(
			'type' => 'v255',
			'desc' => 'Название альбома'
		),
		'created' => array(
			'type' => 'i10',
			'desc' => 'Дата создания альбома (UNIX time)'
		),
		'updated' => array(
			'type' => 'i10',
			'desc' => 'Дата последнего обновления (UNIX time)'
		),
		'img_total' => array(
			'type' => 'i10',
			'desc' => 'Количество фотографий из VK.API'
		),
		'img_done' => array(
			'type' => 'i10',
			'desc' => 'Количество сохраненных фото'
		)
	)
),


'attach' => array(
	'desc' => 'Таблица с данными о вложениях со стены пользователя',
	'cols' => array(
		'uid' => array(
			'type' => 'i11',
			'desc' => 'Уникальный идентификатор'
		),
		'wall_id' => array(
			'type' => 'i11',
			'desc' => 'ID сообщения со стены пользователя к которому относится вложение (связь `wall_id` -> `'.$db_prefix.'wall.id` )'
		),
		'type' => array(
			'type' => 'v255',
			'desc' => 'Тип вложения. Может принимать значения: [ photo | audio | video | link ]'
		),
		'is_local' => array(
			'type' => 'b',
			'desc' => 'Флаг: элемент сохранен локально'
		),
		'attach_id' => array(
			'type' => 'i11',
			'desc' => 'ID вложения'
		),
		'owner_id' => array(
			'type' => 'i11',
			'desc' => 'ID владельца'
		),
	 'uri' => array(
			'type' => 'tx',
			'desc' => 'Ссылка на вложение (УРЛ)'
		),
		'path' => array(
			'type' => 'v255',
			'desc' => 'Полный путь к локальному файлу вложения (если сохранено)'
		),
		'width' => array(
			'type' => 'si5',
			'desc' => 'Ширина фото вложения'
		),
		'height' => array(
			'type' => 'si5',
			'desc' => 'Высота фото вложения'
		),
		'text' => array(
			'type' => 'tx',
			'desc' => 'Описание если тип [ video | link | photo ]'
		),
		'date' => array(
			'type' => 'i11',
			'desc' => ''
		),
		'access_key' => array(
			'type' => 'v255',
			'desc' => 'Значение поля `access key` для доступа к не публичному содержанию'
		),
		'title' => array(
			'type' => 'tx',
			'desc' => 'Заголовок если тип [ video | link ], Название трека если тип [ audio ]'
		),
		'duration' => array(
			'type' => 'i11',
			'desc' => 'Продолжительность (сек.) если тип [ video | audio ]'
		),
		'player' => array(
			'type' => 'tx',
			'desc' => 'Ссылка на встраиваемый плеер если тип [ video ]'
		),
		'link_url' => array(
			'type' => 'tx',
			'desc' => 'Ссылка на страницу если тип [ link ]; Ссылка на аудиофайл если тип [ audio ] (устарело)'
		),
		'caption' => array(
			'type' => 'v255',
			'desc' => 'Домен если тип [ link ]; Испонитель если тип [ audio ] (устарело)'
		),
		'skipthis' => array(
			'type' => 'b',
			'desc' => 'Флаг: пропуск элемента в очереди закачки (например если элемент возвращает ошибку 404)'
		)
	)
),


'counters' => array(
	'desc' => 'Таблица с локальными значениями счетчиков для сравнения с ВК ',
	'cols' => array(
		'album' => array(
			'type' => 'mi8',
			'desc' => 'Количество фото альбомов пользователя'
		),
		'photo' => array(
			'type' => 'mi8',
			'desc' => 'Количество фото пользователя'
		),
		'music' => array(
			'type' => 'mi8',
			'desc' => 'Количество треков пользователя'
		),
		'video' => array(
			'type' => 'mi8',
			'desc' => 'Количество видеозаписей пользователя'
		),
		'wall' => array(
			'type' => 'mi8',
			'desc' => 'Количество постов на стене пользователя'
		),
		'docs' => array(
			'type' => 'mi8',
			'desc' => 'Количество документов пользователя'
		),
		'dialogs' => array(
			'type' => 'mi8',
			'desc' => 'Количество диалогов (бесед) пользователя'
		)
	)
),


'dialogs' => array(
	'desc' => 'Теблица с диалогами пользователя',
	'cols' => array(
		'id' => array(
			'type' => 'i11',
			'desc' => 'ID собеседника'
		),
		'date' => array(
			'type' => 'i11',
			'desc' => 'Дата последнего сообщения в беседе'
		),
		'title' => array(
			'type' => 'v255',
			'desc' => 'Название беседы (для групповых диалогов)'
		),
		'in_read' => array(
			'type' => 'i11',
			'desc' => 'ID последнего сообщения в беседе'
		),
		'multichat' => array(
			'type' => 'b',
			'desc' => 'Флаг: диалог является групповым'
		),
		'chat_id' => array(
			'type' => 'i11',
			'desc' => 'ID группового диалога'
		),
		'admin_id' => array(
			'type' => 'i11',
			'desc' => 'ID последнего активного пользователя диалога'
		),
		'users' => array(
			'type' => 'i11',
			'desc' => 'Количество пользователей в групповом диалоге'
	),
		'is_new' => array(
			'type' => 'b',
			'desc' => 'Флаг: диалог является новым (при синхронизации будут загружены все сообщения)'
		),
		'is_upd' => array(
			'type' => 'b',
			'desc' => 'Флаг: диалог имеет новые сообщения (при синхронизации будут загружены только новые сообщения)'
		)
	)
),


'docs' => array(
	'desc' => 'Таблица с данными о документах пользователя',
	'cols' => array(
		'id' => array(
			'type' => 'i11',
			'desc' => 'ID документа'
		),
		'owner_id' => array(
			'type' => 'i11',
			'desc' => 'ID владельца'
		),
		'title' => array(
			'type' => 'tx',
			'desc' => 'Название документа (файла)'
		),
		'size' => array(
			'type' => 'i11',
			'desc' => 'Размер файла (в байтах)'
		),
		'ext' => array(
			'type' => 'v25',
			'desc' => 'Расширение файла'
		),
		'uri' => array(
			'type' => 'tx',
			'desc' => 'Ссылка на документ (ВК)'
		),
		'date' => array(
			'type' => 'i11',
			'desc' => 'Дата создания документа'
		),
		'type' => array(
			'type' => 'si6',
			'desc' => 'Тип документа [  ]'
		),
		'preview_uri' => array(
			'type' => 'tx',
			'desc' => 'Ссылка на превью для документа (ВК). Например, миниатюра изображения.'
		),
		'preview_path' => array(
			'type' => 'tx',
			'desc' => 'Путь к локальному файлу превью'
		),
		'width' => array(
			'type' => 'si5',
			'desc' => 'Ширина (для фото)'
		),
		'height' => array(
			'type' => 'si5',
			'desc' => 'Высота (для фото)'
		),
		'deleted' => array(
			'type' => 'b',
			'desc' => 'Флаг: элемент был удалён в ВК'
		),
		'in_queue' => array(
			'type' => 'b',
			'desc' => 'Флаг: элемент находится в очереди закачки'
		),
		'local_path' => array(
			'type' => 'tx',
			'desc' => 'Путь к локальному файлу'
		),
		'local_size' => array(
			'type' => 'i11',
			'desc' => 'Размер локального файла (не вычисляется)'
		),
		'local_w' => array(
			'type' => 'si6',
			'desc' => 'Ширина локального файла (не вычисляется)'
		),
		'local_h' => array(
			'type' => 'si6',
			'desc' => 'Высота локального файла (не вычисляется'
		),
		'skipthis' => array(
			'type' => 'b',
			'desc' => 'Флаг: пропуск элемента в очереди закачки (например если элемент возвращает ошибку 404)'
		)
	)
),


'groups' => array(
	'desc' => 'Таблица с информацией о группах (авторы)',
	'cols' => array(
		'id' => array(
			'type' => 'i11',
			'desc' => 'ID группы'
		),
		'name' => array(
			'type' => 'v255',
			'desc' => 'Название группы'
		),
		'nick' => array(
			'type' => 'v255',
			'desc' => 'Ник группы (УРЛ в вк)'
		),
		'photo_uri' => array(
			'type' => 'tx',
			'desc' => 'Ссылка на аватарку группы (ВК)'
		),
		'photo_path' => array(
			'type' => 'v255',
			'desc' => 'Имя локального файла аватарки в директории [ /data/groups/ ]'
		)
	)
),


'messages' => array(
	'desc' => 'Таблица содержит сообщения бесед',
	'cols' => array(
		'uid' => array(
			'type' => 'i11',
			'desc' => 'Уникальный ID'
		),
		'msg_id' => array(
			'type' => 'i11',
			'desc' => 'ID сообщения (для пересылаемых сообшений имеет негативное ID сообщения)'
		),
		'msg_chat' => array(
			'type' => 'i11',
			'desc' => 'ID группового чата которому принадлежит сообщение'
		),
		'msg_dialog' => array(
			'type' => 'i11',
			'desc' => 'ID диалога которому принадлежит сообщение'
		),
		'msg_user' => array(
			'type' => 'i11',
			'desc' => 'ID пользователя который оставил сообщение'
		),
		'msg_date' => array(
			'type' => 'i11',
			'desc' => 'Дата сообщения'
		),
		'msg_body' => array(
			'type' => 'tx',
			'desc' => 'Тело сообщения'
		),
		'msg_attach' => array(
			'type' => 'b',
			'desc' => 'Флаг: Сообщение содержит вложение (связь `msg_id` -> `'.$db_prefix.'messages_attach.wall_id` )'
		),
		'msg_forwarded' => array(
			'type' => 'b',
			'desc' => 'Флаг: Сообщение содержит пересылаемое(ые) сообщения'
		)
	)
),


'messages_attach' => array(
	'desc' => 'Таблица содержит вложения из сообщений в беседах',
	'cols' => array(
		'uid' => array(
			'type' => 'i11',
			'desc' => 'Автоинкремент ID'
		),
		'wall_id' => array(
			'type' => 'i11',
			'desc' => ''
		),
		'type' => array(
			'type' => 'v255',
			'desc' => 'Тип вложения. Может принимать значения: [ doc | photo | sticker | video | link | wall ]'
		),
		'is_local' => array(
			'type' => 'b',
			'desc' => 'Флаг: элемент найден локально'
		),
		'attach_id' => array(
			'type' => 'i11',
			'desc' => 'ID вложения. Для типа `wall` указывает на связь `attach_id` => '.$db_prefix.'messages_wall.id'
		),
		'owner_id' => array(
			'type' => 'i11',
			'desc' => 'ID владельца'
		),
		'uri' => array(
			'type' => 'tx',
			'desc' => 'Ссылка на вложение (УРЛ)'
		),
		'path' => array(
			'type' => 'v255',
			'desc' => 'Полный путь к локальному файлу вложения (если сохранено)'
		),
		'width' => array(
			'type' => 'si5',
			'desc' => 'Ширина изображения'
		),
		'height' => array(
			'type' => 'si5',
			'desc' => 'Высота изображения'
		),
		'text' => array(
			'type' => 'tx',
			'desc' => 'Описание если тип [ video | link | photo ]'
		),
		'date' => array(
			'type' => 'i11',
			'desc' => ''
		),
		'access_key' => array(
			'type' => 'v255',
			'desc' => 'Значение поля `access key` для доступа к не публичному содержанию'
		),
		'title' => array(
			'type' => 'tx',
			'desc' => 'Заголовок если тип [ video | link ], Название трека если тип [ audio ]'
		),
		'duration' => array(
			'type' => 'i11',
			'desc' => 'Тип `wall` - является флагом сохранена ли запись; Тип `doc` - размер документа в байтах; Тип `video` - продолжительность сек.'
		),
		'player' => array(
			'type' => 'tx',
			'desc' => 'Ссылка на встраиваемый плеер если тип [ video ]'
		),
		'link_url' => array(
			'type' => 'tx',
			'desc' => 'Ссылка на страницу если тип [ link ]; Ссылка на аудиофайл если тип [ audio ] (устарело)'
		),
		'caption' => array(
			'type' => 'v255',
			'desc' => 'Домен если тип [ link ]; Испонитель если тип [ audio ] (устарело)'
		),
		'skipthis' => array(
			'type' => 'b',
			'desc' => 'Флаг: пропуск элемента в очереди закачки (например если элемент возвращает ошибку 404)'
		)
	)
),


'messages_wall' => array(
	'desc' => 'Таблица содержит цитируемые сообщения со стены в беседах',
	'cols' => array(
		'id' => array(
			'type' => 'i11',
			'desc' => 'ID поста на стене'
		),
		'from_id' => array(
			'type' => 'i11',
			'desc' => 'ID отправителя'
		),
		'owner_id' => array(
			'type' => 'i11',
			'desc' => 'ID владельца'
		),
		'date' => array(
			'type' => 'i11',
			'desc' => 'Дата сообщения'
		),
		'post_type' => array(
			'type' => 'v255',
			'desc' => 'Тип сообщения'
		),
		'text' => array(
			'type' => 'tx',
			'desc' => 'Содержание сообщения'
		),
		'attach' => array(
			'type' => 'b',
			'desc' => 'Флаг: сообщение содержит вложение (связь `id` -> `'.$db_prefix.'messages_wall_attach.wall_id` )'
		),
		'repost' => array(
			'type' => 'i11',
			'desc' => 'Флаг: ID сообщения репоста (связь `repost` -> `id` )'
		),
		'repost_owner' => array(
			'type' => 'i11',
			'desc' => 'Флаг: ID владельца репост сообщения (связь `repost_owner` -> `owner_id` )'
		),
		'is_repost' => array(
			'type' => 'b',
			'desc' => 'Флаг: Сообщение содержит репост'
		)
	)
),


'messages_wall_attach' => array(
	'desc' => 'Таблица содержит вложения из цитируемых сообщений со стены в беседах',
	'cols' => array(
		'uid' => array(
			'type' => 'i11',
			'desc' => 'Автоинкремент ID'
		),
		'wall_id' => array(
			'type' => 'i11',
			'desc' => 'ID поста на стене'
		),
		'type' => array(
			'type' => 'v255',
			'desc' => 'Тип вложения. Может принимать значения: [ doc | photo | sticker | video | link | wall ]'
		),
		'is_local' => array(
			'type' => 'b',
			'desc' => 'Флаг: элемент найден локально'
		),
		'attach_id' => array(
			'type' => 'i11',
			'desc' => 'ID вложения'
		),
		'owner_id' => array(
			'type' => 'i11',
			'desc' => 'ID владельца'
		),
		'uri' => array(
			'type' => 'tx',
			'desc' => 'Ссылка на вложение (УРЛ)'
		),
		'path' => array(
			'type' => 'v255',
			'desc' => 'Полный путь к локальному файлу вложения (если сохранено)'
		),
		'width' => array(
			'type' => 'si5',
			'desc' => 'Ширина изображения'
		),
		'height' => array(
			'type' => 'si5',
			'desc' => 'Высота изображения'
		),
		'text' => array(
			'type' => 'tx',
			'desc' => 'Описание если тип [ video | link | photo ]'
		),
		'date' => array(
			'type' => 'i11',
			'desc' => ''
		),
		'access_key' => array(
			'type' => 'v255',
			'desc' => 'Значение поля `access key` для доступа к не публичному содержанию'
		),
		'title' => array(
			'type' => 'tx',
			'desc' => 'Заголовок если тип [ video | link ], Название трека если тип [ audio ]'
		),
		'duration' => array(
			'type' => 'i11',
			'desc' => 'Тип `wall` - является флагом сохранена ли запись; Тип `doc` - размер документа в байтах; Тип `video` - продолжительность сек.'
		),
		'player' => array(
			'type' => 'tx',
			'desc' => 'Ссылка на встраиваемый плеер если тип [ video ]'
		),
		'link_url' => array(
			'type' => 'tx',
			'desc' => 'Ссылка на страницу если тип [ link ]; Ссылка на аудиофайл если тип [ audio ] (устарело)'
		),
		'caption' => array(
			'type' => 'v255',
			'desc' => 'Домен если тип [ link ]; Испонитель если тип [ audio ] (устарело)'
		),
		'skipthis' => array(
			'type' => 'b',
			'desc' => 'Флаг: пропуск элемента в очереди закачки (например если элемент возвращает ошибку 404)'
		)
	)
),


'music' => array(
	'desc' => 'Таблица содержит данные о аудио треках пользователя <b>легаси</b>',
	'cols' => array(
		'id' => array(
			'type' => 'i10',
			'desc' => 'ID аудио записи'
		),
		'artist' => array(
			'type' => 'v255',
			'desc' => 'Название исполнителя'
		),
		'title' => array(
			'type' => 'v255',
			'desc' => 'Название трека'
		),
		'album' => array(
			'type' => 'i10',
			'desc' => 'ID альбома музыки (устарело)'
		),
		'duration' => array(
			'type' => 'si5',
			'desc' => 'Продолжительность (сек.)'
		),
		'uri' => array(
			'type' => 'tx',
			'desc' => 'Ссылка на аудио запись (ВК)'
		),
		'date_added' => array(
			'type' => 'i10',
			'desc' => 'Дата создания (ВК)'
		),
		'date_done' => array(
			'type' => 'i10',
			'desc' => 'Дата скачивания (локально)'
		),
		'saved' => array(
			'type' => 'b',
			'desc' => 'Флаг: элемент сохранен локально'
		),
		'deleted' => array(
			'type' => 'b',
			'desc' => 'Флаг: элемент был удален в ВК'
		),
		'path' => array(
			'type' => 'tx',
			'desc' => 'Полный путь к локальному файлу'
		),
		'hash' => array(
			'type' => 'v40',
			'desc' => 'Хэш файла'
		),
		'in_queue' => array(
			'type' => 'b',
			'desc' => 'Флаг: элемент находится в очереди закачки'
		)
	)
),


'music_albums' => array(
	'desc' => 'Таблица содержит информацию о музыкальных альбомах пользователя <b>легаси</b>',
	'cols' => array(
		'id' => array(
			'type' => 'i11',
			'desc' => 'ID альбома'
		),
		'name' => array(
			'type' => 'tx',
			'desc' => 'Название альбома'
		),
		'deleted' => array(
			'type' => 'b',
			'desc' => 'Флаг: элемент был удален в ВК'
		)
	)
),


'photos' => array(
	'desc' => 'Таблица содержит информацию о фотографиях пользователя',
	'cols' => array(
		'id' => array(
			'type' => 'i11',
			'desc' => 'ID изображения'
		),
		'album_id' => array(
			'type' => 'i10',
			'desc' => 'ID альбома в котором находится изображение (связь `album_id` -> `'.$db_prefix.'albums.id` )'
		),
		'date_added' => array(
			'type' => 'i10',
			'desc' => 'Дата загрузки изображения в ВК'
		),
		'uri' => array(
			'type' => 'tx',
			'desc' => 'Ссылка на изображение (ВК)'
		),
		'width' => array(
			'type' => 'si5',
			'desc' => 'Ширина изображения'
		),
	 'height' => array(
			'type' => 'si5',
			'desc' => 'Высота изображения'
		),
		'date_done' => array(
			'type' => 'i11',
			'desc' => 'Дата когда изображение было сохранено локально'
		),
		'saved' => array(
			'type' => 'b',
			'desc' => 'Флаг: элемент сохранен локально'
		),
		'path' => array(
			'type' => 'tx',
			'desc' => 'Полный путь к локальному файлу'
		),
		'hash' => array(
			'type' => 'v40',
			'desc' => 'Хэш файла'
		),
		'in_queue' => array(
			'type' => 'b',
			'desc' => 'Флаг: элемент находится в очереди закачки'
		),
		'skipthis' => array(
		'type' => 'b',
		'desc' => 'Флаг: пропуск элемента в очереди закачки (например если элемент возвращает ошибку 404)'
		)
	)
),


'profiles' => array(
	'desc' => 'Таблица с информацией о пользователях (авторы)',
	'cols' => array(
		'id' => array(
			'type' => 'i11',
			'desc' => 'ID пользователя'
		),
		'first_name' => array(
			'type' => 'v255',
			'desc' => 'Имя пользователя'
		),
		'last_name' => array(
			'type' => 'v255',
			'desc' => 'Фамилия пользователя'
		),
		'sex' => array(
			'type' => 'b',
			'desc' => 'Пол пользователя [ 1 - женский | 2 - муожской ]'
		),
		'nick' => array(
			'type' => 'v255',
			'desc' => 'Ник пользователя (УРЛ в вк)'
		),
		'photo_uri' => array(
			'type' => 'tx',
			'desc' => 'Ссылка на аватарку группы (ВК)'
		),
		'photo_path' => array(
			'type' => 'v255',
			'desc' => 'Имя локального файла аватарки в директории [ /data/profiles/ ]'
		)
	)
),


'session' => array(
	'desc' => 'Таблца сожержит информацию для доступа приложения к API ВКонтакте',
	'cols' => array(
		'vk_id' => array(
			'type' => 'i10',
			'desc' => 'ID (должен быть равен 1)'
		),
		'vk_token' => array(
			'type' => 'v255',
			'desc' => 'Токен авторизации'
		),
		'vk_expire' => array(
			'type' => 'i11',
			'desc' => 'Дата истечения токена'
		),
		'vk_user' => array(
			'type' => 'i11',
			'desc' => 'ID пользователя'
		)
	)
),
 
 
'status' => array(
	'desc' => 'Таблица для хранения настроек, логов и иной технической информации',
	'cols' => array(
		'key' => array(
			'type' => 'v255',
			'desc' => 'Ключ'
		),
		'val' => array(
			'type' => 'tx',
			'desc' => 'Значение'
		)
	)
),
 
 
'stickers' => array(
	'desc' => '<b>на данный момент не используется</b>',
	'cols' => array(
		'product' => array(
			'type' => 'i11',
			'desc' => ''
		),
		'sticker' => array(
			'type' => 'i11',
			'desc' => ''
		),
		'width' => array(
			'type' => 'i10',
			'desc' => ''
		),
		'height' => array(
			'type' => 'i10',
			'desc' => ''
		),
		'uri' => array(
			'type' => 'v255',
			'desc' => ''
		),
		'path' => array(
			'type' => 'v255',
			'desc' => ''
		),
		'in_queue' => array(
			'type' => 'ti4',
			'desc' => ''
		)
	)
),
 
 
'videos' => array(
	'desc' => 'Таблица с информацией о видеозаписях пользователя',
	'cols' => array(
		'id' => array(
			'type' => 'i11',
			'desc' => 'ID видеозаписи'
		),
		'owner_id' => array(
			'type' => 'i11',
			'desc' => 'ID владельца'
		),
		'title' => array(
			'type' => 'tx',
			'desc' => 'Заголовок (название) видео'
		),
		'desc' => array(
			'type' => 'tx',
			'desc' => 'Описание видео'
		),
		'duration' => array(
			'type' => 'si5',
			'desc' => 'Продолжительность (сек.)'
		),
		'preview_uri' => array(
			'type' => 'v255',
			'desc' => 'Ссылка на изображение превью (ВК)'
		),
		'preview_path' => array(
			'type' => 'v255',
			'desc' => 'Полный путь к локальному файлу превью'
		),
		'player_uri' => array(
			'type' => 'tx',
			'desc' => 'Ссылка на встраиваемый плеер'
		),
		'access_key' => array(
			'type' => 'v255',
			'desc' => 'Значение поля `access key` для доступа к не публичному содержанию'
		),
		'date_added' => array(
			'type' => 'i10',
			'desc' => 'Дата создания видеозаписи'
		),
		'date_done' => array(
			'type' => 'i10',
			'desc' => 'Дата сохранения видеозаписи локально'
		),
		'deleted' => array(
			'type' => 'b',
			'desc' => 'Флаг: элемент был удалён в ВК'
		),
		'in_queue' => array(
			'type' => 'b',
			'desc' => 'Флаг: элемент находится в очереди закачки'
		),
		'local_path' => array(
			'type' => 'tx',
			'desc' => 'Полный путь к локальному файлу'
		),
		'local_size' => array(
			'type' => 'i11',
			'desc' => 'Размер локального файла в байтах'
		),
		'local_format' => array(
			'type' => 'v50',
			'desc' => 'Формат (расширение) локального файла'
		),
		'local_w' => array(
			'type' => 'si5',
			'desc' => 'Ширина локального видео'
		),
		'local_h' => array(
			'type' => 'si5',
			'desc' => 'Высота локального видео. В случае если ширина равна 0, означает пропорции. Например 720 -> 720p'
		)
	)
 ),
 
 
'wall' => array(
	'desc' => 'Таблица содержит сообщения со стены пользователя',
	'cols' => array(
		'id' => array(
			'type' => 'i11',
			'desc' => 'ID сообщения'
		),
		'from_id' => array(
			'type' => 'i11',
			'desc' => 'ID отправителя'
		),
		'owner_id' => array(
			'type' => 'i11',
			'desc' => 'ID владельца'
		),
		'date' => array(
			'type' => 'i11',
			'desc' => 'Дата сообщения'
		),
		'post_type' => array(
			'type' => 'v255',
			'desc' => 'Тип сообщения'
		),
		'text' => array(
			'type' => 'tx',
			'desc' => 'Содержание сообщения'
		),
		'attach' => array(
			'type' => 'b',
			'desc' => 'Сообщение содержит вложение (связь `id` -> `'.$db_prefix.'attach.wall_id` )'
		),
		'repost' => array(
			'type' => 'i11',
			'desc' => 'ID сообщения репоста (связь `repost` -> `id` )'
		),
		'repost_owner' => array(
			'type' => 'i11',
			'desc' => 'ID владельца репост сообщения (связь `repost_owner` -> `owner_id` )'
		),
		'is_repost' => array(
			'type' => 'b',
			'desc' => 'Сообщение содержит репост'
		)
	)
),

);

?>