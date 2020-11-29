CREATE TABLE `sentrifugo`.`tm_ts_edited_status`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `emp_id` INT NOT NULL,
    `main_tmsheetconfigrations_id` INT NOT NULL,
    `comment` VARCHAR(255) NULL,
    `rejectNote` VARCHAR(255) NULL,
    `status` ENUM(
        'For Approval',
        'Rejected',
        'Approved',
        ''
    ) NOT NULL DEFAULT 'For Approval',
    `approved_by` INT NULL DEFAULT NULL,
    `created_by` INT NOT NULL,
    `modified_by` INT NOT NULL,
    `isactive` tinyint(1) DEFAULT '1',
    `created` datetime NOT NULL,
    `modified` datetime NOT NULL,
    PRIMARY KEY(`id`)
) ENGINE = MyISAM;