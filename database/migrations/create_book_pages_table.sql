-- Crear tabla book_pages para maquetación de libro de fiestas
CREATE TABLE IF NOT EXISTS book_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    page_number INT NOT NULL,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
