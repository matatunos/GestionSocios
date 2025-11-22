-- Script para aplicar todas las migraciones pendientes
-- Ejecutar como: mysql -u root -p asociacion_db < database/apply_all_migrations_single.sql

USE asociacion_db;

-- ========================================
-- Migration 1: Tasks System
-- ========================================

-- Task categories
CREATE TABLE IF NOT EXISTS task_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) DEFAULT '#6366f1',
    icon VARCHAR(50) DEFAULT 'fas fa-tasks',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_task_category_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default task categories
INSERT IGNORE INTO task_categories (name, color, icon, description) VALUES
('General', '#6366f1', 'fas fa-tasks', 'Tareas generales'),
('Pagos', '#10b981', 'fas fa-euro-sign', 'Seguimiento de pagos pendientes'),
('Eventos', '#f59e0b', 'fas fa-calendar-alt', 'Organización de eventos'),
('Socios', '#06b6d4', 'fas fa-users', 'Gestión de socios'),
('Donantes', '#8b5cf6', 'fas fa-hand-holding-heart', 'Seguimiento de donantes'),
('Urgente', '#ef4444', 'fas fa-exclamation-circle', 'Tareas urgentes');

-- Tasks table
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    due_date DATE,
    due_time TIME,
    assigned_to INT,
    created_by INT NOT NULL,
    completed_at TIMESTAMP NULL,
    completed_by INT,
    reminder_sent BOOLEAN DEFAULT FALSE,
    related_entity_type ENUM('member', 'donor', 'event', 'payment', 'expense', 'other') NULL,
    related_entity_id INT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES task_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (completed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_due_date (due_date),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_priority (priority),
    INDEX idx_related_entity (related_entity_type, related_entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task comments
CREATE TABLE IF NOT EXISTS task_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_task_id (task_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task attachments
CREATE TABLE IF NOT EXISTS task_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(500) NOT NULL,
    filesize INT,
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_task_id (task_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add task permissions
INSERT IGNORE INTO permissions (name, display_name, description, module) VALUES
('tasks.view', 'Ver Tareas', 'Ver listado de tareas', 'tasks'),
('tasks.create', 'Crear Tareas', 'Crear nuevas tareas', 'tasks'),
('tasks.edit', 'Editar Tareas', 'Modificar tareas existentes', 'tasks'),
('tasks.delete', 'Eliminar Tareas', 'Eliminar tareas', 'tasks'),
('tasks.assign', 'Asignar Tareas', 'Asignar tareas a usuarios', 'tasks'),
('tasks.complete', 'Completar Tareas', 'Marcar tareas como completadas', 'tasks');

-- Grant task permissions to admin role
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id 
FROM roles r
CROSS JOIN permissions p
WHERE r.name = 'admin' 
AND p.module = 'tasks'
AND NOT EXISTS (
    SELECT 1 FROM role_permissions rp2 
    WHERE rp2.role_id = r.id AND rp2.permission_id = p.id
);

-- ========================================
-- Migration 2: Organization Settings
-- ========================================

CREATE TABLE IF NOT EXISTS organization_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'file', 'email', 'url') DEFAULT 'text',
    category VARCHAR(50) DEFAULT 'general',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default organization settings
INSERT IGNORE INTO organization_settings (setting_key, setting_value, setting_type, category, description) VALUES
-- General Information
('org_name', 'Mi Asociación', 'text', 'general', 'Nombre oficial de la asociación'),
('org_short_name', 'MA', 'text', 'general', 'Nombre corto o siglas'),
('org_cif', '', 'text', 'general', 'CIF/NIF de la asociación'),
('org_registry_number', '', 'text', 'general', 'Número de registro oficial'),
('org_founded_year', '', 'number', 'general', 'Año de fundación'),

-- Contact Information
('org_address', '', 'text', 'contact', 'Dirección postal completa'),
('org_city', '', 'text', 'contact', 'Ciudad'),
('org_postal_code', '', 'text', 'contact', 'Código postal'),
('org_province', '', 'text', 'contact', 'Provincia'),
('org_country', 'España', 'text', 'contact', 'País'),
('org_phone', '', 'text', 'contact', 'Teléfono de contacto'),
('org_email', '', 'email', 'contact', 'Email de contacto'),
('org_website', '', 'url', 'contact', 'Sitio web'),

-- Branding
('org_logo', '', 'file', 'branding', 'Logo de la asociación (ruta al archivo)'),
('org_logo_width', '180', 'number', 'branding', 'Ancho del logo en píxeles'),
('org_primary_color', '#6366f1', 'text', 'branding', 'Color primario (hex)'),
('org_secondary_color', '#8b5cf6', 'text', 'branding', 'Color secundario (hex)'),

-- Legal
('org_president_name', '', 'text', 'legal', 'Nombre del presidente'),
('org_secretary_name', '', 'text', 'legal', 'Nombre del secretario'),
('org_treasurer_name', '', 'text', 'legal', 'Nombre del tesorero'),
('org_legal_text', '', 'text', 'legal', 'Texto legal para documentos'),

-- Certificates
('cert_show_logo', '1', 'boolean', 'certificates', 'Mostrar logo en certificados'),
('cert_show_seal', '1', 'boolean', 'certificates', 'Mostrar sello en certificados'),
('cert_signature_president', '', 'file', 'certificates', 'Firma digital del presidente'),
('cert_signature_secretary', '', 'file', 'certificates', 'Firma digital del secretario'),
('cert_footer_text', 'Este certificado ha sido generado electrónicamente y tiene validez sin firma manuscrita.', 'text', 'certificates', 'Texto del pie de certificados'),

-- System
('system_maintenance_mode', '0', 'boolean', 'system', 'Modo mantenimiento activado'),
('system_allow_registration', '0', 'boolean', 'system', 'Permitir registro público de socios'),
('system_default_language', 'es', 'text', 'system', 'Idioma por defecto del sistema');

-- ========================================
-- Verificación Final
-- ========================================

SELECT '=== Tablas Creadas ===' as '';
SHOW TABLES LIKE '%task%';
SHOW TABLES LIKE '%organization%';

SELECT '=== Permisos de Tareas ===' as '';
SELECT name, display_name FROM permissions WHERE module = 'tasks';

SELECT '=== Configuraciones de Organización ===' as '';
SELECT COUNT(*) as total_settings FROM organization_settings;

SELECT 'Migraciones aplicadas correctamente' as 'Estado';
