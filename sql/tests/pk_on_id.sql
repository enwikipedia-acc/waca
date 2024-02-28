/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

SELECT "Checking primary keys based on id columns";

CREATE OR REPLACE VIEW schemacheck_pk_on_id AS
WITH col_data AS (
    SELECT table_name, index_name, group_concat(column_name ORDER BY seq_in_index SEPARATOR '_') columns
    FROM information_schema.STATISTICS
    WHERE table_schema = DATABASE() AND index_name = 'PRIMARY'
    GROUP BY table_name, index_name
)
SELECT col_data.*,
    CASE WHEN columns = 'id' THEN 'OK' ELSE 'FAIL' END AS test_status
FROM col_data
WHERE table_name NOT IN (
    -- This is a single-row lookup table. There's no need for a surrogate key.
    'schemaversion', 
    -- Lookup-only readonly table with a low rowcount.
    'netmask'
);
