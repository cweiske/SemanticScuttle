-- Semantic Scuttle - Tables creation SQL script
-- ! Dont forget to change table names according to $tableprefix defined in config.php !

-- 
-- Table structure for table `sc_bookmarks`
-- 

CREATE TABLE `sc_bookmarks` (
  `bId` int(11) NOT NULL auto_increment,
  `uId` int(11) NOT NULL default '0',
  `bIp` varchar(40) default NULL,
  `bStatus` tinyint(1) NOT NULL default '0',
  `bDatetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `bModified` datetime NOT NULL default '0000-00-00 00:00:00',
  `bTitle` varchar(255) NOT NULL default '',
  `bAddress` varchar(1500) NOT NULL,
  `bDescription` text default NULL,
  `bPrivateNote` text default NULL,
  `bHash` varchar(32) NOT NULL default '',
  `bVotes` int(11) NOT NULL,
  `bVoting` int(11) NOT NULL,
  `bShort` varchar(16) default NULL,
  PRIMARY KEY  (`bId`),
  KEY `sc_bookmarks_usd` (`uId`,`bStatus`,`bDatetime`),
  KEY `sc_bookmarks_hui` (`bHash`,`uId`,`bId`),
  KEY `sc_bookmarks_du` (`bDatetime`,`uId`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;

-- --------------------------------------------------------

-- 
-- Table structure for table `sc_tags`
-- 

CREATE TABLE `sc_tags` (
  `tId` int(11) NOT NULL auto_increment,
  `tag` varchar(100) NOT NULL default '',
  `uId` int(11) NOT NULL default '0',
  `tDescription` text default NULL,
  PRIMARY KEY  (`tId`),
  UNIQUE KEY `sc_tags_tag_uId` (`tag`, `uId`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;

-- --------------------------------------------------------

-- 
-- Table structure for table `sc_bookmarks2tags`
-- 

CREATE TABLE `sc_bookmarks2tags` (
  `id` int(11) NOT NULL auto_increment,
  `bId` int(11) NOT NULL default '0',
  `tag` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `sc_bookmarks2tags_tag_bId` (`tag`,`bId`),
  KEY `sc_bookmarks2tags_bId` (`bId`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;

-- --------------------------------------------------------

-- 
-- Table structure for table `sc_users`
-- 

CREATE TABLE `sc_users` (
  `uId` int(11) NOT NULL auto_increment,
  `username` varchar(25) NOT NULL default '',
  `password` varchar(40) NOT NULL default '',
  `uDatetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `uModified` datetime NOT NULL default '0000-00-00 00:00:00',
  `name` varchar(50) default NULL,
  `email` varchar(50) NOT NULL default '',
  `homepage` varchar(255) default NULL,
  `uContent` text,
  `privateKey` varchar(33) default NULL,
  PRIMARY KEY  (`uId`),
  UNIQUE KEY `privateKey` (`privateKey`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;

-- --------------------------------------------------------

CREATE TABLE `sc_users_sslclientcerts` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `uId` INT NOT NULL ,
  `sslSerial` VARCHAR( 32 ) NOT NULL ,
  `sslClientIssuerDn` VARCHAR( 1024 ) NOT NULL ,
  `sslName` VARCHAR( 64 ) NOT NULL ,
  `sslEmail` VARCHAR( 64 ) NOT NULL ,
  PRIMARY KEY ( `id` )
) CHARACTER SET utf8 COLLATE utf8_general_ci;

-- 
-- Table structure for table `sc_watched`
-- 

CREATE TABLE `sc_watched` (
  `wId` int(11) NOT NULL auto_increment,
  `uId` int(11) NOT NULL default '0',
  `watched` int(11) NOT NULL default '0',
  PRIMARY KEY  (`wId`),
  KEY `sc_watched_uId` (`uId`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;

-- --------------------------------------------------------

-- 
-- Table structure for table `sc_tags2tags`
-- 

CREATE TABLE `sc_tags2tags` (
  `ttId` int(11) NOT NULL auto_increment,
  `tag1` varchar(100) NOT NULL default '',
  `tag2` varchar(100) NOT NULL default '',
  `relationType` varchar(32) NOT NULL default '',
  `uId` int(11) NOT NULL default '0',
  PRIMARY KEY (`ttId`),
  UNIQUE KEY `sc_tags2tags_tag1_tag2_uId` (`tag1`,`tag2`,`relationType`,`uId`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;

-- --------------------------------------------------------

-- 
-- Table structure for table `sc_tagsstats`
-- 

CREATE TABLE `sc_tagsstats` (
  `tstId` int(11) NOT NULL auto_increment,
  `tag1` varchar(100) NOT NULL default '',
  `relationType` varchar(32) NOT NULL default '',
  `uId` int(11) NOT NULL default '0',
  `nb` int(11) NOT NULL default '0',
  `depth` int(11) NOT NULL default '0',
  `nbupdate` int(11) NOT NULL default '0',
  PRIMARY KEY (`tstId`),
  UNIQUE KEY `sc_tagsstats_tag1_type_uId` (`tag1`,`relationType`,`uId`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;

-- --------------------------------------------------------

-- 
-- Table structure for table `sc_tagscache`
-- 

CREATE TABLE `sc_tagscache` (
  `tcId` int(11) NOT NULL auto_increment,
  `tag1` varchar(100) NOT NULL default '',
  `tag2` varchar(100) NOT NULL default '',
  `relationType` varchar(32) NOT NULL default '',
  `uId` int(11) NOT NULL default '0',
  PRIMARY KEY (`tcId`),
  UNIQUE KEY `sc_tagscache_tag1_tag2_type_uId` (`tag1`,`tag2`,`relationType`,`uId`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;

-- --------------------------------------------------------

-- 
-- Table structure for table `sc_commondescription`
-- 

CREATE TABLE `sc_commondescription` (
  `cdId` int(11) NOT NULL auto_increment,
  `uId` int(11) NOT NULL default '0',
  `tag` varchar(100) NOT NULL default '',
  `bHash` varchar(32) NOT NULL default '',
  `cdTitle` varchar(255) NOT NULL default '',
  `cdDescription` text default NULL,
  `cdDatetime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`cdId`),
  UNIQUE KEY `sc_commondescription_tag_datetime` (`tag`,`cdDatetime`),
  UNIQUE KEY `sc_commondescription_bookmark_datetime` (`bHash`,`cdDatetime`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;

-- --------------------------------------------------------

-- 
-- Table structure for table `sc_searchhistory`
-- 

CREATE TABLE `sc_searchhistory` (
  `shId` int(11) NOT NULL auto_increment,
  `shTerms` varchar(255) NOT NULL default '',
  `shRange` varchar(32) NOT NULL default '',
  `shDatetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `shNbResults` int(6) NOT NULL default '0',
  `uId` int(11) NOT NULL default '0',
  PRIMARY KEY (`shId`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;


CREATE TABLE `sc_votes` (
  `bId` INT NOT NULL ,
  `uId` INT NOT NULL ,
  `vote` INT( 2 ) NOT NULL ,
  UNIQUE KEY `bid_2` (`bId`,`uId`),
  KEY `bid` (`bId`),
  KEY `uid` (`uId`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;


CREATE TABLE `sc_version` (
  `schema_version` int(11) NOT NULL
) DEFAULT CHARSET=utf8;
INSERT INTO `sc_version` (`schema_version`) VALUES ('6');
