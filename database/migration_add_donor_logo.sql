-- Add logo_url field to donors table for storing donor logos/images
ALTER TABLE donors ADD COLUMN IF NOT EXISTS logo_url VARCHAR(255) DEFAULT NULL;
