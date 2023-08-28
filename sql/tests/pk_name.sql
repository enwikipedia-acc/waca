/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

SELECT "Checking primary key naming";

CREATE OR REPLACE VIEW schemacheck_pk_name AS
SELECT table_name, constraint_type, constraint_name, 
    CASE WHEN constraint_name = 'PRIMARY' THEN 'OK' ELSE 'FAIL' END AS test_status
FROM information_schema.table_constraints
WHERE constraint_schema = DATABASE()
AND constraint_type = 'PRIMARY KEY';
