-- phpMyAdmin SQL Dump
-- version 4.0.6deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 10, 2014 at 09:45 PM
-- Server version: 5.5.35-0ubuntu0.13.10.2
-- PHP Version: 5.5.3-1ubuntu2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "-03:00";

--
-- Database: `THS`
--

-- --------------------------------------------------------

--
-- Table structure for table `branch`
--

CREATE TABLE IF NOT EXISTS `branch` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `address` varchar(100) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `category`
--

CREATE TABLE IF NOT EXISTS `category` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


--
-- Table structure for table `employee`
--

CREATE TABLE IF NOT EXISTS `employee` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `username` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `product`
--

CREATE TABLE IF NOT EXISTS `product` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `partnumber` varchar(50) DEFAULT NULL,
  `new` tinyint(1) NOT NULL,
  `description` varchar(200) NOT NULL,
  `price` int(7) DEFAULT NULL,
  `category_id` int(5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `PART` (`partnumber`,`new`),
  FOREIGN KEY (`category_id`) REFERENCES `category`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
`product_id` int( 5 ) NOT NULL ,
`branch_id` int( 1 ) NOT NULL ,
`stock` int( 5 ) NOT NULL ,
PRIMARY KEY ( `product_id` , `branch_id` ) ,
FOREIGN KEY ( `product_id` ) REFERENCES `product` ( `id` ) ON UPDATE CASCADE ON DELETE CASCADE ,
FOREIGN KEY ( `branch_id` ) REFERENCES `branch` ( `id` ) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = latin1
-- --------------------------------------------------------

--
-- Table structure for table `vehicle`
--

CREATE TABLE IF NOT EXISTS `vehicle` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(4) NOT NULL,
  `version` varchar(50) NOT NULL,
  `transmission` varchar(5) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UN` (`model`,`year`,`version`,`transmission`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `compatibility`
--

CREATE TABLE IF NOT EXISTS `compatibility` (
`product_id` int( 5 ) NOT NULL ,
`vehicle_id` int( 5 ) NOT NULL ,
PRIMARY KEY ( `product_id` , `vehicle_id` ) ,
FOREIGN KEY ( `product_id` ) REFERENCES `product` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
FOREIGN KEY ( `vehicle_id` ) REFERENCES `vehicle` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = latin1;
