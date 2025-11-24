-- Migration: Add category fee history table
-- Created: 2024-11-24
-- Description: Adds table to track historical fees per category per year

CREATE TABLE IF NOT EXISTS category_fee_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    year INT NOT NULL,
    fee_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES member_categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_category_year (category_id, year),
    INDEX idx_year (year),
    INDEX idx_category_id (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Populate with current fees from existing categories
INSERT INTO category_fee_history (category_id, year, fee_amount)
SELECT id, YEAR(CURDATE()), default_fee
FROM member_categories
WHERE default_fee > 0
ON DUPLICATE KEY UPDATE fee_amount = VALUES(fee_amount);
