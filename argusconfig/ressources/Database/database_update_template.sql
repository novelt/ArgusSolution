DELIMITER $$

DROP PROCEDURE IF EXISTS upgrade_database_VX$$
CREATE PROCEDURE upgrade_database_VX()
BEGIN

	-- Remove table table_name
	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'table_name') THEN		
		DROP TABLE table_name;
	END IF;
	
	-- Create table table_name
	IF NOT EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'table_name') THEN		
		CREATE TABLE table_name (
			-- ...
		)		
	END IF;
		
	-- Add column columnName in the table tableName
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tableName' AND COLUMN_NAME = 'columnName') THEN		
		ALTER TABLE tableName ADD columnName ...;		
	END IF;
	
	-- Remove column columnName from the table tableName
	IF EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tableName' AND COLUMN_NAME = 'columnName') THEN		
		ALTER TABLE tableName DROP columnName;		
	END IF;
	
	-- Create index indexName in the table tableName
	IF NOT EXISTS (SELECT * FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tableName' AND INDEX_NAME = 'indexName') THEN
		CREATE INDEX indexName ON tableName (...);
	END IF;
	
	-- Remove index indexName from the table tableName
	IF EXISTS (SELECT * FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tableName' AND INDEX_NAME = 'indexName') THEN
		DROP INDEX indexName ON tableName;
	END IF;
	
	-- Add foreign key constraint FK_name on tableName
	IF NOT EXISTS (SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tableName' AND CONSTRAINT_NAME = 'FK_name') THEN
		ALTER TABLE tableName ADD CONSTRAINT FK_name...;
	END IF;
	
	-- Remove foreign key constraint FK_name from tableName
	IF EXISTS (SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tableName' AND CONSTRAINT_NAME = 'FK_name') THEN
		ALTER TABLE tableName DROP CONSTRAINT FK_name;
	END IF;
	
	
END $$

CALL upgrade_database_VX() $$
DROP PROCEDURE IF EXISTS upgrade_database_VX $$

DELIMITER ;