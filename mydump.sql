-- Dumping tables for database: tickets2_5_db


-- Dumping structure for table: action

DROP TABLE IF EXISTS `action`;
CREATE TABLE `action` (
  `id` bigint(8) NO auto_increment ,
  `ticket_id` int(8) NO ,
  `date` datetime YES ,
  `description` text NO ,
  `user` int(8) YES ,
  `action_type` int(8) YES ,
  `responder` text YES ,
  `updated` datetime YES ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: action

INSERT INTO `action` VALUES('1', '4', '2008-03-14 07:12:40', 'asasa', '1', '10', '1', '2008-03-14 07:10:00');
INSERT INTO `action` VALUES('2', '4', '2008-03-14 14:02:18', 'ads as asd asd s', '1', '10', '', '2008-03-14 14:02:00');
INSERT INTO `action` VALUES('3', '4', '2008-03-14 14:19:49', 'ads as asd asd s', '1', '10', '', '2008-03-14 14:02:00');
INSERT INTO `action` VALUES('4', '1', '2008-03-14 17:21:43', 'criptio', '1', '10', '', '2008-03-14 17:21:00');
INSERT INTO `action` VALUES('5', '1', '2008-03-14 17:35:28', 'criptio', '1', '10', '', '2008-03-14 17:35:00');
INSERT INTO `action` VALUES('6', '1', '2008-03-14 17:41:28', 'criptio', '1', '10', '', '2008-03-14 17:35:00');
INSERT INTO `action` VALUES('7', '1', '2008-03-14 17:49:19', 'Descri', '1', '10', '1', '2008-03-14 17:49:00');
INSERT INTO `action` VALUES('8', '1', '2008-03-14 17:57:32', 'Descriptio', '1', '10', '1', '2008-03-14 17:57:00');


-- Dumping structure for table: assigns

DROP TABLE IF EXISTS `assigns`;
CREATE TABLE `assigns` (
  `id` bigint(4) NO auto_increment ,
  `as_of` datetime YES ,
  `status_id` int(4) YES ,
  `ticket_id` int(4) YES ,
  `responder_id` int(4) YES ,
  `comments` varchar(64) YES ,
  `user_id` int(4) NO ,
  `dispatched` datetime YES ,
  `responding` datetime YES ,
  `clear` datetime YES ,
  `in-quarters` datetime YES ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: assigns

INSERT INTO `assigns` VALUES('3', '2008-03-13 12:37:05', '1', '1', '2', 'Old', '1', '', '', '', '');
INSERT INTO `assigns` VALUES('2', '2008-03-13 12:55:39', '1', '1', '1', 'Old', '1', '', '', '', '');
INSERT INTO `assigns` VALUES('4', '2008-03-13 12:36:20', '1', '2', '2', 'Changed', '1', '', '', '', '');
INSERT INTO `assigns` VALUES('5', '2008-03-08 13:45:05', '1', '3', '1', 'New BUT UPDATED', '1', '', '', '', '');
INSERT INTO `assigns` VALUES('6', '2008-03-10 06:09:56', '1', '4', '1', 'New', '1', '', '', '', '');


-- Dumping structure for table: chat_messages

DROP TABLE IF EXISTS `chat_messages`;
CREATE TABLE `chat_messages` (
  `id` bigint(10) unsigned NO auto_increment ,
  `message` varchar(255) NO ,
  `when` datetime YES ,
  `chat_room_id` int(7) NO ,
  `user_id` int(7) NO ,
  `from` varchar(16) NO ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: chat_messages

INSERT INTO `chat_messages` VALUES('1', 'has joined this chat.', '2008-03-08 13:50:04', '0', '1', '127.0.0.1');
INSERT INTO `chat_messages` VALUES('2', 'SAD AS DAS', '2008-03-08 13:50:17', '0', '1', '127.0.0.1');
INSERT INTO `chat_messages` VALUES('3', 'GD FGFDG DFGDF', '2008-03-08 13:51:06', '0', '1', '127.0.0.1');
INSERT INTO `chat_messages` VALUES('4', 'has left this chat.', '2008-03-08 13:51:23', '0', '1', '127.0.0.1');


-- Dumping structure for table: chat_rooms

DROP TABLE IF EXISTS `chat_rooms`;
CREATE TABLE `chat_rooms` (
  `id` bigint(7) NO auto_increment ,
  `room` varchar(16) NO ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: chat_rooms



-- Dumping structure for table: clones

DROP TABLE IF EXISTS `clones`;
CREATE TABLE `clones` (
  `id` int(4) NO auto_increment ,
  `name` varchar(16) YES ,
  `prefix` varchar(8) YES ,
  `date` datetime YES ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: clones



-- Dumping structure for table: contacts

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
  `id` bigint(7) NO auto_increment ,
  `name` varchar(48) NO ,
  `organization` varchar(48) YES ,
  `phone` varchar(24) YES ,
  `mobile` varchar(24) YES ,
  `email` varchar(48) NO ,
  `other` varchar(24) YES ,
  `as-of` datetime NO ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: contacts



-- Dumping structure for table: in_types

DROP TABLE IF EXISTS `in_types`;
CREATE TABLE `in_types` (
  `id` bigint(4) NO auto_increment ,
  `type` varchar(20) NO ,
  `description` varchar(60) YES ,
  `group` varchar(20) YES ,
  `sort` int(11) NO ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: in_types

INSERT INTO `in_types` VALUES('1', 'examp1', 'Example one', 'grp 1', '1');
INSERT INTO `in_types` VALUES('2', 'examp2', 'Example two', 'grp 1', '2');


-- Dumping structure for table: log

DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
  `id` bigint(7) NO auto_increment ,
  `who` tinyint(7) YES ,
  `from` varchar(20) YES ,
  `when` datetime YES ,
  `code` tinyint(7) NO ,
  `ticket_id` int(7) YES ,
  `responder_id` int(7) YES ,
  `info` int(4) YES ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: log

INSERT INTO `log` VALUES('1', '0', '127.0.0.1', '2008-03-14 17:20:31', '1', '0', '0', '0');
INSERT INTO `log` VALUES('2', '1', '127.0.0.1', '2008-03-14 17:21:43', '13', '4', '1', '0');
INSERT INTO `log` VALUES('3', '1', '127.0.0.1', '2008-03-14 17:35:28', '13', '5', '1', '0');
INSERT INTO `log` VALUES('4', '1', '127.0.0.1', '2008-03-14 17:41:28', '13', '6', '1', '0');
INSERT INTO `log` VALUES('5', '1', '127.0.0.1', '2008-03-14 17:49:19', '13', '7', '1', '0');
INSERT INTO `log` VALUES('6', '1', '127.0.0.1', '2008-03-14 17:57:32', '13', '8', '1', '0');
INSERT INTO `log` VALUES('7', '1', '127.0.0.1', '2008-03-14 17:59:35', '14', '4', '1', '0');
INSERT INTO `log` VALUES('8', '1', '127.0.0.1', '2008-03-14 18:01:57', '14', '5', '1', '0');
INSERT INTO `log` VALUES('9', '0', '127.0.0.1', '2008-03-15 07:18:43', '1', '0', '0', '0');


-- Dumping structure for table: notify

DROP TABLE IF EXISTS `notify`;
CREATE TABLE `notify` (
  `id` bigint(8) NO auto_increment ,
  `ticket_id` int(8) NO ,
  `user` int(8) NO ,
  `execute_path` tinytext YES ,
  `on_action` tinyint(1) YES ,
  `on_ticket` tinyint(1) YES ,
  `on_patient` tinyint(1) YES ,
  `email_address` tinytext YES ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: notify



-- Dumping structure for table: patient

DROP TABLE IF EXISTS `patient`;
CREATE TABLE `patient` (
  `id` bigint(8) NO auto_increment ,
  `ticket_id` int(8) NO ,
  `name` varchar(32) YES ,
  `date` datetime YES ,
  `description` text NO ,
  `user` int(8) YES ,
  `action_type` int(8) YES ,
  `updated` datetime YES ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: patient

INSERT INTO `patient` VALUES('1', '4', 'Nam', '2008-03-14 06:31:56', 'Description', '1', '10', '2008-03-14 06:31:00');
INSERT INTO `patient` VALUES('2', '4', 'Nam', '2008-03-14 06:33:22', 'scription', '1', '10', '2008-03-14 06:33:00');
INSERT INTO `patient` VALUES('3', '4', 'am', '2008-03-14 08:27:25', 'iption:', '1', '10', '2008-03-14 08:27:00');
INSERT INTO `patient` VALUES('4', '1', 'Nam', '2008-03-14 17:59:35', 'Description', '1', '10', '2008-03-14 17:58:00');
INSERT INTO `patient` VALUES('5', '1', 'Nam', '2008-03-14 18:01:57', 'Description', '1', '10', '2008-03-14 17:58:00');


-- Dumping structure for table: responder

DROP TABLE IF EXISTS `responder`;
CREATE TABLE `responder` (
  `id` bigint(8) NO auto_increment ,
  `name` text YES ,
  `mobile` tinyint(2) YES ,
  `description` text NO ,
  `capab` varchar(255) YES ,
  `un_status_id` int(4) NO ,
  `other` varchar(96) YES ,
  `callsign` varchar(24) YES ,
  `contact_name` varchar(64) YES ,
  `contact_via` varchar(64) YES ,
  `lat` double YES ,
  `lng` double YES ,
  `type` tinyint(1) YES ,
  `updated` datetime YES ,
  `user_id` int(4) YES ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: responder

INSERT INTO `responder` VALUES('1', 'First Unit', '0', 'Describe first unit', '', '2', '', '', '', '', '37.439974', '-77.167969', '5', '2008-03-13 13:10:44', '1');
INSERT INTO `responder` VALUES('2', 'Second unit', '0', 'Unit two', '', '1', '', '', '', '', '37.160317', '-77.34375', '5', '2008-03-13 13:11:13', '1');


-- Dumping structure for table: session

DROP TABLE IF EXISTS `session`;
CREATE TABLE `session` (
  `id` bigint(4) NO auto_increment ,
  `sess_id` varchar(40) YES ,
  `user_name` varchar(40) YES ,
  `user_id` int(4) YES ,
  `level` int(2) YES ,
  `ticket_per_page` varchar(16) YES ,
  `sortorder` varchar(16) YES ,
  `scr_width` varchar(16) YES ,
  `scr_height` varchar(16) YES ,
  `browser` varchar(100) YES ,
  `last_in` bigint(20) YES ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: session

INSERT INTO `session` VALUES('26', '2095c30cebbeb00c42538bc7fc0c1db203dec8db', 'admin', '1', '1', '0', 'date DESC ', '1280', '994', 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.8.1.12) Gecko/20080201 Firefox/2.0.0.12', '1205585587');
INSERT INTO `session` VALUES('25', '2095c30cebbeb00c42538bc7fc0c1db203dec8db', 'admin', '1', '1', '0', 'date DESC ', '1280', '994', 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.8.1.12) Gecko/20080201 Firefox/2.0.0.12', '1205440077');
INSERT INTO `session` VALUES('24', '2095c30cebbeb00c42538bc7fc0c1db203dec8db', 'admin', '1', '1', '0', 'date DESC ', '1280', '994', 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.8.1.12) Gecko/20080201 Firefox/2.0.0.12', '1205367731');
INSERT INTO `session` VALUES('27', '2095c30cebbeb00c42538bc7fc0c1db203dec8db', 'admin', '1', '1', '0', 'date DESC ', '1280', '994', 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.8.1.12) Gecko/20080201 Firefox/2.0.0.12', '1205511515');
INSERT INTO `session` VALUES('28', '2095c30cebbeb00c42538bc7fc0c1db203dec8db', 'admin', '1', '1', '0', 'date DESC ', '1280', '994', 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.8.1.12) Gecko/20080201 Firefox/2.0.0.12', '1205533231');
INSERT INTO `session` VALUES('29', '2095c30cebbeb00c42538bc7fc0c1db203dec8db', 'admin', '1', '1', '0', 'date DESC ', '1280', '994', 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.8.1.12) Gecko/20080201 Firefox/2.0.0.12', '1205583523');


-- Dumping structure for table: settings

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` bigint(8) NO auto_increment ,
  `name` tinytext YES ,
  `value` tinytext YES ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: settings

INSERT INTO `settings` VALUES('1', '_aprs_time', '0');
INSERT INTO `settings` VALUES('2', '_version', '2.5A beta');
INSERT INTO `settings` VALUES('3', 'abbreviate_affected', '30');
INSERT INTO `settings` VALUES('4', 'abbreviate_description', '65');
INSERT INTO `settings` VALUES('5', 'allow_custom_tags', '0');
INSERT INTO `settings` VALUES('6', 'allow_notify', '0');
INSERT INTO `settings` VALUES('7', 'aprs_poll', '0');
INSERT INTO `settings` VALUES('8', 'call_board', '4');
INSERT INTO `settings` VALUES('9', 'chat_time', '4');
INSERT INTO `settings` VALUES('10', 'date_format', 'n/j/y H:i');
INSERT INTO `settings` VALUES('11', 'def_city', '');
INSERT INTO `settings` VALUES('12', 'def_lat', '39.1');
INSERT INTO `settings` VALUES('13', 'def_lng', '-90.7');
INSERT INTO `settings` VALUES('14', 'def_st', '');
INSERT INTO `settings` VALUES('15', 'def_zoom', '3');
INSERT INTO `settings` VALUES('16', 'delta_mins', '0');
INSERT INTO `settings` VALUES('17', 'email_reply_to', '');
INSERT INTO `settings` VALUES('18', 'frameborder', '1');
INSERT INTO `settings` VALUES('19', 'framesize', '50');
INSERT INTO `settings` VALUES('20', 'gmaps_api_key', 'ABQIAAAAiLlX5dJnXCkZR5Yil2cQ5BRi_j0U6kJrkFvY4-OX2XYmEAa76BSxM3tBbKeopztUxxRu-Em4ds4HHg');
INSERT INTO `settings` VALUES('21', 'guest_add_ticket', '0');
INSERT INTO `settings` VALUES('22', 'host', 'www.yourdomain.com');
INSERT INTO `settings` VALUES('23', 'link_capt', '');
INSERT INTO `settings` VALUES('24', 'link_url', '');
INSERT INTO `settings` VALUES('25', 'login_banner', 'Welcome to Tickets - an Open Source Dispatch System');
INSERT INTO `settings` VALUES('26', 'map_caption', 'Your area');
INSERT INTO `settings` VALUES('27', 'map_height', '512');
INSERT INTO `settings` VALUES('28', 'map_width', '512');
INSERT INTO `settings` VALUES('29', 'military_time', '0');
INSERT INTO `settings` VALUES('30', 'restrict_user_add', '0');
INSERT INTO `settings` VALUES('31', 'restrict_user_tickets', '0');
INSERT INTO `settings` VALUES('32', 'ticket_per_page', '0');
INSERT INTO `settings` VALUES('33', 'ticket_table_width', '640');
INSERT INTO `settings` VALUES('34', 'UTM', '1');
INSERT INTO `settings` VALUES('35', 'validate_email', '1');


-- Dumping structure for table: ticket

DROP TABLE IF EXISTS `ticket`;
CREATE TABLE `ticket` (
  `id` bigint(8) NO auto_increment ,
  `in_types_id` int(4) NO ,
  `contact` varchar(48) NO ,
  `street` varchar(48) YES ,
  `city` varchar(32) YES ,
  `state` char(2) YES ,
  `phone` varchar(16) YES ,
  `lat` double YES ,
  `lng` double YES ,
  `date` datetime YES ,
  `problemstart` datetime YES ,
  `problemend` datetime YES ,
  `scope` text NO ,
  `affected` text YES ,
  `description` text NO ,
  `comments` text YES ,
  `status` tinyint(1) NO ,
  `owner` tinyint(4) NO ,
  `severity` int(2) NO ,
  `updated` datetime YES ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: ticket

INSERT INTO `ticket` VALUES('1', '1', 'Arnold Shore', '1684 Anne Ct', 'Annapolis', 'MD', '4108498721', '39.013363', '-76.544507', '2008-03-08 09:37:26', '2008-03-08 09:36:00', '', 'First name', '', 'First description', '', '2', '1', '2', '2008-03-14 17:58:00');
INSERT INTO `ticket` VALUES('2', '1', 'Arnold Shore', '1684 Anne Ct', 'Annapolis', 'MD', '4108498721', '39.013363', '-76.544507', '2008-03-08 10:00:27', '2008-03-08 09:58:00', '', '2nd name', '', '2nd descript', '', '2', '1', '1', '2008-03-08 10:00:27');
INSERT INTO `ticket` VALUES('3', '2', 'Arnold Shore', '1684 Anne Ct', 'Annapolis', 'MD', '4108498721', '39.013363', '-76.544507', '2008-03-08 10:22:09', '2008-03-08 10:20:00', '', '3rd one', '', '3rd descr', '', '1', '1', '1', '2008-03-08 13:45:05');
INSERT INTO `ticket` VALUES('4', '1', 'Reported By', '', '', '', '', '43.609234', '-79.383087', '2008-03-09 13:38:01', '2008-03-09 01:35:00', '', 'Incident name: ', '', 'Description:', '43.642567 -79.387139', '2', '1', '0', '2008-03-14 14:02:00');


-- Dumping structure for table: tracks

DROP TABLE IF EXISTS `tracks`;
CREATE TABLE `tracks` (
  `id` bigint(7) NO auto_increment ,
  `packet_id` varchar(48) YES ,
  `source` varchar(96) YES ,
  `latitude` double YES ,
  `longitude` double YES ,
  `speed` int(8) YES ,
  `course` int(8) YES ,
  `altitude` int(8) YES ,
  `symbol_table` varchar(96) YES ,
  `symbol_code` varchar(96) YES ,
  `status` varchar(96) YES ,
  `closest_city` varchar(200) YES ,
  `mapserver_url_street` varchar(200) YES ,
  `mapserver_url_regional` varchar(200) YES ,
  `packet_date` datetime YES ,
  `updated` datetime NO ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: tracks



-- Dumping structure for table: un_status

DROP TABLE IF EXISTS `un_status`;
CREATE TABLE `un_status` (
  `id` bigint(4) NO auto_increment ,
  `status_val` varchar(20) NO ,
  `description` varchar(60) YES ,
  `group` varchar(20) YES ,
  `sort` int(11) NO ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: un_status

INSERT INTO `un_status` VALUES('1', 'examp1', 'Example one', 'first', '1');
INSERT INTO `un_status` VALUES('2', 'examp2', 'Example two', 'second', '2');


-- Dumping structure for table: user

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` bigint(8) NO auto_increment ,
  `passwd` tinytext YES ,
  `hash` varchar(32) YES ,
  `info` text NO ,
  `user` text YES ,
  `level` tinyint(1) YES ,
  `email` text YES ,
  `ticket_per_page` tinyint(1) YES ,
  `sort_desc` tinyint(1) YES ,
  `sortorder` tinytext YES ,
  `reporting` tinyint(1) YES ,
  `callsign` varchar(12) YES ,
  `clone_id` int(11) NO ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: user

INSERT INTO `user` VALUES('1', '*4ACFE3202A5FF5CF467898FC58AAB1D615029441', '', 'Administrator', 'admin', '1', '', '0', '1', 'date', '0', '', '0');
INSERT INTO `user` VALUES('2', '*11DB58B0DD02E290377535868405F11E4CBEFF58', '', 'Guest', 'guest', '3', '', '0', '1', 'date', '0', '', '0');


-- MySQLDump class by CubeScripts, www.cubescripts.com

