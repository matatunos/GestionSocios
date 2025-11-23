-- Migración: Sistema de notificaciones
-- Fecha: 2024
-- Descripción: Crear tabla notifications para almacenar notificaciones de usuarios

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    type VARCHAR(50) NOT NULL COMMENT 'payment_reminder, event_reminder, announcement, system, welcome',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL COMMENT 'URL relativa para acción',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para mejorar rendimiento
CREATE INDEX idx_member_id ON notifications(member_id);
CREATE INDEX idx_is_read ON notifications(is_read);
CREATE INDEX idx_created_at ON notifications(created_at DESC);
CREATE INDEX idx_type ON notifications(type);

-- Notificaciones de ejemplo (opcional)
INSERT INTO notifications (member_id, type, title, message, link) 
SELECT 
    id,
    'welcome',
    '¡Bienvenido al sistema!',
    'Gracias por unirte a nuestra asociación. Explora las funcionalidades disponibles.',
    'index.php?page=dashboard'
FROM members 
WHERE active = 1 
LIMIT 5;
