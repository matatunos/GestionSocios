-- Migración: Crear tablas de facturación emitida
-- Fecha: 2025-12-04
-- Descripción: Tablas para gestión de facturas emitidas

-- Tabla de series de facturas
CREATE TABLE IF NOT EXISTS invoice_series (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    prefix VARCHAR(20) NOT NULL,
    next_number INT NOT NULL DEFAULT 1,
    is_default BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar series por defecto si no existen
INSERT IGNORE INTO invoice_series (code, name, description, prefix, next_number, is_default, is_active) VALUES
('NORMAL', 'Serie Normal', 'Facturas ordinarias', 'F', 1, 1, 1),
('RECT', 'Serie Rectificativa', 'Facturas rectificativas y abonos', 'R', 1, 0, 1),
('SIMP', 'Serie Simplificada', 'Facturas simplificadas (tickets)', 'S', 1, 0, 1);

-- Tabla de facturas emitidas
CREATE TABLE IF NOT EXISTS issued_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    series_id INT NOT NULL,
    invoice_number VARCHAR(50) NOT NULL,
    full_number VARCHAR(100) NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE,
    
    -- Cliente
    customer_type ENUM('member', 'external') DEFAULT 'external',
    member_id INT NULL,
    customer_name VARCHAR(200) NOT NULL,
    customer_tax_id VARCHAR(50),
    customer_address TEXT,
    customer_city VARCHAR(100),
    customer_postal_code VARCHAR(20),
    customer_country VARCHAR(100) DEFAULT 'España',
    customer_email VARCHAR(200),
    customer_phone VARCHAR(50),
    
    -- Concepto y observaciones
    description TEXT NOT NULL,
    notes TEXT,
    
    -- Importes
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_rate DECIMAL(5,2) DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    
    -- Estado y pago
    status ENUM('draft', 'issued', 'paid', 'cancelled', 'overdue') DEFAULT 'draft',
    payment_method ENUM('cash', 'transfer', 'card', 'check', 'other') DEFAULT 'transfer',
    payment_date DATE NULL,
    
    -- Referencias
    reference VARCHAR(100),
    accounting_entry_id INT NULL,
    
    -- PDF
    pdf_path VARCHAR(500),
    
    -- Auditoría
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    issued_by INT NULL,
    issued_at TIMESTAMP NULL,
    cancelled_by INT NULL,
    cancelled_at TIMESTAMP NULL,
    cancellation_reason TEXT,
    
    FOREIGN KEY (series_id) REFERENCES invoice_series(id) ON DELETE RESTRICT,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL,
    FOREIGN KEY (accounting_entry_id) REFERENCES accounting_entries(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_full_number (full_number),
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_series (series_id),
    INDEX idx_issue_date (issue_date),
    INDEX idx_customer (customer_type, member_id),
    INDEX idx_status (status),
    INDEX idx_payment (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de líneas de factura
CREATE TABLE IF NOT EXISTS issued_invoice_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    line_order INT DEFAULT 0,
    
    -- Concepto
    concept VARCHAR(500) NOT NULL,
    description TEXT,
    
    -- Cantidades y precios
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- Impuestos
    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    
    -- Totales calculados
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (invoice_id) REFERENCES issued_invoices(id) ON DELETE CASCADE,
    INDEX idx_invoice (invoice_id),
    INDEX idx_order (line_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
