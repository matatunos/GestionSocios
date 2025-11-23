-- Migration: Add roles and permissions system
-- Date: 2025-11-22

-- Create roles table
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create permissions table
CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    display_name VARCHAR(150) NOT NULL,
    description TEXT,
    module VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create role_permissions pivot table
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add role_id to users table
ALTER TABLE users 
ADD COLUMN role_id INT DEFAULT 1 AFTER password,
ADD CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL;

-- Insert default roles
INSERT INTO roles (name, display_name, description) VALUES
('admin', 'Administrador', 'Acceso completo a todas las funciones del sistema'),
('treasurer', 'Tesorero', 'Gestión de pagos, donaciones, informes financieros'),
('secretary', 'Secretario', 'Gestión de socios, eventos y comunicaciones'),
('viewer', 'Consulta', 'Solo lectura de información básica');

-- Insert permissions by module
INSERT INTO permissions (name, display_name, description, module) VALUES
-- Members module
('members.view', 'Ver Socios', 'Ver listado y detalles de socios', 'members'),
('members.create', 'Crear Socios', 'Añadir nuevos socios', 'members'),
('members.edit', 'Editar Socios', 'Modificar datos de socios', 'members'),
('members.delete', 'Eliminar Socios', 'Eliminar socios del sistema', 'members'),
('members.export', 'Exportar Socios', 'Exportar listados a PDF/Excel', 'members'),

-- Payments module
('payments.view', 'Ver Pagos', 'Ver pagos y cuotas', 'payments'),
('payments.create', 'Registrar Pagos', 'Añadir nuevos pagos', 'payments'),
('payments.edit', 'Editar Pagos', 'Modificar pagos existentes', 'payments'),
('payments.delete', 'Eliminar Pagos', 'Eliminar registros de pago', 'payments'),

-- Events module
('events.view', 'Ver Eventos', 'Ver listado de eventos', 'events'),
('events.create', 'Crear Eventos', 'Añadir nuevos eventos', 'events'),
('events.edit', 'Editar Eventos', 'Modificar eventos', 'events'),
('events.delete', 'Eliminar Eventos', 'Eliminar eventos', 'events'),
('events.attendance', 'Gestionar Asistencia', 'Registrar asistentes a eventos', 'events'),

-- Donors module
('donors.view', 'Ver Donantes', 'Ver listado de donantes', 'donors'),
('donors.create', 'Crear Donantes', 'Añadir nuevos donantes', 'donors'),
('donors.edit', 'Editar Donantes', 'Modificar datos de donantes', 'donors'),
('donors.delete', 'Eliminar Donantes', 'Eliminar donantes', 'donors'),

-- Donations module
('donations.view', 'Ver Donaciones', 'Ver registro de donaciones', 'donations'),
('donations.create', 'Registrar Donaciones', 'Añadir nuevas donaciones', 'donations'),
('donations.edit', 'Editar Donaciones', 'Modificar donaciones', 'donations'),
('donations.delete', 'Eliminar Donaciones', 'Eliminar donaciones', 'donations'),

-- Book (Anuncios) module
('book.view', 'Ver Libro Fiestas', 'Ver anuncios del libro', 'book'),
('book.create', 'Crear Anuncios', 'Añadir nuevos anuncios', 'book'),
('book.edit', 'Editar Anuncios', 'Modificar anuncios', 'book'),
('book.delete', 'Eliminar Anuncios', 'Eliminar anuncios', 'book'),

-- Reports module
('reports.view', 'Ver Informes', 'Acceder a informes y estadísticas', 'reports'),
('reports.financial', 'Informes Financieros', 'Ver informes financieros detallados', 'reports'),
('reports.export', 'Exportar Informes', 'Exportar informes a PDF/Excel', 'reports'),

-- Settings module
('settings.view', 'Ver Configuración', 'Ver configuración del sistema', 'settings'),
('settings.edit', 'Editar Configuración', 'Modificar configuración general', 'settings'),
('settings.database', 'Configurar Base de Datos', 'Modificar parámetros de BD', 'settings'),

-- Categories module
('categories.view', 'Ver Categorías', 'Ver categorías de socios', 'categories'),
('categories.edit', 'Gestionar Categorías', 'Crear, editar y eliminar categorías', 'categories'),

-- Users module
('users.view', 'Ver Usuarios', 'Ver usuarios del sistema', 'users'),
('users.create', 'Crear Usuarios', 'Añadir nuevos usuarios', 'users'),
('users.edit', 'Editar Usuarios', 'Modificar usuarios y roles', 'users'),
('users.delete', 'Eliminar Usuarios', 'Eliminar usuarios', 'users'),

-- Gallery module
('gallery.view', 'Ver Galería', 'Ver galería de imágenes', 'gallery'),
('gallery.upload', 'Subir Imágenes', 'Subir nuevas imágenes', 'gallery'),
('gallery.delete', 'Eliminar Imágenes', 'Eliminar imágenes', 'gallery');

-- Assign all permissions to admin role
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- Assign financial permissions to treasurer
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions 
WHERE module IN ('payments', 'donations', 'reports', 'donors', 'members', 'gallery') 
   OR name LIKE '%view%';

-- Assign member/event management to secretary
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions 
WHERE module IN ('members', 'events', 'categories', 'gallery')
   OR (module = 'reports' AND name = 'reports.view');

-- Assign only view permissions to viewer role
INSERT INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions 
WHERE name LIKE '%view%';

-- Set all existing users as admin by default
UPDATE users SET role_id = 1 WHERE role_id IS NULL;
