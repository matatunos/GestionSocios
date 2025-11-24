-- Script para crear la tabla document_permissions faltante
-- Ejecutar este script en la base de datos para corregir el error 500 en la sección de documentos

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
