-- MariaDB dump
--
-- Host: localhost    Database: luncdash
-- ------------------------------------------------------
-- 

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `apy`
--

DROP TABLE IF EXISTS `apy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apy` (
  `block` int(11) unsigned NOT NULL DEFAULT 0,
  `apy` double(6,1) unsigned NOT NULL DEFAULT 0.0,
  `apy_uluna` double(6,1) unsigned NOT NULL DEFAULT 0.0,
  `apy_uusd` double(6,1) unsigned NOT NULL DEFAULT 0.0,
  PRIMARY KEY (`block`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `balance`
--

DROP TABLE IF EXISTS `balance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `balance` (
  `wallet` varchar(50) NOT NULL,
  `block` int(11) unsigned NOT NULL DEFAULT 0,
  `uluna` double(24,6) unsigned NOT NULL DEFAULT 0.000000,
  `uusd` double(24,6) unsigned NOT NULL DEFAULT 0.000000,
  PRIMARY KEY (`wallet`,`block`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blocks`
--

DROP TABLE IF EXISTS `blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blocks` (
  `block` int(11) unsigned NOT NULL,
  `time` datetime DEFAULT NULL,
  `total_supply_uluna` double(24,6) unsigned NOT NULL DEFAULT 0.000000,
  `total_supply_uusd` double(24,6) unsigned NOT NULL DEFAULT 0.000000,
  `pool_uluna` double(24,6) unsigned NOT NULL DEFAULT 0.000000,
  `pool_uusd` double(24,6) unsigned NOT NULL DEFAULT 0.000000,
  `circulating_uluna` double(24,6) unsigned NOT NULL DEFAULT 0.000000,
  `circulating_uusd` double(24,6) unsigned NOT NULL DEFAULT 0.000000,
  `bonded_uluna` double(24,6) unsigned NOT NULL DEFAULT 0.000000,
  `unbonded_uluna` double(24,6) unsigned NOT NULL DEFAULT 0.000000,
  `unbonded_validators` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `bonded_validators` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `jailed_validators` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `date` date DEFAULT NULL,
  `price_uluna` double(24,18) unsigned DEFAULT NULL,
  PRIMARY KEY (`block`),
  KEY `time` (`time`),
  KEY `date` (`date`),
  KEY `price_uluna` (`price_uluna`,`time`),
  KEY `price_uluna_2` (`price_uluna`,`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `datacache`
--

DROP TABLE IF EXISTS `datacache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datacache` (
  `ident` varchar(255) NOT NULL,
  `data` longtext NOT NULL,
  `expires` datetime DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`ident`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='{"_version":"1"}';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `delegations`
--

DROP TABLE IF EXISTS `delegations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delegations` (
  `block` int(11) unsigned NOT NULL DEFAULT 0,
  `validator` varchar(70) NOT NULL DEFAULT '',
  `delegated` double(20,6) unsigned NOT NULL,
  PRIMARY KEY (`validator`,`block`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `proposals`
--

DROP TABLE IF EXISTS `proposals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proposals` (
  `id` int(11) unsigned NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `status` enum('passed','rejected','voting','deposit','') NOT NULL DEFAULT 'deposit',
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `whitelisted` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `tax_epoch`
--

DROP TABLE IF EXISTS `tax_epoch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tax_epoch` (
  `epoch` int(10) unsigned NOT NULL DEFAULT 0,
  `block` int(11) unsigned NOT NULL DEFAULT 0,
  `tax_uluna` double(20,6) unsigned NOT NULL DEFAULT 0.000000,
  `tax_uusd` double(20,6) unsigned NOT NULL DEFAULT 0.000000,
  PRIMARY KEY (`epoch`,`block`),
  KEY `last_block` (`block`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tweets`
--

DROP TABLE IF EXISTS `tweets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tweets` (
  `id` bigint(20) unsigned NOT NULL,
  `handle` varchar(25) NOT NULL DEFAULT '',
  `author_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `tweet` varchar(1000) NOT NULL DEFAULT '',
  `tweet_time` datetime DEFAULT NULL,
  `entities` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='{"_version":"1"}';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tx`
--

DROP TABLE IF EXISTS `tx`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tx` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sender` varchar(50) NOT NULL DEFAULT '',
  `recipient` varchar(50) NOT NULL DEFAULT '',
  `contract` varchar(50) DEFAULT NULL,
  `amount` double(24,6) unsigned NOT NULL DEFAULT 0.000000,
  `denom` varchar(7) NOT NULL DEFAULT 'uluna',
  `memo` varchar(200) NOT NULL DEFAULT '',
  `block` int(11) unsigned NOT NULL DEFAULT 0,
  `tx_time` datetime DEFAULT NULL,
  `tx_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `denom` (`denom`),
  KEY `recipient` (`recipient`),
  KEY `sender` (`sender`),
  KEY `block` (`block`)
) ENGINE=InnoDB AUTO_INCREMENT=15964208 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tx_fees`
--

DROP TABLE IF EXISTS `tx_fees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tx_fees` (
  `block` int(11) unsigned NOT NULL,
  `fees_uluna` double(16,6) unsigned NOT NULL DEFAULT 0.000000,
  `tax_uluna` double(16,6) unsigned NOT NULL DEFAULT 0.000000,
  `tax_uusd` double(16,6) unsigned NOT NULL DEFAULT 0.000000,
  `fee_uusd` double(16,6) unsigned NOT NULL DEFAULT 0.000000,
  PRIMARY KEY (`block`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tx_fees_new`
--

DROP TABLE IF EXISTS `tx_fees_new`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tx_fees_new` (
  `hash` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `block` int(11) unsigned NOT NULL DEFAULT 0,
  `fees` double(16,6) unsigned NOT NULL DEFAULT 0.000000,
  `denom` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`hash`,`denom`),
  KEY `block` (`block`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tx_new`
--

DROP TABLE IF EXISTS `tx_new`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tx_new` (
  `hash` varchar(70) NOT NULL DEFAULT '',
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sender` varchar(50) NOT NULL DEFAULT '',
  `recipient` varchar(50) NOT NULL DEFAULT '',
  `contract` varchar(50) DEFAULT NULL,
  `amount` double(24,6) unsigned NOT NULL DEFAULT 0.000000,
  `denom` varchar(7) NOT NULL DEFAULT 'uluna',
  `memo` varchar(200) NOT NULL DEFAULT '',
  `block` int(11) unsigned NOT NULL DEFAULT 0,
  `tx_time` datetime DEFAULT NULL,
  `tx_date` date DEFAULT NULL,
  `failed` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `code` mediumint(6) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `recipient` (`recipient`),
  KEY `sender` (`sender`),
  KEY `block` (`block`),
  KEY `tx_time` (`tx_time`),
  KEY `contract` (`contract`)
) ENGINE=InnoDB AUTO_INCREMENT=109409129 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `validators`
--

DROP TABLE IF EXISTS `validators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `validators` (
  `address` varchar(100) NOT NULL DEFAULT '',
  `operator_address` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`address`),
  KEY `operator_address` (`operator_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `votes`
--

DROP TABLE IF EXISTS `votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `votes` (
  `proposal_id` int(11) unsigned NOT NULL DEFAULT 0,
  `validator` varchar(100) NOT NULL DEFAULT '',
  `vote` enum('yes','no','abstain','veto') NOT NULL DEFAULT 'abstain',
  PRIMARY KEY (`proposal_id`,`validator`),
  KEY `validator` (`validator`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wallet`
--

DROP TABLE IF EXISTS `wallet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallet` (
  `wallet` varchar(50) NOT NULL,
  `block` int(11) unsigned NOT NULL DEFAULT 0,
  `uluna` double(24,6) unsigned NOT NULL DEFAULT 0.000000,
  `uusd` double(24,6) unsigned NOT NULL DEFAULT 0.000000,
  `usdr` double(24,6) unsigned NOT NULL DEFAULT 0.000000,
  `descr` varchar(100) DEFAULT NULL,
  `type` enum('','cex','bank','contract','project','internal','whale','swap') NOT NULL DEFAULT '',
  `cosm_type` varchar(100) NOT NULL DEFAULT '',
  `cosm_name` varchar(50) NOT NULL DEFAULT '',
  `project` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`wallet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `withdrawals`
--

DROP TABLE IF EXISTS `withdrawals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `withdrawals` (
  `block` int(11) unsigned NOT NULL DEFAULT 0,
  `uluna` double(20,6) unsigned NOT NULL DEFAULT 0.000000,
  `uusd` double(20,6) unsigned NOT NULL DEFAULT 0.000000,
  `delegated` double(20,6) unsigned NOT NULL DEFAULT 0.000000,
  `ratio` int(8) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`block`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-07-25 22:23:25
