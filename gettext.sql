-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 10, 2010 at 02:19 PM
-- Server version: 5.1.36
-- PHP Version: 5.3.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `arif_big`
--

-- --------------------------------------------------------

--
-- Table structure for table `gettext`
--

CREATE TABLE IF NOT EXISTS `gettext` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `capt` varchar(36) NOT NULL,
  `repl` varchar(36) NOT NULL,
  `_by` int(7) NOT NULL DEFAULT '0',
  `_from` varchar(16) NOT NULL DEFAULT '''''',
  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=87 ;

--
-- Dumping data for table `gettext`
--

INSERT INTO `gettext` (`id`, `capt`, `repl`, `_by`, `_from`, `_on`) VALUES
(1, '911 Contacted', '911 Contacted', 0, '''''', '2010-08-07 16:51:59'),
(2, 'About this version ...', 'About this version ...', 0, '''''', '2010-08-07 16:51:59'),
(3, 'Add Action', 'Add Action', 0, '''''', '2010-08-07 16:51:59'),
(4, 'Add Facility', 'Add Facility', 0, '''''', '2010-08-07 16:51:59'),
(5, 'Add note', 'Add note', 0, '''''', '2010-08-07 16:51:59'),
(6, 'Add Patient', 'Add Patient', 0, '''''', '2010-08-07 16:51:59'),
(7, 'Add Unit', 'Add Unit', 0, '''''', '2010-08-07 16:51:59'),
(8, 'Add user', 'Add user', 0, '''''', '2010-08-07 16:51:59'),
(9, 'Add/Edit Notifies', 'Add/Edit Notifies', 0, '''''', '2010-08-07 16:51:59'),
(10, 'Addr', 'Addr', 0, '''''', '2010-08-07 16:51:59'),
(11, 'admin', 'admin', 0, '''''', '2010-08-07 16:51:59'),
(12, 'Alarm audio test', 'Alarm audio test', 0, '''''', '2010-08-07 16:51:59'),
(13, 'All-Tickets Notify', 'All-Tickets Notify', 0, '''''', '2010-08-07 16:51:59'),
(14, 'As of', 'As of', 0, '''''', '2010-08-07 16:51:59'),
(15, 'Board', 'Board', 0, '''''', '2010-08-07 16:51:59'),
(16, 'Chat', 'Chat', 0, '''''', '2010-08-07 16:51:59'),
(17, 'City', 'City', 0, '''''', '2010-08-07 16:51:59'),
(18, 'Clear', 'Clear', 0, '''''', '2010-08-07 16:51:59'),
(19, 'Close incident', 'Close incident', 0, '''''', '2010-08-07 16:51:59'),
(20, 'Config', 'Config', 0, '''''', '2010-08-07 16:51:59'),
(21, 'Constituents', 'Constituents', 0, '''''', '2010-08-07 16:51:59'),
(22, 'Contacts', 'Contacts', 0, '''''', '2010-08-07 16:51:59'),
(23, 'Current situation', 'Current situation', 0, '''''', '2010-08-07 16:51:59'),
(24, 'Delete Closed Tickets', 'Delete Closed Tickets', 0, '''''', '2010-08-07 16:51:59'),
(25, 'Dispatch Unit', 'Dispatch Unit', 0, '''''', '2010-08-07 16:51:59'),
(26, 'Dispatched', 'Dispatched', 0, '''''', '2010-08-07 16:51:59'),
(27, 'Disposition', 'Disposition', 0, '''''', '2010-08-07 16:51:59'),
(28, 'Dump DB to screen', 'Dump DB to screen', 0, '''''', '2010-08-07 16:51:59'),
(29, 'E-mail', 'E-mail', 0, '''''', '2010-08-07 16:51:59'),
(30, 'Edit My Profile', 'Edit My Profile', 0, '''''', '2010-08-07 16:51:59'),
(31, 'Edit Settings', 'Edit Settings', 0, '''''', '2010-08-07 16:51:59'),
(32, 'Email users', 'Email users', 0, '''''', '2010-08-07 16:51:59'),
(33, 'Fac''s', 'Fac''s', 0, '''''', '2010-08-07 16:51:59'),
(34, 'Facility arrive time', 'Facility arrive time', 0, '''''', '2010-08-07 16:51:59'),
(35, 'Facility clear time', 'Facility clear time', 0, '''''', '2010-08-07 16:51:59'),
(36, 'Facility en-route time', 'Facility en-route time', 0, '''''', '2010-08-07 16:51:59'),
(37, 'Facility Status', 'Facility Status', 0, '''''', '2010-08-07 16:51:59'),
(38, 'Facility Types', 'Facility Types', 0, '''''', '2010-08-07 16:51:59'),
(39, 'Facility', 'Facility', 0, '''''', '2010-08-07 16:51:59'),
(40, 'Help', 'Help', 0, '''''', '2010-08-07 16:51:59'),
(41, 'ID', 'ID', 0, '''''', '2010-08-07 16:51:59'),
(42, 'Incident Lat/Lng', 'Incident Lat/Lng', 0, '''''', '2010-08-07 16:51:59'),
(43, 'Incident name', 'Incident name', 0, '''''', '2010-08-07 16:51:59'),
(44, 'Incident types', 'Incident types', 0, '''''', '2010-08-07 16:51:59'),
(45, 'Incident', 'Incident', 0, '''''', '2010-08-07 16:51:59'),
(46, 'Links', 'Links', 0, '''''', '2010-08-07 16:51:59'),
(47, 'Location', 'Location', 0, '''''', '2010-08-07 16:51:59'),
(48, 'Log', 'Log', 0, '''''', '2010-08-07 16:51:59'),
(49, 'Logged in', 'Logged in', 0, '''''', '2010-08-07 16:51:59'),
(50, 'Logout', 'Logout', 0, '''''', '2010-08-07 16:51:59'),
(51, 'Module', 'Module', 0, '''''', '2010-08-07 16:51:59'),
(52, 'mouseover caption for help informati', 'mouseover caption for help informati', 0, '''''', '2010-08-07 16:51:59'),
(53, 'Nature', 'Nature', 0, '''''', '2010-08-07 16:51:59'),
(54, 'New', 'New', 0, '''''', '2010-08-07 16:51:59'),
(55, 'Notify', 'Notify', 0, '''''', '2010-08-07 16:51:59'),
(56, 'On-scene', 'On-scene', 0, '''''', '2010-08-07 16:51:59'),
(57, 'Optimize Database', 'Optimize Database', 0, '''''', '2010-08-07 16:51:59'),
(58, 'Perm''s', 'Perm''s', 0, '''''', '2010-08-07 16:51:59'),
(59, 'Phone', 'Phone', 0, '''''', '2010-08-07 16:51:59'),
(60, 'Popup', 'Popup', 0, '''''', '2010-08-07 16:51:59'),
(61, 'Position', 'Position', 0, '''''', '2010-08-07 16:51:59'),
(62, 'Print', 'Print', 0, '''''', '2010-08-07 16:51:59'),
(63, 'Protocol', 'Protocol', 0, '''''', '2010-08-07 16:51:59'),
(64, 'Reported by', 'Reported by', 0, '''''', '2010-08-07 16:51:59'),
(65, 'Reports', 'Reports', 0, '''''', '2010-08-07 16:51:59'),
(66, 'Reset Database', 'Reset Database', 0, '''''', '2010-08-07 16:51:59'),
(67, 'Responding', 'Responding', 0, '''''', '2010-08-07 16:51:59'),
(68, 'Run End', 'Run End', 0, '''''', '2010-08-07 16:51:59'),
(69, 'Run Start', 'Run Start', 0, '''''', '2010-08-07 16:51:59'),
(70, 'Scheduled Date', 'Scheduled Date', 0, '''''', '2010-08-07 16:51:59'),
(71, 'Search', 'Search', 0, '''''', '2010-08-07 16:51:59'),
(72, 'Situation', 'Situation', 0, '''''', '2010-08-07 16:51:59'),
(73, 'SOP''s', 'SOP''s', 0, '''''', '2010-08-07 16:51:59'),
(74, 'Sort', 'Sort', 0, '''''', '2010-08-07 16:51:59'),
(75, 'St', 'St', 0, '''''', '2010-08-07 16:51:59'),
(76, 'Status', 'Status', 0, '''''', '2010-08-07 16:51:59'),
(77, 'Synopsis', 'Synopsis', 0, '''''', '2010-08-07 16:51:59'),
(78, 'This Call', 'This Call', 0, '''''', '2010-08-07 16:51:59'),
(79, 'Time', 'Time', 0, '''''', '2010-08-07 16:51:59'),
(80, 'Type', 'Type', 0, '''''', '2010-08-07 16:51:59'),
(81, 'Unit status types', 'Unit status types', 0, '''''', '2010-08-07 16:51:59'),
(82, 'Unit types', 'Unit types', 0, '''''', '2010-08-07 16:51:59'),
(83, 'Unit', 'Unit', 0, '''''', '2010-08-07 16:51:59'),
(84, 'Units', 'Units', 0, '''''', '2010-08-07 16:51:59'),
(85, 'Updated', 'Updated', 0, '''''', '2010-08-07 16:51:59'),
(86, 'USNG', 'USNG', 0, '''''', '2010-08-07 16:51:59');
