-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 06, 2010 at 06:49 PM
-- Server version: 5.1.36
-- PHP Version: 5.3.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE IF NOT EXISTS `member` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `memb_name` varchar(36) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`id`, `memb_name`) VALUES
(1, 'henry'),
(2, 'irving'),
(3, 'walter'),
(4, 'susan'),
(5, 'tom'),
(6, 'doris');

-- --------------------------------------------------------

--
-- Table structure for table `responder`
--

CREATE TABLE IF NOT EXISTS `responder` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `resp_name` varchar(36) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `responder`
--

INSERT INTO `responder` (`id`, `resp_name`) VALUES
(1, 'First'),
(2, 'Second');

-- --------------------------------------------------------

--
-- Table structure for table `resp_to_memb`
--

CREATE TABLE IF NOT EXISTS `resp_to_memb` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `responder_id` int(7) NOT NULL,
  `member_id` int(7) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `resp_to_memb`
--

INSERT INTO `resp_to_memb` (`id`, `responder_id`, `member_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 2, 4);


SELECT * FROM `member` 
LEFT JOIN `resp_to_memb` ON `member`.`id` = `resp_to_memb`.`member_id`
where `resp_to_memb`.`member_id` = 1


SELECT * FROM `responder` 
LEFT JOIN `resp_to_memb` ON (`responder`.`id` = `resp_to_memb`.`responder_id`)
where `resp_to_memb`.`member_id` = 1


What team has member X? OK
SELECT * FROM `responder` 
LEFT JOIN `resp_to_memb` ON (`responder`.`id` = `resp_to_memb`.`responder_id`)
LEFT JOIN `member` ON (`member`.`id` = `resp_to_memb`.`member_id`)
where `member`.`memb_name` = 'susan'


What members does team X have?

SELECT * FROM `member`
LEFT JOIN `resp_to_memb` ON (`member`.`id` = `resp_to_memb`.`member_id`)
where `resp_to_memb`.`responder_id` = 1

What members does team X have?

SELECT * FROM `member`, responder
LEFT JOIN `resp_to_memb` ON ( `resp_to_memb`.`member_id` = `member`.`id`)
where `responder`.`resp_name` = 'First'
