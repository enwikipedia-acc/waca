/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

SELECT "Checking foreign keys are based on table primary keys";

CREATE OR REPLACE VIEW schemacheck_fk_on_pk AS
SELECT constraint_name, unique_constraint_name, table_name, referenced_table_name,
    CASE WHEN unique_constraint_name = 'PRIMARY' THEN 'OK' ELSE 'FAIL' END AS test_status
FROM information_schema.referential_constraints
WHERE constraint_schema = DATABASE();
