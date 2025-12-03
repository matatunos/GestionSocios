-- ============================================================================
-- VISTAS, TRIGGERS Y PROCEDIMIENTOS PARA MÓDULO DE DOCUMENTOS
-- ============================================================================

-- 1. VISTAS ÚTILES
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
-- 2. TRIGGERS PARA MANTENER CONTADORES ACTUALIZADOS
-- ============================================================================

DELIMITER //

-- Trigger para actualizar usage_count en tags cuando se agrega un tag
DROP TRIGGER IF EXISTS `after_document_tag_insert`//
CREATE TRIGGER `after_document_tag_insert` AFTER INSERT ON `document_tag_rel`
FOR EACH ROW
BEGIN
    UPDATE document_tags SET usage_count = usage_count + 1 WHERE id = NEW.tag_id;
END//

-- Trigger para actualizar usage_count en tags cuando se elimina un tag
DROP TRIGGER IF EXISTS `after_document_tag_delete`//
CREATE TRIGGER `after_document_tag_delete` AFTER DELETE ON `document_tag_rel`
FOR EACH ROW
BEGIN
    UPDATE document_tags SET usage_count = usage_count - 1 WHERE id = OLD.tag_id;
END//

DELIMITER ;

-- ============================================================================
-- 3. PROCEDIMIENTOS ALMACENADOS
-- ============================================================================

DELIMITER //

-- Procedimiento para limpiar shares expirados (ejecutar desde cron)
DROP PROCEDURE IF EXISTS `sp_cleanup_expired_shares`//
CREATE PROCEDURE `sp_cleanup_expired_shares`()
BEGIN
    DELETE FROM document_shares 
    WHERE expires_at IS NOT NULL 
    AND expires_at < NOW();
    
    SELECT ROW_COUNT() as deleted_shares;
END//

-- Procedimiento para obtener la ruta completa de una carpeta
DROP PROCEDURE IF EXISTS `sp_get_folder_path`//
CREATE PROCEDURE `sp_get_folder_path`(IN folder_id INT)
BEGIN
    WITH RECURSIVE folder_tree AS (
        SELECT id, name, parent_id, path, 1 as level
        FROM document_folders
        WHERE id = folder_id
        
        UNION ALL
        
        SELECT f.id, f.name, f.parent_id, f.path, ft.level + 1
        FROM document_folders f
        INNER JOIN folder_tree ft ON f.id = ft.parent_id
    )
    SELECT * FROM folder_tree ORDER BY level DESC;
END//

-- Procedimiento para mover documento a papelera (soft delete)
DROP PROCEDURE IF EXISTS `sp_trash_document`//
CREATE PROCEDURE `sp_trash_document`(IN doc_id INT, IN user_id INT)
BEGIN
    UPDATE documents 
    SET deleted_at = NOW(), deleted_by = user_id 
    WHERE id = doc_id AND deleted_at IS NULL;
    
    -- Registrar actividad
    INSERT INTO document_activity_log (document_id, user_id, action, details)
    VALUES (doc_id, user_id, 'trashed', 'Documento movido a papelera');
    
    SELECT ROW_COUNT() > 0 as success;
END//

-- Procedimiento para restaurar documento de papelera
DROP PROCEDURE IF EXISTS `sp_restore_document`//
CREATE PROCEDURE `sp_restore_document`(IN doc_id INT, IN user_id INT)
BEGIN
    UPDATE documents 
    SET deleted_at = NULL, deleted_by = NULL 
    WHERE id = doc_id AND deleted_at IS NOT NULL;
    
    -- Registrar actividad
    INSERT INTO document_activity_log (document_id, user_id, action, details)
    VALUES (doc_id, user_id, 'restored', 'Documento restaurado de papelera');
    
    SELECT ROW_COUNT() > 0 as success;
END//

DELIMITER ;

-- ============================================================================
-- FINALIZADO
-- ============================================================================

SELECT 'Vistas, triggers y procedimientos creados correctamente' as status;
