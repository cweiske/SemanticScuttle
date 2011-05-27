RENAME TABLE `sc_tags`  TO `sc_bookmarks2tags` ;

	CREATE TABLE `sc_searchhistory` (
	  `shId` int(11) NOT NULL auto_increment,
  	`shTerms` varchar(255) NOT NULL default '',
	  `shRange` varchar(32) NOT NULL default '',
	  `shDatetime` datetime NOT NULL default '0000-00-00 00:00:00',
	  `shNbResults` int(6) NOT NULL default '0',
	  `uId` int(11) NOT NULL default '0',
	  PRIMARY KEY (`shId`)
	);

	CREATE TABLE `sc_tags` (
	  `tId` int(11) NOT NULL auto_increment,
	  `tag` varchar(32) NOT NULL default '',
	  `uId` int(11) NOT NULL default '0',
	  `tDescription` varchar(255) default NULL,
	  PRIMARY KEY  (`tId`),
	  UNIQUE KEY `sc_tags_tag_uId` (`tag`, `uId`)
	);
