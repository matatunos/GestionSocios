-- Migration: Add profile images support for members

-- Add profile_image column to members table
ALTER TABLE members 
ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) DEFAULT NULL 
AFTER phone;

-- Create uploads directory structure if not exists
-- This should be done manually: mkdir -p public/uploads/profiles

-- Update existing members to have placeholder initials-based avatars
-- (This will be handled by the application logic)
