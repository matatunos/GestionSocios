-- Migración para actualizar la tabla users a la nueva estructura
-- Esta migración preserva los datos existentes

USE asociacion_db;

-- Crear tabla temporal con la nueva estructura
CREATE TABLE IF NOT EXISTS users_new (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Copiar datos existentes (tabla actual tiene: id, email, name, password, role, active, created_at)
INSERT IGNORE INTO users_new (id, email, name, password, role, active, created_at)
SELECT 
    id,
    email,
    name,
    password,
    role,
    active,
    created_at
FROM users;

-- Renombrar tablas
DROP TABLE users;
RENAME TABLE users_new TO users;

-- Insertar usuario admin por defecto si no existe
INSERT INTO users (email, name, password, role, active) VALUES 
('admin@admin.com', 'Administrador', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1)
ON DUPLICATE KEY UPDATE id=id;

-- Verificar estructura
DESCRIBE users;
SELECT 'Tabla users actualizada correctamente' as status;
