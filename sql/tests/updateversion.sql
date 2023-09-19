/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

SELECT "Checking presence of updateversion column";

CREATE OR REPLACE VIEW schemacheck_updateversion AS
SELECT t.table_name, c.column_name, c.is_nullable, c.column_type, c.extra,
CASE
    WHEN c.column_name IS NULL THEN 'FAIL'
    WHEN c.is_nullable <> 'NO' THEN 'FAIL'
    WHEN c.column_type <> 'int(11) unsigned' AND c.column_type <> 'int(10) unsigned' THEN 'FAIL'
    ELSE 'OK'
END as test_status
FROM information_schema.tables t
LEFT JOIN information_schema.columns c ON c.table_name = t.table_name AND c.table_schema = t.table_schema AND c.column_name = 'updateversion'
WHERE t.table_schema = DATABASE() AND t.table_type = 'BASE TABLE'
AND t.table_name NOT IN (
    -- Non-transactional data
    'applicationlog',

    -- Read-only tables
    'netmask', 'schemaversion',

    -- Non-dataobject tables
    'idcache',

    -- Write-once tables
    'xfftrustcache', 'requestdata'
);
