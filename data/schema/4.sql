ALTER TABLE `sc_bookmarks` ADD `bVoting` INT NOT NULL;
ALTER TABLE `sc_bookmarks` ADD `bVotes` INT NOT NULL;

CREATE TABLE `sc_votes` (
  `bId` INT NOT NULL ,
  `uId` INT NOT NULL ,
  `vote` INT( 2 ) NOT NULL ,
  UNIQUE KEY `bid_2` (`bId`,`uId`),
  KEY `bid` (`bId`),
  KEY `uid` (`uId`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;
