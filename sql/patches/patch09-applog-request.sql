ALTER TABLE `applicationlog` 
ADD COLUMN `request` VARCHAR(1024) NULL AFTER `timestamp`,
ADD COLUMN `request_ts` DECIMAL(38,12) NULL AFTER `request`;
