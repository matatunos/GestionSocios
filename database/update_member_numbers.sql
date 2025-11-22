-- Actualizar números de socio para todos los socios existentes
-- Asignar el ID como número de socio si está vacío o es NULL

UPDATE members 
SET member_number = id 
WHERE member_number IS NULL OR member_number = '';

-- Verificar el resultado
SELECT id, member_number, first_name, last_name 
FROM members 
ORDER BY id;
