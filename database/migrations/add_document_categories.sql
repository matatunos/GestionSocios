-- Migración para añadir categorías a documentos
CREATE TABLE IF NOT EXISTS document_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categorías típicas de asociación
INSERT INTO document_categories (name, description, color) VALUES
('Estatutos', 'Documentos legales y estatutos de la asociación', '#6366f1'),
('Actas', 'Actas de reuniones y asambleas', '#10b981'),
('Informes', 'Informes de gestión, económicos, etc.', '#f59e0b'),
('Convocatorias', 'Convocatorias a reuniones y eventos', '#ef4444'),
('Certificados', 'Certificados y acreditaciones', '#3b82f6'),
('Comunicados', 'Comunicados oficiales y notas informativas', '#8b5cf6'),
('Otros', 'Otros documentos relevantes', '#94a3b8');

ALTER TABLE documents ADD COLUMN category_id INT DEFAULT NULL AFTER title;
ALTER TABLE documents ADD CONSTRAINT fk_document_category FOREIGN KEY (category_id) REFERENCES document_categories(id) ON DELETE SET NULL;
