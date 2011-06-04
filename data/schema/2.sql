ALTER TABLE `sc_bookmarks` CHANGE `bDescription` `bDescription` VARCHAR( 1500 )
CREATE TABLE `sc_tagscache` (
   `tcId` int(11) NOT NULL auto_increment,
   `tag1` varchar(100) NOT NULL default '',
   `tag2` varchar(100) NOT NULL default '',
   `relationType` varchar(32) NOT NULL default '',
   `uId` int(11) NOT NULL default '0',
   PRIMARY KEY (`tcId`),
   UNIQUE KEY `sc_tagscache_tag1_tag2_type_uId` (`tag1`,`tag2`,`relationType`,`uId`)
);
