/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

SELECT "Checking text-based column collations";

CREATE OR REPLACE VIEW schemacheck_invalid_column_collation AS
SELECT c.table_name, c.column_name, c.character_set_name, c.collation_name,
    CASE WHEN c.collation_name IN ('utf8mb4_bin', 'utf8mb4_unicode_520_ci') THEN 'OK' ELSE 'FAIL' END AS test_status
FROM information_schema.columns c
INNER JOIN information_schema.tables t ON t.TABLE_NAME = c.TABLE_NAME AND t.table_schema = c.table_schema
WHERE c.character_set_name IS NOT NULL
AND c.table_schema = DATABASE()
AND t.table_type = 'BASE TABLE';
