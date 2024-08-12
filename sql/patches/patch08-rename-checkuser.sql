/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/
-- ALTER TABLE user
--	CHANGE COLUMN checkuser checkuser INT(1) NOT NULL DEFAULT '0' COMMENT 'Deprecated - OAuth is now used.' ,
--	ADD COLUMN root INT(1) NOT NULL DEFAULT 0;

-- This patch has been reverted. This file is maintained for patch ordering.
