-- Migration: Create public_announcements table
-- Description: Table for storing public announcements displayed on login page

CREATE TABLE IF NOT EXISTS public_announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'danger') DEFAULT 'info',
    is_active BOOLEAN DEFAULT 1,
    priority INT DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_active_priority (is_active, priority, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample announcement
INSERT INTO public_announcements (title, content, type, priority, created_by) 
VALUES 
    ('Bienvenido', 'Sistema de gestión de asociación. Accede con tus credenciales.', 'info', 1, NULL),
    ('Mantenimiento Programado', 'El sistema estará en mantenimiento el próximo domingo de 2:00 a 4:00 AM.', 'warning', 2, NULL);
