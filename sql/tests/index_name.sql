/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

SELECT "Checking index names";

CREATE OR REPLACE VIEW schemacheck_index_name AS
WITH data AS (
    SELECT s.table_name, s.index_name, CONCAT(s.table_name, '_idx_',
        GROUP_CONCAT(s.column_name ORDER BY s.seq_in_index SEPARATOR '_')) calculated_name
    FROM information_schema.statistics s
        LEFT JOIN information_schema.table_constraints cst ON s.index_name = cst.constraint_name
    WHERE s.table_schema = DATABASE() AND cst.constraint_type IS NULL
    GROUP BY s.table_name, s.index_name
)
SELECT data.*, CASE WHEN index_name = calculated_name THEN 'OK' ELSE 'FAIL' END test_status
FROM data;
