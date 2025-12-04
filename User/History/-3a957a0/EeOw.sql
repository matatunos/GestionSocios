-- Migración: Crear tablas del módulo de Subvenciones (Grants)
-- Fecha: 2025-12-04
-- Descripción: Tablas para gestión de subvenciones con scraping de BDNS

-- Tabla principal de subvenciones
CREATE TABLE IF NOT EXISTS grants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    description TEXT,
    organization VARCHAR(255),
    amount DECIMAL(12,2),
    start_date DATE,
    end_date DATE,
    application_deadline DATE,
    status ENUM('abierta', 'cerrada', 'en_proceso', 'concedida', 'denegada', 'desestimada') DEFAULT 'abierta',
    category VARCHAR(100),
    source VARCHAR(50) DEFAULT 'BDNS',
    source_url TEXT,
    bdns_code VARCHAR(50),
    requirements TEXT,
    documentation_needed TEXT,
    contact_info TEXT,
    notes TEXT,
    tracked BOOLEAN DEFAULT 0,
    applied BOOLEAN DEFAULT 0,
    application_date DATE,
    result_date DATE,
    awarded_amount DECIMAL(12,2),
    alert_sent BOOLEAN DEFAULT 0,
    alert_days_before INT DEFAULT 7,
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_deadline (application_deadline),
    INDEX idx_tracked (tracked),
    INDEX idx_bdns (bdns_code),
    INDEX idx_category (category),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de documentos asociados a subvenciones
CREATE TABLE IF NOT EXISTS grant_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grant_id INT NOT NULL,
    document_type ENUM('application', 'requirements', 'justification', 'report', 'other') NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT,
    uploaded_by INT,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (grant_id) REFERENCES grants(id) ON DELETE CASCADE,
    INDEX idx_grant (grant_id),
    INDEX idx_type (document_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de seguimiento de actividades de subvenciones
CREATE TABLE IF NOT EXISTS grant_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grant_id INT NOT NULL,
    activity_type ENUM('application', 'documentation', 'query', 'response', 'resolution', 'payment', 'justification', 'other') NOT NULL,
    description TEXT NOT NULL,
    activity_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    notes TEXT,
    FOREIGN KEY (grant_id) REFERENCES grants(id) ON DELETE CASCADE,
    INDEX idx_grant (grant_id),
    INDEX idx_type (activity_type),
    INDEX idx_date (activity_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de recordatorios de subvenciones
CREATE TABLE IF NOT EXISTS grant_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grant_id INT NOT NULL,
    reminder_date DATETIME NOT NULL,
    reminder_type ENUM('deadline', 'documentation', 'justification', 'payment', 'other') NOT NULL,
    message TEXT NOT NULL,
    is_sent BOOLEAN DEFAULT 0,
    sent_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (grant_id) REFERENCES grants(id) ON DELETE CASCADE,
    INDEX idx_grant (grant_id),
    INDEX idx_date (reminder_date),
    INDEX idx_sent (is_sent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar algunas categorías ejemplo
INSERT IGNORE INTO grants (title, organization, amount, application_deadline, status, category, source, bdns_code) VALUES
('Subvención para Asociaciones Culturales 2025', 'Ministerio de Cultura', 50000.00, '2025-12-31', 'abierta', 'Cultura', 'BDNS', 'BDNS-2025-001'),
('Ayudas para Digitalización', 'Junta de Andalucía', 30000.00, '2025-11-30', 'abierta', 'Tecnología', 'BDNS', 'BDNS-2025-002'),
('Subvenciones Deporte Base', 'Diputación Provincial', 25000.00, '2025-10-15', 'cerrada', 'Deportes', 'BDNS', 'BDNS-2025-003');

SELECT '✓ Tablas de Subvenciones creadas correctamente' AS Status;
