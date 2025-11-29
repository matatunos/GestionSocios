-- Password Policy and Account Lockout Migration
-- Run this script to add password policy and account lockout features

-- Add password policy columns to organization_settings
ALTER TABLE organization_settings 
ADD COLUMN IF NOT EXISTS password_min_length INT DEFAULT 8,
ADD COLUMN IF NOT EXISTS password_require_uppercase BOOLEAN DEFAULT 0,
ADD COLUMN IF NOT EXISTS password_require_lowercase BOOLEAN DEFAULT 1,
ADD COLUMN IF NOT EXISTS password_require_numbers BOOLEAN DEFAULT 1,
ADD COLUMN IF NOT EXISTS password_require_special BOOLEAN DEFAULT 0,
ADD COLUMN IF NOT EXISTS login_max_attempts INT DEFAULT 5,
ADD COLUMN IF NOT EXISTS login_lockout_duration INT DEFAULT 15;

-- Add lockout tracking to users table
ALTER TABLE users
ADD COLUMN IF NOT EXISTS locked_until DATETIME NULL,
ADD COLUMN IF NOT EXISTS failed_attempts INT DEFAULT 0;

-- Create login attempts tracking table
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT 0,
    INDEX idx_username (username),
    INDEX idx_ip (ip_address),
    INDEX idx_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
