-- Migración para profesionalizar el módulo de proveedores
-- Fecha: 2025-12-03
-- IMPORTANTE: Hacer backup antes de ejecutar esta migración

-- 1. Mejorar tabla suppliers con campos profesionales
ALTER TABLE suppliers
ADD COLUMN tax_id VARCHAR(50) AFTER cif_nif,
ADD COLUMN postal_code VARCHAR(10) AFTER address,
ADD COLUMN city VARCHAR(100) AFTER postal_code,
ADD COLUMN province VARCHAR(100) AFTER city,
ADD COLUMN country VARCHAR(100) DEFAULT 'España' AFTER province,
ADD COLUMN tipo_proveedor ENUM('servicios', 'productos', 'mixto', 'profesional') DEFAULT 'servicios' AFTER logo_path,
ADD COLUMN categoria VARCHAR(100) AFTER tipo_proveedor,
ADD COLUMN estado ENUM('activo', 'inactivo', 'bloqueado') DEFAULT 'activo' AFTER categoria,
ADD COLUMN payment_terms INT DEFAULT 30 AFTER estado,
ADD COLUMN default_payment_method ENUM('transfer', 'cash', 'card', 'check', 'other') DEFAULT 'transfer' AFTER payment_terms,
ADD COLUMN iban VARCHAR(34) AFTER default_payment_method,
ADD COLUMN swift VARCHAR(11) AFTER iban,
ADD COLUMN bank_name VARCHAR(255) AFTER swift,
ADD COLUMN default_discount DECIMAL(5,2) DEFAULT 0.00 AFTER bank_name,
ADD COLUMN credit_limit DECIMAL(10,2) AFTER default_discount,
ADD COLUMN contact_person VARCHAR(255) AFTER credit_limit,
ADD COLUMN contact_email VARCHAR(255) AFTER contact_person,
ADD COLUMN contact_phone VARCHAR(20) AFTER contact_email,
ADD COLUMN rating TINYINT CHECK (rating >= 1 AND rating <= 5) AFTER contact_phone,
ADD INDEX idx_tipo (tipo_proveedor),
ADD INDEX idx_estado (estado),
ADD INDEX idx_categoria (categoria);

-- 2. Crear tabla de contactos de proveedores
CREATE TABLE IF NOT EXISTS supplier_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(100),
    email VARCHAR(255),
    phone VARCHAR(20),
    mobile VARCHAR(20),
    is_primary BOOLEAN DEFAULT 0,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    INDEX idx_supplier (supplier_id),
    INDEX idx_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Crear tabla de documentos de proveedores
CREATE TABLE IF NOT EXISTS supplier_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    document_id INT,
    document_type ENUM('contrato', 'certificado', 'seguro', 'licencia', 'otro') DEFAULT 'otro',
    name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255),
    description TEXT,
    upload_date DATE,
    expiry_date DATE,
    status ENUM('vigente', 'caducado', 'renovado', 'cancelado') DEFAULT 'vigente',
    tags VARCHAR(255),
    uploaded_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_supplier (supplier_id),
    INDEX idx_type (document_type),
    INDEX idx_status (status),
    INDEX idx_expiry (expiry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Crear tabla de órdenes de compra
CREATE TABLE IF NOT EXISTS supplier_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    order_date DATE NOT NULL,
    expected_delivery_date DATE,
    status ENUM('draft', 'sent', 'confirmed', 'received', 'cancelled') DEFAULT 'draft',
    subtotal DECIMAL(10,2) DEFAULT 0.00,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT,
    approved_by INT,
    approved_at DATETIME,
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_supplier (supplier_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_order_date (order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Crear tabla de líneas de órdenes de compra
CREATE TABLE IF NOT EXISTS supplier_order_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    line_number INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    tax_rate DECIMAL(5,2) DEFAULT 21.00,
    discount_rate DECIMAL(5,2) DEFAULT 0.00,
    line_total DECIMAL(10,2),
    notes TEXT,
    FOREIGN KEY (order_id) REFERENCES supplier_orders(id) ON DELETE CASCADE,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Mejorar tabla supplier_invoices
ALTER TABLE supplier_invoices
ADD COLUMN order_id INT AFTER supplier_id,
ADD COLUMN due_date DATE AFTER invoice_date,
ADD COLUMN payment_date DATE AFTER due_date,
ADD COLUMN subtotal DECIMAL(10,2) DEFAULT 0.00 AFTER payment_date,
ADD COLUMN tax_amount DECIMAL(10,2) DEFAULT 0.00 AFTER subtotal,
ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00 AFTER tax_amount,
MODIFY COLUMN status ENUM('paid', 'pending', 'overdue', 'cancelled') DEFAULT 'pending',
ADD COLUMN payment_method ENUM('transfer', 'cash', 'card', 'check', 'other') DEFAULT 'transfer' AFTER status,
ADD COLUMN bank_reference VARCHAR(100) AFTER payment_method,
ADD COLUMN tipo_factura ENUM('normal', 'rectificativa', 'abono') DEFAULT 'normal' AFTER bank_reference,
ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at,
ADD FOREIGN KEY (order_id) REFERENCES supplier_orders(id) ON DELETE SET NULL,
ADD INDEX idx_due_date (due_date),
ADD INDEX idx_status (status);

-- 7. Migrar datos existentes
-- Copiar datos de contacto de suppliers a la tabla de contactos
INSERT INTO supplier_contacts (supplier_id, name, email, phone, is_primary)
SELECT id, COALESCE(name, 'Contacto Principal'), email, phone, 1
FROM suppliers
WHERE email IS NOT NULL OR phone IS NOT NULL;

-- 8. Calcular fecha de vencimiento para facturas existentes basándose en payment_terms por defecto
UPDATE supplier_invoices si
JOIN suppliers s ON si.supplier_id = s.id
SET si.due_date = DATE_ADD(si.invoice_date, INTERVAL COALESCE(s.payment_terms, 30) DAY)
WHERE si.due_date IS NULL;

-- 9. Actualizar subtotales para facturas existentes (asumiendo 21% IVA)
UPDATE supplier_invoices
SET subtotal = ROUND(amount / 1.21, 2),
    tax_amount = ROUND(amount - (amount / 1.21), 2)
WHERE subtotal IS NULL OR subtotal = 0;

-- 10. Marcar facturas vencidas
UPDATE supplier_invoices
SET status = 'overdue'
WHERE status = 'pending' 
  AND due_date < CURDATE();

-- Fin de la migración
