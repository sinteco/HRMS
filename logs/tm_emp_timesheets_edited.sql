DROP TABLE IF EXISTS `tm_emp_timesheets_edited`;
CREATE TABLE IF NOT EXISTS `tm_emp_timesheets_edited` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) UNSIGNED DEFAULT NULL,
  `project_task_id` int(11) UNSIGNED DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `duration` VARCHAR(30) DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `createdby` int(11) DEFAULT NULL,
  `modifiedby` int(11) DEFAULT NULL,
  `createddate` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;
