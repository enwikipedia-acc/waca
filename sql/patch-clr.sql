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
-- Table structure for table `acc_clr`
--

DROP TABLE IF EXISTS `acc_clr`;
CREATE TABLE IF NOT EXISTS `acc_clr` (
  `clr_id` int(11) NOT NULL AUTO_INCREMENT,
  `clr_desc` varchar(255) NOT NULL,
  `clr_text` blob NOT NULL,
  `clr_question` mediumtext NOT NULL,
  `clr_decline` tinyint(1) NOT NULL DEFAULT '1',
  `clr_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`clr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Close reasons' AUTO_INCREMENT=8 ;

-- Add new columns to the `closes` table
ALTER TABLE `closes` ADD COLUMN `CONCAT("ClosedNew ",clr_id)` varbinary(45) DEFAULT NULL;
ALTER TABLE `closes` ADD COLUMN `clr_desc` varchar(255) DEFAULT NULL;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
