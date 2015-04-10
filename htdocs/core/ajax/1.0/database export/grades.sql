-- phpMyAdmin SQL Dump
-- version 4.3.6
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 10. Apr 2015 um 16:43
-- Server-Version: 5.6.22
-- PHP-Version: 5.5.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `grades`
--
CREATE DATABASE IF NOT EXISTS `grades` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `grades`;

DELIMITER $$
--
-- Prozeduren
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `getCapabilities`(IN `input_group_id` BIGINT(20) UNSIGNED, IN `input_user_id` BIGINT(20) UNSIGNED, OUT `caps` VARCHAR(512))
    READS SQL DATA
BEGIN
	
	SET caps = ''; /*Init output value*/
	SET @new_caps = '';
	SET @query_group_id = input_group_id; /*Init group id value*/
	SET @count = 1;

	
	/* Loop through group and parent group */
	WHILE (@count != 0) DO
	
		/* Get capabilities of current group */
		
		SELECT @new_caps:=group_concat(group_capabilities.capability separator ',')
			FROM group_relations
				LEFT JOIN group_capabilities ON group_relations.id=group_capabilities.relation_id
			WHERE group_relations.group_id=@query_group_id
				AND group_relations.member_id=input_user_id 
				AND group_relations.member_type=1
			GROUP BY group_capabilities.relation_id;
		
		SET caps = CONCAT(caps,",",@new_caps);
	
		SELECT @query_group_id:=group_id, @count:=COUNT(*) FROM group_relations WHERE member_type = 2 AND member_id=@query_group_id;
		
	END WHILE;

END$$

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `event_options`
--

