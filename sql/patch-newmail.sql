-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Apr 15, 2013 at 01:25 PM
-- Server version: 5.5.27
-- PHP Version: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `acc`
--

-- --------------------------------------------------------

--
-- Table structure for table `acc_newmail`
--

DROP TABLE IF EXISTS `acc_newmail`;
CREATE TABLE IF NOT EXISTS `acc_newmail` (
  `newmail_id` int(11) NOT NULL AUTO_INCREMENT,
  `newmail_name` varchar(255) NOT NULL,
  `newmail_text` blob NOT NULL,
  `newmail_question` mediumtext NOT NULL,
  `newmail_decline` tinyint(1) NOT NULL DEFAULT '1',
  `newmail_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`newmail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Email templates';

-- Add new columns to the `closes` table
ALTER TABLE `closes` ADD COLUMN `CONCAT("ClosedNew ",newmail_id)` varbinary(45) DEFAULT NULL;
ALTER TABLE `closes` ADD COLUMN `newmail_name` varchar(255) DEFAULT NULL;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
