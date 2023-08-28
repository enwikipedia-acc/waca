/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

SELECT "Checking foreign keys are based on table primary keys";

CREATE OR REPLACE VIEW schemacheck_fk_on_pk AS
SELECT constraint_name, unique_constraint_name, table_name, referenced_table_name,
    CASE WHEN unique_constraint_name = 'PRIMARY' THEN 'OK' ELSE 'FAIL' END AS test_status
FROM information_schema.referential_constraints
WHERE constraint_schema = DATABASE();
