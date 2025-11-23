-- Migration: Allow NULL for member_id in payments table
-- This is needed for book_ad payments which don't have an associated member

ALTER TABLE payments 
MODIFY COLUMN member_id INT DEFAULT NULL;

-- Also ensure payment_date can be NULL for pending payments
ALTER TABLE payments 
MODIFY COLUMN payment_date DATE DEFAULT NULL;
