/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/
ALTER TABLE `applicationlog`
ADD COLUMN `request` VARCHAR(1024) NULL AFTER `timestamp`,
ADD COLUMN `request_ts` DECIMAL(38,12) NULL AFTER `request`;
