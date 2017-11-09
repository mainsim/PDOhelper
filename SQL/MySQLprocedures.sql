CREATE PROCEDURE `selectData`(
	IN _tables VARCHAR(255),
	IN _fields TEXT,
	IN _joins TEXT,
	IN _condition TEXT,
	IN _group TEXT,
	IN _order TEXT,
	IN _limit TEXT
)
BEGIN 
	DECLARE code CHAR(5) DEFAULT '00000';
	DECLARE msg TEXT;
	DECLARE rows INT;
	DECLARE result TEXT;
    
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
    BEGIN
      GET DIAGNOSTICS CONDITION 1
        code = RETURNED_SQLSTATE, msg = MESSAGE_TEXT;
    END;
    
    SET @sql = CONCAT('SELECT ', _fields, ' FROM ', _tables, ' ', _joins, ' ', _condition, ' ', _group, ' ', _order, ' ', _limit, ';');
	PREPARE _query FROM @sql;
	EXECUTE _query;
    
    IF code = '00000' THEN
		GET DIAGNOSTICS rows = ROW_COUNT;
		/* for eventualy log of selected items */
	ELSE
		SET result = CONCAT('select failed, error = ', code,', message = ', msg, @sql);
        SELECT result AS mysqlerror;
	END IF;
END

CREATE PROCEDURE `insertData`(
	IN _table VARCHAR(255),
	IN _fields TEXT,
	IN _values TEXT,
	IN _increment VARCHAR(30)
)
BEGIN 
	DECLARE code CHAR(5) DEFAULT '00000';
	DECLARE msg TEXT;
	DECLARE rows INT;
	DECLARE result TEXT;
    
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
    BEGIN
      GET DIAGNOSTICS CONDITION 1
        code = RETURNED_SQLSTATE, msg = MESSAGE_TEXT;
    END;
  
	SET @sql = CONCAT('INSERT INTO ', _table, ' (', _fields, ') VALUES (', _values, ');');
	PREPARE _query FROM @sql;
	EXECUTE _query;
    
    IF code = '00000' THEN
		GET DIAGNOSTICS rows = ROW_COUNT;
		SET @sql = CONCAT('SELECT MAX(', _increment, ') AS lastID FROM ', _table, ';');
		PREPARE _query FROM @sql;
		EXECUTE _query;
	ELSE
		SET result = CONCAT('insert failed, error = ', code,', message = ', msg, @sql);
        SELECT result AS mysqlerror;
	END IF;
END

CREATE PROCEDURE `insertMultipleData`(
	IN _table VARCHAR(255),
	IN _fields TEXT,
	IN _data LONGTEXT,
	IN _increment VARCHAR(30)
)
BEGIN 
	DECLARE code CHAR(5) DEFAULT '00000';
	DECLARE msg TEXT;
	DECLARE rows INT;
	DECLARE result TEXT;
    
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
    BEGIN 
      GET DIAGNOSTICS CONDITION 1
        code = RETURNED_SQLSTATE, msg = MESSAGE_TEXT;
    END;

    SET @sql = CONCAT('SELECT MAX(', _increment,') INTO @rows FROM ', _table);
    PREPARE _alterquery FROM @sql;
	EXECUTE _alterquery;
    
    SET @sql = CONCAT('ALTER TABLE ', _table, ' AUTO_INCREMENT =', @rows);
    PREPARE _alterquery FROM @sql;
	EXECUTE _alterquery;
    
    SET @sql = CONCAT('SELECT MAX(', _increment, ') INTO @firstID FROM ', _table, ';');
	PREPARE _query FROM @sql;
	EXECUTE _query;
  
	SET @sql = CONCAT('INSERT INTO ', _table, ' (', _fields, ') VALUES ', _data, ';');
	PREPARE _query FROM @sql;
	EXECUTE _query;
    
    IF code = '00000' THEN
		GET DIAGNOSTICS rows = ROW_COUNT;
		SET @sql = CONCAT('SELECT MAX(', _increment, ') INTO @lastID FROM ', _table, ';');
		PREPARE _query FROM @sql;
		EXECUTE _query;
        SET @count = @firstID;
		SET @crumbs = '';
        WHILE @count < @lastID DO
			SET @count = @count + 1;
            SET @crumbs = CONCAT(@crumbs, @count, ',');
        END WHILE;
        SELECT SUBSTRING(@crumbs FROM 1 FOR LENGTH(@crumbs) - 1) AS lastIDS;
	ELSE
		SET result = CONCAT('insert failed, error = ', code,', message = ', msg, @sql);
        SELECT result as mysqlerror;
	END IF;
