DROP PROCEDURE IF EXISTS SCHEMA_GRANTS_REFRESH;
DELIMITER ';;'
CREATE PROCEDURE SCHEMA_GRANTS_REFRESH(
      IN schemaname VARCHAR(64)
    , IN targetuser VARCHAR(190)
    , IN privmode   VARCHAR(20)
) BEGIN
    -- Declare some quick variables
    DECLARE cursor_done INT;
    DECLARE tableName VARCHAR(64);
    DECLARE vsql VARCHAR(4000);

    -- --------------------------------------------------------------------------------------------------
    -- PERMISSIONS
    -- Add tables to these queries to assign grants as needed. Limit the rows by adding where clauses
    -- against privmode
    -- --------------------------------------------------------------------------------------------------
    DECLARE cur_select CURSOR FOR
        SELECT 'antispoofcache' UNION
        SELECT 'ban' UNION
        SELECT 'comment' UNION
        SELECT 'emailtemplate' UNION
        SELECT 'geolocation' UNION
        SELECT 'interfacemessage' UNION
        SELECT 'log' UNION
        SELECT 'rdnscache' UNION
        SELECT 'request' UNION
        SELECT 'schemaversion' UNION
        SELECT 'user' UNION
        SELECT 'welcometemplate' UNION
        SELECT 'xfftrustcache';

    DECLARE cur_update CURSOR FOR
        SELECT 'ban' UNION
        SELECT 'comment' UNION
        SELECT 'emailtemplate' UNION
        SELECT 'geolocation' UNION
        SELECT 'interfacemessage' UNION
        SELECT 'rdnscache' UNION
        SELECT 'request' UNION
        SELECT 'schemaversion' FROM DUAL WHERE privmode = 'maintenance' UNION
        SELECT 'user' UNION
        SELECT 'welcometemplate';

    DECLARE cur_insert CURSOR FOR
        SELECT 'antispoofcache' UNION
        SELECT 'applicationlog' UNION
        SELECT 'ban' UNION
        SELECT 'comment' UNION
        SELECT 'emailtemplate' UNION
        SELECT 'geolocation' UNION
        SELECT 'interfacemessage' UNION
        SELECT 'log' UNION
        SELECT 'rdnscache' UNION
        SELECT 'request' UNION
        SELECT 'user' UNION
        SELECT 'welcometemplate' UNION
        SELECT 'xfftrustcache' FROM DUAL WHERE privmode = 'maintenance';

    DECLARE cur_delete CURSOR FOR
        SELECT 'antispoofcache' FROM DUAL WHERE privmode = 'maintenance' UNION
        SELECT 'geolocation' FROM DUAL WHERE privmode = 'maintenance' UNION
        SELECT 'rdnscache' FROM DUAL WHERE privmode = 'maintenance' UNION
        SELECT 'xfftrustcache' FROM DUAL WHERE privmode = 'maintenance';

    -- --------------------------------------------------------------------------------------------------
    -- These two cursors find all existing mysql grants on this schema and systematically revoke them.
    DECLARE cur_revschema CURSOR FOR
        SELECT CONCAT('REVOKE ALL PRIVILEGES ON ', table_schema, '.* FROM ', grantee)
        FROM information_schema.schema_privileges
        WHERE grantee = targetuser
        GROUP BY table_schema, grantee;

    DECLARE cur_revtable CURSOR FOR
        SELECT CONCAT('REVOKE ALL PRIVILEGES ON ', table_schema, '.', table_name, ' FROM ', grantee)
        FROM information_schema.table_privileges
        WHERE grantee = targetuser AND table_schema = schemaname
        GROUP BY grantee, table_schema, table_name;

    -- Continue handler for when a cursor fetch retrieves no data.
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET cursor_done = 1;

    IF privmode NOT IN ('web', 'maintenance') THEN
        SIGNAL SQLSTATE '45000' SET message_text = 'The privmode parameter should be either web or maintenance';
    END IF;

    IF targetuser NOT LIKE '\'%\'@\'%\'' THEN
        SIGNAL SQLSTATE '45000' SET message_text = 'Please specify user in the standard MySQL format of \'user\'@\'host\'.';
    END IF;

    -- Revoke everything to begin with - looping over result sets from the revocation
    -- cursors and running the dynamic sql; firstly schema-wide, then table-specific.
    SET cursor_done = 0;
    OPEN cur_revschema;
    revschema: REPEAT
        FETCH cur_revschema INTO vsql;
        IF cursor_done THEN
            LEAVE revschema;
        END IF;
        SET @sqlText = vsql;
        PREPARE statement FROM @sqlText;
        EXECUTE statement;
        DEALLOCATE PREPARE statement;
    UNTIL cursor_done END REPEAT revschema;
    CLOSE cur_revschema;

    SET cursor_done = 0;
    OPEN cur_revtable;
    revtable: REPEAT
        FETCH cur_revtable INTO vsql;
        IF cursor_done THEN
            LEAVE revtable;
        END IF;
        SET @sqlText = vsql;
        PREPARE statement FROM @sqlText;
        EXECUTE statement;
        DEALLOCATE PREPARE statement;
    UNTIL cursor_done END REPEAT revtable;
    CLOSE cur_revtable;

    -- Set up the granular permissions as needed using the table lists in the cursors at the
    -- top of the file
    SET cursor_done = 0;
    OPEN cur_select;
    lselect: REPEAT
        FETCH cur_select INTO tableName;
        IF cursor_done THEN
            LEAVE lselect;
        END IF;
        SET @sqlText = CONCAT('GRANT SELECT ON ', schemaname, '.', tableName, ' TO ', targetuser);
        PREPARE statement FROM @sqlText;
        EXECUTE statement;
        DEALLOCATE PREPARE statement;
    UNTIL cursor_done END REPEAT lselect;
    CLOSE cur_select;

    SET cursor_done = 0;
    OPEN cur_update;
    lupdate: REPEAT
        FETCH cur_update INTO tableName;
        IF cursor_done THEN
            LEAVE lupdate;
        END IF;
        SET @sqlText = CONCAT('GRANT UPDATE ON ', schemaname, '.', tableName, ' TO ', targetuser);
        PREPARE statement FROM @sqlText;
        EXECUTE statement;
        DEALLOCATE PREPARE statement;
    UNTIL cursor_done END REPEAT lupdate;
    CLOSE cur_update;

    SET cursor_done = 0;
    OPEN cur_insert;
    linsert: REPEAT
        FETCH cur_insert INTO tableName;
        IF cursor_done THEN
            LEAVE linsert;
        END IF;
        SET @sqlText = CONCAT('GRANT INSERT ON ', schemaname, '.', tableName, ' TO ', targetuser);
        PREPARE statement FROM @sqlText;
        EXECUTE statement;
        DEALLOCATE PREPARE statement;
    UNTIL cursor_done END REPEAT linsert;
    CLOSE cur_insert;

    SET cursor_done = 0;
    OPEN cur_delete;
    ldelete: REPEAT
        FETCH cur_delete INTO tableName;
        IF cursor_done THEN
            LEAVE ldelete;
        END IF;
        SET @sqlText = CONCAT('GRANT DELETE ON ', schemaname, '.', tableName, ' TO ', targetuser);
        PREPARE statement FROM @sqlText;
        EXECUTE statement;
        DEALLOCATE PREPARE statement;
    UNTIL cursor_done END REPEAT ldelete;
    CLOSE cur_delete;

    -- All done!
END;;
DELIMITER ';'
