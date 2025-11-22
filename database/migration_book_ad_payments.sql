-- Add book_ad_id column to payments table for tracking book ads payments

ALTER TABLE payments 
ADD COLUMN IF NOT EXISTS book_ad_id INT DEFAULT NULL,
ADD CONSTRAINT fk_payments_book_ad 
    FOREIGN KEY (book_ad_id) 
    REFERENCES book_ads(id) 
    ON DELETE CASCADE;

-- Create index for better performance
CREATE INDEX IF NOT EXISTS idx_payments_book_ad ON payments(book_ad_id);