CREATE TABLE IF NOT EXISTS `event_options` (
  `id` bigint(20) unsigned NOT NULL,
  `event_id` bigint(20) unsigned NOT NULL,
  `event_type_option_id` bigint(20) unsigned NOT NULL,
  `value` varchar(512) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `event_types`
--

CREATE TABLE IF NOT EXISTS `event_types` (
  `id` bigint(20) unsigned NOT NULL,
  `title` varchar(256) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

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

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(256) NOT NULL,
  `invite_only` varchar(1) NOT NULL,
  `type_id` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `group_capabilities`
--

CREATE TABLE IF NOT EXISTS `group_capabilities` (
  `id` bigint(20) unsigned NOT NULL,
  `relation_id` bigint(20) unsigned NOT NULL,
  `capability` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `group_member_types`
--

CREATE TABLE IF NOT EXISTS `group_member_types` (
  `id` bigint(20) unsigned NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `group_options`
--

CREATE TABLE IF NOT EXISTS `group_options` (
  `id` bigint(20) unsigned NOT NULL,
  `group_id` bigint(20) unsigned NOT NULL,
  `group_type_option_id` bigint(20) unsigned NOT NULL,
  `value` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `group_relations`
--

CREATE TABLE IF NOT EXISTS `group_relations` (
  `id` bigint(20) unsigned NOT NULL,
  `member_id` bigint(20) unsigned NOT NULL,
  `group_id` bigint(20) unsigned NOT NULL,
  `member_type` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `group_types`
--

CREATE TABLE IF NOT EXISTS `group_types` (
  `id` bigint(20) unsigned NOT NULL,
  `title` varchar(256) NOT NULL,
  `parent_group_type_id` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `login_tokens`
--

CREATE TABLE IF NOT EXISTS `login_tokens` (
  `id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `login_token` varchar(128) NOT NULL,
  `ip` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
-- Tabellenstruktur für Tabelle `subjects`
--

CREATE TABLE IF NOT EXISTS `subjects` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL,
  `mail` varchar(256) NOT NULL,
  `password` varchar(256) NOT NULL,
  `user_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_meta`
--

CREATE TABLE IF NOT EXISTS `user_meta` (
  `id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `user_meta_option_id` bigint(20) unsigned NOT NULL,
  `value` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_meta_options`
--

CREATE TABLE IF NOT EXISTS `user_meta_options` (
  `id` bigint(20) unsigned NOT NULL,
  `option_key` varchar(255) NOT NULL,
  `input_data_type` varchar(32) NOT NULL,
  `options` varchar(1024) NOT NULL,
  `description_translation_key` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`), ADD KEY `events_link_event_type_id` (`type_id`);

--
-- Indizes für die Tabelle `event_options`
--
ALTER TABLE `event_options`
  ADD PRIMARY KEY (`id`), ADD KEY `eo_event_id` (`event_id`), ADD KEY `eo_event_type_option_id` (`event_type_option_id`);

--
-- Indizes für die Tabelle `event_types`
--
ALTER TABLE `event_types`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `event_type_options`
--
ALTER TABLE `event_type_options`
  ADD PRIMARY KEY (`id`), ADD KEY `event_type_options_link_event_type_id` (`event_type_id`);

--
-- Indizes für die Tabelle `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`), ADD KEY `gr_user_id` (`user_id`), ADD KEY `gr_event_id` (`event_id`);

--
-- Indizes für die Tabelle `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`), ADD KEY `groups_link_type_id` (`type_id`);

--
-- Indizes für die Tabelle `group_capabilities`
--
ALTER TABLE `group_capabilities`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`), ADD KEY `group_capabilities_link_relation_id` (`relation_id`);

--
-- Indizes für die Tabelle `group_member_types`
--
ALTER TABLE `group_member_types`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`);

--
-- Indizes für die Tabelle `group_options`
--
ALTER TABLE `group_options`
  ADD PRIMARY KEY (`id`), ADD KEY `group_options_link_options_id` (`group_type_option_id`), ADD KEY `group_id` (`group_id`);

--
-- Indizes für die Tabelle `group_relations`
--
ALTER TABLE `group_relations`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`), ADD KEY `group_relations_link_member_id` (`member_id`), ADD KEY `group_relations_link_group_id` (`group_id`), ADD KEY `group_relations_link_member_type` (`member_type`);

--
-- Indizes für die Tabelle `group_types`
--
ALTER TABLE `group_types`
  ADD PRIMARY KEY (`id`), ADD KEY `group_types_link_group_type_id` (`parent_group_type_id`);

--
-- Indizes für die Tabelle `group_type_options`
--
ALTER TABLE `group_type_options`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `option_key` (`option_key`), ADD KEY `group_type_options_ibfk_1` (`group_type_id`);

--
-- Indizes für die Tabelle `login_tokens`
--
ALTER TABLE `login_tokens`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `login_token` (`login_token`), ADD KEY `login_tokens_link_user_id` (`user_id`);

--
-- Indizes für die Tabelle `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`), ADD KEY `ntfc_event_id` (`event_id`);

--
-- Indizes für die Tabelle `reset_tokens`
--
ALTER TABLE `reset_tokens`
  ADD PRIMARY KEY (`id`), ADD KEY `reset_tokens_link_user_id` (`user_id`);

--
-- Indizes für die Tabelle `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `mail` (`mail`);

--
-- Indizes für die Tabelle `user_meta`
--
ALTER TABLE `user_meta`
  ADD PRIMARY KEY (`id`), ADD KEY `user_meta_ibfk_2` (`user_meta_option_id`), ADD KEY `user_meta_ibfk_3` (`user_id`);

--
-- Indizes für die Tabelle `user_meta_options`
--
ALTER TABLE `user_meta_options`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `option_key` (`option_key`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT für Tabelle `event_options`
--
ALTER TABLE `event_options`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT für Tabelle `event_types`
--
ALTER TABLE `event_types`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT für Tabelle `event_type_options`
--
ALTER TABLE `event_type_options`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT für Tabelle `grades`
--
ALTER TABLE `grades`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT für Tabelle `groups`
--
ALTER TABLE `groups`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT für Tabelle `group_capabilities`
--
ALTER TABLE `group_capabilities`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT für Tabelle `group_member_types`
--
ALTER TABLE `group_member_types`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT für Tabelle `group_options`
--
ALTER TABLE `group_options`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `group_relations`
--
ALTER TABLE `group_relations`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT für Tabelle `group_types`
--
ALTER TABLE `group_types`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT für Tabelle `group_type_options`
--
ALTER TABLE `group_type_options`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT für Tabelle `login_tokens`
--
ALTER TABLE `login_tokens`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
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
-- AUTO_INCREMENT für Tabelle `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
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
