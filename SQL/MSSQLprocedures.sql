USE [your_db]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER OFF
GO
-- =============================================
-- Author:		Sven Macolic
-- Create date: 26.06.2017
-- Description:	Select query
-- =============================================
CREATE PROCEDURE selectData
	@_tables VARCHAR(1000),
	@_fields VARCHAR(1000),
	@_joins VARCHAR(4000),
	@_condition VARCHAR(4000),
	@_group VARCHAR(255),
	@_order VARCHAR(255),
	@_limit VARCHAR(35)
AS
BEGIN
	DECLARE @_query NVARCHAR(max);
	DECLARE @DetailedErrorDesc NVARCHAR(MAX);
	SET NOCOUNT ON;
	BEGIN TRY
		SET @_query = 'SELECT '+@_fields+' FROM '+@_tables+' '+@_joins+' '+@_condition+' '+@_group+' '+@_order+' '+@_limit;
		EXEC(@_query);
	END TRY
	BEGIN CATCH
		SET @DetailedErrorDesc =         
		  CAST(ERROR_NUMBER() AS VARCHAR) + ' : ' +
		  CAST(ERROR_SEVERITY() AS VARCHAR) + ' : ' +
		  CAST(ERROR_STATE() AS VARCHAR) + ' : ' +
		  'selectData : ' +
		  ERROR_MESSAGE() + ' : ' + 
		  CAST(ERROR_LINE() AS VARCHAR) + ' : ' +
		  @_query;
		SELECT @DetailedErrorDesc AS mssqlerror;
	END CATCH
END


USE [your_db]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Sven Macolic
-- Create date: 26.06.2017
-- Description:	Insert query
-- =============================================
CREATE PROCEDURE insertData
	@_table VARCHAR(255),
	@_fields VARCHAR(1000),
	@_values VARCHAR(1000),
	@_increment VARCHAR(35)
AS
BEGIN
	DECLARE @_query NVARCHAR(max);
	DECLARE @DetailedErrorDesc NVARCHAR(MAX);
	SET NOCOUNT ON;
	BEGIN TRY
		SET @_query = 'INSERT INTO '+@_table+' ('+@_fields+') VALUES ('+@_values+')';
		EXEC(@_query);
		SET @_query = 'SELECT TOP 1 '+@_increment+' AS lastID FROM '+@_table+' ORDER BY '+@_increment+' DESC';
		EXEC(@_query);
	END TRY
	BEGIN CATCH
		SET @DetailedErrorDesc =         
		  CAST(ERROR_NUMBER() AS VARCHAR) + ' : ' +
		  CAST(ERROR_SEVERITY() AS VARCHAR) + ' : ' +
		  CAST(ERROR_STATE() AS VARCHAR) + ' : ' +
		  'insertData : ' +
		  ERROR_MESSAGE() + ' : ' +
		  CAST(ERROR_LINE() AS VARCHAR) + ' : ' +
		  @_query;
		SELECT @DetailedErrorDesc AS mssqlerror;
	END CATCH
END

USE [your_db]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Sven Macolic
-- Create date: 29.06.2017
-- Description:	Insert query
-- =============================================
CREATE PROCEDURE insertMultipleData
	@_table VARCHAR(255),
	@_fields VARCHAR(4000),
	@_data NVARCHAR(MAX),
	@_increment VARCHAR(35)
AS
BEGIN	
	DECLARE @DetailedErrorDesc NVARCHAR(MAX);
	DECLARE @_firstID INT;
	DECLARE @_query NVARCHAR(MAX);
	DECLARE @_lastID INT;
	DECLARE @_count INT;
	DECLARE @_crumbs NVARCHAR(MAX) = '';
	DECLARE @_rquery NVARCHAR(MAX);
	DECLARE @_rows INT;
	DECLARE @_aquery NVARCHAR(MAX);
	SET NOCOUNT ON;
	BEGIN TRY         
		SET @_rquery = N'SELECT @rows = MAX('+@_increment+') FROM '+@_table;
		EXECUTE sp_executesql @_rquery, N'@rows INT OUTPUT', @rows = @_rows OUTPUT;
		SET @_aquery = 'DBCC CHECKIDENT('+@_table+', RESEED, '+CAST(@_rows AS NVARCHAR(30))+')';
		EXEC(@_aquery);
		SET @_query = N'SELECT TOP 1 @firstID = '+@_increment+' FROM '+@_table+' ORDER BY '+@_increment+' DESC';
		EXECUTE sp_executesql @_query, N'@firstID INT OUTPUT', @firstID = @_firstID OUTPUT;
		SET @_query = 'INSERT INTO '+@_table+' ('+@_fields+') VALUES '+@_data;
		EXEC(@_query);
		SET @_query = N'SELECT TOP 1 @lastID = '+@_increment+' FROM '+@_table+' ORDER BY '+@_increment+' DESC';
		EXECUTE sp_executesql @_query, N'@lastID INT OUTPUT', @lastID = @_lastID OUTPUT;
		SET @_count = @_firstID;
		WHILE @_count < @_lastID 
		    BEGIN
				SET @_count = @_count + 1;
				SET @_crumbs += CAST(@_count AS NVARCHAR(30)) + CASE WHEN @_lastID > @_count THEN ',' ELSE '' END;				
			END
		SELECT @_crumbs AS lastIDS;
	END TRY
	BEGIN CATCH
		SET @DetailedErrorDesc =         
		  CAST(ERROR_NUMBER() AS VARCHAR) + ' : ' +
		  CAST(ERROR_SEVERITY() AS VARCHAR) + ' : ' +
		  CAST(ERROR_STATE() AS VARCHAR) + ' : ' +
		  'insertMultipleData : ' +
		  ERROR_MESSAGE() + ' : ' +
		  CAST(ERROR_LINE() AS VARCHAR) + ' : ' +
		  @_query;
		SELECT @DetailedErrorDesc AS mssqlerror;
	END CATCH
