-- Migración para añadir categorías a documentos
CREATE TABLE IF NOT EXISTS document_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE documents ADD COLUMN category_id INT DEFAULT NULL AFTER title;
ALTER TABLE documents ADD CONSTRAINT fk_document_category FOREIGN KEY (category_id) REFERENCES document_categories(id) ON DELETE SET NULL;
