CREATE TABLE `sc_version` (
  `schema_version` int(11) NOT NULL
) DEFAULT CHARSET=utf8;
INSERT INTO `sc_version` (`schema_version`) VALUES ('6');

ALTER TABLE `sc_users` ADD `privateKey` VARCHAR(33) NULL;
CREATE INDEX `privateKey` ON `sc_users` (`privateKey`);
