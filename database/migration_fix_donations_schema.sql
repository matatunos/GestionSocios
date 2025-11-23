-- Add donor_id column to donations table
ALTER TABLE donations ADD COLUMN donor_id INT AFTER id;

-- Update the foreign key if necessary (assuming donors table exists)
-- ALTER TABLE donations ADD CONSTRAINT fk_donations_donor FOREIGN KEY (donor_id) REFERENCES donors(id);

-- Drop the old member_id column
-- WARNING: This will remove the link to members for existing donations. 
-- If you need to preserve history, you might want to migrate data first or keep the column.
ALTER TABLE donations DROP COLUMN member_id;
