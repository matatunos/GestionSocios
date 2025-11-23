-- Migration: Improve events table for calendar and attendance system

-- Add new columns to events table for better calendar functionality
ALTER TABLE events 
ADD COLUMN IF NOT EXISTS location VARCHAR(255) DEFAULT NULL AFTER description,
ADD COLUMN IF NOT EXISTS start_time TIME DEFAULT NULL AFTER date,
ADD COLUMN IF NOT EXISTS end_time TIME DEFAULT NULL AFTER start_time,
ADD COLUMN IF NOT EXISTS max_attendees INT DEFAULT NULL AFTER price,
ADD COLUMN IF NOT EXISTS requires_registration BOOLEAN DEFAULT FALSE AFTER max_attendees,
ADD COLUMN IF NOT EXISTS registration_deadline DATE DEFAULT NULL AFTER requires_registration,
ADD COLUMN IF NOT EXISTS event_type ENUM('meeting', 'celebration', 'activity', 'assembly', 'other') DEFAULT 'other' AFTER name,
ADD COLUMN IF NOT EXISTS color VARCHAR(7) DEFAULT '#6366f1' AFTER event_type;

-- Create event_attendance table for tracking who attends events
CREATE TABLE IF NOT EXISTS event_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    member_id INT NOT NULL,
    status ENUM('registered', 'confirmed', 'attended', 'cancelled') DEFAULT 'registered',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT DEFAULT NULL,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    UNIQUE KEY unique_event_member (event_id, member_id)
);

-- Add index for better query performance
CREATE INDEX idx_event_date ON events(date);
CREATE INDEX idx_event_active ON events(is_active);
CREATE INDEX idx_attendance_status ON event_attendance(status);
CREATE INDEX idx_attendance_event ON event_attendance(event_id);

-- Update payments table to ensure year column exists (renamed from fee_year)
ALTER TABLE payments 
ADD COLUMN IF NOT EXISTS year INT DEFAULT NULL AFTER fee_year;

-- Migrate data from fee_year to year if needed
UPDATE payments SET year = fee_year WHERE year IS NULL AND fee_year IS NOT NULL;

-- Note: Don't drop fee_year yet in case some queries still reference it
