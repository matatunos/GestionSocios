-- Migración: Sistema de enlaces públicos para documentos
-- Fecha: 2025-12-03
-- Descripción: Permite compartir documentos mediante enlaces públicos con límites de tiempo y descargas

-- Verificar si la columna public_token ya existe (del código anterior)
SELECT COUNT(*) INTO @column_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'documents' 
AND COLUMN_NAME = 'public_token';

-- Si no existe, agregarla
SET @query = IF(@column_exists = 0,
    'ALTER TABLE documents 
     ADD COLUMN public_token VARCHAR(64) NULL UNIQUE AFTER version,
     ADD COLUMN token_expires_at DATETIME NULL AFTER public_token',
    'SELECT "Columnas public_token ya existen"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar nuevas columnas para límites de descargas públicas
ALTER TABLE documents 
ADD COLUMN IF NOT EXISTS public_download_limit INT NULL COMMENT 'Límite de descargas públicas (NULL = ilimitado)' AFTER token_expires_at,
ADD COLUMN IF NOT EXISTS public_downloads INT DEFAULT 0 COMMENT 'Contador de descargas públicas' AFTER public_download_limit,
ADD COLUMN IF NOT EXISTS public_enabled BOOLEAN DEFAULT FALSE COMMENT 'Si el enlace público está activo' AFTER public_downloads,
ADD COLUMN IF NOT EXISTS public_created_at DATETIME NULL COMMENT 'Fecha de creación del enlace público' AFTER public_enabled,
ADD COLUMN IF NOT EXISTS public_created_by INT NULL COMMENT 'Usuario que creó el enlace público' AFTER public_created_at,
ADD COLUMN IF NOT EXISTS public_last_access DATETIME NULL COMMENT 'Última vez que se accedió al enlace' AFTER public_created_by;

-- Agregar índices si no existen
SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'documents' AND index_name = 'idx_public_token');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE documents ADD INDEX idx_public_token (public_token)', 'SELECT "Index already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'documents' AND index_name = 'idx_public_enabled');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE documents ADD INDEX idx_public_enabled (public_enabled)', 'SELECT "Index already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'documents' AND index_name = 'idx_token_expires');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE documents ADD INDEX idx_token_expires (token_expires_at)', 'SELECT "Index already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar 'public_link_created' y 'public_link_revoked' al ENUM de acciones de document_activity_log
ALTER TABLE document_activity_log 
MODIFY COLUMN action ENUM(
    'view','download','edit','delete','restore','share','comment','upload_version',
    'uploaded','created','updated','moved','copied','favorited','unfavorited','previewed',
    'public_link_created','public_link_revoked'
) NOT NULL;

-- Tabla de log de accesos públicos
CREATE TABLE IF NOT EXISTS document_public_access_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    access_token VARCHAR(64) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    referer VARCHAR(255) NULL,
    downloaded BOOLEAN DEFAULT FALSE,
    access_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_document_id (document_id),
    INDEX idx_access_token (access_token),
    INDEX idx_access_date (access_date),
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vista para documentos públicos activos
CREATE OR REPLACE VIEW public_documents_active AS
SELECT 
    d.id,
    d.title,
    d.file_name,
    d.file_size,
    d.public_token,
    d.token_expires_at,
    d.public_download_limit,
    d.public_downloads,
    d.public_created_at,
    d.public_last_access,
    m.first_name,
    m.last_name,
    CASE 
        WHEN d.public_download_limit IS NOT NULL AND d.public_downloads >= d.public_download_limit THEN 'limit_reached'
        WHEN d.token_expires_at IS NOT NULL AND d.token_expires_at < NOW() THEN 'expired'
        ELSE 'active'
    END as status,
    CASE 
        WHEN d.public_download_limit IS NOT NULL 
        THEN CONCAT(d.public_downloads, '/', d.public_download_limit)
        ELSE CONCAT(d.public_downloads, '/∞')
    END as download_stats
FROM documents d
LEFT JOIN members m ON d.public_created_by = m.id
WHERE d.public_enabled = TRUE
    AND d.deleted_at IS NULL;

