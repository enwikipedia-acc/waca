/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

SELECT "Checking transactional engines used for transactional tables";

CREATE OR REPLACE VIEW schemacheck_transactional_engines AS
SELECT t.table_name, t.engine, e.transactions,
    CASE WHEN e.transactions = 'YES' THEN 'OK' ELSE 'FAIL' END AS test_status
FROM information_schema.tables t
INNER JOIN information_schema.engines e ON t.engine = e.engine
WHERE t.table_schema = DATABASE()
    AND t.table_type = 'BASE TABLE'
    AND t.table_name NOT IN ('applicationlog');
