/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

SELECT "Checking table default collations";

CREATE OR REPLACE VIEW schemacheck_invalid_table_collation AS
SELECT table_name, table_collation,
    CASE WHEN table_collation IN ('utf8mb4_unicode_520_ci') THEN 'OK' ELSE 'FAIL' END AS test_status
FROM information_schema.tables
WHERE table_schema = DATABASE()
AND table_type = 'BASE TABLE';
