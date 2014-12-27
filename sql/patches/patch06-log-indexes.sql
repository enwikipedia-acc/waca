ALTER TABLE `log` 
ADD INDEX `log_idx_action` (`action` ASC),
ADD INDEX `log_idx_objectid` (`objectid` ASC),
ADD INDEX `log_idx_user` (`user` ASC),
ADD INDEX `log_idx_timestamp` (`timestamp` ASC);
