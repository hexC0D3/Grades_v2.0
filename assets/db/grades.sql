-- phpMyAdmin SQL Dump
-- version 4.4.12
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 26. Jul 2015 um 08:12
-- Server-Version: 5.6.22
-- PHP-Version: 5.5.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `grades`
--

DELIMITER $$
--
-- Prozeduren
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `getParentGroups`(IN `input_group_id` BIGINT(20) UNSIGNED, OUT `group_ids` VARCHAR(64))
BEGIN
	
	SET group_ids = ""; /*Init output value*/
	SET @query_group_id = input_group_id; /*Init group id value*/
	SET @count = -1;

	
	WHILE (@count != 0) DO
		IF (@count = -1) THEN
			SET group_ids = @query_group_id;
		ELSE
			SET group_ids = CONCAT(group_ids, ",", @query_group_id);
		END IF;
	
		SELECT @query_group_id:=group_id, @count:=COUNT(*) FROM group_relations WHERE member_type = 2 AND member_id=@query_group_id;
		
	END WHILE;

	END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `events`
--

CREATE TABLE IF NOT EXISTS `events` (
  `id` bigint(20) unsigned NOT NULL,
  `title` varchar(256) NOT NULL,
  `type_id` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `events`
--

INSERT INTO `events` (`id`, `title`, `type_id`) VALUES
(9, 'MaturprÃ¼fung', 4),
(10, 'Test Event', 4),
(11, 'LektÃ¼re', 4),
(12, 'Mathematik, Donnerstag', 2),
(13, 'Trigonometrie', 1),
(14, 'Trigonometrie Arbeitsblatt 1', 3);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `event_options`
--

CREATE TABLE IF NOT EXISTS `event_options` (
  `id` bigint(20) unsigned NOT NULL,
  `event_id` bigint(20) unsigned NOT NULL,
  `event_type_option_id` bigint(20) unsigned NOT NULL,
  `value` varchar(512) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `event_options`
--

INSERT INTO `event_options` (`id`, `event_id`, `event_type_option_id`, `value`) VALUES
(3, 9, 6, '1'),
(4, 9, 7, '1435881600'),
(5, 9, 8, '1436054400'),
(7, 10, 6, '1'),
(8, 10, 7, '1436227200'),
(9, 10, 8, '1436400000'),
(10, 11, 6, '0'),
(11, 11, 7, '1438231800'),
(12, 11, 8, '1438259100'),
(13, 12, 3, '1437636900'),
(14, 12, 4, '1437645900'),
(15, 12, 2, '1'),
(16, 13, 1, '12'),
(17, 13, 11, '1'),
(18, 13, 12, '1437602400'),
(19, 14, 14, '12'),
(20, 14, 15, 'Es muss bla und bla erledigt werden. Tipp: none'),
(21, 14, 16, '1438207200');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `event_types`
--

CREATE TABLE IF NOT EXISTS `event_types` (
  `id` bigint(20) unsigned NOT NULL,
  `title` varchar(256) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `event_types`
--

INSERT INTO `event_types` (`id`, `title`) VALUES
(1, 'test'),
(2, 'lesson'),
(3, 'task'),
(4, 'event');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `event_type_options`
--

CREATE TABLE IF NOT EXISTS `event_type_options` (
  `id` bigint(20) unsigned NOT NULL,
  `event_type_id` bigint(20) unsigned NOT NULL,
  `option_key` varchar(256) NOT NULL,
  `input_data_type` varchar(32) NOT NULL,
  `required` tinyint(1) NOT NULL,
  `options` varchar(1024) NOT NULL,
  `description_translation_key` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `event_type_options`
--

INSERT INTO `event_type_options` (`id`, `event_type_id`, `option_key`, `input_data_type`, `required`, `options`, `description_translation_key`) VALUES
(1, 1, 'lesson_id', 'event_id:lesson', 1, '{"input_type":"event:lesson"}', 'DYNAMIC_EVENT_TYPE_OPTIONS_TEST_LESSON_ID_DESC'),
(2, 2, 'lesson_repetition_interval', 'int', 1, '{"input_type":"select","select":[{"value":0, "title_tanslation_key":"DYNAMIC_EVENT_TYPE_OPTIONS_REPETITION_INTERVAL_0_DESC"},{"value":1, "title_tanslation_key":"DYNAMIC_EVENT_TYPE_OPTIONS_REPETITION_INTERVAL_1_DESC"}, {"value":2, "title_tanslation_key":"DYNAMIC_EVENT_TYPE_OPTIONS_REPETITION_INTERVAL_2_DESC"}]}', 'DYNAMIC_EVENT_TYPE_OPTIONS_REPETITION_INTERVAL_DESC'),
(3, 2, 'time_from', 'timestamp', 1, '{"input_type":"datepicker","time":true}', 'DYNAMIC_EVENT_TYPE_OPTIONS_LESSON_TIME_FROM_DESC'),
(4, 2, 'time_to', 'timestamp', 1, '{"input_type":"datepicker", "time":true}', 'DYNAMIC_EVENT_TYPE_OPTIONS_LESSON_TIME_TO_DESC'),
(6, 4, 'event_full_day', 'boolean', 1, '{"input_type":"checkbox"}', 'DYNAMIC_EVENT_TYPE_OPTIONS_EVENT_FULL_DAY_DESC'),
(7, 4, 'time_from', 'timestamp', 1, '{"input_type":"datepicker", "time":true}', 'DYNAMIC_EVENT_TYPE_OPTIONS_EVENT_TIME_FROM_DESC'),
(8, 4, 'time_to', 'timestamp', 1, '{"input_type":"datepicker", "time":true}', 'DYNAMIC_EVENT_TYPE_OPTIONS_EVENT_TIME_TO_DESC'),
(11, 1, 'grade_weight', 'float', 1, '{"input_type":"grade_weight"}', 'DYNAMIC_EVENT_TYPE_OPTIONS_TEST_GRADE_WEIGT_DESC'),
(12, 1, 'date', 'timestamp', 1, '{"input_type":"datepicker", "time":false}', 'DYNAMIC_EVENT_TYPE_OPTIONS_TEST_DAY_DESC'),
(14, 3, 'lesson_id', 'event_id:lesson', 1, '{"input_type":"event:lesson"}', 'DYNAMIC_EVENT_TYPE_OPTIONS_TASK_TIME_LESSON'),
(15, 3, 'description', 'text', 1, '{"input_type":"textarea"}', 'DYNAMIC_EVENT_TYPE_OPTIONS_TASK_DESC'),
(16, 3, 'date', 'timestamp', 1, '{"input_type":"datepicker", "time":false}', 'DYNAMIC_EVENT_TYPE_OPTIONS_TASK_DAY_DESC');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `grades`
--

CREATE TABLE IF NOT EXISTS `grades` (
  `id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `event_id` bigint(20) unsigned NOT NULL,
  `grade` float unsigned NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `grades`
--

INSERT INTO `grades` (`id`, `user_id`, `event_id`, `grade`) VALUES
(1, 6, 13, 5.5);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(256) NOT NULL,
  `invite_only` varchar(1) NOT NULL,
  `type_id` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `groups`
--

INSERT INTO `groups` (`id`, `name`, `invite_only`, `type_id`) VALUES
(1, 'da_school', '0', 2),
(2, 'NKSA', '0', 2),
(9, 'Mathematik', '0', 3);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `group_capabilities`
--

CREATE TABLE IF NOT EXISTS `group_capabilities` (
  `id` bigint(20) unsigned NOT NULL,
  `relation_id` bigint(20) unsigned NOT NULL,
  `capability` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `group_capabilities`
--

INSERT INTO `group_capabilities` (`id`, `relation_id`, `capability`) VALUES
(1, 2, 'manage_capabilities'),
(2, 2, 'manage_options'),
(5, 2, 'manage_members');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `group_member_types`
--

CREATE TABLE IF NOT EXISTS `group_member_types` (
  `id` bigint(20) unsigned NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `group_member_types`
--

INSERT INTO `group_member_types` (`id`, `table_name`) VALUES
(1, 'users'),
(2, 'groups'),
(3, 'events');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `group_options`
--

CREATE TABLE IF NOT EXISTS `group_options` (
  `id` bigint(20) unsigned NOT NULL,
  `group_id` bigint(20) unsigned NOT NULL,
  `group_type_option_id` bigint(20) unsigned NOT NULL,
  `value` varchar(512) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `group_options`
--

INSERT INTO `group_options` (`id`, `group_id`, `group_type_option_id`, `value`) VALUES
(9, 1, 5, '6'),
(11, 1, 4, 'KÃ¼ttigerstrasse 44 5018'),
(14, 1, 3, 'http://nksa.com'),
(17, 2, 4, 'ka'),
(18, 2, 5, '6'),
(25, 9, 7, '1'),
(26, 9, 8, 'Mathematik');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `group_relations`
--

CREATE TABLE IF NOT EXISTS `group_relations` (
  `id` bigint(20) unsigned NOT NULL,
  `member_id` bigint(20) unsigned NOT NULL,
  `group_id` bigint(20) unsigned NOT NULL,
  `member_type` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `group_relations`
--

INSERT INTO `group_relations` (`id`, `member_id`, `group_id`, `member_type`) VALUES
(2, 6, 1, 1),
(20, 9, 1, 3),
(21, 10, 1, 3),
(22, 11, 1, 3),
(25, 9, 2, 2),
(28, 2, 1, 2),
(29, 12, 9, 3),
(30, 13, 9, 3),
(31, 14, 9, 3),
(32, 6, 9, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `group_types`
--

CREATE TABLE IF NOT EXISTS `group_types` (
  `id` bigint(20) unsigned NOT NULL,
  `title` varchar(256) NOT NULL,
  `parent_group_type_id` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `group_types`
--

INSERT INTO `group_types` (`id`, `title`, `parent_group_type_id`) VALUES
(1, 'institution', 1),
(2, 'school', 1),
(3, 'subject', 2);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `group_type_options`
--

CREATE TABLE IF NOT EXISTS `group_type_options` (
  `id` bigint(20) unsigned NOT NULL,
  `group_type_id` bigint(20) unsigned NOT NULL,
  `option_key` varchar(255) NOT NULL,
  `input_data_type` varchar(32) NOT NULL,
  `required` tinyint(1) unsigned NOT NULL,
  `options` varchar(1024) NOT NULL,
  `description_translation_key` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `group_type_options`
--

INSERT INTO `group_type_options` (`id`, `group_type_id`, `option_key`, `input_data_type`, `required`, `options`, `description_translation_key`) VALUES
(3, 2, 'website', 'url', 0, '{"input_type":"url"}', 'DYNAMIC_GROUP_TYPE_OPTIONS_WEBSITE_DESC'),
(4, 2, 'address', 'text', 1, '{"input_type":"textfield"}', 'DYNAMIC_GROUP_TYPE_OPTIONS_ADDRESS_DESC'),
(5, 2, 'school_admin', 'user_id', 1, '{"input_type":"user"}', 'DYNAMIC_GROUP_TYPE_OPTIONS_SCHOOL_ADMIN_DESC'),
(7, 3, 'subject_grades_included_average', 'boolean', 1, '{"input_type":"checkbox"}', 'DYNAMIC_GROUP_TYPE_OPTIONS_SUBJECT_GRADES_INCLUDED_AVERAGE'),
(8, 3, 'subject', 'text', 1, '{"input_type":"textfield"}', 'DYNAMIC_GROUP_TYPE_OPTIONS_SUBJECT_NAME');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `login_tokens`
--

CREATE TABLE IF NOT EXISTS `login_tokens` (
  `id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `login_token` varchar(128) NOT NULL,
  `ip` varchar(64) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `login_tokens`
--

INSERT INTO `login_tokens` (`id`, `user_id`, `login_token`, `ip`) VALUES
(135, 6, 'ab40cd99e8351355332ad2eb69f78ef8', '127.0.0.1');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `notifications`
--

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint(20) unsigned NOT NULL,
  `title` varchar(64) NOT NULL,
  `message` varchar(255) NOT NULL,
  `color` varchar(6) NOT NULL,
  `date_reminder` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `event_id` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `reset_tokens`
--

CREATE TABLE IF NOT EXISTS `reset_tokens` (
  `id` int(11) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `token` varchar(256) NOT NULL,
  `ip` varchar(256) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL,
  `mail` varchar(256) NOT NULL,
  `password` varchar(256) NOT NULL,
  `user_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`id`, `mail`, `password`, `user_created`) VALUES
(6, 'me@tyratox.ch', '$2y$10$a2nFU5t0S9.ZIDCYhvuLf.DhgJ63EPRO2/G9RNlkdKutslwLuG7Fm', '2015-04-15 09:49:11');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_meta`
--

CREATE TABLE IF NOT EXISTS `user_meta` (
  `id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `user_meta_option_id` bigint(20) unsigned NOT NULL,
  `value` varchar(512) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `user_meta`
--

INSERT INTO `user_meta` (`id`, `user_id`, `user_meta_option_id`, `value`) VALUES
(3, 6, 1, 'Nico'),
(4, 6, 2, 'Hauser'),
(6, 6, 4, '890607600'),
(7, 6, 3, '0'),
(8, 6, 5, '2');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_meta_options`
--

CREATE TABLE IF NOT EXISTS `user_meta_options` (
  `id` bigint(20) unsigned NOT NULL,
  `option_key` varchar(255) NOT NULL,
  `input_data_type` varchar(32) NOT NULL,
  `required` tinyint(1) NOT NULL,
  `options` varchar(1024) NOT NULL,
  `description_translation_key` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `user_meta_options`
--

INSERT INTO `user_meta_options` (`id`, `option_key`, `input_data_type`, `required`, `options`, `description_translation_key`) VALUES
(1, 'first_name', 'text', 1, '{"input_type":"textfield"}', 'DYNAMIC_USER_OPTIONS_FIRST_NAME_DESC'),
(2, 'last_name', 'text', 1, '{"input_type":"textfield"}', 'DYNAMIC_USER_OPTIONS_LAST_NAME_DESC'),
(3, 'gender', 'int', 0, '{"input_type":"select","select":[{"value":0,"title_tanslation_key":"DYNAMIC_USER_OPTIONS_GENDER_MALE"},{"value":1,"title_tanslation_key":"DYNAMIC_USER_OPTIONS_GENDER_FEMALE"}]}', 'DYNAMIC_USER_OPTIONS_GENDER_DESC'),
(4, 'birthday', 'timestamp', 0, '{"input_type":"datepicker", "time":false}', 'DYNAMIC_USER_OPTIONS_BIRTHDAY_DESC'),
(5, 'mark_calc_method', 'int', 1, '{"input_type":"select","select":[{"value":0, "title_tanslation_key":"DYNAMIC_USER_OPTIONS_MARK_CALC_METHOD_0"},{"value":1, "title_tanslation_key":"DYNAMIC_USER_OPTIONS_MARK_CALC_METHOD_1"},{"value":2, "title_tanslation_key":"DYNAMIC_USER_OPTIONS_MARK_CALC_METHOD_2"}]}', 'DYNAMIC_USER_OPTIONS_MARK_CALC_METHOD_DESC');

-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `v_user_caps`
--
CREATE TABLE IF NOT EXISTS `v_user_caps` (
`group_id` bigint(20) unsigned
,`user_id` bigint(20) unsigned
,`caps` text
);

-- --------------------------------------------------------

--
-- Struktur des Views `v_user_caps`
--
DROP TABLE IF EXISTS `v_user_caps`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_user_caps` AS select `group_relations`.`group_id` AS `group_id`,`group_relations`.`member_id` AS `user_id`,group_concat(`group_capabilities`.`capability` separator ',') AS `caps` from (`group_capabilities` left join `group_relations` on((`group_capabilities`.`relation_id` = `group_relations`.`id`))) where (`group_relations`.`member_type` = 1) group by `group_capabilities`.`relation_id`;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `events_link_event_type_id` (`type_id`);

--
-- Indizes für die Tabelle `event_options`
--
ALTER TABLE `event_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eo_event_id` (`event_id`),
  ADD KEY `eo_event_type_option_id` (`event_type_option_id`);

--
-- Indizes für die Tabelle `event_types`
--
ALTER TABLE `event_types`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `event_type_options`
--
ALTER TABLE `event_type_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_type_options_link_event_type_id` (`event_type_id`);

--
-- Indizes für die Tabelle `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gr_user_id` (`user_id`),
  ADD KEY `gr_event_id` (`event_id`);

--
-- Indizes für die Tabelle `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `groups_link_type_id` (`type_id`);

--
-- Indizes für die Tabelle `group_capabilities`
--
ALTER TABLE `group_capabilities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `group_capabilities_link_relation_id` (`relation_id`);

--
-- Indizes für die Tabelle `group_member_types`
--
ALTER TABLE `group_member_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indizes für die Tabelle `group_options`
--
ALTER TABLE `group_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_options_link_options_id` (`group_type_option_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indizes für die Tabelle `group_relations`
--
ALTER TABLE `group_relations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `group_relations_link_member_id` (`member_id`),
  ADD KEY `group_relations_link_group_id` (`group_id`),
  ADD KEY `group_relations_link_member_type` (`member_type`);

--
-- Indizes für die Tabelle `group_types`
--
ALTER TABLE `group_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_types_link_group_type_id` (`parent_group_type_id`);

--
-- Indizes für die Tabelle `group_type_options`
--
ALTER TABLE `group_type_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `option_key` (`option_key`),
  ADD KEY `group_type_options_ibfk_1` (`group_type_id`);

--
-- Indizes für die Tabelle `login_tokens`
--
ALTER TABLE `login_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login_token` (`login_token`),
  ADD KEY `login_tokens_link_user_id` (`user_id`);

--
-- Indizes für die Tabelle `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ntfc_event_id` (`event_id`);

--
-- Indizes für die Tabelle `reset_tokens`
--
ALTER TABLE `reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reset_tokens_link_user_id` (`user_id`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mail` (`mail`);

--
-- Indizes für die Tabelle `user_meta`
--
ALTER TABLE `user_meta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_meta_ibfk_2` (`user_meta_option_id`),
  ADD KEY `user_meta_ibfk_3` (`user_id`);

--
-- Indizes für die Tabelle `user_meta_options`
--
ALTER TABLE `user_meta_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `option_key` (`option_key`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT für Tabelle `event_options`
--
ALTER TABLE `event_options`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT für Tabelle `event_types`
--
ALTER TABLE `event_types`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT für Tabelle `event_type_options`
--
ALTER TABLE `event_type_options`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT für Tabelle `grades`
--
ALTER TABLE `grades`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT für Tabelle `groups`
--
ALTER TABLE `groups`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT für Tabelle `group_capabilities`
--
ALTER TABLE `group_capabilities`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT für Tabelle `group_member_types`
--
ALTER TABLE `group_member_types`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT für Tabelle `group_options`
--
ALTER TABLE `group_options`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=27;
--
-- AUTO_INCREMENT für Tabelle `group_relations`
--
ALTER TABLE `group_relations`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=33;
--
-- AUTO_INCREMENT für Tabelle `group_types`
--
ALTER TABLE `group_types`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT für Tabelle `group_type_options`
--
ALTER TABLE `group_type_options`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT für Tabelle `login_tokens`
--
ALTER TABLE `login_tokens`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=136;
--
-- AUTO_INCREMENT für Tabelle `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `reset_tokens`
--
ALTER TABLE `reset_tokens`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT für Tabelle `user_meta`
--
ALTER TABLE `user_meta`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT für Tabelle `user_meta_options`
--
ALTER TABLE `user_meta_options`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_link_event_type_id` FOREIGN KEY (`type_id`) REFERENCES `event_types` (`id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `event_options`
--
ALTER TABLE `event_options`
  ADD CONSTRAINT `eo_event_id` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `eo_event_type_option_id` FOREIGN KEY (`event_type_option_id`) REFERENCES `event_type_options` (`id`);

--
-- Constraints der Tabelle `event_type_options`
--
ALTER TABLE `event_type_options`
  ADD CONSTRAINT `event_type_options_link_event_type_id` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `gr_event_id` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `gr_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_link_type_id` FOREIGN KEY (`type_id`) REFERENCES `group_types` (`id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `group_capabilities`
--
ALTER TABLE `group_capabilities`
  ADD CONSTRAINT `group_capabilities_link_relation_id` FOREIGN KEY (`relation_id`) REFERENCES `group_relations` (`id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `group_options`
--
ALTER TABLE `group_options`
  ADD CONSTRAINT `group_options_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`),
  ADD CONSTRAINT `group_options_link_options_id` FOREIGN KEY (`group_type_option_id`) REFERENCES `group_type_options` (`id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `group_relations`
--
ALTER TABLE `group_relations`
  ADD CONSTRAINT `group_relations_link_group_id` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `group_relations_link_member_type` FOREIGN KEY (`member_type`) REFERENCES `group_member_types` (`id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `group_types`
--
ALTER TABLE `group_types`
  ADD CONSTRAINT `group_types_link_group_type_id` FOREIGN KEY (`parent_group_type_id`) REFERENCES `group_types` (`id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `group_type_options`
--
ALTER TABLE `group_type_options`
  ADD CONSTRAINT `group_type_options_ibfk_1` FOREIGN KEY (`group_type_id`) REFERENCES `group_types` (`id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `login_tokens`
--
ALTER TABLE `login_tokens`
  ADD CONSTRAINT `login_tokens_link_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `ntfc_event_id` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `reset_tokens`
--
ALTER TABLE `reset_tokens`
  ADD CONSTRAINT `reset_tokens_link_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `user_meta`
--
ALTER TABLE `user_meta`
  ADD CONSTRAINT `user_meta_ibfk_2` FOREIGN KEY (`user_meta_option_id`) REFERENCES `user_meta_options` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_meta_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
