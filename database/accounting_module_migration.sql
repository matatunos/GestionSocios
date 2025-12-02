-- ============================================
-- Advanced Accounting Module Migration
-- ============================================
-- This migration adds advanced accounting features including:
-- - Chart of accounts
-- - Double-entry bookkeeping (journal entries)
-- - Accounting periods
-- - Budget management
-- ============================================

-- Tabla de períodos contables
CREATE TABLE IF NOT EXISTS accounting_periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    fiscal_year INT NOT NULL,
    status ENUM('open', 'closed', 'locked') DEFAULT 'open',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de plan de cuentas (Chart of Accounts)
CREATE TABLE IF NOT EXISTS accounting_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    account_type ENUM('asset', 'liability', 'equity', 'income', 'expense') NOT NULL,
    parent_id INT DEFAULT NULL,
    level INT DEFAULT 0,
    balance_type ENUM('debit', 'credit') NOT NULL,
    is_active BOOLEAN DEFAULT 1,
    is_system BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES accounting_accounts(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_type (account_type),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de asientos contables (Journal Entries)
CREATE TABLE IF NOT EXISTS accounting_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_number VARCHAR(50) NOT NULL,
    entry_date DATE NOT NULL,
    period_id INT NOT NULL,
    description TEXT NOT NULL,
    reference VARCHAR(100),
    entry_type ENUM('manual', 'automatic') DEFAULT 'manual',
    source_type ENUM('expense', 'payment', 'donation', 'manual') DEFAULT 'manual',
    source_id INT DEFAULT NULL,
    status ENUM('draft', 'posted', 'cancelled') DEFAULT 'draft',
    created_by INT NOT NULL,
    posted_by INT DEFAULT NULL,
    posted_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (period_id) REFERENCES accounting_periods(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_entry_number (entry_number),
    INDEX idx_entry_date (entry_date),
    INDEX idx_period (period_id),
    INDEX idx_status (status),
    INDEX idx_source (source_type, source_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de líneas de asiento (Entry Lines - double entry)
CREATE TABLE IF NOT EXISTS accounting_entry_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_id INT NOT NULL,
    account_id INT NOT NULL,
    description TEXT,
    debit DECIMAL(10,2) DEFAULT 0.00,
    credit DECIMAL(10,2) DEFAULT 0.00,
    line_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entry_id) REFERENCES accounting_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounting_accounts(id) ON DELETE CASCADE,
    INDEX idx_entry (entry_id),
    INDEX idx_account (account_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de presupuestos
CREATE TABLE IF NOT EXISTS budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    fiscal_year INT NOT NULL,
    account_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    period_type ENUM('yearly', 'monthly', 'quarterly') DEFAULT 'yearly',
    period_number INT DEFAULT NULL,
    status ENUM('draft', 'approved', 'active', 'closed') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounting_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_account (account_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar plan de cuentas básico (Spanish Chart of Accounts - PGC simplified)
INSERT INTO accounting_accounts (code, name, account_type, balance_type, level, is_system) VALUES
-- ACTIVO (Assets)
('100', 'Capital Social', 'equity', 'credit', 0, 1),
('129', 'Resultados del Ejercicio', 'equity', 'credit', 0, 1),
('430', 'Clientes', 'asset', 'debit', 0, 1),
('440', 'Deudores', 'asset', 'debit', 0, 1),
('470', 'Hacienda Pública, Deudora', 'asset', 'debit', 0, 1),
('570', 'Caja', 'asset', 'debit', 0, 1),
('572', 'Bancos e Instituciones de Crédito', 'asset', 'debit', 0, 1),

-- PASIVO (Liabilities)
('400', 'Proveedores', 'liability', 'credit', 0, 1),
('410', 'Acreedores', 'liability', 'credit', 0, 1),
('475', 'Hacienda Pública, Acreedora', 'liability', 'credit', 0, 1),

-- INGRESOS (Income)
('700', 'Ventas de Mercaderías', 'income', 'credit', 0, 1),
('705', 'Prestaciones de Servicios', 'income', 'credit', 0, 1),
('720', 'Cuotas de Socios', 'income', 'credit', 0, 1),
('721', 'Subvenciones', 'income', 'credit', 0, 1),
('722', 'Donaciones', 'income', 'credit', 0, 1),
('759', 'Otros Ingresos', 'income', 'credit', 0, 1),

-- GASTOS (Expenses)
('600', 'Compras', 'expense', 'debit', 0, 1),
('621', 'Arrendamientos', 'expense', 'debit', 0, 1),
('622', 'Reparaciones y Conservación', 'expense', 'debit', 0, 1),
('623', 'Servicios de Profesionales Independientes', 'expense', 'debit', 0, 1),
('624', 'Transportes', 'expense', 'debit', 0, 1),
('625', 'Primas de Seguros', 'expense', 'debit', 0, 1),
('626', 'Servicios Bancarios', 'expense', 'debit', 0, 1),
('627', 'Publicidad y Propaganda', 'expense', 'debit', 0, 1),
('628', 'Suministros', 'expense', 'debit', 0, 1),
('629', 'Otros Servicios', 'expense', 'debit', 0, 1),
('640', 'Sueldos y Salarios', 'expense', 'debit', 0, 1),
('642', 'Seguridad Social a cargo de la Empresa', 'expense', 'debit', 0, 1),
('649', 'Otros Gastos Sociales', 'expense', 'debit', 0, 1),
('678', 'Gastos Excepcionales', 'expense', 'debit', 0, 1);

-- Insertar período contable por defecto para año actual
INSERT INTO accounting_periods (name, start_date, end_date, fiscal_year, status, created_by) 
VALUES (
    CONCAT('Ejercicio ', YEAR(CURDATE())), 
    CONCAT(YEAR(CURDATE()), '-01-01'), 
    CONCAT(YEAR(CURDATE()), '-12-31'),
    YEAR(CURDATE()),
    'open',
    1
) ON DUPLICATE KEY UPDATE id=id;