END

CREATE PROCEDURE `updateData`(
	IN _table VARCHAR(255),
	IN _fields TEXT,
	IN _condition TEXT
)
BEGIN
	DECLARE code CHAR(5) DEFAULT '00000';
	DECLARE msg TEXT;
	DECLARE rows INT;
	DECLARE result TEXT;
    
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
    BEGIN
      GET DIAGNOSTICS CONDITION 1
        code = RETURNED_SQLSTATE, msg = MESSAGE_TEXT;
    END;
    
	SET @sql = CONCAT('UPDATE ', _table, ' SET ', _fields, _condition);
	PREPARE _query FROM @sql;
	EXECUTE _query;
    
	IF code = '00000' THEN
		GET DIAGNOSTICS rows = ROW_COUNT;
		SELECT true AS updated;
	ELSE
		SET result = CONCAT('update failed, error = ', code,', message = ', msg, @sql);
        SELECT result AS mysqlerror;
	END IF;
END

CREATE PROCEDURE `deleteData`(
	IN _table VARCHAR(255),
	IN _condition TEXT
)
BEGIN
	DECLARE code CHAR(5) DEFAULT '00000';
	DECLARE msg TEXT;
	DECLARE rows INT;
	DECLARE result TEXT;
    
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
    BEGIN
      GET DIAGNOSTICS CONDITION 1
        code = RETURNED_SQLSTATE, msg = MESSAGE_TEXT;
    END;
    
	SET @sql = CONCAT('DELETE FROM ', _table, _condition, ';');
	PREPARE _query FROM @sql;
	EXECUTE _query;
    
    SET @sql = CONCAT('SELECT MAX(', _increment,') INTO @rows FROM ', _table);
    PREPARE _alterquery FROM @sql;
	EXECUTE _alterquery;
    
    SET @sql = CONCAT('ALTER TABLE ', _table, ' AUTO_INCREMENT =', @rows);
    PREPARE _alterquery FROM @sql;
	EXECUTE _alterquery;
    
    IF code = '00000' THEN
		GET DIAGNOSTICS rows = ROW_COUNT;
		SELECT true AS deleted;
	ELSE
		SET result = CONCAT('delete failed, error = ', code,', message = ', msg, @sql);
        SELECT result AS mysqlerror;
	END IF;
END

CREATE PROCEDURE `tablesColumnsData`(
	IN _db VARCHAR(45)
)
BEGIN
    DECLARE code CHAR(5) DEFAULT '00000';
	DECLARE msg TEXT;
	DECLARE rows INT;
	DECLARE result TEXT;
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
		BEGIN
		  GET DIAGNOSTICS CONDITION 1
			code = RETURNED_SQLSTATE, msg = MESSAGE_TEXT;
		END;

	SET SESSION group_concat_max_len = 1000000;
	SET @sql = CONCAT('SELECT TABLE_NAME AS tname, GROUP_CONCAT(COLUMN_NAME SEPARATOR \',\') AS fname, GROUP_CONCAT(DATA_TYPE SEPARATOR \',\') AS ftype FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA =\'', _db, '\' GROUP BY TABLE_NAME');
	PREPARE _query FROM @sql;
	EXECUTE _query;

    IF code = '00000' THEN
		GET DIAGNOSTICS rows = ROW_COUNT;
        /* for eventualy log of returned items */
	ELSE
		SET result = CONCAT('select failed, error = ', code,', message = ', msg, @sql);
        SELECT result AS mysqlerror;
	END IF;
END