-- Procedimiento para generar token único
DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS generate_public_token(
    IN p_document_id INT,
    IN p_user_id INT,
    IN p_expires_at DATETIME,
    IN p_download_limit INT,
    OUT p_token VARCHAR(64)
)
BEGIN
    DECLARE v_token VARCHAR(64);
    DECLARE v_exists INT;
    
    -- Generar token único
    REPEAT
        SET v_token = SHA2(CONCAT(p_document_id, NOW(), RAND(), UUID()), 256);
        SELECT COUNT(*) INTO v_exists FROM documents WHERE public_token = v_token;
    UNTIL v_exists = 0 END REPEAT;
    
    -- Actualizar documento con el token
    UPDATE documents 
    SET public_token = v_token,
        token_expires_at = p_expires_at,
        public_download_limit = p_download_limit,
        public_downloads = 0,
        public_enabled = TRUE,
        public_created_at = NOW(),
        public_created_by = p_user_id,
        public_last_access = NULL
    WHERE id = p_document_id;
    
    -- Registrar en log de auditoría
    INSERT INTO document_activity_log (document_id, action, user_id, ip_address, details)
    VALUES (
        p_document_id, 
        'public_link_created', 
        p_user_id, 
        'SYSTEM',
        JSON_OBJECT(
            'expires_at', p_expires_at,
            'download_limit', p_download_limit
        )
    );
    
    SET p_token = v_token;
END$$

-- Procedimiento para revocar enlace público
CREATE PROCEDURE IF NOT EXISTS revoke_public_token(
    IN p_document_id INT,
    IN p_user_id INT
)
BEGIN
    UPDATE documents 
    SET public_enabled = FALSE
    WHERE id = p_document_id;
    
    -- Registrar en log de auditoría
    INSERT INTO document_activity_log (document_id, action, user_id, ip_address, details)
    VALUES (p_document_id, 'public_link_revoked', p_user_id, 'SYSTEM', NULL);
END$$

-- Procedimiento para registrar acceso público
CREATE PROCEDURE IF NOT EXISTS log_public_access(
    IN p_document_id INT,
    IN p_token VARCHAR(64),
    IN p_ip_address VARCHAR(45),
    IN p_user_agent TEXT,
    IN p_referer VARCHAR(255),
    IN p_downloaded BOOLEAN
)
BEGIN
    -- Registrar acceso
    INSERT INTO document_public_access_log (
        document_id, 
        access_token, 
        ip_address, 
        user_agent, 
        referer, 
        downloaded
    ) VALUES (
        p_document_id,
        p_token,
        p_ip_address,
        p_user_agent,
        p_referer,
        p_downloaded
    );
    
    -- Actualizar última fecha de acceso y contador de descargas
    IF p_downloaded THEN
        UPDATE documents 
        SET public_downloads = public_downloads + 1,
            public_last_access = NOW()
        WHERE id = p_document_id;
    ELSE
        UPDATE documents 
        SET public_last_access = NOW()
        WHERE id = p_document_id;
    END IF;
END$$

-- Función para verificar si un token público es válido
CREATE FUNCTION IF NOT EXISTS is_public_token_valid(
    p_token VARCHAR(64)
) RETURNS BOOLEAN
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_valid BOOLEAN DEFAULT FALSE;
    
    SELECT 
        (public_enabled = TRUE
        AND deleted_at IS NULL
        AND (token_expires_at IS NULL OR token_expires_at > NOW())
        AND (public_download_limit IS NULL OR public_downloads < public_download_limit))
    INTO v_valid
    FROM documents
    WHERE public_token = p_token;
    
    RETURN COALESCE(v_valid, FALSE);
END$$

DELIMITER ;

-- Evento para limpiar tokens expirados automáticamente (ejecutar diariamente)
DELIMITER $$
CREATE EVENT IF NOT EXISTS cleanup_expired_public_tokens
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    UPDATE documents 
    SET public_enabled = FALSE
    WHERE public_enabled = TRUE
        AND token_expires_at IS NOT NULL
        AND token_expires_at < NOW();
        
    -- Limpiar logs antiguos (más de 1 año)
    DELETE FROM document_public_access_log
    WHERE access_date < DATE_SUB(NOW(), INTERVAL 1 YEAR);
END$$
DELIMITER ;

-- Agregar robots.txt para no indexar enlaces públicos (se debe crear el archivo físico)
