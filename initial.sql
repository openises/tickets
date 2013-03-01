
-- start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start 

-- Dumping tables for database: Tickets_JUNE_RC_1


-- Dumping structure for table: pre_action

CREATE TABLE `pre_action` (
  `id` bigint(8) NOT NULL auto_increment ,
  `ticket_id` int(8) NOT NULL ,
  `date` datetime NULL ,
  `description` text NOT NULL ,
  `user` int(8) NULL ,
  `action_type` int(8) NULL ,
  `responder` text NULL ,
  `updated` datetime NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_action



-- Dumping structure for table: pre_allocates

CREATE TABLE `pre_allocates` (
  `id` bigint(8) NOT NULL auto_increment ,
  `group` int(4) NOT NULL ,
  `type` tinyint(1) NOT NULL ,
  `al_as_of` datetime NULL ,
  `al_status` int(4) NULL ,
  `resource_id` int(4) NULL ,
  `sys_comments` varchar(64) NULL ,
  `user_id` int(4) NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_allocates

INSERT INTO `pre_allocates` VALUES('1', '1', '4', '2012-06-21 11:35:39', '0', '1', 'Updated to Regional capability by upgrade routine', '0');
INSERT INTO `pre_allocates` VALUES('2', '1', '4', '2012-06-21 11:35:39', '0', '2', 'Updated to Regional capability by upgrade routine', '0');
INSERT INTO `pre_allocates` VALUES('3', '1', '2', '2012-06-21 07:36:37', '1', '5', 'Allocated to Group', '1');
INSERT INTO `pre_allocates` VALUES('4', '1', '2', '2012-06-21 07:36:37', '1', '6', 'Allocated to Group', '1');


-- Dumping structure for table: pre_assigns

CREATE TABLE `pre_assigns` (
  `id` bigint(8) NOT NULL auto_increment ,
  `as_of` datetime NULL ,
  `status_id` int(4) NULL ,
  `ticket_id` int(4) NULL ,
  `responder_id` int(4) NULL ,
  `comments` varchar(64) NULL ,
  `start_miles` int(8) NULL ,
  `on_scene_miles` int(8) NULL ,
  `end_miles` int(8) NULL ,
  `user_id` int(4) NOT NULL ,
  `dispatched` datetime NULL ,
  `responding` datetime NULL ,
  `clear` datetime NULL ,
  `on_scene` datetime NULL ,
  `facility_id` int(8) NULL ,
  `rec_facility_id` int(8) NULL ,
  `u2fenr` datetime NULL ,
  `u2farr` datetime NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_assigns



-- Dumping structure for table: pre_captions

CREATE TABLE `pre_captions` (
  `id` int(7) NOT NULL auto_increment ,
  `capt` varchar(64) NOT NULL ,
  `repl` varchar(64) NOT NULL ,
  `_by` int(7) NOT NULL ,
  `_from` varchar(16) NULL ,
  `_on` timestamp NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_captions

INSERT INTO `pre_captions` VALUES('1', '911 Contacted', '911 Contacted', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('2', 'A', 'A', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('3', 'About this version ...', 'About this version ...', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('4', 'Add Action', 'Add Action', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('5', 'Add Facility', 'Add Facility', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('6', 'Add note', 'Add note', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('7', 'Add patient', 'Add patient', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('8', 'Add Unit', 'Add Unit', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('9', 'Add user', 'Add user', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('10', 'Add/Edit Notifies', 'Add/Edit Notifies', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('11', 'Addr', 'Addr', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('12', 'admin', 'admin', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('13', 'Alarm audio test', 'Alarm audio test', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('14', 'All-Tickets Notify', 'All-Tickets Notify', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('15', 'As of', 'As of', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('16', 'Board', 'Board', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('17', 'Cancel', 'Cancel', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('18', 'Capability', 'Capability', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('19', 'Change display', 'Change display', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('20', 'Chat', 'Chat', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('21', 'City', 'City', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('22', 'Clear', 'Clear', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('23', 'Close incident', 'Close incident', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('24', 'Config', 'Config', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('25', 'Constituents', 'Constituents', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('26', 'Contact email', 'Contact email', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('27', 'Contact name', 'Contact name', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('28', 'Contact phone', 'Contact phone', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('29', 'Contacts', 'Contacts', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('30', 'Current situation', 'Current situation', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('31', 'Date of birth', 'Date of birth', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('32', 'Delete Closed Tickets', 'Delete Closed Tickets', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('33', 'Description', 'Description', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('34', 'Dispatch Unit', 'Dispatch Unit', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('35', 'Dispatched', 'Dispatched', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('36', 'Disposition', 'Disposition', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('37', 'Dump DB to screen', 'Dump DB to screen', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('38', 'E-mail', 'E-mail', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('39', 'Edit My Profile', 'Edit My Profile', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('40', 'Edit Settings', 'Edit Settings', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('41', 'Email users', 'Email users', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('42', 'Fac\'s', 'Fac\'s', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('43', 'Facility arrive time', 'Facility arrive time', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('44', 'Facility clear time', 'Facility clear time', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('45', 'Facility contact', 'Facility contact', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('46', 'Facility en-route time', 'Facility en-route time', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('47', 'Facility Status', 'Facility Status', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('48', 'Facility Types', 'Facility Types', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('49', 'Facility', 'Facility', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('50', 'Full name', 'Full name', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('51', 'Gender', 'Gender', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('52', 'Handle', 'Handle', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('53', 'Help', 'Help', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('54', 'High', 'High', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('55', 'ID', 'ID', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('56', 'Incident Lat/Lng', 'Incident Lat/Lng', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('57', 'Incident name', 'Incident name', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('58', 'Incident types', 'Incident types', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('59', 'Incident', 'Incident', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('60', 'Incidents', 'Incidents', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('61', 'Insurance', 'Insurance', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('62', 'Lat/Lng', 'Lat/Lng', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('63', 'Links', 'Links', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('64', 'Location', 'Location', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('65', 'Log In', 'Log In', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('66', 'Log', 'Log', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('67', 'Logged in', 'Logged in', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('68', 'Logout', 'Logout', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('69', 'Medium', 'Medium', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('70', 'Mobile', 'Mobile', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('71', 'Module', 'Module', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('72', 'mouseover caption for help information', 'mouseover caption for help information', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('73', 'Name', 'Name', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('74', 'Nature', 'Nature', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('75', 'New', 'New', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('76', 'Next', 'Next', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('77', 'Normal', 'Normal', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('78', 'Notify', 'Notify', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('79', 'On-scene', 'On-scene', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('80', 'Opening hours', 'Opening hours', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('81', 'Optimize Database', 'Optimize Database', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('82', 'P', 'P', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('83', 'Patient', 'Patient', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('84', 'Patient ID', 'Patient ID', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('85', 'Password', 'Password', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('86', 'Perm\'s', 'Perm\'s', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('87', 'Phone', 'Phone', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('88', 'Popup', 'Popup', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('89', 'Position', 'Position', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('90', 'Primary pager', 'Primary pager', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('91', 'Print', 'Print', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('92', 'Priority', 'Priority', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('93', 'Protocol', 'Protocol', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('94', 'Reported by', 'Reported by', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('95', 'Reports', 'Reports', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('96', 'Reset Database', 'Reset Database', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('97', 'Responding', 'Responding', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('98', 'Run End', 'Run End', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('99', 'Run Start', 'Run Start', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('100', 'Scheduled Date', 'Scheduled Date', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('101', 'Search', 'Search', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('102', 'Security contact', 'Security contact', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('103', 'Security email', 'Security email', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('104', 'Security phone', 'Security phone', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('105', 'Security reqs', 'Security reqs', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('106', 'Severities', 'Severities', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('107', 'Situation', 'Situation', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('108', 'SOP\'s', 'SOP\'s', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('109', 'Sort', 'Sort', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('110', 'St', 'St', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('111', 'Status', 'Status', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('112', 'Synopsis', 'Synopsis', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('113', 'This Call', 'This Call', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('114', 'Time', 'Time', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('115', 'Type', 'Type', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('116', 'U', 'U', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('117', 'Unit status types', 'Unit status types', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('118', 'Unit types', 'Unit types', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('119', 'Unit', 'Unit', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('120', 'Units', 'Units', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('121', 'Updated', 'Updated', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('122', 'User', 'User', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('123', 'USNG', 'USNG', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('124', 'Written', 'Written', '0', '\'\'', '2012-06-21 07:35:37');
INSERT INTO `pre_captions` VALUES('125', 'Facs', 'Facs', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_captions` VALUES('126', 'mouseover caption for help informati', 'mouseover caption for help informati', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_captions` VALUES('127', 'Region', 'Region', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_captions` VALUES('128', 'Facility id', 'Facility id', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_captions` VALUES('129', 'Catchment Area', 'Catchment Area', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_captions` VALUES('130', 'Ring Fence', 'Ring Fence', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_captions` VALUES('131', 'Exclusion Zone', 'Exclusion Zone', '0', '', '2012-06-21 07:35:38');


-- Dumping structure for table: pre_certs

CREATE TABLE `pre_certs` (
  `id` int(7) NOT NULL auto_increment ,
  `certificate` varchar(48) NOT NULL ,
  `source` varchar(48) NOT NULL ,
  `by` int(7) NOT NULL ,
  `from` varchar(16) NOT NULL ,
  `on` datetime NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_certs



-- Dumping structure for table: pre_certs_x_user

CREATE TABLE `pre_certs_x_user` (
  `id` int(7) NOT NULL auto_increment ,
  `certificate_id` int(3) NOT NULL ,
  `user_id` int(4) NOT NULL ,
  `date` date NULL ,
  `comment` varchar(48) NULL ,
  `by` int(7) NOT NULL ,
  `from` varchar(16) NULL ,
  `on` datetime NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_certs_x_user



-- Dumping structure for table: pre_chat_invites

CREATE TABLE `pre_chat_invites` (
  `id` int(7) NOT NULL auto_increment ,
  `to` varchar(64) NOT NULL ,
  `_by` int(7) NOT NULL ,
  `_from` varchar(16) NOT NULL ,
  `_on` timestamp NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_chat_invites



-- Dumping structure for table: pre_chat_messages

CREATE TABLE `pre_chat_messages` (
  `id` bigint(10) unsigned NOT NULL auto_increment ,
  `message` varchar(2048) NOT NULL ,
  `when` datetime NULL ,
  `chat_room_id` int(7) NOT NULL ,
  `user_id` int(7) NOT NULL ,
  `from` varchar(16) NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_chat_messages



-- Dumping structure for table: pre_chat_rooms

CREATE TABLE `pre_chat_rooms` (
  `id` bigint(7) NOT NULL auto_increment ,
  `room` varchar(16) NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_chat_rooms



-- Dumping structure for table: pre_cities

CREATE TABLE `pre_cities` (
  `id` int(11) NOT NULL auto_increment ,
  `city_zip` int(5) unsigned zerofill NOT NULL ,
  `city_name` varchar(50) NOT NULL ,
  `city_state` char(2) NOT NULL ,
  `city_lat` double NOT NULL ,
  `city_lng` double NOT NULL ,
  `city_county` varchar(50) NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_cities



-- Dumping structure for table: pre_clones

CREATE TABLE `pre_clones` (
  `id` int(4) NOT NULL auto_increment ,
  `name` varchar(16) NULL ,
  `prefix` varchar(8) NULL ,
  `date` datetime NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_clones



-- Dumping structure for table: pre_codes

CREATE TABLE `pre_codes` (
  `id` int(7) NOT NULL auto_increment ,
  `code` varchar(20) NOT NULL ,
  `text` varchar(64) NOT NULL ,
  `sort` int(3) NOT NULL ,
  `_by` int(7) NOT NULL ,
  `_from` varchar(16) NOT NULL ,
  `_on` timestamp NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_codes

INSERT INTO `pre_codes` VALUES('1', 'ex-1', 'Instructed to return to station ASAP', '999', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_codes` VALUES('2', 'ex-2', 'Requested to contact Dispatch Central by voice', '999', '0', '', '2012-06-21 07:35:38');


-- Dumping structure for table: pre_constituents

CREATE TABLE `pre_constituents` (
  `id` bigint(7) NOT NULL auto_increment ,
  `contact` varchar(48) NOT NULL ,
  `street` varchar(48) NULL ,
  `apartment` varchar(48) NULL ,
  `city` varchar(48) NULL ,
  `state` char(2) NULL ,
  `miscellaneous` varchar(80) NULL ,
  `phone` varchar(16) NOT NULL ,
  `phone_2` varchar(16) NULL ,
  `phone_3` varchar(16) NULL ,
  `phone_4` varchar(16) NULL ,
  `email` varchar(48) NULL ,
  `lat` double NULL ,
  `lng` double NULL ,
  `updated` varchar(16) NULL ,
  `_by` int(7) NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_constituents



-- Dumping structure for table: pre_contacts

CREATE TABLE `pre_contacts` (
  `id` bigint(7) NOT NULL auto_increment ,
  `name` varchar(48) NOT NULL ,
  `organization` varchar(48) NULL ,
  `phone` varchar(24) NULL ,
  `mobile` varchar(24) NULL ,
  `email` varchar(48) NOT NULL ,
  `other` varchar(24) NULL ,
  `as-of` datetime NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_contacts



-- Dumping structure for table: pre_courses

CREATE TABLE `pre_courses` (
  `id` int(7) NOT NULL auto_increment ,
  `course` varchar(48) NOT NULL ,
  `source` varchar(48) NOT NULL ,
  `location` varchar(48) NOT NULL ,
  `duration` varchar(48) NOT NULL ,
  `basis` varchar(48) NOT NULL ,
  `by` int(7) NOT NULL ,
  `from` varchar(16) NOT NULL ,
  `on` datetime NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_courses



-- Dumping structure for table: pre_courses_x_user

CREATE TABLE `pre_courses_x_user` (
  `id` int(7) NOT NULL auto_increment ,
  `courses_id` int(4) NOT NULL ,
  `user_id` int(4) NOT NULL ,
  `date` date NULL ,
  `comment` varchar(48) NULL ,
  `by` int(7) NOT NULL ,
  `from` varchar(16) NULL ,
  `on` datetime NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_courses_x_user



-- Dumping structure for table: pre_css_day

CREATE TABLE `pre_css_day` (
  `id` bigint(8) NOT NULL auto_increment ,
  `name` tinytext NULL ,
  `value` tinytext NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_css_day

INSERT INTO `pre_css_day` VALUES('1', 'page_background', 'EFEFEF');
INSERT INTO `pre_css_day` VALUES('2', 'normal_text', '000000');
INSERT INTO `pre_css_day` VALUES('3', 'header_text', '000000');
INSERT INTO `pre_css_day` VALUES('4', 'header_background', 'EFEFEF');
INSERT INTO `pre_css_day` VALUES('5', 'titlebar_text', '000000');
INSERT INTO `pre_css_day` VALUES('6', 'links', '000099');
INSERT INTO `pre_css_day` VALUES('7', 'other_text', '000000');
INSERT INTO `pre_css_day` VALUES('8', 'legend', '000000');
INSERT INTO `pre_css_day` VALUES('9', 'row_light', 'DEE3E7');
INSERT INTO `pre_css_day` VALUES('10', 'row_light_text', '000000');
INSERT INTO `pre_css_day` VALUES('11', 'row_dark', 'EFEFEF');
INSERT INTO `pre_css_day` VALUES('12', 'row_dark_text', '000000');
INSERT INTO `pre_css_day` VALUES('13', 'row_plain', 'FFFFFF');
INSERT INTO `pre_css_day` VALUES('14', 'row_plain_text', '000000');
INSERT INTO `pre_css_day` VALUES('15', 'row_heading_background', '707070');
INSERT INTO `pre_css_day` VALUES('16', 'row_heading_text', 'FFFFFF');
INSERT INTO `pre_css_day` VALUES('17', 'row_spacer', 'FFFFFF');
INSERT INTO `pre_css_day` VALUES('18', 'form_input_background', 'FFFFFF');
INSERT INTO `pre_css_day` VALUES('19', 'form_input_text', '000000');
INSERT INTO `pre_css_day` VALUES('20', 'select_menu_background', 'FFFFFF');
INSERT INTO `pre_css_day` VALUES('21', 'select_menu_text', '000000');
INSERT INTO `pre_css_day` VALUES('22', 'label_text', '000000');


-- Dumping structure for table: pre_css_night

CREATE TABLE `pre_css_night` (
  `id` bigint(8) NOT NULL auto_increment ,
  `name` tinytext NULL ,
  `value` tinytext NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_css_night

INSERT INTO `pre_css_night` VALUES('1', 'page_background', '121212');
INSERT INTO `pre_css_night` VALUES('2', 'normal_text', 'DAEDE2');
INSERT INTO `pre_css_night` VALUES('3', 'header_text', 'DAEDE2');
INSERT INTO `pre_css_night` VALUES('4', 'header_background', '2B2B2B');
INSERT INTO `pre_css_night` VALUES('5', 'titlebar_text', 'FFFFFF');
INSERT INTO `pre_css_night` VALUES('6', 'links', '3F23F7');
INSERT INTO `pre_css_night` VALUES('7', 'other_text', 'FFFFFF');
INSERT INTO `pre_css_night` VALUES('8', 'legend', 'ECFC05');
INSERT INTO `pre_css_night` VALUES('9', 'row_light', 'BEC3C7');
INSERT INTO `pre_css_night` VALUES('10', 'row_light_text', '04043D');
INSERT INTO `pre_css_night` VALUES('11', 'row_dark', '9E9E9E');
INSERT INTO `pre_css_night` VALUES('12', 'row_dark_text', '000000');
INSERT INTO `pre_css_night` VALUES('13', 'row_plain', 'A3A3A3');
INSERT INTO `pre_css_night` VALUES('14', 'row_plain_text', '000000');
INSERT INTO `pre_css_night` VALUES('15', 'row_heading_background', '262626');
INSERT INTO `pre_css_night` VALUES('16', 'row_heading_text', 'F0F0F0');
INSERT INTO `pre_css_night` VALUES('17', 'row_spacer', 'F2E3F2');
INSERT INTO `pre_css_night` VALUES('18', 'form_input_background', 'B5B5B5');
INSERT INTO `pre_css_night` VALUES('19', 'form_input_text', '212422');
INSERT INTO `pre_css_night` VALUES('20', 'select_menu_background', 'B5B5B5');
INSERT INTO `pre_css_night` VALUES('21', 'select_menu_text', '151716');
INSERT INTO `pre_css_night` VALUES('22', 'label_text', '000000');


-- Dumping structure for table: pre_documents

CREATE TABLE `pre_documents` (
  `id` int(10) unsigned NOT NULL auto_increment ,
  `name` varchar(64) NOT NULL ,
  `status` enum('locked','unlocked','na') NOT NULL ,
  `locked_by` int(7) NOT NULL ,
  `locked_on` datetime NULL ,
  `info` tinytext NULL ,
  `keyword` varchar(64) NULL ,
  `type` varchar(64) NULL ,
  `size` int(10) unsigned NOT NULL ,
  `author` int(10) unsigned NULL ,
  `source` int(10) unsigned NULL ,
  `maintainer` int(10) unsigned NULL ,
  `revision` varchar(64) NULL ,
  `created` datetime NULL ,
  `modified` datetime NULL ,
  `by` int(7) NOT NULL ,
  `from` varchar(16) NOT NULL ,
  `on` datetime NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_documents



-- Dumping structure for table: pre_documents_log

CREATE TABLE `pre_documents_log` (
  `id` int(10) unsigned NOT NULL auto_increment ,
  `user_id` int(10) unsigned NOT NULL ,
  `document_id` int(10) unsigned NOT NULL ,
  `revision` int(10) unsigned NOT NULL ,
  `date` timestamp NOT NULL on update CURRENT_TIMESTAMP ,
  `by` int(7) NOT NULL ,
  `from` varchar(16) NOT NULL ,
  `on` datetime NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_documents_log



-- Dumping structure for table: pre_fac_status

CREATE TABLE `pre_fac_status` (
  `id` bigint(4) NOT NULL auto_increment ,
  `status_val` varchar(20) NOT NULL ,
  `description` varchar(60) NOT NULL ,
  `group` varchar(20) NULL ,
  `sort` int(11) NOT NULL ,
  `bg_color` varchar(16) NOT NULL ,
  `text_color` varchar(16) NOT NULL ,
  `_by` int(7) NOT NULL ,
  `_from` varchar(16) NOT NULL ,
  `_on` datetime NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_fac_status



-- Dumping structure for table: pre_fac_types

CREATE TABLE `pre_fac_types` (
  `id` int(11) NOT NULL auto_increment ,
  `name` varchar(48) NOT NULL ,
  `description` varchar(96) NOT NULL ,
  `icon` int(3) NOT NULL ,
  `_by` int(7) NOT NULL ,
  `_from` varchar(16) NOT NULL ,
  `_on` datetime NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_fac_types



-- Dumping structure for table: pre_facilities

CREATE TABLE `pre_facilities` (
  `id` bigint(8) NOT NULL auto_increment ,
  `name` text NULL ,
  `street` varchar(28) NULL ,
  `city` varchar(28) NULL ,
  `state` char(4) NULL ,
  `direcs` tinyint(2) NOT NULL ,
  `description` text NOT NULL ,
  `capab` varchar(255) NULL ,
  `status_id` int(4) NOT NULL ,
  `other` varchar(96) NULL ,
  `handle` varchar(24) NULL ,
  `icon_str` char(3) NULL ,
  `boundary` int(3) NOT NULL ,
  `contact_name` varchar(64) NULL ,
  `contact_email` varchar(64) NULL ,
  `contact_phone` varchar(15) NULL ,
  `security_contact` varchar(64) NULL ,
  `security_email` varchar(64) NULL ,
  `security_phone` varchar(15) NULL ,
  `opening_hours` mediumtext NULL ,
  `access_rules` mediumtext NULL ,
  `security_reqs` mediumtext NULL ,
  `pager_p` varchar(64) NULL ,
  `pager_s` varchar(64) NULL ,
  `send_no` varchar(64) NULL ,
  `lat` double NULL ,
  `lng` double NULL ,
  `type` tinyint(1) NULL ,
  `updated` datetime NULL ,
  `user_id` int(4) NULL ,
  `callsign` varchar(24) NULL ,
  `_by` int(7) NULL ,
  `_from` varchar(16) NULL ,
  `_on` datetime NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_facilities



-- Dumping structure for table: pre_hints

CREATE TABLE `pre_hints` (
  `id` int(7) NOT NULL auto_increment ,
  `tag` varchar(8) NOT NULL ,
  `hint` varchar(200) NOT NULL ,
  `_by` int(7) NOT NULL ,
  `_from` varchar(16) NULL ,
  `_on` timestamp NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_hints

INSERT INTO `pre_hints` VALUES('1', '_loca', 'Location - type in location in fields, click location on map or use *Located at Facility* menu below ', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('2', '_city', 'City - defaults to default city set in configuration. Enter City if required', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('3', '_state', 'State - US State or non-US Country code - e.g. UK for United Kingdom', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('4', '_phone', 'Phone number - for US only, you can use the lookup button to get the callers name and location using the White Pages', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('5', '_nature', 'Incident  nature or Type - Available types are set in in_types table in the configuration', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('6', '_prio', 'Incident priority - Normal, Medium or High. Affects order and coloring of incidents on Situation display', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('7', '_proto', 'Incident Protocol - this will show automatically if a protocol is set for the Incident Enter the configuration', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('8', '_synop', 'Synopsis - Details about the incident, ensure as much detail as possible is completed', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('9', '_911', '911 contact information', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('10', '_caller', 'Caller reporting the incident', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('11', '_name', 'Incident Name - Partially completed and prepend or append incident ID depending on setting. Enter an easily identifiable name.', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('12', '_booked', 'Scheduled Date. Must be set if incident Status is *Scheduled*. Sets date and time for a future booked Incident, mainly used for non immediate patient transport. Click on Radio button to show date fiel', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('13', '_facy', 'Use the first dropdown menu to select the Facility where the incident is located at, use the second dropdown menu to select the facility where persons from the Incident will be received', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('14', '_start', 'Run-start, Incident start time. Defaults to current date and time or edit by clicking padlock icon to enable date & time fields', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('15', '_status', 'Incident  Status - Open or Closed or set to Scheduled for future booked calls', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('16', '_end', 'Run-end - incident  end time. When incident is closed, click on radio button which will enable date & time fields', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('17', '_disp', 'Disposition - additional comments about incident, particularly closing it', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('18', '_coords', 'Incident Lat/Lng - set by clicking on the map for the location or by selecting location with the address fields.', '0', '', '2012-06-21 07:35:38');
INSERT INTO `pre_hints` VALUES('19', '_asof', 'Date/time of most recent incident data update', '0', '', '2012-06-21 07:35:38');


-- Dumping structure for table: pre_in_types

CREATE TABLE `pre_in_types` (
  `id` bigint(4) NOT NULL auto_increment ,
  `type` varchar(20) NOT NULL ,
  `description` varchar(60) NOT NULL ,
  `protocol` varchar(255) NULL ,
  `set_severity` int(1) NOT NULL ,
  `group` varchar(20) NULL ,
  `sort` int(11) NULL ,
  `radius` int(4) NULL ,
  `color` varchar(8) NULL ,
  `opacity` int(3) NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_in_types

INSERT INTO `pre_in_types` VALUES('1', 'examp1', 'Example one', '', '0', 'grp 1', '1', '0', '', '0');
INSERT INTO `pre_in_types` VALUES('2', 'examp2', 'Example two', '', '0', 'grp 2', '2', '0', '', '0');
INSERT INTO `pre_in_types` VALUES('3', 'MVC', 'Motor Vehicle Accident - no injuries', '', '1', 'Traffic', '', '', '', '');
INSERT INTO `pre_in_types` VALUES('4', 'Ambulance - BLS', 'Medical Response Ambulance - Basic Life Support', '', '2', 'Medical', '', '', '', '');


-- Dumping structure for table: pre_insurance

CREATE TABLE `pre_insurance` (
  `id` int(7) NOT NULL auto_increment ,
  `ins_value` varchar(64) NOT NULL ,
  `sort_order` int(3) NOT NULL ,
  `_by` int(7) NOT NULL ,
  `_from` varchar(16) NULL ,
  `_on` timestamp NOT NULL on update CURRENT_TIMESTAMP ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_insurance

INSERT INTO `pre_insurance` VALUES('1', 'Example', '0', '0', '', '2012-06-21 07:35:38');


-- Dumping structure for table: pre_log

CREATE TABLE `pre_log` (
  `id` bigint(8) NOT NULL auto_increment ,
  `who` tinyint(7) NULL ,
  `from` varchar(20) NULL ,
  `when` datetime NULL ,
  `code` tinyint(7) NOT NULL ,
  `ticket_id` int(7) NULL ,
  `responder_id` int(7) NULL ,
  `info` varchar(2048) NULL ,
  `facility` int(7) NULL ,
  `rec_facility` int(7) NULL ,
  `mileage` int(8) NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_log

INSERT INTO `pre_log` VALUES('1', '1', '127.0.0.1', '2012-06-21 07:36:44', '1', '0', '0', '1', '0', '0', '0');


-- Dumping structure for table: pre_logins

CREATE TABLE `pre_logins` (
  `id` bigint(8) NOT NULL auto_increment ,
  `ip` varchar(15) NOT NULL ,
  `salt` varchar(36) NOT NULL ,
  `intime` timestamp NOT NULL on update CURRENT_TIMESTAMP ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_logins



-- Dumping structure for table: pre_mmarkup

CREATE TABLE `pre_mmarkup` (
  `id` bigint(4) NOT NULL auto_increment ,
  `line_name` varchar(32) NOT NULL ,
  `line_status` int(2) NOT NULL ,
  `line_type` varchar(1) NULL ,
  `line_ident` varchar(10) NULL ,
  `line_cat_id` int(3) NOT NULL ,
  `line_data` varchar(4096) NOT NULL ,
  `use_with_bm` tinyint(1) NOT NULL ,
  `use_with_r` tinyint(1) NOT NULL ,
  `use_with_f` tinyint(1) NOT NULL ,
  `use_with_u_ex` tinyint(1) NOT NULL ,
  `use_with_u_rf` tinyint(1) NOT NULL ,
  `line_color` varchar(8) NULL ,
  `line_opacity` float NULL ,
  `line_width` int(2) NULL ,
  `fill_color` varchar(8) NULL ,
  `fill_opacity` float NULL ,
  `filled` int(1) NULL ,
  `_by` int(7) NOT NULL ,
  `_from` varchar(16) NULL ,
  `_on` timestamp NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_mmarkup



-- Dumping structure for table: pre_mmarkup_cats

CREATE TABLE `pre_mmarkup_cats` (
  `id` bigint(4) NOT NULL auto_increment ,
  `category` varchar(24) NOT NULL ,
  `_by` int(7) NOT NULL ,
  `_from` varchar(16) NULL ,
  `_on` timestamp NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_mmarkup_cats

INSERT INTO `pre_mmarkup_cats` VALUES('1', 'Region Boundary', '1', 'install routine', '2012-06-21 11:35:38');
INSERT INTO `pre_mmarkup_cats` VALUES('2', 'Banners', '1', 'install routine', '2012-06-21 11:35:38');
INSERT INTO `pre_mmarkup_cats` VALUES('3', 'Facility Catchment', '1', 'install routine', '2012-06-21 11:35:38');
INSERT INTO `pre_mmarkup_cats` VALUES('4', 'Ring Fence', '1', 'install routine', '2012-06-21 11:35:38');
INSERT INTO `pre_mmarkup_cats` VALUES('5', 'Exclusion Zone', '1', 'install routine', '2012-06-21 11:35:38');


-- Dumping structure for table: pre_notify

CREATE TABLE `pre_notify` (
  `id` bigint(8) NOT NULL auto_increment ,
  `ticket_id` int(8) NOT NULL ,
  `user` int(8) NOT NULL ,
  `execute_path` tinytext NULL ,
  `severities` int(1) NOT NULL ,
  `on_action` tinyint(1) NULL ,
  `on_ticket` tinyint(1) NULL ,
  `on_patient` tinyint(1) NULL ,
  `email_address` varchar(255) NULL ,
  `pager` varchar(255) NULL ,
  `pager_cb` varchar(96) NULL ,
  `by` int(7) NOT NULL ,
  `from` varchar(16) NOT NULL ,
  `on` datetime NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_notify



-- Dumping structure for table: pre_patient

CREATE TABLE `pre_patient` (
  `id` bigint(8) NOT NULL auto_increment ,
  `ticket_id` int(8) NOT NULL ,
  `name` varchar(32) NULL ,
  `fullname` varchar(64) NULL ,
  `dob` varchar(32) NULL ,
  `gender` int(1) NOT NULL ,
  `insurance_id` int(3) NOT NULL ,
  `facility_contact` varchar(64) NULL ,
  `facility_id` int(3) NOT NULL ,
  `date` datetime NULL ,
  `description` text NOT NULL ,
  `user` int(8) NULL ,
  `action_type` int(8) NULL ,
  `updated` datetime NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_patient



-- Dumping structure for table: pre_photos

CREATE TABLE `pre_photos` (
  `id` bigint(8) NOT NULL auto_increment ,
  `description` varchar(256) NOT NULL ,
  `ticket_id` int(7) NOT NULL ,
  `taken_by` varchar(48) NULL ,
  `taken_on` varchar(24) NULL ,
  `by` int(7) NOT NULL ,
  `on` datetime NOT NULL ,
  `from` varchar(16) NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_photos



-- Dumping structure for table: pre_pin_ctrl

CREATE TABLE `pre_pin_ctrl` (
  `id` int(7) NOT NULL auto_increment ,
  `responder_id` int(7) NOT NULL ,
  `pin` varchar(4) NOT NULL ,
  `_by` int(7) NOT NULL ,
  `_from` varchar(30) NULL ,
  `_on` timestamp NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_pin_ctrl



-- Dumping structure for table: pre_places

CREATE TABLE `pre_places` (
  `id` int(7) NOT NULL auto_increment ,
  `name` varchar(64) NULL ,
  `lat` float NULL ,
  `lon` float NULL ,
  `zoom` int(2) NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_places



-- Dumping structure for table: pre_region

CREATE TABLE `pre_region` (
  `id` bigint(8) NOT NULL auto_increment ,
  `group_name` varchar(60) NOT NULL ,
  `category` int(2) NULL ,
  `description` varchar(60) NULL ,
  `owner` int(2) NOT NULL ,
  `def_area_code` varchar(4) NULL ,
  `def_city` varchar(20) NULL ,
  `def_lat` double NULL ,
  `def_lng` double NULL ,
  `def_st` varchar(20) NULL ,
  `def_zoom` int(2) NOT NULL ,
  `boundary` int(4) NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_region

INSERT INTO `pre_region` VALUES('1', 'General', '4', 'General - group 0', '1', '', '', '', '', '10', '10', '0');


-- Dumping structure for table: pre_region_type

CREATE TABLE `pre_region_type` (
  `id` int(11) NOT NULL auto_increment ,
  `name` varchar(16) NOT NULL ,
  `description` varchar(48) NOT NULL ,
  `_on` datetime NOT NULL ,
  `_from` varchar(16) NOT NULL ,
  `_by` int(7) NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_region_type

INSERT INTO `pre_region_type` VALUES('1', 'EMS', 'Medical Services', '2011-06-17 14:21:39', '127.0.0.1', '1');
INSERT INTO `pre_region_type` VALUES('2', 'Security', 'Security Services', '2011-06-17 14:21:55', '127.0.0.1', '1');
INSERT INTO `pre_region_type` VALUES('3', 'Fire', 'Fire Services', '2011-06-17 14:22:10', '127.0.0.1', '1');
INSERT INTO `pre_region_type` VALUES('4', 'General', 'General Use', '2011-06-17 14:22:10', '127.0.0.1', '1');


-- Dumping structure for table: pre_remote_devices

CREATE TABLE `pre_remote_devices` (
  `id` bigint(64) NOT NULL auto_increment ,
  `lat` double NULL ,
  `lng` double NULL ,
  `time` datetime NOT NULL ,
  `speed` int(4) NOT NULL ,
  `altitude` int(6) NOT NULL ,
  `direction` double NOT NULL ,
  `user` varchar(64) NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_remote_devices



-- Dumping structure for table: pre_responder

CREATE TABLE `pre_responder` (
  `id` bigint(8) NOT NULL auto_increment ,
  `name` text NULL ,
  `street` varchar(28) NULL ,
  `city` varchar(28) NULL ,
  `state` char(4) NULL ,
  `phone` varchar(16) NULL ,
  `mobile` tinyint(2) NULL ,
  `direcs` tinyint(2) NOT NULL ,
  `multi` int(1) NOT NULL ,
  `aprs` tinyint(2) NOT NULL ,
  `instam` tinyint(2) NOT NULL ,
  `ogts` tinyint(2) NOT NULL ,
  `t_tracker` tinyint(2) NOT NULL ,
  `ring_fence` int(3) NOT NULL ,
  `excl_zone` int(3) NOT NULL ,
  `locatea` tinyint(2) NOT NULL ,
  `gtrack` tinyint(2) NOT NULL ,
  `glat` tinyint(2) NOT NULL ,
  `description` text NOT NULL ,
  `capab` varchar(255) NULL ,
  `un_status_id` int(4) NOT NULL ,
  `other` varchar(96) NULL ,
  `callsign` varchar(24) NULL ,
  `handle` varchar(24) NULL ,
  `icon_str` char(3) NULL ,
  `contact_name` varchar(64) NULL ,
  `contact_via` varchar(64) NULL ,
  `pager_p` varchar(64) NULL ,
  `pager_s` varchar(64) NULL ,
  `send_no` varchar(64) NULL ,
  `lat` double NULL ,
  `lng` double NULL ,
  `type` tinyint(1) NULL ,
  `updated` datetime NULL ,
  `user_id` int(4) NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_responder

INSERT INTO `pre_responder` VALUES('5', 'Responder_1', '', '', '', '', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', 'Auto entered', '', '1', '', '', 'Res1', '1', '', '', '', '', '', '0.999999', '0.999999', '1', '2012-06-21 07:36:37', '1');
INSERT INTO `pre_responder` VALUES('6', 'Responder_2', '', '', '', '', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', 'Auto entered', '', '1', '', '', 'Res2', '2', '', '', '', '', '', '0.999999', '0.999999', '1', '2012-06-21 07:36:37', '1');


-- Dumping structure for table: pre_settings

CREATE TABLE `pre_settings` (
  `id` bigint(8) NOT NULL auto_increment ,
  `name` tinytext NULL ,
  `value` varchar(512) NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_settings

INSERT INTO `pre_settings` VALUES('1', '_aprs_time', '1340278727');
INSERT INTO `pre_settings` VALUES('2', '_sleep', '5');
INSERT INTO `pre_settings` VALUES('3', '_version', '2.20 G beta - 6/21/12');
INSERT INTO `pre_settings` VALUES('4', 'abbreviate_affected', '30');
INSERT INTO `pre_settings` VALUES('5', 'abbreviate_description', '30');
INSERT INTO `pre_settings` VALUES('6', 'allow_custom_tags', '0');
INSERT INTO `pre_settings` VALUES('7', 'allow_notify', '1');
INSERT INTO `pre_settings` VALUES('8', 'auto_poll', '0');
INSERT INTO `pre_settings` VALUES('9', 'auto_route', '1');
INSERT INTO `pre_settings` VALUES('10', 'call_board', '1');
INSERT INTO `pre_settings` VALUES('11', 'chat_time', '4');
INSERT INTO `pre_settings` VALUES('12', 'closed_interval', '');
INSERT INTO `pre_settings` VALUES('13', 'date_format', 'n/j/y H:i');
INSERT INTO `pre_settings` VALUES('14', 'def_area_code', '');
INSERT INTO `pre_settings` VALUES('15', 'def_city', '');
INSERT INTO `pre_settings` VALUES('16', 'def_lat', '38.83');
INSERT INTO `pre_settings` VALUES('17', 'def_lng', '-76.74');
INSERT INTO `pre_settings` VALUES('18', 'def_st', ' MD');
INSERT INTO `pre_settings` VALUES('19', 'def_zoom', '3');
INSERT INTO `pre_settings` VALUES('20', 'def_zoom_fixed', '0');
INSERT INTO `pre_settings` VALUES('21', 'delta_mins', '240');
INSERT INTO `pre_settings` VALUES('22', 'email_reply_to', '');
INSERT INTO `pre_settings` VALUES('23', 'frameborder', '1');
INSERT INTO `pre_settings` VALUES('24', 'framesize', '50');
INSERT INTO `pre_settings` VALUES('25', 'gmaps_api_key', 'ABQIAAAAiLlX5dJnXCkZR5Yil2cQ5BRi_j0U6kJrkFvY4-OX2XYmEAa76BSxM3tBbKeopztUxxRu-Em4ds4HHg');
INSERT INTO `pre_settings` VALUES('26', 'group_or_dispatch', '0');
INSERT INTO `pre_settings` VALUES('27', 'guest_add_ticket', '0');
INSERT INTO `pre_settings` VALUES('28', 'host', 'www.yourdomain.com');
INSERT INTO `pre_settings` VALUES('29', 'instam_key', '');
INSERT INTO `pre_settings` VALUES('30', 'kml_files', '1');
INSERT INTO `pre_settings` VALUES('31', 'lat_lng', '0');
INSERT INTO `pre_settings` VALUES('32', 'link_capt', '');
INSERT INTO `pre_settings` VALUES('33', 'link_url', '');
INSERT INTO `pre_settings` VALUES('34', 'login_banner', 'Welcome to Tickets - an Open Source Dispatch System');
INSERT INTO `pre_settings` VALUES('35', 'map_caption', 'Your area');
INSERT INTO `pre_settings` VALUES('36', 'map_height', '512');
INSERT INTO `pre_settings` VALUES('37', 'map_width', '512');
INSERT INTO `pre_settings` VALUES('38', 'military_time', '1');
INSERT INTO `pre_settings` VALUES('39', 'msg_text_1', '');
INSERT INTO `pre_settings` VALUES('40', 'msg_text_2', '');
INSERT INTO `pre_settings` VALUES('41', 'msg_text_3', '');
INSERT INTO `pre_settings` VALUES('42', 'quick', '0');
INSERT INTO `pre_settings` VALUES('43', 'restrict_user_add', '0');
INSERT INTO `pre_settings` VALUES('44', 'restrict_user_tickets', '0');
INSERT INTO `pre_settings` VALUES('45', 'serial_no_ap', '1');
INSERT INTO `pre_settings` VALUES('46', 'situ_refr', '');
INSERT INTO `pre_settings` VALUES('47', 'terrain', '1');
INSERT INTO `pre_settings` VALUES('48', 'ticket_per_page', '0');
INSERT INTO `pre_settings` VALUES('49', 'ticket_table_width', '640');
INSERT INTO `pre_settings` VALUES('50', 'UTM', '0');
INSERT INTO `pre_settings` VALUES('51', 'validate_email', '1');
INSERT INTO `pre_settings` VALUES('52', 'wp_key', '729c1a751fd3d2428cfe2a7b43442c64');
INSERT INTO `pre_settings` VALUES('53', 'internet', '1');
INSERT INTO `pre_settings` VALUES('54', 'smtp_acct', '');
INSERT INTO `pre_settings` VALUES('55', 'email_from', '');
INSERT INTO `pre_settings` VALUES('56', 'gtrack_url', '');
INSERT INTO `pre_settings` VALUES('57', 'maptype', '1');
INSERT INTO `pre_settings` VALUES('58', 'locale', '0');
INSERT INTO `pre_settings` VALUES('59', 'func_key1', 'http://openises.sourceforge.net/,Open ISES');
INSERT INTO `pre_settings` VALUES('60', 'func_key2', '');
INSERT INTO `pre_settings` VALUES('61', 'func_key3', '');
INSERT INTO `pre_settings` VALUES('62', 'reverse_geo', '0');
INSERT INTO `pre_settings` VALUES('63', 'logo', 't.png');
INSERT INTO `pre_settings` VALUES('64', 'pie_charts', '300/450/300');
INSERT INTO `pre_settings` VALUES('65', 'title_string', 'My Very Own Example Site');
INSERT INTO `pre_settings` VALUES('66', 'regions_control', '0');
INSERT INTO `pre_settings` VALUES('67', 'sound_wav', 'aooga.wav');
INSERT INTO `pre_settings` VALUES('68', 'sound_mp3', 'phonesring.mp3');
INSERT INTO `pre_settings` VALUES('69', 'disp_stat', 'D/R/O/FE/FA/Clear');
INSERT INTO `pre_settings` VALUES('70', 'oper_can_edit', '0');
INSERT INTO `pre_settings` VALUES('71', '_inc_num', 'YTo2OntpOjA7czoxOiIwIjtpOjE7czowOiIiO2k6MjtzOjA6IiI7aTozO3M6MDoiIjtpOjQ7czoxOiIwIjtpOjU7czoyOiIxMiI7fQ==');
INSERT INTO `pre_settings` VALUES('72', '_cloud', '0');
INSERT INTO `pre_settings` VALUES('73', 'aprs_fi_key', '');
INSERT INTO `pre_settings` VALUES('74', 'ogts_info', '');


-- Dumping structure for table: pre_skills

CREATE TABLE `pre_skills` (
  `id` int(7) NOT NULL auto_increment ,
  `skill` varchar(48) NOT NULL ,
  `by` int(7) NOT NULL ,
  `from` varchar(16) NOT NULL ,
  `on` datetime NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_skills



-- Dumping structure for table: pre_skills_x_user

CREATE TABLE `pre_skills_x_user` (
  `id` int(7) NOT NULL auto_increment ,
  `skills_id` int(3) NOT NULL ,
  `user_id` int(4) NOT NULL ,
  `level` enum('b','m','h','x','na') NOT NULL ,
  `comment` varchar(48) NULL ,
  `by` int(7) NOT NULL ,
  `from` varchar(16) NULL ,
  `on` datetime NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_skills_x_user



-- Dumping structure for table: pre_stats_type

CREATE TABLE `pre_stats_type` (
  `st_id` int(2) NOT NULL auto_increment ,
  `name` varchar(64) NOT NULL ,
  `stat_type` varchar(3) NOT NULL ,
  PRIMARY KEY  (`st_id`)
);


-- Dumping data for table: pre_stats_type

INSERT INTO `pre_stats_type` VALUES('1', 'Number of Open Tickets', 'int');
INSERT INTO `pre_stats_type` VALUES('2', 'Tickets not Assigned', 'int');
INSERT INTO `pre_stats_type` VALUES('3', 'Units Assgnd not Responding', 'int');
INSERT INTO `pre_stats_type` VALUES('4', 'Units Respg Not On Scene', 'int');
INSERT INTO `pre_stats_type` VALUES('5', 'Units On Scene', 'int');
INSERT INTO `pre_stats_type` VALUES('6', 'Average Time to Dispatch', 'avg');
INSERT INTO `pre_stats_type` VALUES('7', 'Average Dispatched to Responding', 'avg');
INSERT INTO `pre_stats_type` VALUES('8', 'Average Dispatched to On Scene', 'avg');
INSERT INTO `pre_stats_type` VALUES('9', 'Average Time Ticket Open', 'avg');
INSERT INTO `pre_stats_type` VALUES('10', 'Number of available Responders', 'int');
INSERT INTO `pre_stats_type` VALUES('11', 'Average time to close ticket', 'avg');


-- Dumping structure for table: pre_team_types

CREATE TABLE `pre_team_types` (
  `id` int(7) NOT NULL auto_increment ,
  `type` varchar(48) NOT NULL ,
  `comment` varchar(48) NOT NULL ,
  `by` int(7) NOT NULL ,
  `from` varchar(16) NOT NULL ,
  `on` datetime NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_team_types



-- Dumping structure for table: pre_teams

CREATE TABLE `pre_teams` (
  `id` int(7) NOT NULL auto_increment ,
  `team` varchar(48) NOT NULL ,
  `sub-group` varchar(48) NOT NULL ,
  `ttypes_id` int(7) NOT NULL ,
  `mission` varchar(48) NOT NULL ,
  `leader` int(4) NOT NULL ,
  `leader_dpty` int(4) NOT NULL ,
  `formed` date NULL ,
  `by` int(7) NOT NULL ,
  `from` varchar(16) NOT NULL ,
  `on` datetime NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_teams



-- Dumping structure for table: pre_teams_x_user

CREATE TABLE `pre_teams_x_user` (
  `id` int(7) NOT NULL auto_increment ,
  `teams_id` int(4) NOT NULL ,
  `member_id` int(7) NOT NULL ,
  `status` int(2) NULL ,
  `date_a` date NULL ,
  `date_e` date NULL ,
  `comment` varchar(48) NULL ,
  `by` int(7) NULL ,
  `from` varchar(16) NULL ,
  `on` datetime NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_teams_x_user



-- Dumping structure for table: pre_ticket

CREATE TABLE `pre_ticket` (
  `id` bigint(8) NOT NULL auto_increment ,
  `in_types_id` int(4) NOT NULL ,
  `contact` varchar(48) NOT NULL ,
  `street` varchar(96) NULL ,
  `city` varchar(32) NULL ,
  `state` char(4) NULL ,
  `phone` varchar(16) NULL ,
  `facility` int(4) NULL ,
  `rec_facility` int(4) NULL ,
  `lat` double NULL ,
  `lng` double NULL ,
  `date` datetime NULL ,
  `problemstart` datetime NULL ,
  `problemend` datetime NULL ,
  `scope` text NOT NULL ,
  `affected` text NULL ,
  `description` text NOT NULL ,
  `comments` text NULL ,
  `nine_one_one` varchar(96) NULL ,
  `status` tinyint(1) NOT NULL ,
  `owner` tinyint(4) NOT NULL ,
  `severity` int(2) NOT NULL ,
  `updated` datetime NULL ,
  `booked_date` datetime NULL ,
  `_by` int(7) NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_ticket



-- Dumping structure for table: pre_titles

CREATE TABLE `pre_titles` (
  `id` int(7) NOT NULL auto_increment ,
  `title` varchar(24) NOT NULL ,
  `by` int(7) NOT NULL ,
  `from` varchar(16) NOT NULL ,
  `on` datetime NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_titles



-- Dumping structure for table: pre_tracks

CREATE TABLE `pre_tracks` (
  `id` bigint(7) NOT NULL auto_increment ,
  `packet_id` varchar(48) NULL ,
  `source` varchar(96) NULL ,
  `latitude` double NULL ,
  `longitude` double NULL ,
  `speed` int(8) NULL ,
  `course` int(8) NULL ,
  `altitude` int(8) NULL ,
  `symbol_table` varchar(96) NULL ,
  `symbol_code` varchar(96) NULL ,
  `status` varchar(96) NULL ,
  `closest_city` varchar(200) NULL ,
  `mapserver_url_street` varchar(200) NULL ,
  `mapserver_url_regional` varchar(200) NULL ,
  `packet_date` datetime NULL ,
  `updated` datetime NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_tracks



-- Dumping structure for table: pre_tracks_hh

CREATE TABLE `pre_tracks_hh` (
  `id` bigint(7) NOT NULL auto_increment ,
  `source` varchar(96) NULL ,
  `latitude` double NULL ,
  `longitude` double NULL ,
  `speed` int(8) NULL ,
  `course` int(8) NULL ,
  `altitude` int(8) NULL ,
  `utc_stamp` bigint(12) NULL ,
  `status` varchar(96) NULL ,
  `closest_city` varchar(200) NULL ,
  `updated` datetime NOT NULL ,
  `from` varchar(16) NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_tracks_hh



-- Dumping structure for table: pre_un_status

CREATE TABLE `pre_un_status` (
  `id` bigint(4) NOT NULL auto_increment ,
  `status_val` varchar(20) NOT NULL ,
  `description` varchar(60) NOT NULL ,
  `dispatch` int(1) NOT NULL ,
  `hide` enum('n','y') NOT NULL ,
  `group` varchar(20) NULL ,
  `sort` int(11) NOT NULL ,
  `bg_color` varchar(16) NOT NULL ,
  `text_color` varchar(16) NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_un_status

INSERT INTO `pre_un_status` VALUES('1', 'available', 'Available', '0', 'n', 'av', '1', 'transparent', '#000000');
INSERT INTO `pre_un_status` VALUES('2', 'unavailable', 'Unavailable', '0', 'n', 'unav', '3', 'transparent', '#000000');
INSERT INTO `pre_un_status` VALUES('3', 'in_service', 'In service', '0', 'n', 'inserv', '0', 'transparent', '#000000');
INSERT INTO `pre_un_status` VALUES('4', 'On Duty', 'Responder on Duty', '0', 'y', 'Available', '0', '#1FFF1F', '#FFFFFF');


-- Dumping structure for table: pre_unit_types

CREATE TABLE `pre_unit_types` (
  `id` int(11) NOT NULL auto_increment ,
  `name` varchar(16) NOT NULL ,
  `description` varchar(48) NOT NULL ,
  `icon` int(3) NOT NULL ,
  `_on` datetime NOT NULL ,
  `_from` varchar(16) NOT NULL ,
  `_by` int(7) NOT NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_unit_types

INSERT INTO `pre_unit_types` VALUES('1', 'example', 'An example unit type', '3', '2009-01-28 14:13:06', '127.0.0.1', '1');
INSERT INTO `pre_unit_types` VALUES('6', '1st Responder', 'Fast Response Paramedic', '2', '2012-06-21 07:36:37', '127.0.0.1', '1');
INSERT INTO `pre_unit_types` VALUES('7', 'Trans Ambulance', 'Transport Ambulance - no emergency use', '5', '2012-06-21 07:36:37', '127.0.0.1', '1');


-- Dumping structure for table: pre_user

CREATE TABLE `pre_user` (
  `id` bigint(8) NOT NULL auto_increment ,
  `user` text NOT NULL ,
  `passwd` tinytext NOT NULL ,
  `name_l` text NULL ,
  `name_f` text NULL ,
  `name_mi` text NULL ,
  `dob` text NULL ,
  `title_id` tinyint(2) NULL ,
  `addr_street` text NULL ,
  `addr_city` text NULL ,
  `addr_st` text NULL ,
  `disp` tinyint(1) NULL ,
  `files` tinyint(1) NULL ,
  `pers` tinyint(1) NULL ,
  `teams` tinyint(1) NULL ,
  `status` enum('approved','pending','na') NOT NULL ,
  `open_at` enum('d','f','p','t') NOT NULL ,
  `ident` text NULL ,
  `info` text NULL ,
  `phone_p` text NULL ,
  `phone_s` text NULL ,
  `phone_m` text NULL ,
  `level` tinyint(1) NOT NULL ,
  `responder_id` int(7) NOT NULL ,
  `email` text NULL ,
  `email_s` text NULL ,
  `ticket_per_page` tinyint(1) NULL ,
  `sort_desc` tinyint(1) NULL ,
  `sortorder` tinytext NULL ,
  `reporting` tinyint(1) NULL ,
  `callsign` varchar(12) NULL ,
  `db_prefix` text NULL ,
  `expires` timestamp NULL ,
  `sid` varchar(40) NULL ,
  `login` timestamp NULL ,
  `_from` varchar(24) NULL ,
  `browser` varchar(40) NULL ,
  PRIMARY KEY  (`id`)
);


-- Dumping data for table: pre_user

INSERT INTO `pre_user` VALUES('1', 'admin', '21232f297a57a5a743894a0e4a801fc3', '', '', '', '', '', '', '', '', '1', '0', '0', '0', 'approved', 'd', '', 'Super-administrator', '', '', '', '0', '0', '', '', '0', '1', 'date', '0', '', 'pre_', '2012-06-21 15:38:05', 'hnbdugekd0l8f0b4am8i6mutp5', '2012-06-21 07:36:44', '127.0.0.1', 'firefox 13.0');
INSERT INTO `pre_user` VALUES('2', 'guest', '084e0343a0486ff05530df6c705c8bb4', '', '', '', '', '', '', '', '', '1', '0', '0', '0', 'approved', 'd', '', 'Guest', '', '', '', '3', '0', '', '', '0', '1', 'date', '0', '', 'pre_', '', '', '', '', '');


-- end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end 
