# VKBK
Инструмент для создания и синхронизации локального бэкапа вашего лампового профиля ВКонтакте.

### Актуальная версия 0.9.0

### Системные требования
Софт | Версия | Модули
--- | --- | ---
Apache | 2.4+ | 
PHP | 7.3+ | curl, mbstring, mysqli, json
MySQL | 5.5+ | 
Composer | 1.10.6+ | twig, vk-auth, guzzle

## Инсталляция
[Инстукция по установке и настройке](https://github.com/Chiaki/VKBK/wiki/Установка-и-настройка)

## История версий
Посмотреть [историю версий](CHANGELOG.md) проекта VKBK.

## Функционал
На данный момент реализован следующий функционал:
+ Получение диалогов (личные сообщения) (тип: фото, видео, ссылки, документы, стикеры, репосты стен и сообществ, цитирование)
- Получение альбомов (в том числе и системных)
- Получение фотографий
- Получение документов
- Получение аудиозаписей ~~и альбомов~~
> ~~В версии 0.8.5 снова появилась возможность сохранять свои аудиозаписи.~~ ВК опять всё поломали. ):
- Получение видеозаписей
> (просмотр через плеер, возможность создать локальную копию при помощи [youtube-dl](https://github.com/rg3/youtube-dl))
* Получение сообщений, комментариев и репостов со стены (тип: фото, видео, ссылки, документы, стикеры, ~~музыка~~)

Через веб-интерфейс возможен просмотр диалогов, альбомов, фотографий, стены, прослушивание аудиозаписей, просмотр видеозаписей через локальный и сторонние плееры.

