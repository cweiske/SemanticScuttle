/* modify and add fields */
ALTER TABLE `sc_bookmarks` MODIFY `bAddress` varchar(1500) NOT NULL;
ALTER TABLE `sc_bookmarks` MODIFY `bDescription` TEXT default NULL;
ALTER TABLE `sc_bookmarks` ADD `bPrivateNote` TEXT NULL AFTER `bDescription` ;
ALTER TABLE `sc_tags` MODIFY `tDescription` TEXT default NULL;
ALTER TABLE `sc_commondescription` MODIFY `cdDescription` TEXT default NULL;

/* convert to UTF-8 if your table is ISO-something (through BLOB: tips provided by MYSQL documentation)*/
/* first need to remove index keys because of BLOB constraints*/
ALTER TABLE `sc_tags` DROP INDEX `sc_tags_tag_uId`;
ALTER TABLE `sc_bookmarks2tags` DROP INDEX `sc_bookmarks2tags_tag_bId`;
ALTER TABLE `sc_bookmarks2tags` DROP INDEX `sc_bookmarks2tags_bId`;
ALTER TABLE `sc_tags2tags` DROP INDEX `sc_tags2tags_tag1_tag2_uId`;
ALTER TABLE `sc_commondescription` DROP INDEX `sc_commondescription_tag_datetime`;
ALTER TABLE `sc_tagscache` DROP INDEX `sc_tagscache_tag1_tag2_type_uId`;
ALTER TABLE `sc_tagsstats` DROP INDEX `sc_tagsstats_tag1_type_uId`;

/* secondly convert through BLOB type */
ALTER TABLE `sc_bookmarks` CHANGE `bTitle` `bTitle` BLOB;
ALTER TABLE `sc_bookmarks` CHANGE `bTitle` `bTitle` varchar(255) CHARACTER SET utf8;
ALTER TABLE `sc_bookmarks` CHANGE `bAddress` `bAddress` BLOB;
ALTER TABLE `sc_bookmarks` CHANGE `bAddress` `bAddress` varchar(1500) CHARACTER SET utf8;
ALTER TABLE `sc_bookmarks` CHANGE `bDescription` `bDescription` BLOB;
ALTER TABLE `sc_bookmarks` CHANGE `bDescription` `bDescription` text CHARACTER SET utf8;
ALTER TABLE `sc_bookmarks` CHANGE `bPrivateNote` `bPrivateNote` BLOB;
ALTER TABLE `sc_bookmarks` CHANGE `bPrivateNote` `bPrivateNote` text CHARACTER SET utf8;

ALTER TABLE `sc_tags` CHANGE `tag` `tag` BLOB;
ALTER TABLE `sc_tags` CHANGE `tag` `tag` varchar(100) CHARACTER SET utf8;
ALTER TABLE `sc_tags` CHANGE `tDescription` `tDescription` BLOB;
ALTER TABLE `sc_tags` CHANGE `tDescription` `tDescription` text CHARACTER SET utf8;

ALTER TABLE `sc_bookmarks2tags` CHANGE `tag` `tag` BLOB;
ALTER TABLE `sc_bookmarks2tags` CHANGE `tag` `tag` varchar(100) CHARACTER SET utf8;

ALTER TABLE `sc_users` CHANGE `name` `name` BLOB;
ALTER TABLE `sc_users` CHANGE `name` `name` varchar(50) CHARACTER SET utf8;
ALTER TABLE `sc_users` CHANGE `uContent` `uContent` BLOB;
ALTER TABLE `sc_users` CHANGE `uContent` `uContent` text CHARACTER SET utf8;

ALTER TABLE `sc_tags2tags` CHANGE `tag1` `tag1` BLOB;
ALTER TABLE `sc_tags2tags` CHANGE `tag1` `tag1` varchar(100) CHARACTER SET utf8;
ALTER TABLE `sc_tags2tags` CHANGE `tag2` `tag2` BLOB;
ALTER TABLE `sc_tags2tags` CHANGE `tag2` `tag2` varchar(100) CHARACTER SET utf8;

ALTER TABLE `sc_tagsstats` CHANGE `tag1` `tag1` BLOB;
ALTER TABLE `sc_tagsstats` CHANGE `tag1` `tag1` varchar(100) CHARACTER SET utf8;

ALTER TABLE `sc_tagscache` CHANGE `tag1` `tag1` BLOB;
ALTER TABLE `sc_tagscache` CHANGE `tag1` `tag1` varchar(100) CHARACTER SET utf8;
ALTER TABLE `sc_tagscache` CHANGE `tag2` `tag2` BLOB;
ALTER TABLE `sc_tagscache` CHANGE `tag2` `tag2` varchar(100) CHARACTER SET utf8;

ALTER TABLE `sc_commondescription` CHANGE `tag` `tag` BLOB;
ALTER TABLE `sc_commondescription` CHANGE `tag` `tag` varchar(100) CHARACTER SET utf8;
ALTER TABLE `sc_commondescription` CHANGE `cdTitle` `cdTitle` BLOB;
ALTER TABLE `sc_commondescription` CHANGE `cdTitle` `cdTitle` varchar(255) CHARACTER SET utf8;
ALTER TABLE `sc_commondescription` CHANGE `cdDescription` `cdDescription` BLOB;
ALTER TABLE `sc_commondescription` CHANGE `cdDescription` `cdDescription` text CHARACTER SET utf8;

ALTER TABLE `sc_searchhistory` CHANGE `shTerms` `shTerms` BLOB;
ALTER TABLE `sc_searchhistory` CHANGE `shTerms` `shTerms` varchar(255) CHARACTER SET utf8;
ALTER TABLE `sc_searchhistory` CHANGE `shRange` `shRange` BLOB;
ALTER TABLE `sc_searchhistory` CHANGE `shRange` `shRange` varchar(32) CHARACTER SET utf8;

/* Thirdly re-add index keys */
ALTER TABLE `sc_tags` ADD UNIQUE KEY `sc_tags_tag_uId` (`tag`, `uId`);
ALTER TABLE `sc_bookmarks2tags` ADD UNIQUE KEY `sc_bookmarks2tags_tag_bId` (`tag`,`bId`);
ALTER TABLE `sc_bookmarks2tags` ADD KEY `sc_bookmarks2tags_bId` (`bId`);
ALTER TABLE `sc_tags2tags` ADD UNIQUE KEY `sc_tags2tags_tag1_tag2_uId` (`tag1`,`tag2`,`relationType`,`uId`);
ALTER TABLE `sc_commondescription` ADD UNIQUE KEY `sc_commondescription_tag_datetime` (`tag`,`cdDatetime`);
ALTER TABLE `sc_tagscache` ADD UNIQUE KEY `sc_tagscache_tag1_tag2_type_uId` (`tag1`,`tag2`,`relationType`,`uId`);
ALTER TABLE `sc_tagsstats` ADD UNIQUE KEY `sc_tagsstats_tag1_type_uId` (`tag1`,`relationType`,`uId`);

/* Change tables to utf-8 charset */
ALTER TABLE `sc_bookmarks` CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `sc_tags` CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `sc_bookmarks2tags` CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `sc_users` CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `sc_watched` CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `sc_tags2tags` CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `sc_tagsstats` CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `sc_tagscache` CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `sc_commondescription` CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `sc_searchhistory` CHARACTER SET utf8 COLLATE utf8_general_ci;
