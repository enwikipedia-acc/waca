/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

SELECT "Checking primary key naming";

CREATE OR REPLACE VIEW schemacheck_pk_name AS
SELECT table_name, constraint_type, constraint_name, 
    CASE WHEN constraint_name = 'PRIMARY' THEN 'OK' ELSE 'FAIL' END AS test_status
FROM information_schema.table_constraints
WHERE constraint_schema = DATABASE()
AND constraint_type = 'PRIMARY KEY';
