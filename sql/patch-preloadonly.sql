ALTER TABLE `emailtemplate` ADD COLUMN `preloadonly` TINYINT(1) NOT NULL DEFAULT '0' AFTER `active`;
