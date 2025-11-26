-- Migración: Añadir campo 'position' a la tabla book_pages para soportar imágenes a media página
-- Fecha: 2025-11-26
ALTER TABLE book_pages ADD COLUMN position ENUM('full', 'top', 'bottom') DEFAULT 'full' AFTER page_number;

-- Ejemplo de uso:
-- 'full' = página completa
-- 'top' = parte superior de la página
-- 'bottom' = parte inferior de la página
