CREATE TABLE `sc_version` (
  `schema_version` int(11) NOT NULL
) DEFAULT CHARSET=utf8;
INSERT INTO `sc_version` (`schema_version`) VALUES ('6');

CREATE TABLE `sc_users_sslclientcerts` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `uId` INT NOT NULL ,
  `sslSerial` VARCHAR( 32 ) NOT NULL ,
  `sslClientIssuerDn` VARCHAR( 1024 ) NOT NULL ,
  `sslName` VARCHAR( 64 ) NOT NULL ,
  `sslEmail` VARCHAR( 64 ) NOT NULL ,
  PRIMARY KEY ( `id` ) ,
  UNIQUE (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE `sc_users` ADD `privateKey` VARCHAR(33) NULL;
CREATE UNIQUE INDEX `privateKey` ON `sc_users` (`privateKey`);

