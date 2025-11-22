-- Migración: Sistema de gestión de documentos
-- Fecha: 2024
-- Descripción: Crear tabla documents para almacenar documentos compartidos

CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL COMMENT 'Tamaño en bytes',
    file_type VARCHAR(100) NOT NULL COMMENT 'MIME type',
    category VARCHAR(50) DEFAULT 'general' COMMENT 'general, actas, estatutos, facturas, otros',
    uploaded_by INT NOT NULL,
    is_public BOOLEAN DEFAULT TRUE COMMENT 'Si es visible para todos los socios',
    downloads INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para mejorar rendimiento
CREATE INDEX idx_category ON documents(category);
CREATE INDEX idx_is_public ON documents(is_public);
CREATE INDEX idx_uploaded_by ON documents(uploaded_by);
CREATE INDEX idx_created_at ON documents(created_at DESC);

-- Tabla para control de acceso a documentos privados
CREATE TABLE IF NOT EXISTS document_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    member_id INT NOT NULL,
    can_view BOOLEAN DEFAULT TRUE,
    can_download BOOLEAN DEFAULT TRUE,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    granted_by INT NOT NULL,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES members(id) ON DELETE CASCADE,
    UNIQUE KEY unique_permission (document_id, member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices
CREATE INDEX idx_document_member ON document_permissions(document_id, member_id);
