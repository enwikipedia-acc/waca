/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

SELECT "Checking table default collations";

CREATE OR REPLACE VIEW schemacheck_invalid_table_collation AS
SELECT table_name, table_collation,
    CASE WHEN table_collation IN ('utf8mb4_unicode_520_ci') THEN 'OK' ELSE 'FAIL' END AS test_status
FROM information_schema.tables
WHERE table_schema = DATABASE()
AND table_type = 'BASE TABLE';
