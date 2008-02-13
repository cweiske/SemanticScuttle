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
  `bAddress` text NOT NULL,
  `bDescription` varchar(255) default NULL,
  `bHash` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`bId`),
  KEY `sc_bookmarks_usd` (`uId`,`bStatus`,`bDatetime`),
  KEY `sc_bookmarks_hui` (`bHash`,`uId`,`bId`),
  KEY `sc_bookmarks_du` (`bDatetime`,`uId`)
);

-- --------------------------------------------------------

-- 
-- Table structure for table `sc_tags`
-- 

CREATE TABLE `sc_tags` (
  `id` int(11) NOT NULL auto_increment,
  `bId` int(11) NOT NULL default '0',
  `tag` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `sc_tags_tag_bId` (`tag`,`bId`),
  KEY `sc_tags_bId` (`bId`)
);

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
  PRIMARY KEY  (`uId`)
);

-- --------------------------------------------------------

-- 
-- Table structure for table `sc_watched`
-- 

CREATE TABLE `sc_watched` (
  `wId` int(11) NOT NULL auto_increment,
  `uId` int(11) NOT NULL default '0',
  `watched` int(11) NOT NULL default '0',
  PRIMARY KEY  (`wId`),
  KEY `sc_watched_uId` (`uId`)
);

-- --------------------------------------------------------

-- 
-- Table structure for table `sc_tags2tags`
-- 

CREATE TABLE `sc_tags2tags` (
  `ttId` int(11) NOT NULL auto_increment,
  `tag1` varchar(32) NOT NULL default '',
  `tag2` varchar(32) NOT NULL default '',
  `relationType` varchar(32) NOT NULL default '',
  `uId` int(11) NOT NULL default '0',
  PRIMARY KEY (`ttId`),
  UNIQUE KEY `sc_tags2tags_tag1_tag2_uId` (`tag1`,`tag2`,`relationType`,`uId`)
);

-- --------------------------------------------------------

-- 
-- Table structure for table `sc_tagsstats`
-- 

CREATE TABLE `sc_tagsstats` (
  `tstId` int(11) NOT NULL auto_increment,
  `tag1` varchar(32) NOT NULL default '',
  `relationType` varchar(32) NOT NULL default '',
  `uId` int(11) NOT NULL default '0',
  `nb` int(11) NOT NULL default '0',
  `depth` int(11) NOT NULL default '0',
  `nbupdate` int(11) NOT NULL default '0',
  PRIMARY KEY (`tstId`),
  UNIQUE KEY `sc_tagsstats_tag1_type_uId` (`tag1`,`relationType`,`uId`)
);

-- --------------------------------------------------------

-- 
-- Table structure for table `sc_commondescription`
-- 

CREATE TABLE `sc_commondescription` (
  `cdId` int(11) NOT NULL auto_increment,
  `uId` int(11) NOT NULL default '0',
  `tag` varchar(32) NOT NULL default '',
  `bHash` varchar(32) NOT NULL default '',
  `cdTitle` varchar(255) NOT NULL default '',
  `cdDescription` varchar(2000) default NULL,
  `cdDatetime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`cdId`),
  UNIQUE KEY `sc_commondescription_tag_datetime` (`tag`,`cdDatetime`),
  UNIQUE KEY `sc_commondescription_bookmark_datetime` (`bHash`,`cdDatetime`)
);
