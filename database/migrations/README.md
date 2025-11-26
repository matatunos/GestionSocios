# Migración: Añadir columnas is_current y replaced_at

## Problema
El modelo `DonorImageHistory` y `MemberImageHistory` intentan insertar datos en las columnas `is_current` y `replaced_at`, pero estas columnas no existen en la base de datos de producción.

## Error
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'is_current' in 'INSERT INTO'
```

## Solución
Ejecutar la migración `add_image_history_columns.sql` para añadir las columnas faltantes.

## Instrucciones para aplicar la migración

### Opción 1: Usando el script bash (Recomendado)
```bash
cd /opt/GestionSocios/database/migrations
chmod +x apply_migration.sh
./apply_migration.sh
```

### Opción 2: Manualmente con MySQL
```bash
cd /opt/GestionSocios/database/migrations
mysql -u root -p gestion_socios < add_image_history_columns.sql
```

### Opción 3: Desde el cliente MySQL
```sql
-- Conectarse a MySQL
mysql -u root -p gestion_socios

-- Copiar y pegar el contenido de add_image_history_columns.sql
```

## Verificación
Después de aplicar la migración, verificar que las columnas se hayan añadido correctamente:

```sql
DESCRIBE donor_image_history;
DESCRIBE member_image_history;
```

Deberías ver las siguientes columnas en ambas tablas:
- `id`
- `donor_id` / `member_id`
- `image_url`
- `is_current` ← **NUEVA**
- `uploaded_at`
- `uploaded_by`
- `replaced_at` ← **NUEVA**

## Archivos modificados
- ✅ `database/schema.sql` - Actualizado con las nuevas columnas
- ✅ `database/migrations/add_image_history_columns.sql` - Script de migración
- ✅ `database/migrations/apply_migration.sh` - Script de aplicación

## Notas
- Esta migración es **segura** y no afecta a los datos existentes
- Las columnas nuevas tienen valores por defecto:
  - `is_current`: 1 (verdadero)
  - `replaced_at`: NULL
- Se han añadido índices para mejorar el rendimiento de las consultas

## Próximos pasos
1. Hacer commit de los cambios en la rama `devel`
2. Aplicar la migración en el servidor de producción
3. Verificar que el error ya no aparece en los logs
