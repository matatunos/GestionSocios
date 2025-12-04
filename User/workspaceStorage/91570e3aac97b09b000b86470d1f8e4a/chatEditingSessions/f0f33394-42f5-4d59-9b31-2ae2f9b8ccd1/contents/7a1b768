-- Migración SEGURA para profesionalizar el módulo de proveedores
-- Fecha: 2025-12-03
-- Versión: SAFE (con verificación de columnas existentes)

-- Verificar qué columnas ya existen y solo agregar las que faltan
SET @dbname = DATABASE();

-- Agregar tax_id solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='tax_id') > 0,
    'SELECT "tax_id already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN tax_id VARCHAR(50) AFTER cif_nif'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar postal_code solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='postal_code') > 0,
    'SELECT "postal_code already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN postal_code VARCHAR(10) AFTER address'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar city solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='city') > 0,
    'SELECT "city already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN city VARCHAR(100) AFTER postal_code'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar province solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='province') > 0,
    'SELECT "province already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN province VARCHAR(100) AFTER city'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar country solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='country') > 0,
    'SELECT "country already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN country VARCHAR(100) DEFAULT "España" AFTER province'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar tipo_proveedor solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='tipo_proveedor') > 0,
    'SELECT "tipo_proveedor already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN tipo_proveedor ENUM("servicios", "productos", "mixto", "profesional") DEFAULT "servicios" AFTER logo_path'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar categoria solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='categoria') > 0,
    'SELECT "categoria already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN categoria VARCHAR(100) AFTER tipo_proveedor'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar estado solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='estado') > 0,
    'SELECT "estado already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN estado ENUM("activo", "inactivo", "bloqueado") DEFAULT "activo" AFTER categoria'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar payment_terms solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='payment_terms') > 0,
    'SELECT "payment_terms already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN payment_terms INT DEFAULT 30 AFTER estado'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar default_payment_method solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='default_payment_method') > 0,
    'SELECT "default_payment_method already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN default_payment_method ENUM("transfer", "cash", "card", "check", "other") DEFAULT "transfer" AFTER payment_terms'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar iban solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='iban') > 0,
    'SELECT "iban already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN iban VARCHAR(34) AFTER default_payment_method'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar swift solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='swift') > 0,
    'SELECT "swift already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN swift VARCHAR(11) AFTER iban'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar bank_name solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='bank_name') > 0,
    'SELECT "bank_name already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN bank_name VARCHAR(255) AFTER swift'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar default_discount solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='default_discount') > 0,
    'SELECT "default_discount already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN default_discount DECIMAL(5,2) DEFAULT 0.00 AFTER bank_name'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar credit_limit solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='credit_limit') > 0,
    'SELECT "credit_limit already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN credit_limit DECIMAL(10,2) AFTER default_discount'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar contact_person solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='contact_person') > 0,
    'SELECT "contact_person already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN contact_person VARCHAR(255) AFTER credit_limit'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar contact_email solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='contact_email') > 0,
    'SELECT "contact_email already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN contact_email VARCHAR(255) AFTER contact_person'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar contact_phone solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='contact_phone') > 0,
    'SELECT "contact_phone already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN contact_phone VARCHAR(20) AFTER contact_email'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar rating solo si no existe
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND COLUMN_NAME='rating') > 0,
    'SELECT "rating already exists" AS message',
    'ALTER TABLE suppliers ADD COLUMN rating TINYINT CHECK (rating >= 1 AND rating <= 5) AFTER contact_phone'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar índices solo si no existen
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND INDEX_NAME='idx_tipo') > 0,
    'SELECT "idx_tipo already exists" AS message',
    'ALTER TABLE suppliers ADD INDEX idx_tipo (tipo_proveedor)'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND INDEX_NAME='idx_estado') > 0,
    'SELECT "idx_estado already exists" AS message',
    'ALTER TABLE suppliers ADD INDEX idx_estado (estado)'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA=@dbname 
     AND TABLE_NAME='suppliers' 
     AND INDEX_NAME='idx_categoria') > 0,
    'SELECT "idx_categoria already exists" AS message',
    'ALTER TABLE suppliers ADD INDEX idx_categoria (categoria)'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Crear tabla de contactos de proveedores
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

-- Crear tabla de documentos de proveedores
CREATE TABLE IF NOT EXISTS supplier_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    document_type ENUM('contract', 'certificate', 'insurance', 'tax', 'other') NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT,
    expiry_date DATE,
    notes TEXT,
    uploaded_by INT,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    INDEX idx_supplier (supplier_id),
    INDEX idx_type (document_type),
    INDEX idx_expiry (expiry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de categorías de proveedores
CREATE TABLE IF NOT EXISTS supplier_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50),
    color VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar categorías predefinidas
INSERT IGNORE INTO supplier_categories (name, description, icon, color) VALUES
('Construcción', 'Proveedores de materiales y servicios de construcción', 'fa-hard-hat', '#e67e22'),
('Tecnología', 'Hardware, software y servicios IT', 'fa-laptop', '#3498db'),
('Alimentación', 'Proveedores de alimentos y catering', 'fa-utensils', '#27ae60'),
('Oficina', 'Material de oficina y papelería', 'fa-briefcase', '#95a5a6'),
('Limpieza', 'Servicios y productos de limpieza', 'fa-broom', '#1abc9c'),
('Marketing', 'Publicidad y servicios de marketing', 'fa-bullhorn', '#e74c3c'),
('Legal', 'Servicios legales y asesoría', 'fa-gavel', '#34495e'),
('Transporte', 'Logística y transporte', 'fa-truck', '#f39c12'),
('Mantenimiento', 'Servicios de mantenimiento general', 'fa-tools', '#d35400'),
('Consultoría', 'Servicios de consultoría profesional', 'fa-user-tie', '#8e44ad');

-- Crear tabla de valoraciones de proveedores
CREATE TABLE IF NOT EXISTS supplier_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    rated_by INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    quality_score TINYINT CHECK (quality_score >= 1 AND quality_score <= 5),
    delivery_score TINYINT CHECK (delivery_score >= 1 AND delivery_score <= 5),
    price_score TINYINT CHECK (price_score >= 1 AND price_score <= 5),
    service_score TINYINT CHECK (service_score >= 1 AND service_score <= 5),
    comments TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    INDEX idx_supplier (supplier_id),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mensaje de finalización
SELECT '✓ Migración completada exitosamente' AS Status;
SELECT CONCAT('✓ Total de columnas en suppliers: ', COUNT(*)) AS Columns
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'suppliers';
