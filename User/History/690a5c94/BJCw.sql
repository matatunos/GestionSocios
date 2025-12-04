-- =====================================================
-- Migration: Update grants table - Add alert columns
-- Date: 2025-12-04
-- Description: Añadir columnas alert_sent, alert_days_before, created_by a tabla grants existente
-- =====================================================

-- Añadir columnas si no existen
ALTER TABLE grants 
ADD COLUMN IF NOT EXISTS alert_sent BOOLEAN DEFAULT 0,
ADD COLUMN IF NOT EXISTS alert_days_before INT DEFAULT 7,
ADD COLUMN IF NOT EXISTS created_by INT;

-- Verificación
SELECT 'Columnas de alerta añadidas correctamente' as Status;
