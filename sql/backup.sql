-- MySQL dump 10.9
--
-- Host: localhost    Database: fmm_database
-- ------------------------------------------------------
-- Server version	4.1.22-standard-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `aggregate_deal`
--

DROP TABLE IF EXISTS `aggregate_deal`;
CREATE TABLE `aggregate_deal` (
  `aggregate_deal_id` int(11) NOT NULL auto_increment,
  `aggregate_deal_category_id` int(11) NOT NULL default '0',
  `aggregate_deal_region_id` int(11) NOT NULL default '0',
  `logo_img` varchar(155) NOT NULL default '',
  `deal_address` text NOT NULL,
  `title` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `deal_url` varchar(255) NOT NULL default '',
  `price` decimal(10,2) NOT NULL default '0.00',
  `value` decimal(10,2) NOT NULL default '0.00',
  `aggregate_deal_source_id` int(11) NOT NULL default '0',
  `time_added` int(65) NOT NULL default '0',
  `date` datetime default NULL,
  `img_url` varchar(255) default NULL,
  `vendor_name` varchar(255) default NULL,
  PRIMARY KEY  (`aggregate_deal_id`),
  KEY `aggregate_deal_category_idx` (`aggregate_deal_category_id`),
  KEY `aggregate_deal_source_id` (`aggregate_deal_source_id`),
  KEY `aggregate_deal_region_idx` (`aggregate_deal_region_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2710 DEFAULT CHARSET=latin1 COMMENT='Aggregated Deals';

--
-- Table structure for table `aggregate_deal_category`
--

DROP TABLE IF EXISTS `aggregate_deal_category`;
CREATE TABLE `aggregate_deal_category` (
  `aggregate_deal_category_id` int(11) NOT NULL default '0',
  `category_name` varchar(155) NOT NULL default '',
  PRIMARY KEY  (`aggregate_deal_category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Table structure for table `aggregate_deal_location`
--

DROP TABLE IF EXISTS `aggregate_deal_location`;
CREATE TABLE `aggregate_deal_location` (
  `aggregate_deal_location_id` int(11) NOT NULL auto_increment,
  `address` varchar(255) default NULL,
  `city` varchar(255) default NULL,
  `state` varchar(255) default NULL,
  `zipcode` varchar(15) default NULL,
  `phone` varchar(15) default NULL,
  `latitude` varchar(50) default NULL,
  `longitude` varchar(50) default NULL,
  `aggregate_deal_id` int(11) NOT NULL default '0',
  `address_all` text,
  PRIMARY KEY  (`aggregate_deal_location_id`),
  KEY `aggregate_deal_idx` (`aggregate_deal_id`)
) ENGINE=MyISAM AUTO_INCREMENT=652 DEFAULT CHARSET=latin1;

--
-- Table structure for table `aggregate_deal_referred`
--

DROP TABLE IF EXISTS `aggregate_deal_referred`;
CREATE TABLE `aggregate_deal_referred` (
  `aggregate_deal_referred_id` int(10) unsigned NOT NULL auto_increment,
  `date` datetime default NULL,
  `aggregate_deal_source_id` int(11) NOT NULL default '0',
  `ip_address` varchar(255) default NULL,
  PRIMARY KEY  (`aggregate_deal_referred_id`),
  KEY `aggregate_deal_source_idx` (`aggregate_deal_source_id`)
) ENGINE=MyISAM AUTO_INCREMENT=149 DEFAULT CHARSET=latin1;

--
-- Table structure for table `aggregate_deal_region`
--

DROP TABLE IF EXISTS `aggregate_deal_region`;
CREATE TABLE `aggregate_deal_region` (
  `aggregate_deal_region_id` int(11) NOT NULL auto_increment,
  `location` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`aggregate_deal_region_id`),
  UNIQUE KEY `location` (`location`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;

--
-- Table structure for table `aggregate_deal_source`
--

DROP TABLE IF EXISTS `aggregate_deal_source`;
CREATE TABLE `aggregate_deal_source` (
  `aggregate_deal_source_id` int(11) NOT NULL auto_increment,
  `source_name` varchar(100) NOT NULL default '',
  `source_url` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`aggregate_deal_source_id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=latin1 COMMENT='Deal Aggregator Sources';

--
-- Table structure for table `aggregate_deal_tag`
--

DROP TABLE IF EXISTS `aggregate_deal_tag`;
CREATE TABLE `aggregate_deal_tag` (
  `aggregate_deal_tag_id` int(10) unsigned NOT NULL auto_increment,
  `tag_id` int(10) unsigned NOT NULL default '0',
  `aggregate_deal_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`aggregate_deal_tag_id`),
  KEY `aggregate_deal_tag_FKIndex1` (`tag_id`),
  KEY `aggregate_deal_idx` (`aggregate_deal_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_accountbalance`
--

DROP TABLE IF EXISTS `table_accountbalance`;
CREATE TABLE `table_accountbalance` (
  `user_id` int(15) NOT NULL default '0',
  `balance` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_charities`
--

DROP TABLE IF EXISTS `table_charities`;
CREATE TABLE `table_charities` (
  `charity_id` int(15) NOT NULL auto_increment,
  `charity_name` varchar(155) NOT NULL default '',
  `charity_description` tinytext NOT NULL,
  `amt_donated` decimal(15,2) NOT NULL default '0.00',
  PRIMARY KEY  (`charity_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_cms`
--

DROP TABLE IF EXISTS `table_cms`;
CREATE TABLE `table_cms` (
  `page_id` varchar(155) NOT NULL default '',
  `page_title` varchar(155) NOT NULL default '',
  `content` longtext NOT NULL,
  PRIMARY KEY  (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_commissions`
--

DROP TABLE IF EXISTS `table_commissions`;
CREATE TABLE `table_commissions` (
  `commission_id` int(15) NOT NULL auto_increment,
  `paid_to_user_id` int(15) NOT NULL default '0',
  `transaction_id` int(15) NOT NULL default '0',
  `amount` decimal(11,2) NOT NULL default '0.00',
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY  (`commission_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_credits`
--

DROP TABLE IF EXISTS `table_credits`;
CREATE TABLE `table_credits` (
  `credit_id` int(15) NOT NULL auto_increment,
  `user_id_credited` int(15) NOT NULL default '0',
  PRIMARY KEY  (`credit_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_discussions`
--

DROP TABLE IF EXISTS `table_discussions`;
CREATE TABLE `table_discussions` (
  `comment_id` int(15) NOT NULL auto_increment,
  `offer_id` int(15) NOT NULL default '0',
  `timestamp` int(55) NOT NULL default '0',
  `comment` text NOT NULL,
  `user_id` int(15) NOT NULL default '0',
  `status` int(2) NOT NULL default '0',
  PRIMARY KEY  (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_ip2location`
--

DROP TABLE IF EXISTS `table_ip2location`;
CREATE TABLE `table_ip2location` (
  `locId` int(45) NOT NULL default '0',
  `country` char(2) NOT NULL default '',
  `region` varchar(155) NOT NULL default '',
  `city` varchar(155) NOT NULL default '',
  `postalCode` varchar(15) NOT NULL default '',
  `latitude` varchar(25) NOT NULL default '',
  `longitude` varchar(25) NOT NULL default '',
  `metroCode` varchar(25) NOT NULL default '',
  `areaCode` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`locId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_ipmap`
--

DROP TABLE IF EXISTS `table_ipmap`;
CREATE TABLE `table_ipmap` (
  `id` int(11) NOT NULL auto_increment,
  `startIpNum` int(55) NOT NULL default '0',
  `endIpNum` int(55) NOT NULL default '0',
  `locId` int(55) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3886163 DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_merchantinfo`
--

DROP TABLE IF EXISTS `table_merchantinfo`;
CREATE TABLE `table_merchantinfo` (
  `merchant_id` int(15) NOT NULL default '0',
  `company_name` varchar(155) NOT NULL default '',
  `street_address` varchar(155) NOT NULL default '',
  `city` varchar(155) NOT NULL default '',
  `state` char(2) NOT NULL default '',
  `zipcode` varchar(7) NOT NULL default '',
  `website_url` varchar(155) NOT NULL default '',
  PRIMARY KEY  (`merchant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_merchants`
--

DROP TABLE IF EXISTS `table_merchants`;
CREATE TABLE `table_merchants` (
  `merchant_id` int(15) NOT NULL auto_increment,
  `email_address` varchar(155) NOT NULL default '',
  `password` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`merchant_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_messages`
--

DROP TABLE IF EXISTS `table_messages`;
CREATE TABLE `table_messages` (
  `message_id` int(15) NOT NULL auto_increment,
  `message_name` varchar(155) NOT NULL default '',
  `richtext` int(1) NOT NULL default '0',
  `message_title` varchar(155) NOT NULL default '',
  `message` text NOT NULL,
  PRIMARY KEY  (`message_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_offerlocations`
--

DROP TABLE IF EXISTS `table_offerlocations`;
CREATE TABLE `table_offerlocations` (
  `offer_location_id` int(15) NOT NULL auto_increment,
  `offer_id` int(15) NOT NULL default '0',
  `location_id` int(15) NOT NULL default '0',
  PRIMARY KEY  (`offer_location_id`)
) ENGINE=MyISAM AUTO_INCREMENT=68 DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_offers`
--

DROP TABLE IF EXISTS `table_offers`;
CREATE TABLE `table_offers` (
  `offer_id` int(15) NOT NULL auto_increment,
  `offer_code` varchar(25) NOT NULL default '',
  `name` varchar(45) NOT NULL default '',
  `one_liner` varchar(255) NOT NULL default '',
  `details` text NOT NULL,
  `description` text NOT NULL,
  `company` text NOT NULL,
  `graphic` text NOT NULL,
  `expiration` bigint(45) NOT NULL default '0',
  `price` decimal(5,2) NOT NULL default '0.00',
  `value` decimal(10,2) NOT NULL default '0.00',
  `discount` int(10) NOT NULL default '0',
  `status` varchar(25) NOT NULL default '',
  `limit` int(2) NOT NULL default '0',
  PRIMARY KEY  (`offer_id`),
  UNIQUE KEY `offer_code` (`offer_code`),
  FULLTEXT KEY `description` (`description`),
  FULLTEXT KEY `description_2` (`description`),
  FULLTEXT KEY `one_liner` (`one_liner`),
  FULLTEXT KEY `details` (`details`),
  FULLTEXT KEY `name` (`name`),
  FULLTEXT KEY `offer_code_2` (`offer_code`),
  FULLTEXT KEY `name_2` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_purchased`
--

DROP TABLE IF EXISTS `table_purchased`;
CREATE TABLE `table_purchased` (
  `purchase_id` int(15) NOT NULL auto_increment,
  `user_id` int(15) NOT NULL default '0',
  `offer_id` int(15) NOT NULL default '0',
  PRIMARY KEY  (`purchase_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_questionnaire`
--

DROP TABLE IF EXISTS `table_questionnaire`;
CREATE TABLE `table_questionnaire` (
  `user_id` int(15) NOT NULL default '0',
  `dob` int(45) NOT NULL default '0',
  `income_level` varchar(155) NOT NULL default '',
  `location_city` varchar(155) NOT NULL default '',
  `location_state` char(2) NOT NULL default '',
  `education` varchar(155) NOT NULL default '',
  `gender` varchar(25) NOT NULL default '',
  `interests` text NOT NULL,
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_queue`
--

DROP TABLE IF EXISTS `table_queue`;
CREATE TABLE `table_queue` (
  `offer_id` int(15) NOT NULL default '0',
  `status` int(2) NOT NULL default '0',
  `timestamp` int(55) NOT NULL default '0',
  PRIMARY KEY  (`offer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_referrals`
--

DROP TABLE IF EXISTS `table_referrals`;
CREATE TABLE `table_referrals` (
  `referral_id` int(15) NOT NULL auto_increment,
  `user_id_referred` int(15) NOT NULL default '0',
  `user_id_credit` int(15) NOT NULL default '0',
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY  (`referral_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_subscribers`
--

DROP TABLE IF EXISTS `table_subscribers`;
CREATE TABLE `table_subscribers` (
  `subscriber_id` int(15) NOT NULL auto_increment,
  `email_address` varchar(155) NOT NULL default '',
  `location_id` int(15) NOT NULL default '0',
  PRIMARY KEY  (`subscriber_id`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_transactions`
--

DROP TABLE IF EXISTS `table_transactions`;
CREATE TABLE `table_transactions` (
  `transaction_id` int(15) NOT NULL auto_increment,
  `user_id` int(15) NOT NULL default '0',
  `total` decimal(12,2) NOT NULL default '0.00',
  `contents` text NOT NULL,
  `timestamp` int(45) NOT NULL default '0',
  PRIMARY KEY  (`transaction_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_userinfo`
--

DROP TABLE IF EXISTS `table_userinfo`;
CREATE TABLE `table_userinfo` (
  `user_id` int(15) NOT NULL default '0',
  `firstname` varchar(45) NOT NULL default '',
  `lastname` varchar(45) NOT NULL default '',
  `billing_address1` varchar(155) NOT NULL default '',
  `billing_address2` varchar(155) NOT NULL default '',
  `billing_city` varchar(125) NOT NULL default '',
  `billing_state` char(2) NOT NULL default '',
  `billing_zip` varchar(5) NOT NULL default '',
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `table_users`
--

DROP TABLE IF EXISTS `table_users`;
CREATE TABLE `table_users` (
  `user_id` int(15) NOT NULL auto_increment,
  `email_address` varchar(155) NOT NULL default '',
  `password` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `email_address` (`email_address`)
) ENGINE=MyISAM AUTO_INCREMENT=105 DEFAULT CHARSET=latin1;

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
CREATE TABLE `tag` (
  `tag_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  PRIMARY KEY  (`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

