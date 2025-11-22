-- Migration: Add member_number column to members table
-- Date: 2025-11-22
-- Description: Adds member_number column and auto-populates it with member ID

-- Add member_number column if it doesn't exist
ALTER TABLE members 
ADD COLUMN IF NOT EXISTS member_number VARCHAR(50) DEFAULT NULL AFTER id;

-- Update existing members to use their ID as member_number
UPDATE members 
SET member_number = id 
WHERE member_number IS NULL OR member_number = '';

-- Add index for better performance
CREATE INDEX IF NOT EXISTS idx_member_number ON members(member_number);

-- Verification query
SELECT id, member_number, first_name, last_name 
FROM members 
ORDER BY id 
LIMIT 10;
