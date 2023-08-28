/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/
ALTER TABLE `log`
ADD INDEX `log_idx_action` (`action` ASC),
ADD INDEX `log_idx_objectid` (`objectid` ASC),
ADD INDEX `log_idx_user` (`user` ASC),
ADD INDEX `log_idx_timestamp` (`timestamp` ASC);
