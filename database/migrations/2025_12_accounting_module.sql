-- ============================================
-- Advanced Accounting Module Migration Patch
-- ============================================
-- Versión: 1.0
-- Fecha: Diciembre 2025
-- Descripción: Añade módulo de contabilidad avanzada con partida doble
-- 
-- INSTRUCCIONES DE USO:
-- 1. Hacer backup de la base de datos antes de aplicar
-- 2. Ejecutar: mysql -u usuario -p nombre_bd < 2025_12_accounting_module.sql
-- 3. Verificar que las tablas se crearon correctamente
-- ============================================

-- Verificar que estamos en la base de datos correcta
SELECT 'Iniciando migración del módulo de contabilidad...' as mensaje;

-- ============================================
-- TABLA: accounting_periods
-- Gestión de períodos contables (ejercicios fiscales)
-- ============================================
CREATE TABLE IF NOT EXISTS accounting_periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT 'Nombre del período (ej: Ejercicio 2025)',
    start_date DATE NOT NULL COMMENT 'Fecha de inicio del período',
    end_date DATE NOT NULL COMMENT 'Fecha de fin del período',
    fiscal_year INT NOT NULL COMMENT 'Año fiscal',
    status ENUM('open', 'closed', 'locked') DEFAULT 'open' COMMENT 'Estado del período',
    created_by INT NOT NULL COMMENT 'Usuario que creó el período',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Períodos contables para organización por ejercicio fiscal';

-- ============================================
-- TABLA: accounting_accounts
-- Plan de cuentas contable (Chart of Accounts)
-- ============================================
CREATE TABLE IF NOT EXISTS accounting_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE COMMENT 'Código de cuenta (ej: 570, 700)',
    name VARCHAR(200) NOT NULL COMMENT 'Nombre de la cuenta',
    description TEXT COMMENT 'Descripción detallada',
    account_type ENUM('asset', 'liability', 'equity', 'income', 'expense') NOT NULL COMMENT 'Tipo de cuenta',
    parent_id INT DEFAULT NULL COMMENT 'Cuenta padre para jerarquía',
    level INT DEFAULT 0 COMMENT 'Nivel jerárquico (0-5)',
    balance_type ENUM('debit', 'credit') NOT NULL COMMENT 'Tipo de saldo natural',
    is_active BOOLEAN DEFAULT 1 COMMENT 'Si la cuenta está activa',
    is_system BOOLEAN DEFAULT 0 COMMENT 'Si es cuenta del sistema (no editable)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES accounting_accounts(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_type (account_type),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Plan de cuentas contable con estructura jerárquica';

-- ============================================
-- TABLA: accounting_entries
-- Cabecera de asientos contables (Journal Entries)
-- ============================================
CREATE TABLE IF NOT EXISTS accounting_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_number VARCHAR(50) NOT NULL COMMENT 'Número único del asiento (ej: AS-2025-000001)',
    entry_date DATE NOT NULL COMMENT 'Fecha del asiento',
    period_id INT NOT NULL COMMENT 'Período contable al que pertenece',
    description TEXT NOT NULL COMMENT 'Descripción del asiento',
    reference VARCHAR(100) COMMENT 'Referencia externa (número de factura, etc)',
    entry_type ENUM('manual', 'automatic') DEFAULT 'manual' COMMENT 'Tipo de asiento',
    source_type ENUM('expense', 'payment', 'donation', 'manual') DEFAULT 'manual' COMMENT 'Origen del asiento',
    source_id INT DEFAULT NULL COMMENT 'ID del registro origen',
    status ENUM('draft', 'posted', 'cancelled') DEFAULT 'draft' COMMENT 'Estado del asiento',
    created_by INT NOT NULL COMMENT 'Usuario que creó el asiento',
    posted_by INT DEFAULT NULL COMMENT 'Usuario que contabilizó el asiento',
    posted_at DATETIME DEFAULT NULL COMMENT 'Fecha de contabilización',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cabecera de asientos contables (libro diario)';

-- ============================================
-- TABLA: accounting_entry_lines
-- Líneas de asientos contables (Partida Doble)
-- ============================================
CREATE TABLE IF NOT EXISTS accounting_entry_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_id INT NOT NULL COMMENT 'Asiento al que pertenece',
    account_id INT NOT NULL COMMENT 'Cuenta contable',
    description TEXT COMMENT 'Descripción de la línea',
    debit DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Importe en el Debe',
    credit DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Importe en el Haber',
    line_order INT DEFAULT 0 COMMENT 'Orden de la línea en el asiento',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entry_id) REFERENCES accounting_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounting_accounts(id) ON DELETE CASCADE,
    INDEX idx_entry (entry_id),
    INDEX idx_account (account_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Líneas de asientos contables (partida doble: Debe = Haber)';

-- ============================================
-- TABLA: budgets
-- Presupuestos por cuenta y período
-- ============================================
CREATE TABLE IF NOT EXISTS budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL COMMENT 'Nombre del presupuesto',
    description TEXT COMMENT 'Descripción detallada',
    fiscal_year INT NOT NULL COMMENT 'Año fiscal',
    account_id INT NOT NULL COMMENT 'Cuenta contable asociada',
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Importe presupuestado',
    period_type ENUM('yearly', 'monthly', 'quarterly') DEFAULT 'yearly' COMMENT 'Tipo de período',
    period_number INT DEFAULT NULL COMMENT 'Número de período (mes o trimestre)',
    status ENUM('draft', 'approved', 'active', 'closed') DEFAULT 'draft' COMMENT 'Estado del presupuesto',
    created_by INT NOT NULL COMMENT 'Usuario que creó el presupuesto',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounting_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_account (account_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Presupuestos asociados a cuentas contables';

-- ============================================
-- DATOS INICIALES
-- ============================================

-- Insertar plan de cuentas básico según PGC español
SELECT 'Insertando plan de cuentas básico...' as mensaje;

INSERT IGNORE INTO accounting_accounts (code, name, account_type, balance_type, level, is_system) VALUES
-- PATRIMONIO (Equity)
('100', 'Capital Social', 'equity', 'credit', 0, 1),
('129', 'Resultados del Ejercicio', 'equity', 'credit', 0, 1),

-- ACTIVO (Assets)
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
-- Solo si existe al menos un usuario en el sistema
SELECT 'Insertando período contable del año actual...' as mensaje;

INSERT IGNORE INTO accounting_periods (name, start_date, end_date, fiscal_year, status, created_by) 
SELECT 
    CONCAT('Ejercicio ', YEAR(CURDATE())), 
    CONCAT(YEAR(CURDATE()), '-01-01'), 
    CONCAT(YEAR(CURDATE()), '-12-31'),
    YEAR(CURDATE()),
    'open',
    MIN(id)
FROM users
WHERE EXISTS (SELECT 1 FROM users LIMIT 1);

-- ============================================
-- VERIFICACIÓN
-- ============================================
SELECT 'Verificando tablas creadas...' as mensaje;

SELECT 
    CASE 
        WHEN COUNT(*) = 5 THEN '✓ Todas las tablas creadas correctamente'
        ELSE '✗ Error: Faltan tablas por crear'
    END as resultado
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name IN (
    'accounting_periods', 
    'accounting_accounts', 
    'accounting_entries', 
    'accounting_entry_lines', 
    'budgets'
);

SELECT 
    CONCAT('Cuentas contables insertadas: ', COUNT(*)) as resultado
FROM accounting_accounts;

SELECT 
    CONCAT('Períodos contables creados: ', COUNT(*)) as resultado
FROM accounting_periods;

SELECT '¡Migración completada exitosamente!' as mensaje;
