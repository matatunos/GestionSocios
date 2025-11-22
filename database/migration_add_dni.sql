-- Migration: Add DNI field to members table
-- Date: 2025-11-22
-- Description: Adds dni (identification document) field to members table for better identification in certificates

-- Add dni column if it doesn't exist
ALTER TABLE members 
ADD COLUMN IF NOT EXISTS dni VARCHAR(20) DEFAULT NULL AFTER last_name;

-- Add index for better performance on searches
CREATE INDEX IF NOT EXISTS idx_dni ON members(dni);

-- Verification query
SELECT id, first_name, last_name, dni, email 
FROM members 
ORDER BY id 
LIMIT 10;
