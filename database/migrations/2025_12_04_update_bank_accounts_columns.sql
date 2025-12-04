-- Migración: Actualizar tabla bank_accounts con columnas adicionales
-- Fecha: 2025-12-04
-- Descripción: Añadir columnas faltantes para integración contable y control bancario

-- Añadir columna de vinculación contable
ALTER TABLE bank_accounts 
ADD COLUMN IF NOT EXISTS accounting_account_id INT COMMENT 'ID en accounting_accounts (572, etc.)' AFTER currency;

-- Añadir columna de fecha de saldo
ALTER TABLE bank_accounts 
ADD COLUMN IF NOT EXISTS balance_date DATE COMMENT 'Fecha del último saldo conocido' AFTER current_balance;

-- Añadir límite de descubierto
ALTER TABLE bank_accounts 
ADD COLUMN IF NOT EXISTS overdraft_limit DECIMAL(15,2) DEFAULT 0 COMMENT 'Límite de descubierto' AFTER balance_date;

-- Añadir comisión mensual
ALTER TABLE bank_accounts 
ADD COLUMN IF NOT EXISTS monthly_fee DECIMAL(10,2) DEFAULT 0 COMMENT 'Comisión mensual' AFTER overdraft_limit;

-- Añadir columnas de conciliación
ALTER TABLE bank_accounts 
ADD COLUMN IF NOT EXISTS last_reconciliation_date DATE COMMENT 'Última conciliación bancaria' AFTER is_default;

ALTER TABLE bank_accounts 
ADD COLUMN IF NOT EXISTS last_reconciliation_balance DECIMAL(15,2) AFTER last_reconciliation_date;

-- Añadir foreign key para accounting_account_id si no existe
-- Nota: Solo se ejecuta si la columna ya existe y no tiene FK
SET @fk_exists = (SELECT COUNT(*) 
                  FROM information_schema.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'bank_accounts' 
                  AND COLUMN_NAME = 'accounting_account_id' 
                  AND REFERENCED_TABLE_NAME IS NOT NULL);

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE bank_accounts ADD CONSTRAINT fk_bank_accounting 
     FOREIGN KEY (accounting_account_id) REFERENCES accounting_accounts(id) ON DELETE SET NULL',
    'SELECT "Foreign key already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
