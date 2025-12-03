-- ============================================================================
-- Migración: Mejoras en el Sistema de Documentos
-- Fecha: Diciembre 2025
-- Descripción: Agrega versionado, carpetas, tags, metadatos, comentarios,
--              soft delete, compartir por enlace y mejoras de seguridad
-- ============================================================================

-- ============================================================================
-- 1. MODIFICACIONES A LA TABLA DOCUMENTS
-- ============================================================================

-- Agregar campos para control de versiones
ALTER TABLE `documents`
ADD COLUMN `version` INT DEFAULT 1 AFTER `downloads`,
ADD COLUMN `parent_document_id` INT DEFAULT NULL AFTER `version`,
ADD COLUMN `is_latest_version` BOOLEAN DEFAULT TRUE AFTER `parent_document_id`;

-- Agregar campos para soft delete (papelera)
ALTER TABLE `documents`
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` INT NULL AFTER `deleted_at`;

-- Agregar campo para carpetas
ALTER TABLE `documents`
ADD COLUMN `folder_id` INT DEFAULT NULL AFTER `category_id`;

-- Agregar campos para metadatos de archivo
ALTER TABLE `documents`
ADD COLUMN `file_extension` VARCHAR(10) AFTER `file_type`,
ADD COLUMN `mime_type_verified` VARCHAR(100) AFTER `file_extension`;

-- Agregar campos para compartir por enlace
ALTER TABLE `documents`
ADD COLUMN `public_token` VARCHAR(64) UNIQUE DEFAULT NULL AFTER `is_public`,
ADD COLUMN `token_expires_at` DATETIME NULL AFTER `public_token`;

-- Agregar campo para workflow/estado
ALTER TABLE `documents`
ADD COLUMN `status` ENUM('draft', 'published', 'archived') DEFAULT 'published' AFTER `is_public`;

-- Agregar campo para búsqueda de texto completo
ALTER TABLE `documents`
ADD COLUMN `extracted_text` LONGTEXT AFTER `description`;

-- Agregar índices para optimizar consultas
ALTER TABLE `documents`
ADD INDEX `idx_folder` (`folder_id`),
ADD INDEX `idx_parent` (`parent_document_id`),
ADD INDEX `idx_deleted` (`deleted_at`),
ADD INDEX `idx_status` (`status`),
ADD INDEX `idx_version` (`version`, `is_latest_version`),
ADD INDEX `idx_public_token` (`public_token`),
ADD INDEX `idx_token_expires` (`token_expires_at`);

-- Agregar índice de texto completo para búsqueda
ALTER TABLE `documents`
ADD FULLTEXT INDEX `idx_fulltext_search` (`title`, `description`, `extracted_text`);

-- Agregar foreign keys
ALTER TABLE `documents`
ADD CONSTRAINT `fk_documents_parent` FOREIGN KEY (`parent_document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_documents_deleted_by` FOREIGN KEY (`deleted_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- ============================================================================
-- 2. TABLA: document_versions (Historial de versiones)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `document_versions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT NOT NULL COMMENT 'ID del documento padre',
    `version_number` INT NOT NULL COMMENT 'Número de versión',
    `file_name` VARCHAR(255) NOT NULL COMMENT 'Nombre original del archivo',
    `file_path` VARCHAR(500) NOT NULL COMMENT 'Ruta del archivo en servidor',
    `file_size` INT NOT NULL COMMENT 'Tamaño en bytes',
    `file_type` VARCHAR(100) NOT NULL COMMENT 'MIME type',
    `uploaded_by` INT NOT NULL COMMENT 'Usuario que subió esta versión',
    `change_notes` TEXT COMMENT 'Notas sobre los cambios en esta versión',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_document_version` (`document_id`, `version_number`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial de versiones de documentos';

-- ============================================================================
-- 3. TABLA: document_folders (Carpetas jerárquicas)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `document_folders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL COMMENT 'Nombre de la carpeta',
    `description` TEXT COMMENT 'Descripción de la carpeta',
    `parent_id` INT DEFAULT NULL COMMENT 'ID de la carpeta padre (NULL = raíz)',
    `path` VARCHAR(500) COMMENT 'Ruta completa ej: /Estatutos/2024',
    `level` INT DEFAULT 0 COMMENT 'Nivel de profundidad (0 = raíz)',
    `color` VARCHAR(20) DEFAULT '#6366f1' COMMENT 'Color para UI',
    `icon` VARCHAR(50) DEFAULT 'fa-folder' COMMENT 'Icono FontAwesome',
    `created_by` INT NOT NULL COMMENT 'Usuario que creó la carpeta',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_id`) REFERENCES `document_folders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_parent` (`parent_id`),
    INDEX `idx_path` (`path`),
    INDEX `idx_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Carpetas para organizar documentos';

-- Agregar foreign key de documents a folders
ALTER TABLE `documents`
ADD CONSTRAINT `fk_documents_folder` FOREIGN KEY (`folder_id`) REFERENCES `document_folders`(`id`) ON DELETE SET NULL;

-- ============================================================================
-- 4. TABLA: document_tags (Etiquetas libres)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `document_tags` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Nombre de la etiqueta',
    `slug` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Slug para URLs',
    `color` VARCHAR(20) DEFAULT '#6366f1' COMMENT 'Color hex para UI',
    `description` TEXT COMMENT 'Descripción de la etiqueta',
    `usage_count` INT DEFAULT 0 COMMENT 'Contador de uso',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_usage` (`usage_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Etiquetas para clasificar documentos';

-- Tabla relacional muchos a muchos: documentos ↔ tags
CREATE TABLE IF NOT EXISTS `document_tag_rel` (
    `document_id` INT NOT NULL,
    `tag_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`document_id`, `tag_id`),
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tag_id`) REFERENCES `document_tags`(`id`) ON DELETE CASCADE,
    INDEX `idx_tag` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relación documentos-etiquetas';

-- ============================================================================
-- 5. TABLA: document_metadata (Campos personalizados)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `document_metadata` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT NOT NULL COMMENT 'ID del documento',
    `meta_key` VARCHAR(100) NOT NULL COMMENT 'Clave del metadato (ej: autor, expediente)',
    `meta_value` TEXT COMMENT 'Valor del metadato',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
    INDEX `idx_document_key` (`document_id`, `meta_key`),
    INDEX `idx_meta_key` (`meta_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Metadatos personalizados de documentos';

