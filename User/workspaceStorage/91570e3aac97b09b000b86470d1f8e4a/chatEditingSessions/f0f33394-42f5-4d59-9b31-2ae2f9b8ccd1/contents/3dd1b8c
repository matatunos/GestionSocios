-- =====================================================
-- Migration: Create grant_applications table
-- Date: 2025-12-04
-- Description: Tabla de solicitudes de subvenciones
-- =====================================================

-- Crear tabla de solicitudes de subvenciones
CREATE TABLE IF NOT EXISTS grant_applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    grant_id INT NOT NULL,
    application_number VARCHAR(100),
    application_date DATE,
    requested_amount DECIMAL(10,2),
    status ENUM('borrador', 'presentada', 'subsanacion', 'en_evaluacion', 'concedida', 'denegada', 'desistida', 'renunciada') DEFAULT 'borrador',
    resolution_date DATE,
    granted_amount DECIMAL(10,2),
    resolution_text TEXT,
    justification_deadline DATE,
    justification_status ENUM('pendiente', 'en_curso', 'presentada', 'aprobada', 'rechazada') DEFAULT 'pendiente',
    justification_date DATE,
    payment_type ENUM('unico', 'anticipo_liquidacion', 'multiple') DEFAULT 'unico',
    advance_payment DECIMAL(10,2) DEFAULT 0.00,
    final_payment DECIMAL(10,2) DEFAULT 0.00,
    documents_folder VARCHAR(255),
    notes TEXT,
    internal_notes TEXT,
    responsible_user_id INT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_grant_id (grant_id),
    INDEX idx_status (status),
    INDEX idx_application_date (application_date),
    INDEX idx_justification_status (justification_status),
    
    CONSTRAINT fk_grant_app_grant FOREIGN KEY (grant_id) 
        REFERENCES grants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de pagos de subvenciones
CREATE TABLE IF NOT EXISTS grant_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    payment_number INT DEFAULT 1,
    payment_type ENUM('anticipo', 'liquidacion', 'pago_unico', 'otro') DEFAULT 'pago_unico',
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE,
    bank_transaction_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_application_id (application_id),
    INDEX idx_payment_date (payment_date),
    
    CONSTRAINT fk_grant_payment_app FOREIGN KEY (application_id) 
        REFERENCES grant_applications(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de justificaciones/gastos
CREATE TABLE IF NOT EXISTS grant_expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    expense_date DATE NOT NULL,
    description VARCHAR(255) NOT NULL,
    supplier VARCHAR(255),
    invoice_number VARCHAR(100),
    amount DECIMAL(10,2) NOT NULL,
    eligible_amount DECIMAL(10,2) NOT NULL,
    category VARCHAR(100),
    document_path VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_application_id (application_id),
    INDEX idx_expense_date (expense_date),
    INDEX idx_category (category),
    
    CONSTRAINT fk_grant_expense_app FOREIGN KEY (application_id) 
        REFERENCES grant_applications(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Datos de ejemplo
-- =====================================================

-- Insertar solicitud de ejemplo para la subvención de formación
INSERT INTO grant_applications 
(grant_id, application_number, application_date, requested_amount, status, 
 resolution_date, granted_amount, payment_type, justification_deadline, 
 justification_status, notes, created_by)
SELECT 
    id,
    'SOL-2025-001',
    '2025-01-15',
    15000.00,
    'concedida',
    '2025-02-20',
    12000.00,
    'anticipo_liquidacion',
    '2026-02-28',
    'pendiente',
    'Solicitud para programa de formación continua',
    1
FROM grants 
WHERE title LIKE '%Formación Continua%'
LIMIT 1;

-- Insertar pago de anticipo
INSERT INTO grant_payments (application_id, payment_number, payment_type, amount, payment_date, notes)
SELECT 
    id,
    1,
    'anticipo',
    6000.00,
    '2025-03-15',
    'Anticipo del 50%'
FROM grant_applications
WHERE application_number = 'SOL-2025-001'
LIMIT 1;

-- Insertar algunos gastos de ejemplo
INSERT INTO grant_expenses 
(application_id, expense_date, description, supplier, invoice_number, amount, eligible_amount, category)
SELECT 
    id,
    '2025-04-10',
    'Curso de gestión administrativa',
    'Centro de Formación ABC',
    'FC-2025-045',
    3500.00,
    3500.00,
    'Formación'
FROM grant_applications
WHERE application_number = 'SOL-2025-001'
LIMIT 1;

INSERT INTO grant_expenses 
(application_id, expense_date, description, supplier, invoice_number, amount, eligible_amount, category)
SELECT 
    id,
    '2025-05-15',
    'Material didáctico',
    'Papelería Técnica SL',
    'PT-2025-123',
    850.00,
    850.00,
    'Material'
FROM grant_applications
WHERE application_number = 'SOL-2025-001'
LIMIT 1;
