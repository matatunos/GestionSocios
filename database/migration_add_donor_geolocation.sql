-- Migration: Add geolocation fields to donors table
-- Date: 2025-11-22
-- Description: Adds latitude and longitude for GPS-based donor location tracking

-- Add geolocation columns
ALTER TABLE donors 
ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8) DEFAULT NULL AFTER address,
ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8) DEFAULT NULL AFTER latitude;

-- Add index for better performance on location-based queries
CREATE INDEX IF NOT EXISTS idx_donor_location ON donors(latitude, longitude);

-- Verification query
SELECT id, name, address, latitude, longitude 
FROM donors 
WHERE latitude IS NOT NULL 
ORDER BY id 
LIMIT 10;
