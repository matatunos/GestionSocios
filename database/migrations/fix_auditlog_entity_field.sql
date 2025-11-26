-- Migración para corregir el campo 'entity' en la tabla audit_log
-- Renombra 'entity' a 'entity_type' si existe, y elimina el campo incorrecto si es necesario

-- Renombrar el campo si existe
ALTER TABLE audit_log CHANGE entity entity_type VARCHAR(50) NOT NULL;

-- Si el campo 'entity' no existe, pero falta 'entity_type', añadirlo:
-- ALTER TABLE audit_log ADD COLUMN entity_type VARCHAR(50) NOT NULL AFTER action;

-- Si necesitas eliminar el campo 'entity' sobrante:
-- ALTER TABLE audit_log DROP COLUMN entity;

-- Revisa la estructura antes de ejecutar para evitar errores.
