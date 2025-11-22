-- Script para aplicar todas las migraciones pendientes
-- Ejecutar en tu servidor MySQL/MariaDB

USE asociacion_db;

-- Migration: member_image_history
CREATE TABLE IF NOT EXISTS member_image_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_current TINYINT(1) DEFAULT 0,
    replaced_at TIMESTAMP NULL,
    INDEX idx_member_id (member_id),
    INDEX idx_is_current (is_current),
    CONSTRAINT fk_member_image_history_member 
        FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

-- Migration: donor_image_history
CREATE TABLE IF NOT EXISTS donor_image_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_current TINYINT(1) DEFAULT 0,
    replaced_at TIMESTAMP NULL,
    INDEX idx_donor_id (donor_id),
    INDEX idx_is_current (is_current),
    CONSTRAINT fk_donor_image_history_donor 
        FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE
);

-- Verificar tablas creadas
SHOW TABLES LIKE '%image_history%';
