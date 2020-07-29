-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 28, 2020 at 06:53 PM
-- Server version: 10.2.13-MariaDB
-- PHP Version: 7.3.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `vkbk`
--

-- --------------------------------------------------------

--
-- Table structure for table `vk_albums`
--

CREATE TABLE IF NOT EXISTS `vk_albums` (
  `id` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created` int(10) UNSIGNED NOT NULL,
  `updated` int(10) UNSIGNED NOT NULL,
  `img_total` int(10) UNSIGNED NOT NULL,
  `img_done` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created` (`created`),
  KEY `updated` (`updated`),
  KEY `images` (`img_total`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `vk_albums`
--

INSERT INTO `vk_albums` (`id`, `name`, `created`, `updated`, `img_total`, `img_done`) VALUES
(-9000, 'Системный альбом', 1457276070, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `vk_attach`
--

CREATE TABLE IF NOT EXISTS `vk_attach` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `wall_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `is_local` tinyint(1) NOT NULL,
  `attach_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `uri` text NOT NULL,
  `path` varchar(255) NOT NULL,
  `width` smallint(5) UNSIGNED NOT NULL,
  `height` smallint(5) UNSIGNED NOT NULL,
  `text` text CHARACTER SET utf8mb4 NOT NULL,
  `date` int(11) NOT NULL,
  `access_key` varchar(255) NOT NULL,
  `title` text CHARACTER SET utf8mb4 NOT NULL,
  `duration` int(11) NOT NULL,
  `player` text NOT NULL,
  `link_url` text NOT NULL,
  `caption` varchar(255) NOT NULL,
  `skipthis` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `uniqid` (`wall_id`,`attach_id`),
  KEY `local` (`is_local`),
  KEY `width` (`width`),
  KEY `height` (`height`),
  KEY `skip` (`skipthis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `vk_counters`
--

CREATE TABLE IF NOT EXISTS `vk_counters` (
  `album` mediumint(8) UNSIGNED NOT NULL,
  `photo` mediumint(8) UNSIGNED NOT NULL,
  `music` mediumint(8) UNSIGNED NOT NULL,
  `video` mediumint(8) UNSIGNED NOT NULL,
  `wall` mediumint(8) UNSIGNED NOT NULL,
  `docs` mediumint(8) UNSIGNED NOT NULL,
  `dialogs` mediumint(8) UNSIGNED NOT NULL,
  `comments` mediumint(8) UNSIGNED NOT NULL,
  UNIQUE KEY `counters` (`album`,`photo`,`music`,`video`,`wall`,`docs`,`dialogs`,`comments`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `vk_counters`
--

INSERT INTO `vk_counters` (`album`, `photo`, `music`, `video`, `wall`, `docs`, `dialogs`) VALUES
(0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `vk_dialogs`
--

CREATE TABLE IF NOT EXISTS `vk_dialogs` (
  `id` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `in_read` int(11) NOT NULL,
  `multichat` tinyint(1) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `users` int(11) NOT NULL,
  `is_new` tinyint(1) NOT NULL,
  `is_upd` tinyint(1) NOT NULL,
  UNIQUE KEY `uchat` (`id`,`multichat`,`chat_id`,`admin_id`),
  KEY `multi` (`multichat`),
  KEY `admin` (`admin_id`),
  KEY `uid` (`id`),
  KEY `new` (`is_new`),
  KEY `upd` (`is_upd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vk_docs`
--

CREATE TABLE IF NOT EXISTS `vk_docs` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `size` int(11) NOT NULL,
  `ext` varchar(25) NOT NULL,
  `uri` text NOT NULL,
  `date` int(11) NOT NULL,
  `type` smallint(6) NOT NULL,
  `preview_uri` text NOT NULL,
  `preview_path` text NOT NULL,
  `width` smallint(5) UNSIGNED NOT NULL,
  `height` smallint(5) UNSIGNED NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `in_queue` tinyint(1) NOT NULL,
  `local_path` text NOT NULL,
  `local_size` int(11) NOT NULL,
  `local_w` smallint(6) NOT NULL,
  `local_h` smallint(6) NOT NULL,
  `skipthis` tinyint(1) NOT NULL DEFAULT 0,
  UNIQUE KEY `id` (`id`),
  KEY `type` (`type`),
  KEY `deleted` (`deleted`),
  KEY `queue` (`in_queue`),
  KEY `skip` (`skipthis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vk_groups`
--

CREATE TABLE IF NOT EXISTS `vk_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `nick` varchar(255) NOT NULL,
  `photo_uri` text NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vk_messages`
--

CREATE TABLE IF NOT EXISTS `vk_messages` (
  `uid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `msg_id` int(11) NOT NULL,
  `msg_chat` int(11) NOT NULL,
  `msg_dialog` int(11) NOT NULL,
  `msg_user` int(11) NOT NULL,
  `msg_date` int(11) UNSIGNED NOT NULL,
  `msg_body` text CHARACTER SET utf8mb4 NOT NULL,
  `msg_attach` tinyint(1) NOT NULL,
  `msg_forwarded` tinyint(1) NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `msg_uid` (`msg_id`,`msg_chat`,`msg_dialog`,`msg_date`) USING BTREE,
  KEY `user` (`msg_user`),
  KEY `attach` (`msg_attach`),
  KEY `forwarded` (`msg_forwarded`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `vk_messages_attach`
--

CREATE TABLE IF NOT EXISTS `vk_messages_attach` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `wall_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `is_local` tinyint(1) NOT NULL,
  `attach_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `uri` text NOT NULL,
  `path` varchar(255) NOT NULL,
  `width` smallint(5) UNSIGNED NOT NULL,
  `height` smallint(5) UNSIGNED NOT NULL,
  `text` text CHARACTER SET utf8mb4 NOT NULL,
  `date` int(11) NOT NULL,
  `access_key` varchar(255) NOT NULL,
  `title` text CHARACTER SET utf8mb4 NOT NULL,
  `duration` int(11) NOT NULL,
  `player` text NOT NULL,
  `link_url` text NOT NULL,
  `caption` varchar(255) NOT NULL,
  `skipthis` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `uniqid` (`wall_id`,`attach_id`),
  KEY `local` (`is_local`),
  KEY `width` (`width`),
  KEY `height` (`height`),
  KEY `skip` (`skipthis`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `vk_messages_wall`
--

CREATE TABLE IF NOT EXISTS `vk_messages_wall` (
  `id` int(11) NOT NULL,
  `from_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `post_type` varchar(255) NOT NULL,
  `text` text CHARACTER SET utf8mb4 NOT NULL,
  `attach` tinyint(1) NOT NULL,
  `repost` int(11) NOT NULL,
  `repost_owner` int(11) NOT NULL,
  `is_repost` tinyint(1) NOT NULL,
  UNIQUE KEY `id` (`id`,`owner_id`),
  KEY `from` (`from_id`),
  KEY `owner` (`owner_id`),
  KEY `type` (`post_type`),
  KEY `attach` (`attach`),
  KEY `repost` (`is_repost`),
  KEY `repost_owner` (`repost_owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vk_messages_wall_attach`
--

CREATE TABLE IF NOT EXISTS `vk_messages_wall_attach` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `wall_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `is_local` tinyint(1) NOT NULL,
  `attach_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `uri` text NOT NULL,
  `path` varchar(255) NOT NULL,
  `width` smallint(5) UNSIGNED NOT NULL,
  `height` smallint(5) UNSIGNED NOT NULL,
  `text` text CHARACTER SET utf8mb4 NOT NULL,
  `date` int(11) NOT NULL,
  `access_key` varchar(255) NOT NULL,
  `title` text CHARACTER SET utf8mb4 NOT NULL,
  `duration` int(11) NOT NULL,
  `player` text NOT NULL,
  `link_url` text NOT NULL,
  `caption` varchar(255) NOT NULL,
  `skipthis` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `uniqid` (`wall_id`,`attach_id`),
  KEY `local` (`is_local`),
  KEY `width` (`width`),
  KEY `height` (`height`),
  KEY `skip` (`skipthis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vk_music`
--

CREATE TABLE IF NOT EXISTS `vk_music` (
  `id` int(10) UNSIGNED NOT NULL,
  `artist` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `album` int(10) UNSIGNED NOT NULL,
  `duration` smallint(5) UNSIGNED NOT NULL,
  `uri` text NOT NULL,
  `date_added` int(10) UNSIGNED NOT NULL,
  `date_done` int(10) UNSIGNED NOT NULL,
  `saved` tinyint(1) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `path` text NOT NULL,
  `hash` varchar(40) NOT NULL,
  `in_queue` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `queue` (`in_queue`),
  KEY `d_added` (`date_added`),
  KEY `d_saved` (`date_done`),
  KEY `saved` (`saved`),
  KEY `deleted` (`deleted`),
  KEY `track` (`artist`,`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vk_music_albums`
--

CREATE TABLE IF NOT EXISTS `vk_music_albums` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vk_photos`
--

CREATE TABLE IF NOT EXISTS `vk_photos` (
  `id` int(11) NOT NULL,
  `album_id` int(10) NOT NULL,
  `date_added` int(10) UNSIGNED NOT NULL,
  `uri` text NOT NULL,
  `width` smallint(5) UNSIGNED NOT NULL,
  `height` smallint(5) UNSIGNED NOT NULL,
  `date_done` int(11) UNSIGNED NOT NULL,
  `saved` tinyint(1) NOT NULL,
  `path` text NOT NULL,
  `hash` varchar(40) NOT NULL,
  `in_queue` tinyint(1) NOT NULL,
  `skipthis` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `queue` (`in_queue`),
  KEY `album` (`album_id`),
  KEY `width` (`width`),
  KEY `height` (`height`),
  KEY `dsaved` (`date_done`),
  KEY `saved` (`saved`),
  KEY `dadded` (`date_added`),
  KEY `skip` (`skipthis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vk_profiles`
--

CREATE TABLE IF NOT EXISTS `vk_profiles` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `sex` tinyint(1) NOT NULL DEFAULT 0,
  `nick` varchar(255) NOT NULL,
  `photo_uri` text NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vk_session`
--

CREATE TABLE IF NOT EXISTS `vk_session` (
  `vk_id` int(10) UNSIGNED NOT NULL,
  `vk_token` varchar(255) NOT NULL,
  `vk_expire` int(11) NOT NULL,
  `vk_user` int(11) NOT NULL,
  PRIMARY KEY (`vk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `vk_session`
--

INSERT INTO `vk_session` (`vk_id`, `vk_token`, `vk_expire`, `vk_user`) VALUES
(1, '', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `vk_status`
--

CREATE TABLE IF NOT EXISTS `vk_status` (
  `key` varchar(255) NOT NULL,
  `val` text NOT NULL,
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `vk_status`
--

INSERT INTO `vk_status` (`key`, `val`) VALUES
('log_docs', ''),
('log_music', ''),
('log_photo', ''),
('log_video', ''),
('version', '2020072801'),
('auto-queue-audio', '0'),
('auto-queue-photo', '0'),
('play-local-video', '0');

-- --------------------------------------------------------

--
-- Table structure for table `vk_stickers`
--

CREATE TABLE IF NOT EXISTS `vk_stickers` (
  `product` int(11) NOT NULL,
  `sticker` int(11) NOT NULL,
  `width` int(10) UNSIGNED NOT NULL,
  `height` int(10) UNSIGNED NOT NULL,
  `uri` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `in_queue` tinyint(4) NOT NULL,
  UNIQUE KEY `stick` (`product`,`sticker`),
  KEY `product` (`product`),
  KEY `queue` (`in_queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vk_videos`
--

CREATE TABLE IF NOT EXISTS `vk_videos` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `title` text CHARACTER SET utf8mb4 NOT NULL,
  `desc` text CHARACTER SET utf8mb4 NOT NULL,
  `duration` smallint(5) UNSIGNED NOT NULL,
  `preview_uri` varchar(255) NOT NULL,
  `preview_path` varchar(255) NOT NULL,
  `player_uri` text NOT NULL,
  `access_key` varchar(255) NOT NULL,
  `date_added` int(10) UNSIGNED NOT NULL,
  `date_done` int(10) UNSIGNED NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `in_queue` tinyint(1) NOT NULL,
  `local_path` text NOT NULL,
  `local_size` int(11) NOT NULL,
  `local_format` varchar(50) NOT NULL,
  `local_w` smallint(5) UNSIGNED NOT NULL,
  `local_h` smallint(5) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`,`owner_id`),
  KEY `dur` (`duration`),
  KEY `dadded` (`date_added`),
  KEY `dsaved` (`date_done`),
  KEY `deleted` (`deleted`),
  KEY `queue` (`in_queue`),
  KEY `local_w` (`local_w`),
  KEY `local_h` (`local_h`),
  KEY `format` (`local_format`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vk_wall`
--

CREATE TABLE IF NOT EXISTS `vk_wall` (
  `id` int(11) NOT NULL,
  `from_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `post_type` varchar(255) NOT NULL,
  `text` text CHARACTER SET utf8mb4 NOT NULL,
  `attach` tinyint(1) NOT NULL,
  `repost` int(11) NOT NULL,
  `repost_owner` int(11) NOT NULL,
  `is_repost` tinyint(1) NOT NULL,
  `comments` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `comm_upd` tinyint(1) NOT NULL,
  UNIQUE KEY `id` (`id`,`owner_id`),
  KEY `from` (`from_id`),
  KEY `owner` (`owner_id`),
  KEY `type` (`post_type`),
  KEY `attach` (`attach`),
  KEY `repost` (`is_repost`),
  KEY `repost_owner` (`repost_owner`),
  KEY `comm_upd` (`comm_upd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vk_wall_comments`
--

CREATE TABLE IF NOT EXISTS `vk_wall_comments` (
  `id` int(11) NOT NULL,
  `from_id` int(11) NOT NULL,
  `wall_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `text` text NOT NULL,
  `attach` tinyint(1) NOT NULL,
  `p_stack` text NOT NULL,
  `t_count` mediumint(8) NOT NULL,
  `reply_u` int(11) NOT NULL,
  `reply_c` int(11) NOT NULL,
  UNIQUE KEY `uid` (`id`,`from_id`,`wall_id`),
  KEY `attach` (`id`),
  KEY `owner_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vk_wall_comments_attach`
--

CREATE TABLE IF NOT EXISTS `vk_wall_comments_attach` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `wall_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `is_local` tinyint(1) NOT NULL,
  `attach_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `uri` text NOT NULL,
  `path` varchar(255) NOT NULL,
  `width` smallint(5) UNSIGNED NOT NULL,
  `height` smallint(5) UNSIGNED NOT NULL,
  `text` text CHARACTER SET utf8mb4 NOT NULL,
  `date` int(11) NOT NULL,
  `access_key` varchar(255) NOT NULL,
  `title` text CHARACTER SET utf8mb4 NOT NULL,
  `duration` int(11) NOT NULL,
  `player` text NOT NULL,
  `link_url` text NOT NULL,
  `caption` varchar(255) NOT NULL,
  `skipthis` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `uniqid` (`wall_id`,`attach_id`),
  KEY `local` (`is_local`),
  KEY `width` (`width`),
  KEY `height` (`height`),
  KEY `skip` (`skipthis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
COMMIT;
