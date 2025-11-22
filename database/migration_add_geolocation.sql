-- Migration: Add geolocation fields to members table
-- Date: 2025-11-22
-- Description: Adds latitude and longitude for GPS-based member location tracking
--              Useful for field work and mapping members on Google Maps

-- Add geolocation columns
ALTER TABLE members 
ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8) DEFAULT NULL AFTER address,
ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8) DEFAULT NULL AFTER latitude;

-- Add index for better performance on location-based queries
CREATE INDEX IF NOT EXISTS idx_location ON members(latitude, longitude);

-- Verification query
SELECT id, first_name, last_name, address, latitude, longitude 
FROM members 
WHERE latitude IS NOT NULL 
ORDER BY id 
LIMIT 10;
