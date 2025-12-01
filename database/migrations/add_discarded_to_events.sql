-- Migración para añadir el campo 'discarded' a la tabla 'events'
ALTER TABLE events ADD COLUMN discarded TINYINT(1) DEFAULT 0 AFTER updated_at;