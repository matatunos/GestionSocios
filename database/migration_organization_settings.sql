-- Migration: Organization Settings
-- Description: Add organization configuration table for logo, contact info, and branding
-- Date: 2025-11-22

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
INSERT INTO organization_settings (setting_key, setting_value, setting_type, category, description) VALUES
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

-- Create directory for logo uploads if not exists (handled by PHP)
-- Logo files will be stored in: /public/uploads/organization/
