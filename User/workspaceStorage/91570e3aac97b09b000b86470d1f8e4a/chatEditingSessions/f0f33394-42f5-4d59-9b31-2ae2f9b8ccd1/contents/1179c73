-- Migración: Crear tablas del módulo Bancario
-- Fecha: 2025-12-04
-- Descripción: Tablas para gestión bancaria completa

-- Tabla de cuentas bancarias
CREATE TABLE IF NOT EXISTS bank_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_name VARCHAR(255) NOT NULL,
    account_number VARCHAR(50) NOT NULL UNIQUE,
    iban VARCHAR(34),
    swift VARCHAR(11),
    bank_name VARCHAR(255) NOT NULL,
    bank_branch VARCHAR(255),
    account_type ENUM('checking', 'savings', 'credit', 'other') DEFAULT 'checking',
    currency VARCHAR(3) DEFAULT 'EUR',
    initial_balance DECIMAL(12,2) DEFAULT 0.00,
    current_balance DECIMAL(12,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT 1,
    is_default BOOLEAN DEFAULT 0,
    opening_date DATE,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_default (is_default),
    INDEX idx_account (account_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de transacciones bancarias
CREATE TABLE IF NOT EXISTS bank_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    transaction_date DATE NOT NULL,
    value_date DATE,
    transaction_type ENUM('debit', 'credit', 'transfer_in', 'transfer_out', 'fee', 'interest', 'other') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    balance_after DECIMAL(12,2),
    concept VARCHAR(500),
    description TEXT,
    reference VARCHAR(100),
    payee VARCHAR(255),
    category VARCHAR(100),
    invoice_id INT,
    is_reconciled BOOLEAN DEFAULT 0,
    reconciled_at DATETIME,
    reconciled_by INT,
    notes TEXT,
    imported_from VARCHAR(50),
    imported_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES bank_accounts(id) ON DELETE CASCADE,
    INDEX idx_account (account_id),
    INDEX idx_date (transaction_date),
    INDEX idx_type (transaction_type),
    INDEX idx_reconciled (is_reconciled),
    INDEX idx_amount (amount),
    INDEX idx_reference (reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de conciliaciones bancarias
CREATE TABLE IF NOT EXISTS bank_reconciliations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    reconciliation_date DATE NOT NULL,
    statement_balance DECIMAL(12,2) NOT NULL,
    book_balance DECIMAL(12,2) NOT NULL,
    difference DECIMAL(12,2) NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    transactions_reconciled INT DEFAULT 0,
    transactions_pending INT DEFAULT 0,
    reconciled_by INT,
    started_at DATETIME,
    completed_at DATETIME,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES bank_accounts(id) ON DELETE CASCADE,
    INDEX idx_account (account_id),
    INDEX idx_date (reconciliation_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de matching entre transacciones y facturas/movimientos
CREATE TABLE IF NOT EXISTS bank_transaction_matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    match_type ENUM('invoice', 'payment', 'expense', 'transfer', 'manual') NOT NULL,
    match_id INT NOT NULL,
    match_amount DECIMAL(12,2) NOT NULL,
    matched_by INT,
    matched_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    confidence DECIMAL(3,2) DEFAULT 1.00,
    notes TEXT,
    FOREIGN KEY (transaction_id) REFERENCES bank_transactions(id) ON DELETE CASCADE,
    INDEX idx_transaction (transaction_id),
    INDEX idx_match (match_type, match_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de reglas de importación/categorización
CREATE TABLE IF NOT EXISTS bank_import_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rule_name VARCHAR(100) NOT NULL,
    pattern VARCHAR(255) NOT NULL,
    pattern_field ENUM('concept', 'description', 'reference', 'payee') DEFAULT 'concept',
    category VARCHAR(100),
    transaction_type ENUM('debit', 'credit', 'transfer_in', 'transfer_out', 'fee', 'interest', 'other'),
    auto_apply BOOLEAN DEFAULT 1,
    priority INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar cuenta bancaria ejemplo
INSERT IGNORE INTO bank_accounts (account_name, account_number, iban, bank_name, account_type, initial_balance, current_balance, is_default) VALUES
('Cuenta Principal', '1234567890', 'ES1234567890123456789012', 'Banco Ejemplo', 'checking', 10000.00, 10000.00, 1);

-- Insertar reglas de categorización ejemplo
INSERT IGNORE INTO bank_import_rules (rule_name, pattern, pattern_field, category, transaction_type) VALUES
('Pagos de Nómina', 'NOMINA', 'concept', 'Nóminas', 'debit'),
('Ingresos Cuotas', 'CUOTA', 'concept', 'Cuotas Socios', 'credit'),
('Pagos Suministros', 'SUMINISTRO|LUZ|AGUA|GAS', 'concept', 'Suministros', 'debit'),
('Comisiones Bancarias', 'COMISION|FEE', 'concept', 'Comisiones', 'fee');

SELECT '✓ Tablas Bancarias creadas correctamente' AS Status;
SELECT CONCAT('✓ ', COUNT(*), ' cuentas bancarias') AS Accounts FROM bank_accounts;
