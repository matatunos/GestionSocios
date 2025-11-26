-- Migración: Añadir columnas is_current y replaced_at a las tablas de historial de imágenes
-- Fecha: 2025-11-26

-- Añadir columnas a donor_image_history
ALTER TABLE donor_image_history 
ADD COLUMN is_current TINYINT(1) DEFAULT 1 AFTER image_url,
ADD COLUMN replaced_at TIMESTAMP NULL DEFAULT NULL AFTER uploaded_by;

-- Añadir columnas a member_image_history
ALTER TABLE member_image_history 
ADD COLUMN is_current TINYINT(1) DEFAULT 1 AFTER image_url,
ADD COLUMN replaced_at TIMESTAMP NULL DEFAULT NULL AFTER uploaded_by;

-- Crear índices para mejorar el rendimiento de las consultas
CREATE INDEX idx_donor_current ON donor_image_history(donor_id, is_current);
CREATE INDEX idx_member_current ON member_image_history(member_id, is_current);
