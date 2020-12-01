DROP TABLE IF EXISTS `main_tmsheetconfigrations`;
CREATE TABLE IF NOT EXISTS `main_tmsheetconfigrations` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `month` int(11) UNSIGNED DEFAULT NULL,
  `year` int(11) UNSIGNED DEFAULT NULL,
  `form` datetime DEFAULT NULL,
  `to` datetime DEFAULT NULL,
  `createdby` int(11) DEFAULT NULL,
  `modifiedby` int(11) DEFAULT NULL,
  `createddate` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  `isactive` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;
