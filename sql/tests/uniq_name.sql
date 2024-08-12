/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

SELECT "Checking unique constraint naming";

CREATE OR REPLACE VIEW schemacheck_uniq_name AS
WITH columns AS (
    SELECT table_name, index_name, group_concat(column_name ORDER BY seq_in_index SEPARATOR '_') columns
    FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND index_name <> 'PRIMARY'
    GROUP BY table_name, index_name
),
ui_data AS (
    SELECT tc.table_name, tc.constraint_type, tc.constraint_name, c.columns, concat(tc.table_name, '_uidx_', c.columns) calculated_name
    FROM information_schema.table_constraints tc
        INNER JOIN columns c ON c.table_name = tc.table_name AND c.index_name = tc.constraint_name
    WHERE tc.constraint_schema = DATABASE() AND tc.constraint_type = 'UNIQUE'
)
SELECT d.*, CASE WHEN constraint_name = calculated_name THEN 'OK' ELSE 'FAIL' END AS test_status
FROM ui_data d;
