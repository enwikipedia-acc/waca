-- phpMyAdmin SQL Dump
-- version 3.1.4-rc2
-- http://www.phpmyadmin.net
--
-- Host: 192.168.1.3
-- Generation Time: May 31, 2009 at 12:24 AM
-- Server version: 5.1.34
-- PHP Version: 5.2.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `acc`
--

-- --------------------------------------------------------

--
-- Table structure for table `acc_cmt`
--

CREATE TABLE IF NOT EXISTS `acc_cmt` (
  `cmt_id` int(11) NOT NULL AUTO_INCREMENT,
  `cmt_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cmt_user` varchar(255) NOT NULL,
  `cmt_comment` mediumtext NOT NULL,
  `cmt_visability` varchar(255) NOT NULL,
  `pend_id` int(11) NOT NULL,
  PRIMARY KEY (`cmt_id`),
  UNIQUE KEY `cmt_id` (`cmt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
