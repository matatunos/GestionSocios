-- ============================================
-- Social Media Sharing Integration
-- Migration: 2025_12_social_media_sharing.sql
-- ============================================

INSERT INTO organization_settings (category, setting_key, setting_value, setting_type, description) VALUES
('social_media', 'facebook_enabled', '0', 'boolean', 'Habilitar compartir en Facebook'),
('social_media', 'facebook_app_id', '', 'text', 'Facebook App ID para compartir contenido'),
('social_media', 'twitter_enabled', '0', 'boolean', 'Habilitar compartir en Twitter/X'),
('social_media', 'linkedin_enabled', '0', 'boolean', 'Habilitar compartir en LinkedIn'),
('social_media', 'instagram_enabled', '0', 'boolean', 'Habilitar compartir en Instagram'),
('social_media', 'share_default_image', '', 'text', 'URL de imagen por defecto para compartir'),
('social_media', 'share_site_name', '', 'text', 'Nombre del sitio para metadatos Open Graph'),
('social_media', 'share_description', '', 'text', 'Descripción por defecto para compartir')
ON DUPLICATE KEY UPDATE setting_key=setting_key;
