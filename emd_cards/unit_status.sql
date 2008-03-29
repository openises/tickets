-- phpMyAdmin SQL Dump
-- version 2.10.3deb1ubuntu0.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Mar 08, 2008 at 07:51 PM
-- Server version: 5.0.45
-- PHP Version: 5.2.3-1ubuntu6.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `tickets`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `un_status`
-- 

CREATE TABLE IF NOT EXISTS `un_status` (
  `id` bigint(4) NOT NULL auto_increment,
  `status_val` varchar(16) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ID` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

-- 
-- Dumping data for table `un_status`
-- 

INSERT INTO `un_status` (`id`, `status_val`) VALUES 
(1, 'In Service'),
(2, 'Responding'),
(3, 'On Scene'),
(4, 'Enroute Hospital'),
(5, 'At Hospital'),
(6, 'Delayed'),
(7, 'Out Of Service');

