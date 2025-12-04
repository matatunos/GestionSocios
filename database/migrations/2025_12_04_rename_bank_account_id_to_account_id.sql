-- Migración: Renombrar bank_account_id a account_id en tablas bancarias
-- Fecha: 2025-12-04
-- Descripción: Estandarizar nombres de columnas en módulo bancario

-- Verificar si bank_transactions existe y tiene la columna bank_account_id
SET @table_exists = (SELECT COUNT(*) 
                     FROM information_schema.TABLES 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'bank_transactions');

SET @old_column_exists = (SELECT COUNT(*) 
                          FROM information_schema.COLUMNS 
                          WHERE TABLE_SCHEMA = DATABASE() 
                          AND TABLE_NAME = 'bank_transactions' 
                          AND COLUMN_NAME = 'bank_account_id');

SET @new_column_exists = (SELECT COUNT(*) 
                          FROM information_schema.COLUMNS 
                          WHERE TABLE_SCHEMA = DATABASE() 
                          AND TABLE_NAME = 'bank_transactions' 
                          AND COLUMN_NAME = 'account_id');

-- Solo renombrar si existe la columna antigua y no existe la nueva
SET @sql = IF(@table_exists > 0 AND @old_column_exists > 0 AND @new_column_exists = 0,
    'ALTER TABLE bank_transactions CHANGE COLUMN bank_account_id account_id INT NOT NULL',
    'SELECT "Column already renamed or table does not exist" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Hacer lo mismo para bank_reconciliations
SET @old_column_exists_rec = (SELECT COUNT(*) 
                              FROM information_schema.COLUMNS 
                              WHERE TABLE_SCHEMA = DATABASE() 
                              AND TABLE_NAME = 'bank_reconciliations' 
                              AND COLUMN_NAME = 'bank_account_id');

SET @new_column_exists_rec = (SELECT COUNT(*) 
                              FROM information_schema.COLUMNS 
                              WHERE TABLE_SCHEMA = DATABASE() 
                              AND TABLE_NAME = 'bank_reconciliations' 
                              AND COLUMN_NAME = 'account_id');

SET @sql2 = IF(@old_column_exists_rec > 0 AND @new_column_exists_rec = 0,
    'ALTER TABLE bank_reconciliations CHANGE COLUMN bank_account_id account_id INT NOT NULL',
    'SELECT "Column already renamed in bank_reconciliations or table does not exist" AS message');

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SELECT '✓ Columnas bank_account_id renombradas a account_id' AS Status;
