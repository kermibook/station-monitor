-- MySQL dump 10.14  Distrib 5.5.32-MariaDB, for Linux (i686)
--
-- Host: localhost    Database: seismoIOT
-- ------------------------------------------------------
-- Server version	5.5.32-MariaDB

--
-- Table structure for table `daystats`
--

DROP TABLE IF EXISTS `daystats`;
CREATE TABLE `daystats` (
  `daystats_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL DEFAULT '0',
  `start_time` int(10) unsigned DEFAULT NULL,
  `availability` float(4,1) unsigned DEFAULT NULL,
  `level_aver` tinyint(4) DEFAULT NULL,
  `level_min` tinyint(4) DEFAULT NULL,
  `level_max` tinyint(4) DEFAULT NULL,
  `latency` smallint(5) unsigned DEFAULT NULL,
  `reconnect` smallint(5) unsigned DEFAULT NULL,
  `reboot` smallint(5) unsigned DEFAULT NULL,
  `handover` smallint(5) unsigned DEFAULT NULL,
  `rx` int(10) unsigned DEFAULT NULL,
  `tx` int(10) unsigned DEFAULT NULL,
  `top_cell` varchar(10) DEFAULT NULL,
  `batt_gradient` smallint(5) DEFAULT NULL,
  PRIMARY KEY (`daystats_id`),
  UNIQUE KEY `UNIQUE` (`device_id`,`start_time`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

--
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
CREATE TABLE `devices` (
  `device_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hostname` varchar(32) DEFAULT NULL,
  `description` varchar(32) DEFAULT NULL,
  `location` varchar(32) DEFAULT NULL,
  `network` varchar(15) DEFAULT NULL,
  `product` varchar(32) DEFAULT NULL,
  `firmware` varchar(32) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `imei` varchar(15) DEFAULT NULL,
  `last_rx` int(10) unsigned DEFAULT NULL,
  `last_tx` int(10) unsigned DEFAULT NULL,
  `connection_date` int(10) unsigned DEFAULT NULL,
  `last_status` tinyint(2) unsigned DEFAULT NULL,
  `last_level` tinyint(4) DEFAULT NULL,
  `health` tinyint(2) unsigned DEFAULT NULL,
  `last_time_act` int(10) unsigned DEFAULT NULL,
  `lat` varchar(10) DEFAULT NULL,
  `lon` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`device_id`),
  UNIQUE KEY `hostname` (`hostname`)
) ENGINE=MyISAM AUTO_INCREMENT=100 DEFAULT CHARSET=utf8 PACK_KEYS=0;

--
-- Table structure for table `snmperr`
--

DROP TABLE IF EXISTS `snmperr`;
CREATE TABLE `snmperr` (
  `err_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stat_id` int(10) unsigned DEFAULT NULL,
  `plmn_old` int(10) unsigned DEFAULT NULL,
  `plmn_new` int(10) unsigned DEFAULT NULL,
  `lev_old` tinyint(4) DEFAULT NULL,
  `lev_new` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`err_id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

--
-- Table structure for table `stats`
--

DROP TABLE IF EXISTS `stats`;
CREATE TABLE `stats` (
  `stat_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `latency` int(10) unsigned DEFAULT NULL,
  `uptime` int(10) unsigned DEFAULT NULL,
  `connection_date` int(10) unsigned DEFAULT NULL,
  `offline_period` int(10) unsigned DEFAULT NULL,
  `rx` int(10) unsigned DEFAULT NULL,
  `tx` int(10) unsigned DEFAULT NULL,
  `rx_diff` int(10) unsigned DEFAULT NULL,
  `tx_diff` int(10) unsigned DEFAULT NULL,
  `data_archived` int(10) DEFAULT NULL,
  `plmn` int(10) unsigned DEFAULT NULL,
  `cell` varchar(10) DEFAULT NULL,
  `channel` smallint(5) unsigned DEFAULT NULL,
  `level` tinyint(4) DEFAULT NULL,
  `signal_qly` tinyint(4) DEFAULT NULL,
  `conntype` tinyint(2) DEFAULT NULL,
  `temp_cpu` tinyint(3) DEFAULT NULL,
  `temp` tinyint(3) DEFAULT NULL,
  `hum` tinyint(3) DEFAULT NULL,
  `volt` smallint(5) DEFAULT NULL,
  `power_in` smallint(5) DEFAULT NULL, 
  `power_out` smallint(5) DEFAULT NULL,
  `status` tinyint(2) unsigned DEFAULT NULL,
  `relay0` tinyint(2) DEFAULT NULL,
  `relay1` tinyint(2) DEFAULT NULL,
  `relay2` tinyint(2) DEFAULT NULL,
  `relay3` tinyint(2) DEFAULT NULL,
  `relay4` tinyint(2) DEFAULT NULL,
  `binout0` tinyint(2) unsigned DEFAULT NULL,
  PRIMARY KEY (`stat_id`,`time`),
  KEY `device_id` (`device_id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

--
-- Table structure for table `rt_stats`
-- 

DROP TABLE IF EXISTS `rt_stats`;
CREATE TABLE `rt_stats` (
  `device_id` int(10) unsigned NOT NULL DEFAULT '0',  
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `latency` int(10) unsigned DEFAULT NULL,
  `uptime` int(10) unsigned DEFAULT NULL,
  `connection_date` int(10) unsigned DEFAULT NULL,
  `offline_period` int(10) unsigned DEFAULT NULL,
  `rx` int(10) unsigned DEFAULT NULL,
  `tx` int(10) unsigned DEFAULT NULL,
  `rx_diff` int(10) unsigned DEFAULT NULL,
  `tx_diff` int(10) unsigned DEFAULT NULL,
  `data_archived` int(10) DEFAULT NULL,
  `plmn` int(10) unsigned DEFAULT NULL,
  `cell` varchar(10) DEFAULT NULL,
  `channel` smallint(5) unsigned DEFAULT NULL,
  `level` tinyint(4) DEFAULT NULL,
  `signal_qly` tinyint(4) DEFAULT NULL,
  `conntype` tinyint(2) DEFAULT NULL,
  `temp_cpu` tinyint(3) DEFAULT NULL,
  `temp` tinyint(3) DEFAULT NULL,
  `hum` tinyint(3) DEFAULT NULL,
  `volt` smallint(5) DEFAULT NULL,
  `power_in` smallint(5) DEFAULT NULL,
  `power_out` smallint(5) DEFAULT NULL,
  `status` tinyint(2) unsigned DEFAULT NULL,
  `relay0` tinyint(2) DEFAULT NULL,
  `relay1` tinyint(2) DEFAULT NULL,
  `relay2` tinyint(2) DEFAULT NULL,
  `relay3` tinyint(2) DEFAULT NULL,
  `relay4` tinyint(2) DEFAULT NULL,
  `binout0` tinyint(2) unsigned DEFAULT NULL,
  PRIMARY KEY (`device_id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

-- Dump completed on 2023-04-03 16:45:51
