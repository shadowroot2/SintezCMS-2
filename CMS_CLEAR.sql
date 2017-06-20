-- phpMyAdmin SQL Dump
-- version 3.2.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 24, 2013 at 12:06 PM
-- Server version: 5.1.40
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `asiaglass`
--

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE IF NOT EXISTS `classes` (
  `id` smallint(4) NOT NULL AUTO_INCREMENT,
  `protected` tinyint(1) NOT NULL DEFAULT '0',
  `additional` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `desc` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Дамп данных таблицы `classes`
--

INSERT INTO `classes` (`id`, `protected`, `additional`, `name`, `desc`) VALUES
(1, 1, 0, 'Текстовая страница', 'Подходит для всех текстовых страниц'),
(2, 1, 0, 'Публикация', 'Подходит для новостей и статей'),
(3, 1, 0, 'Фотография', 'Подходит для создания галлерей'),
(6, 1, 0, 'Файл', 'Подходит для создания файловых архивов или прикреплений к объектам'),
(4, 1, 0, 'MP3 файл', 'Подходит для создания аудио архивов'),
(5, 1, 0, 'Видео файл', 'Подходит для создания видео архивов'),
(7, 1, 0, 'Значение', 'Подходит для храниения единичных значений'),
(8, 1, 0, 'Ссылка', 'Подходит для создания ссылок в меню');

-- --------------------------------------------------------

--
-- Table structure for table `class_1`
--

CREATE TABLE IF NOT EXISTS `class_1` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `object_id` int(10) NOT NULL,
  `lang` char(2) NOT NULL DEFAULT 'ru',
  `f_1` varchar(255) NOT NULL,
  `f_2` longtext,
  `f_3` text,
  `f_4` text,
  `f_156` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `object` (`object_id`,`lang`),
  FULLTEXT KEY `f_2` (`f_2`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `class_1`
--

INSERT INTO `class_1` (`id`, `object_id`, `lang`, `f_1`, `f_2`, `f_3`, `f_4`, `f_156`) VALUES
(1, 7, 'ru', 'Основной шаблон рассылки', '<div align="center" style="font:12px/20px Verdana,Arial;color:#2c2c2c;">\n	<div align="left" style="width:730px;padding:20px;background-color:#ffffff;border:1px solid #c0c0c0;">\n		<table cellpadding="0" cellspacing="0" height="82" width="100%">\n			<tbody>\n				<tr>\n					<td align="center" valign="center" width="200">\n						&nbsp;</td>\n					<td>\n						&nbsp;</td>\n				</tr>\n			</tbody>\n		</table>\n		<div style="margin:20px 0">\n			#CONTENT#</div>\n		<div>\n			&nbsp;</div>\n	</div>\n</div>\n<p>\n	&nbsp;</p>\n', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `class_2`
--

CREATE TABLE IF NOT EXISTS `class_2` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `object_id` int(10) NOT NULL,
  `lang` char(2) NOT NULL DEFAULT 'ru',
  `f_5` varchar(255) NOT NULL,
  `f_6` varchar(255) NOT NULL,
  `f_7` date NOT NULL,
  `f_8` text NOT NULL,
  `f_9` longtext NOT NULL,
  `f_11` text NOT NULL,
  `f_12` text NOT NULL,
  `f_78` int(10) NOT NULL DEFAULT '0',
  `f_157` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `object` (`object_id`,`lang`),
  FULLTEXT KEY `f_9` (`f_9`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `class_2`
--


-- --------------------------------------------------------

--
-- Table structure for table `class_3`
--

CREATE TABLE IF NOT EXISTS `class_3` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `object_id` int(10) NOT NULL,
  `lang` char(2) NOT NULL DEFAULT 'ru',
  `f_13` varchar(255) NOT NULL,
  `f_14` varchar(250) NOT NULL,
  `f_15` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `object` (`object_id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `class_3`
--


-- --------------------------------------------------------

--
-- Table structure for table `class_4`
--

CREATE TABLE IF NOT EXISTS `class_4` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `object_id` int(10) NOT NULL,
  `lang` char(2) NOT NULL DEFAULT 'ru',
  `f_19` varchar(255) NOT NULL,
  `f_20` char(50) NOT NULL,
  `f_21` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `object` (`object_id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `class_4`
--


-- --------------------------------------------------------

--
-- Table structure for table `class_5`
--

CREATE TABLE IF NOT EXISTS `class_5` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `object_id` int(10) NOT NULL,
  `lang` char(2) NOT NULL DEFAULT 'ru',
  `f_22` varchar(255) NOT NULL,
  `f_23` char(50) NOT NULL,
  `f_24` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `object` (`object_id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `class_5`
--


-- --------------------------------------------------------

--
-- Table structure for table `class_6`
--

CREATE TABLE IF NOT EXISTS `class_6` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `object_id` int(10) NOT NULL,
  `lang` char(2) NOT NULL DEFAULT 'ru',
  `f_16` varchar(255) NOT NULL,
  `f_17` varchar(255) NOT NULL,
  `f_18` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `object` (`object_id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `class_6`
--


-- --------------------------------------------------------

--
-- Table structure for table `class_7`
--

CREATE TABLE IF NOT EXISTS `class_7` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `object_id` int(10) NOT NULL,
  `lang` char(2) NOT NULL DEFAULT 'ru',
  `f_25` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `object` (`object_id`,`lang`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `class_7`
--

INSERT INTO `class_7` (`id`, `object_id`, `lang`, `f_25`) VALUES
(1, 2, 'ru', 'Название сайта'),
(2, 3, 'ru', 'Тут описание'),
(3, 4, 'ru', 'Ключевые слова'),
(4, 5, 'ru', 'shadow_root@mail.ru');

-- --------------------------------------------------------

--
-- Table structure for table `class_8`
--

CREATE TABLE IF NOT EXISTS `class_8` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `object_id` int(10) NOT NULL,
  `lang` char(2) NOT NULL DEFAULT 'ru',
  `f_68` varchar(255) NOT NULL,
  `f_69` varchar(255) NOT NULL,
  `f_70` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `object` (`object_id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `class_8`
--


-- --------------------------------------------------------

--
-- Table structure for table `fields`
--

CREATE TABLE IF NOT EXISTS `fields` (
  `id` mediumint(5) NOT NULL AUTO_INCREMENT,
  `class_id` mediumint(4) NOT NULL,
  `name` varchar(30) NOT NULL,
  `type` varchar(15) NOT NULL,
  `atribute` text,
  `sort` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=158 ;

--
-- Dumping data for table `fields`
--

INSERT INTO `fields` (`id`, `class_id`, `name`, `type`, `atribute`, `sort`) VALUES
(1, 1, 'Заголовок', 'text', '', 1),
(2, 1, 'Текст', 'html', '', 3),
(3, 1, 'description', 'textarea', '', 4),
(4, 1, 'keywords', 'textarea', '', 5),
(5, 2, 'Заголовок', 'text', '', 2),
(6, 2, 'Изображение', 'image', '', 4),
(7, 2, 'Дата', 'date', '', 1),
(8, 2, 'Анонс', 'textarea', '', 5),
(9, 2, 'Текст', 'html', '', 6),
(156, 1, 'ЧПУ', 'text', '', 2),
(11, 2, 'keywords', 'textarea', '', 8),
(12, 2, 'description', 'textarea', '', 9),
(13, 3, 'Название', 'text', '', 1),
(14, 3, 'Изображение', 'image', '', 2),
(15, 3, 'Просмотры', 'integer', '', 3),
(16, 6, 'Название', 'text', '', 1),
(17, 6, 'Файл', 'file', 'doc,docx,xls,xlsx,pdf,zip,rar,7z,tar,jpg,jpeg,png,gif,ppt', 2),
(18, 6, 'Скачиваний', 'integer', '', 3),
(19, 4, 'Название', 'text', '', 1),
(20, 4, 'Файл', 'audio', '', 2),
(21, 4, 'Прослушиваний', 'integer', '', 3),
(22, 5, 'Название', 'text', '', 1),
(23, 5, 'Файл', 'video', '', 2),
(24, 5, 'Просмотров', 'integer', '', 3),
(25, 7, 'Значение', 'textarea', '', 1),
(68, 8, 'Название', 'text', '', 1),
(69, 8, 'URL', 'text', '', 2),
(70, 8, 'В новом окне', 'checkbox', '', 3),
(78, 2, 'Просмотров', 'integer', '', 7),
(157, 2, 'ЧПУ', 'text', '', 3);

-- --------------------------------------------------------

--
-- Table structure for table `md_cron`
--

CREATE TABLE IF NOT EXISTS `md_cron` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `run` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL,
  `controller` varchar(30) NOT NULL,
  `start_date` int(10) NOT NULL,
  `interval` int(10) NOT NULL DEFAULT '0',
  `last_start` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `active` (`active`,`start_date`,`interval`,`last_start`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `md_cron`
--


-- --------------------------------------------------------

--
-- Table structure for table `md_languages`
--

CREATE TABLE IF NOT EXISTS `md_languages` (
  `id` tinyint(3) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `set` tinyint(1) NOT NULL DEFAULT '0',
  `code` char(2) NOT NULL,
  `name` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `active` (`active`,`set`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `md_languages`
--

INSERT INTO `md_languages` (`id`, `active`, `set`, `code`, `name`) VALUES
(1, 1, 1, 'ru', 'Русский'),
(2, 0, 0, 'en', 'English'),
(3, 0, 0, 'kz', 'Казахский');

-- --------------------------------------------------------

--
-- Table structure for table `md_logs`
--

CREATE TABLE IF NOT EXISTS `md_logs` (
  `id` bigint(18) NOT NULL AUTO_INCREMENT,
  `date` int(10) NOT NULL,
  `user_id` smallint(5) NOT NULL DEFAULT '0',
  `user_name` varchar(30) NOT NULL,
  `action` tinyint(1) NOT NULL DEFAULT '1',
  `object_id` int(10) NOT NULL DEFAULT '0',
  `object_name` varchar(100) DEFAULT NULL,
  `class_id` smallint(4) NOT NULL DEFAULT '0',
  `class_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `md_logs`
--


-- --------------------------------------------------------

--
-- Table structure for table `md_planed_mailer`
--

CREATE TABLE IF NOT EXISTS `md_planed_mailer` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `to` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `files` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `md_planed_mailer`
--


-- --------------------------------------------------------

--
-- Table structure for table `md_routes`
--

CREATE TABLE IF NOT EXISTS `md_routes` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `route` varchar(100) NOT NULL,
  `replace` varchar(100) NOT NULL,
  `sort` bigint(12) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `active` (`active`,`route`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `md_routes`
--


-- --------------------------------------------------------

--
-- Table structure for table `md_site_users_ram`
--

CREATE TABLE IF NOT EXISTS `md_site_users_ram` (
  `id` int(10) NOT NULL,
  `sess` char(32) NOT NULL,
  `name` varchar(50) NOT NULL,
  `basket` varchar(5000) NOT NULL,
  PRIMARY KEY (`id`,`sess`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

--
-- Dumping data for table `md_site_users_ram`
--


-- --------------------------------------------------------

--
-- Table structure for table `md_users`
--

CREATE TABLE IF NOT EXISTS `md_users` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `gid` smallint(4) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `protected` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(30) NOT NULL,
  `login` varchar(15) NOT NULL,
  `pass` char(32) NOT NULL,
  `add_ts` int(10) NOT NULL,
  `login_ts` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `active` (`active`,`login`),
  KEY `group` (`gid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `md_users`
--

INSERT INTO `md_users` (`id`, `gid`, `active`, `protected`, `name`, `login`, `pass`, `add_ts`, `login_ts`) VALUES
(1, 1, 1, 1, 'root', 'root', '41df931328b41273778c382838339382', 0, 1354217003);

-- --------------------------------------------------------

--
-- Table structure for table `md_users_ram`
--

CREATE TABLE IF NOT EXISTS `md_users_ram` (
  `user_id` smallint(5) NOT NULL,
  `sess` char(32) NOT NULL,
  PRIMARY KEY (`user_id`,`sess`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `md_user_groups`
--

CREATE TABLE IF NOT EXISTS `md_user_groups` (
  `id` smallint(4) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `protected` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(30) NOT NULL,
  `access` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `active` (`active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `md_user_groups`
--

INSERT INTO `md_user_groups` (`id`, `active`, `protected`, `name`, `access`) VALUES
(1, 1, 1, 'Администраторы', '*');

-- --------------------------------------------------------

--
-- Table structure for table `objects`
--

CREATE TABLE IF NOT EXISTS `objects` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `mother` int(10) NOT NULL DEFAULT '0',
  `class_id` smallint(4) NOT NULL DEFAULT '0',
  `fix_class` smallint(4) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(100) NOT NULL,
  `inside` int(10) NOT NULL DEFAULT '0',
  `c_date` int(10) NOT NULL,
  `m_date` int(10) NOT NULL,
  `sort` bigint(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `params` (`class_id`, `active`, `mother`),
  FULLTEXT KEY `search` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `objects`
--

INSERT INTO `objects` (`id`, `mother`, `class_id`, `fix_class`, `active`, `name`, `inside`, `c_date`, `m_date`, `sort`) VALUES
(1, 0, 0, 7, 1, 'Параметры сайта', 5, 1322140757, 1330429187, 13221470698),
(2, 1, 7, 0, 1, 'Название сайта', 0, 1322147074, 1354217106, 13221470748),
(3, 1, 7, 0, 1, 'Описание сайта', 0, 1322149119, 1349085915, 132214911968),
(4, 1, 7, 0, 1, 'Ключевые слова', 0, 1322149136, 1349085923, 132214913679),
(5, 1, 7, 0, 1, 'E-mail администратора', 0, 1322149315, 1353338023, 132214931568),
(6, 1, 0, 1, 1, 'Шаблоны рассылки', 1, 1322149442, 1349085998, 132420991991),
(7, 6, 1, 0, 1, 'Основной шаблон рассылки', 0, 1331405642, 1350322839, 133140564287);
