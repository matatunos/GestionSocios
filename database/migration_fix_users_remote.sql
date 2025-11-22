-- Migración para actualizar tabla users desde estructura antigua a nueva
-- Ejecutar en el servidor de base de datos remoto (192.168.1.22)

USE asociacion_db;

-- Desactivar verificación de claves foráneas temporalmente
SET FOREIGN_KEY_CHECKS = 0;

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

-- Migrar datos de estructura antigua (username, password_hash) a nueva (email, name, password)
INSERT IGNORE INTO users_new (id, email, name, password, role, active, created_at)
SELECT 
    id,
    CONCAT(username, '@temp.com') as email,
    username as name,
    password_hash as password,
    CASE WHEN role = 'admin' THEN 'admin' ELSE 'member' END as role,
    1 as active,
    created_at
FROM users;

-- Renombrar tablas
DROP TABLE users;
RENAME TABLE users_new TO users;

-- Reactivar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

-- Insertar usuario admin por defecto
INSERT INTO users (email, name, password, role, active) VALUES 
('admin@admin.com', 'Administrador', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1)
ON DUPLICATE KEY UPDATE password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- Verificar estructura
DESCRIBE users;
SELECT 'Tabla users migrada correctamente' as status;