-- ============================================================================
-- 6. TABLA: document_comments (Comentarios en documentos)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `document_comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT NOT NULL COMMENT 'ID del documento comentado',
    `user_id` INT NOT NULL COMMENT 'Usuario que comentó',
    `comment` TEXT NOT NULL COMMENT 'Texto del comentario',
    `parent_comment_id` INT DEFAULT NULL COMMENT 'ID del comentario padre (para respuestas)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`parent_comment_id`) REFERENCES `document_comments`(`id`) ON DELETE CASCADE,
    INDEX `idx_document` (`document_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_parent` (`parent_comment_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Comentarios en documentos';

-- ============================================================================
-- 7. TABLA: document_shares (Compartir por enlace público)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `document_shares` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT NOT NULL COMMENT 'ID del documento compartido',
    `token` VARCHAR(64) UNIQUE NOT NULL COMMENT 'Token único para acceso',
    `password_hash` VARCHAR(255) DEFAULT NULL COMMENT 'Hash de contraseña opcional',
    `expires_at` DATETIME DEFAULT NULL COMMENT 'Fecha de expiración del enlace',
    `max_downloads` INT DEFAULT NULL COMMENT 'Máximo de descargas permitidas',
    `download_count` INT DEFAULT 0 COMMENT 'Contador de descargas realizadas',
    `is_active` BOOLEAN DEFAULT TRUE COMMENT 'Si el enlace está activo',
    `created_by` INT NOT NULL COMMENT 'Usuario que compartió',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_accessed_at` DATETIME NULL COMMENT 'Última vez que se accedió',
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token` (`token`),
    INDEX `idx_expires` (`expires_at`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Enlaces públicos para compartir documentos';

-- ============================================================================
-- 8. TABLA: document_activity_log (Log de actividad detallado)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `document_activity_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT NOT NULL COMMENT 'ID del documento',
    `user_id` INT DEFAULT NULL COMMENT 'Usuario que realizó la acción',
    `action` ENUM('view', 'download', 'edit', 'delete', 'restore', 'share', 'comment', 'upload_version') NOT NULL COMMENT 'Tipo de acción',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP del usuario',
    `user_agent` TEXT DEFAULT NULL COMMENT 'User agent del navegador',
    `details` TEXT COMMENT 'Detalles adicionales en JSON',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_document` (`document_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log detallado de actividad en documentos';

-- ============================================================================
-- 9. TABLA: document_favorites (Documentos favoritos por usuario)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `document_favorites` (
    `user_id` INT NOT NULL COMMENT 'Usuario',
    `document_id` INT NOT NULL COMMENT 'Documento marcado como favorito',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `document_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
    INDEX `idx_document` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Documentos favoritos de usuarios';

-- ============================================================================
-- 10. INSERTAR CARPETAS POR DEFECTO
-- ============================================================================

-- Carpetas raíz predefinidas
INSERT IGNORE INTO `document_folders` (`name`, `description`, `parent_id`, `path`, `level`, `color`, `icon`, `created_by`) VALUES
('Estatutos', 'Estatutos y normativas de la asociación', NULL, '/Estatutos', 0, '#6366f1', 'fa-balance-scale', 1),
('Actas', 'Actas de reuniones y asambleas', NULL, '/Actas', 0, '#10b981', 'fa-file-signature', 1),
('Informes', 'Informes y reportes', NULL, '/Informes', 0, '#f59e0b', 'fa-chart-line', 1),
('Convocatorias', 'Convocatorias de eventos y reuniones', NULL, '/Convocatorias', 0, '#ef4444', 'fa-bell', 1),
('Certificados', 'Certificados emitidos', NULL, '/Certificados', 0, '#3b82f6', 'fa-certificate', 1),
('Contratos', 'Contratos y acuerdos', NULL, '/Contratos', 0, '#8b5cf6', 'fa-file-contract', 1),
('Facturas', 'Facturas y documentos fiscales', NULL, '/Facturas', 0, '#ec4899', 'fa-file-invoice', 1),
('General', 'Documentos generales', NULL, '/General', 0, '#94a3b8', 'fa-folder', 1);

-- ============================================================================
-- 11. INSERTAR TAGS POR DEFECTO
-- ============================================================================

INSERT IGNORE INTO `document_tags` (`name`, `slug`, `color`, `description`) VALUES
('Urgente', 'urgente', '#ef4444', 'Documentos que requieren atención inmediata'),
('Importante', 'importante', '#f59e0b', 'Documentos de alta prioridad'),
('Revisión', 'revision', '#3b82f6', 'Documentos pendientes de revisión'),
('Aprobado', 'aprobado', '#10b981', 'Documentos aprobados'),
('Borrador', 'borrador', '#6b7280', 'Documentos en borrador'),
('Confidencial', 'confidencial', '#7c3aed', 'Documentos confidenciales'),
('Público', 'publico', '#06b6d4', 'Documentos de acceso público'),
('Archivo', 'archivo', '#64748b', 'Documentos archivados');

-- ============================================================================
-- 12. VISTAS ÚTILES
-- ============================================================================

-- Vista de documentos activos (no eliminados) con toda su información
CREATE OR REPLACE VIEW `v_documents_active` AS
SELECT 
    d.*,
    m.first_name as uploaded_by_first_name,
    m.last_name as uploaded_by_last_name,
    m.email as uploaded_by_email,
    f.name as folder_name,
    f.path as folder_path,
    GROUP_CONCAT(DISTINCT dc.name SEPARATOR ', ') as category_names,
    GROUP_CONCAT(DISTINCT dt.name SEPARATOR ', ') as tag_names,
    COUNT(DISTINCT dc_comments.id) as comment_count,
    COUNT(DISTINCT dv.id) as version_count
FROM documents d
LEFT JOIN members m ON d.uploaded_by = m.id
LEFT JOIN document_folders f ON d.folder_id = f.id
LEFT JOIN document_category_rel dcr ON d.id = dcr.document_id
LEFT JOIN document_categories dc ON dcr.category_id = dc.id
LEFT JOIN document_tag_rel dtr ON d.id = dtr.document_id
LEFT JOIN document_tags dt ON dtr.tag_id = dt.id
LEFT JOIN document_comments dc_comments ON d.id = dc_comments.document_id
LEFT JOIN document_versions dv ON d.id = dv.document_id
WHERE d.deleted_at IS NULL
GROUP BY d.id;

-- Vista de documentos más descargados
CREATE OR REPLACE VIEW `v_documents_most_downloaded` AS
SELECT 
    d.id,
    d.title,
    d.downloads,
    d.file_name,
    d.created_at,
    CONCAT(m.first_name, ' ', m.last_name) as uploaded_by,
    GROUP_CONCAT(DISTINCT dc.name SEPARATOR ', ') as categories
FROM documents d
LEFT JOIN members m ON d.uploaded_by = m.id
LEFT JOIN document_category_rel dcr ON d.id = dcr.document_id
LEFT JOIN document_categories dc ON dcr.category_id = dc.id
WHERE d.deleted_at IS NULL
GROUP BY d.id
ORDER BY d.downloads DESC
LIMIT 20;

-- Vista de actividad reciente
CREATE OR REPLACE VIEW `v_document_recent_activity` AS
SELECT 
    dal.id,
    dal.document_id,
    d.title as document_title,
    dal.user_id,
    CONCAT(m.first_name, ' ', m.last_name) as username,
    dal.action,
    dal.created_at,
    dal.details
FROM document_activity_log dal
LEFT JOIN documents d ON dal.document_id = d.id
LEFT JOIN members m ON dal.user_id = m.id
ORDER BY dal.created_at DESC
LIMIT 100;

-- ============================================================================
-- 13. TRIGGERS PARA MANTENER CONTADORES ACTUALIZADOS
-- ============================================================================

-- Trigger para actualizar usage_count en tags
DELIMITER //

CREATE TRIGGER `after_document_tag_insert` AFTER INSERT ON `document_tag_rel`
FOR EACH ROW
BEGIN
    UPDATE document_tags SET usage_count = usage_count + 1 WHERE id = NEW.tag_id;
END//

CREATE TRIGGER `after_document_tag_delete` AFTER DELETE ON `document_tag_rel`
FOR EACH ROW
BEGIN
    UPDATE document_tags SET usage_count = usage_count - 1 WHERE id = OLD.tag_id;
END//

DELIMITER ;

-- ============================================================================
-- 14. PROCEDIMIENTOS ALMACENADOS ÚTILES
-- ============================================================================

DELIMITER //

-- Procedimiento para limpiar enlaces compartidos expirados
CREATE PROCEDURE `sp_cleanup_expired_shares`()
BEGIN
    UPDATE document_shares 
    SET is_active = FALSE 
    WHERE expires_at IS NOT NULL 
    AND expires_at < NOW() 
    AND is_active = TRUE;
    
    SELECT ROW_COUNT() as cleaned_shares;
END//

-- Procedimiento para obtener la ruta completa de una carpeta
CREATE PROCEDURE `sp_get_folder_path`(IN folder_id INT)
BEGIN
    WITH RECURSIVE folder_tree AS (
        SELECT id, name, parent_id, 0 as level
        FROM document_folders
        WHERE id = folder_id
        
        UNION ALL
        
        SELECT f.id, f.name, f.parent_id, ft.level + 1
        FROM document_folders f
        INNER JOIN folder_tree ft ON f.id = ft.parent_id
    )
    SELECT 
        GROUP_CONCAT(name ORDER BY level DESC SEPARATOR ' / ') as full_path
    FROM folder_tree;
END//

-- Procedimiento para mover documento a papelera
CREATE PROCEDURE `sp_trash_document`(IN doc_id INT, IN user_id INT)
BEGIN
    UPDATE documents 
    SET deleted_at = NOW(), deleted_by = user_id 
    WHERE id = doc_id;
    
    INSERT INTO document_activity_log (document_id, user_id, action, details)
    VALUES (doc_id, user_id, 'delete', JSON_OBJECT('type', 'soft_delete'));
END//

-- Procedimiento para restaurar documento de papelera
CREATE PROCEDURE `sp_restore_document`(IN doc_id INT, IN user_id INT)
BEGIN
    UPDATE documents 
    SET deleted_at = NULL, deleted_by = NULL 
    WHERE id = doc_id;
    
    INSERT INTO document_activity_log (document_id, user_id, action, details)
    VALUES (doc_id, user_id, 'restore', JSON_OBJECT('type', 'restore_from_trash'));
END//

DELIMITER ;

-- ============================================================================
-- 15. GRANTS Y PERMISOS (Opcional)
-- ============================================================================

-- Si usas usuario específico de aplicación, dar permisos
-- GRANT EXECUTE ON PROCEDURE asociacion.sp_cleanup_expired_shares TO 'gestion_user'@'localhost';
-- GRANT EXECUTE ON PROCEDURE asociacion.sp_get_folder_path TO 'gestion_user'@'localhost';
-- GRANT EXECUTE ON PROCEDURE asociacion.sp_trash_document TO 'gestion_user'@'localhost';
-- GRANT EXECUTE ON PROCEDURE asociacion.sp_restore_document TO 'gestion_user'@'localhost';

-- ============================================================================
-- FIN DE LA MIGRACIÓN
-- ============================================================================

-- Verificar que todo se creó correctamente
SELECT 'Migración completada exitosamente' as status;

-- Mostrar resumen de tablas creadas
SELECT 
    TABLE_NAME, 
    TABLE_ROWS, 
    DATA_LENGTH, 
    CREATE_TIME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME LIKE 'document%'
ORDER BY TABLE_NAME;
