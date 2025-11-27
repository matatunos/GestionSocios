-- ============================================
-- Migración: Crear tablas faltantes para el sistema de libros
-- ============================================
-- Este script crea las tablas necesarias para el sistema de versiones
-- y páginas del libro de fiestas que faltan en la base de datos de producción.

-- 1. Crear tabla book_versions
CREATE TABLE IF NOT EXISTS book_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Verificar si book_pages existe y tiene la columna version_id
-- Si book_pages no existe, crearla
CREATE TABLE IF NOT EXISTS book_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    page_number INT NOT NULL,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Añadir columna version_id a book_pages si no existe
-- Primero verificamos si la columna ya existe
SET @dbname = DATABASE();
SET @tablename = "book_pages";
SET @columnname = "version_id";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " INT DEFAULT NULL AFTER book_id")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 4. Añadir columna position a book_pages si no existe
SET @columnname = "position";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " ENUM('full', 'top', 'bottom') DEFAULT 'full' AFTER page_number")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 5. Añadir foreign key para version_id si no existe
-- Nota: MySQL no permite verificar fácilmente si una FK existe, así que usamos un procedimiento
DELIMITER $$
CREATE PROCEDURE AddFKIfNotExists()
BEGIN
    IF NOT EXISTS (
        SELECT NULL
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE
            CONSTRAINT_SCHEMA = DATABASE() AND
            CONSTRAINT_NAME   = 'book_pages_ibfk_version' AND
            CONSTRAINT_TYPE   = 'FOREIGN KEY'
    ) THEN
        ALTER TABLE book_pages 
        ADD CONSTRAINT book_pages_ibfk_version 
        FOREIGN KEY (version_id) REFERENCES book_versions(id) ON DELETE CASCADE;
    END IF;
END$$
DELIMITER ;

CALL AddFKIfNotExists();
DROP PROCEDURE AddFKIfNotExists;

-- ============================================
-- Fin de la migración
-- ============================================
