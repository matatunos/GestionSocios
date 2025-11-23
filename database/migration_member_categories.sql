-- Migration: Add member categories system
-- Date: 2025-11-22

-- Create member_categories table
CREATE TABLE IF NOT EXISTS member_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    default_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    color VARCHAR(7) DEFAULT '#6366f1',
    is_active TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_category_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO member_categories (name, description, default_fee, color, display_order) VALUES
('General', 'Socio estándar con cuota regular', 30.00, '#6366f1', 1),
('Juvenil', 'Socio menor de 18 años con cuota reducida', 15.00, '#10b981', 2),
('Senior', 'Socio mayor de 65 años con cuota reducida', 20.00, '#f59e0b', 3),
('Familiar', 'Cuota familiar (incluye cónyuge e hijos)', 50.00, '#8b5cf6', 4),
('Honorífico', 'Socio de honor sin cuota', 0.00, '#ef4444', 5);

-- Add category_id to members table
ALTER TABLE members 
ADD COLUMN category_id INT DEFAULT NULL AFTER status,
ADD CONSTRAINT fk_member_category FOREIGN KEY (category_id) REFERENCES member_categories(id) ON DELETE SET NULL;

-- Set default category for existing members
UPDATE members SET category_id = 1 WHERE category_id IS NULL;
