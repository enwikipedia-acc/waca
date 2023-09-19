/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

SELECT "Checking foreign key naming";

CREATE OR REPLACE VIEW schemacheck_fk_name AS
WITH columns AS (
    SELECT table_name, index_name, group_concat(column_name ORDER BY seq_in_index SEPARATOR '_') columns
    FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND index_name <> 'PRIMARY'
    GROUP BY table_name, index_name
),
fk_data AS (
    SELECT fk.constraint_name, fk.table_name, fk.referenced_table_name, c.columns, concat(fk.table_name, '_fk_', c.columns, '_', fk.referenced_table_name) calculated_name
    FROM information_schema.referential_constraints fk
        INNER JOIN columns c ON c.index_name = fk.constraint_name AND c.table_name = fk.table_name
    WHERE fk.constraint_schema = DATABASE()
)
SELECT table_name, constraint_name, referenced_table_name, columns, calculated_name,
    CASE WHEN constraint_name = calculated_name THEN 'OK' ELSE 'FAIL' END AS test_status
FROM fk_data;
