-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Mar 19, 2019 at 03:10 PM
-- Server version: 10.1.16-MariaDB
-- PHP Version: 7.0.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `transmission`
--

-- --------------------------------------------------------

--
-- Table structure for table `colour_nodes`
--

CREATE TABLE `colour_nodes` (
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `node_id` int(11) UNSIGNED NOT NULL,
  `tree` int(11) NOT NULL,
  `parent_id` int(10) UNSIGNED NOT NULL,
  `generation` int(11) UNSIGNED NOT NULL,
  `session_number` int(10) UNSIGNED NOT NULL,
  `node_type` varchar(11) NOT NULL,
  `status` varchar(16) NOT NULL,
  `expires` timestamp NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `colour_transmissions`
--

CREATE TABLE `colour_transmissions` (
  `task_id` int(11) UNSIGNED NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `session_number` int(11) UNSIGNED NOT NULL,
  `node_id` int(11) UNSIGNED NOT NULL,
  `tree` int(11) NOT NULL,
  `parent_id` int(10) UNSIGNED NOT NULL,
  `generation` int(11) UNSIGNED NOT NULL,
  `section` varchar(32) NOT NULL,
  `cycle` int(11) NOT NULL,
  `item_order` tinyint(4) UNSIGNED NOT NULL,
  `object` char(1) NOT NULL,
  `target` varchar(20) NOT NULL,
  `input` varchar(20) NOT NULL,
  `correct` tinyint(1) NOT NULL,
  `edit_distance` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `colour_transmission_sessions`
--

CREATE TABLE `colour_transmission_sessions` (
  `session_number` int(11) UNSIGNED NOT NULL,
  `prolific_id` text NOT NULL,
  `prolific_session` text NOT NULL,
  `completion_code` char(10) NOT NULL,
  `node_id` int(11) UNSIGNED NOT NULL,
  `tree` int(11) NOT NULL,
  `parent_id` int(10) UNSIGNED NOT NULL,
  `generation` int(11) UNSIGNED NOT NULL,
  `buttons` varchar(9) NOT NULL,
  `progress` varchar(32) NOT NULL,
  `start_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `enjoy` tinyint(3) UNSIGNED NOT NULL,
  `outreach` varchar(30) NOT NULL,
  `comment` varchar(500) NOT NULL,
  `browser` tinytext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `nodes`
--

CREATE TABLE `nodes` (
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `node_id` int(11) UNSIGNED NOT NULL,
  `tree` int(11) NOT NULL,
  `parent_id` int(10) UNSIGNED NOT NULL,
  `generation` int(11) UNSIGNED NOT NULL,
  `session_number` int(10) UNSIGNED NOT NULL,
  `node_type` varchar(11) NOT NULL,
  `status` varchar(16) NOT NULL,
  `expires` timestamp NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `transmissions`
--

CREATE TABLE `transmissions` (
  `task_id` int(11) UNSIGNED NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `session_number` int(11) UNSIGNED NOT NULL,
  `node_id` int(11) UNSIGNED NOT NULL,
  `tree` int(11) NOT NULL,
  `parent_id` int(10) UNSIGNED NOT NULL,
  `generation` int(11) UNSIGNED NOT NULL,
  `section` varchar(32) NOT NULL,
  `cycle` int(11) NOT NULL,
  `item_order` tinyint(4) UNSIGNED NOT NULL,
  `object` char(1) NOT NULL,
  `target` varchar(20) NOT NULL,
  `input` varchar(20) NOT NULL,
  `correct` tinyint(1) NOT NULL,
  `edit_distance` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `transmission_sessions`
--

CREATE TABLE `transmission_sessions` (
  `session_number` int(11) UNSIGNED NOT NULL,
  `prolific_id` text NOT NULL,
  `prolific_session` text NOT NULL,
  `completion_code` char(10) NOT NULL,
  `node_id` int(11) UNSIGNED NOT NULL,
  `tree` int(11) NOT NULL,
  `parent_id` int(10) UNSIGNED NOT NULL,
  `generation` int(11) UNSIGNED NOT NULL,
  `buttons` varchar(9) NOT NULL,
  `progress` varchar(32) NOT NULL,
  `start_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `enjoy` tinyint(3) UNSIGNED NOT NULL,
  `outreach` varchar(30) NOT NULL,
  `comment` varchar(500) NOT NULL,
  `browser` tinytext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `colour_nodes`
--
ALTER TABLE `colour_nodes`
  ADD PRIMARY KEY (`node_id`);

--
-- Indexes for table `colour_transmissions`
--
ALTER TABLE `colour_transmissions`
  ADD PRIMARY KEY (`task_id`);

--
-- Indexes for table `colour_transmission_sessions`
--
ALTER TABLE `colour_transmission_sessions`
  ADD PRIMARY KEY (`session_number`);

--
-- Indexes for table `nodes`
--
ALTER TABLE `nodes`
  ADD PRIMARY KEY (`node_id`);

--
-- Indexes for table `transmissions`
--
ALTER TABLE `transmissions`
  ADD PRIMARY KEY (`task_id`);

--
-- Indexes for table `transmission_sessions`
--
ALTER TABLE `transmission_sessions`
  ADD PRIMARY KEY (`session_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `colour_nodes`
--
ALTER TABLE `colour_nodes`
  MODIFY `node_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `colour_transmissions`
--
ALTER TABLE `colour_transmissions`
  MODIFY `task_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `colour_transmission_sessions`
--
ALTER TABLE `colour_transmission_sessions`
  MODIFY `session_number` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `nodes`
--
ALTER TABLE `nodes`
  MODIFY `node_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `transmissions`
--
ALTER TABLE `transmissions`
  MODIFY `task_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `transmission_sessions`
--
ALTER TABLE `transmission_sessions`
  MODIFY `session_number` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
