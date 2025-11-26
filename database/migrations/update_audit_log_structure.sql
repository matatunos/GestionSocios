-- Migration: Update audit_log table structure
-- This migration updates the audit_log table to match the expected structure

-- Add new columns if they don't exist
ALTER TABLE audit_log 
ADD COLUMN IF NOT EXISTS entity_type VARCHAR(50) AFTER action,
ADD COLUMN IF NOT EXISTS old_values JSON AFTER entity_id,
ADD COLUMN IF NOT EXISTS new_values JSON AFTER old_values,
ADD COLUMN IF NOT EXISTS ip_address VARCHAR(45) AFTER new_values,
ADD COLUMN IF NOT EXISTS user_agent VARCHAR(255) AFTER ip_address;

-- Migrate data from old 'entity' column to 'entity_type' if needed
UPDATE audit_log SET entity_type = entity WHERE entity_type IS NULL AND entity IS NOT NULL;

-- Drop old 'entity' column if it exists and entity_type is populated
-- ALTER TABLE audit_log DROP COLUMN IF EXISTS entity;

-- Drop old 'details' column if it exists (replaced by old_values/new_values)
-- ALTER TABLE audit_log DROP COLUMN IF EXISTS details;