END


USE [your_db]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Sven Macolic
-- Create date: 29.06.2017
-- Description:	Update query
-- =============================================
CREATE PROCEDURE updateData
	@_table VARCHAR(750),
	@_fields VARCHAR(750),
	@_condition VARCHAR(4000)
AS
BEGIN
	DECLARE @_query NVARCHAR(MAX);
	DECLARE @DetailedErrorDesc NVARCHAR(MAX);
	SET NOCOUNT ON;
	BEGIN TRY
		SET @_query = 'UPDATE '+@_table+' SET '+@_fields+' '+@_condition;
		EXECUTE(@_query);
		SELECT 1 AS updated;
	END TRY
	BEGIN CATCH
		SET @DetailedErrorDesc =         
		  CAST(ERROR_NUMBER() AS VARCHAR) + ' : ' +
		  CAST(ERROR_SEVERITY() AS VARCHAR) + ' : ' +
		  CAST(ERROR_STATE() AS VARCHAR) + ' : ' +
		  'updateData : ' +
		  ERROR_MESSAGE() + ' : ' +
		  CAST(ERROR_LINE() AS VARCHAR) + ' : ' +
		  @_query;
		SELECT @DetailedErrorDesc AS mssqlerror;
	END CATCH
END


USE [your_db]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Sven Macolic
-- Create date: 29.06.2017
-- Description:	Delete query
-- =============================================
CREATE PROCEDURE deleteData
	@_table VARCHAR(750),
	@_condition VARCHAR(4000)
AS
BEGIN
	DECLARE @_query NVARCHAR(MAX);
	DECLARE @DetailedErrorDesc NVARCHAR(MAX);
	DECLARE @_rquery NVARCHAR(MAX);
	DECLARE @_rows INT;
	DECLARE @_aquery NVARCHAR(MAX);
	SET NOCOUNT ON;
	BEGIN TRY
		SET @_query = 'DELETE FROM '+@_table+' '+@_condition;
		EXEC(@_query);
		SET @_rquery = N'SELECT @rows = MAX('+@_increment+') FROM '+@_table;
		EXECUTE sp_executesql @_rquery, N'@rows INT OUTPUT', @rows = @_rows OUTPUT;
		SET @_aquery = 'DBCC CHECKIDENT('+@_table+', RESEED, '+CAST(@_rows AS NVARCHAR(30))+')';
		EXEC(@_aquery);
		SELECT 1 AS deleted;
	END TRY
	BEGIN CATCH
		SET @DetailedErrorDesc =         
		  CAST(ERROR_NUMBER() AS VARCHAR) + ' : ' +
		  CAST(ERROR_SEVERITY() AS VARCHAR) + ' : ' +
		  CAST(ERROR_STATE() AS VARCHAR) + ' : ' +
		  'deleteData : ' +
		  ERROR_MESSAGE() + ' : ' +
		  CAST(ERROR_LINE() AS VARCHAR) + ' : ' +
		  @_query;
		SELECT @DetailedErrorDesc AS mssqlerror;
	END CATCH
END

USE [your_db]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Sven Macolic
-- Create date: 03.07.2017
-- Description:	Get shema tables and fields per table
-- =============================================
CREATE PROCEDURE tablesColumnsData
	@_db VARCHAR(45)
AS
BEGIN
	DECLARE @_query NVARCHAR(max);
	DECLARE @DetailedErrorDesc NVARCHAR(MAX);
	DECLARE @sec INT;
	SET @sec = 1;
	SET NOCOUNT ON;
	BEGIN TRY
		BEGIN
			SET @_query = 'SELECT TABLE_NAME AS tname, STUFF((SELECT '',''+COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ext.TABLE_NAME FOR XML PATH('''')), 1, 1, '''') AS fname, STUFF((SELECT '',''+DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ext.TABLE_NAME FOR XML PATH('''')), 1, 1, '''') AS ftype FROM INFORMATION_SCHEMA.COLUMNS AS ext WHERE TABLE_CATALOG = '''+@_db+''' GROUP BY TABLE_NAME';
			EXEC(@_query);
		END
	END TRY
	BEGIN CATCH
		SET @DetailedErrorDesc =         
		  CAST(ERROR_NUMBER() AS VARCHAR) + ' : ' +
		  CAST(ERROR_SEVERITY() AS VARCHAR) + ' : ' +
		  CAST(ERROR_STATE() AS VARCHAR) + ' : ' +
		  'tablesColumnsData : ' +
		  ERROR_MESSAGE() + ' : ' +
		  CAST(ERROR_LINE() AS VARCHAR) + ' : ' +
		  @_query;
		SELECT @DetailedErrorDesc AS mssqlerror;
	END CATCH
END
