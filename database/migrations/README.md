# Migraciones de Base de Datos

Este directorio contiene los scripts de migración para actualizar la base de datos en instalaciones existentes.

## 📋 ¿Qué es una migración?

Una migración es un script SQL que contiene los cambios necesarios para actualizar la estructura de la base de datos de una versión a otra. Cada migración es independiente y puede aplicarse a una base de datos existente sin necesidad de reinstalar todo el sistema.

## 🚀 Cómo Aplicar una Migración

### Opción 1: Desde la línea de comandos

```bash
# 1. Hacer backup de la base de datos
mysqldump -u usuario -p nombre_bd > backup_$(date +%Y%m%d_%H%M%S).sql

# 2. Aplicar la migración
mysql -u usuario -p nombre_bd < migrations/2025_12_accounting_module.sql

# 3. Verificar que se aplicó correctamente
mysql -u usuario -p nombre_bd -e "SHOW TABLES LIKE 'accounting%'"
```

### Opción 2: Desde phpMyAdmin

1. Acceder a phpMyAdmin
2. Seleccionar la base de datos
3. Ir a la pestaña "SQL"
4. Copiar y pegar el contenido del archivo de migración
5. Ejecutar

### Opción 3: Desde MySQL Workbench

1. Abrir MySQL Workbench
2. Conectar a la base de datos
3. Abrir el archivo de migración: File → Open SQL Script
4. Ejecutar: Query → Execute (Ctrl+Shift+Enter)

## 📁 Migraciones Disponibles

### 2025_12_accounting_module.sql

**Descripción**: Añade el módulo de contabilidad avanzada con partida doble

**Cambios incluidos**:
- 5 nuevas tablas: `accounting_periods`, `accounting_accounts`, `accounting_entries`, `accounting_entry_lines`, `budgets`
- Plan de cuentas básico con 28 cuentas según PGC español
- Período contable inicial para el año actual
- Índices y relaciones de clave foránea

**Versión**: 1.0  
**Fecha**: Diciembre 2025  
**Compatible con**: Schema base v0.5+

**Requisitos previos**:
- Tabla `users` debe existir con al menos un usuario
- Motor InnoDB habilitado
- MySQL 5.7+ o MariaDB 10.3+

## ⚠️ Recomendaciones Importantes

### Antes de Aplicar una Migración

1. **Hacer Backup**: Siempre hacer una copia de seguridad completa antes de aplicar cualquier migración
   ```bash
   mysqldump -u usuario -p nombre_bd > backup.sql
   ```

2. **Verificar Requisitos**: Asegurarse de que se cumplen todos los requisitos previos de la migración

3. **Probar en Desarrollo**: Si es posible, probar la migración en un entorno de desarrollo primero

4. **Mantenimiento**: Poner la aplicación en modo mantenimiento durante la migración

### Durante la Aplicación

1. **Monitorear**: Observar los mensajes de salida para detectar errores
2. **No Interrumpir**: No interrumpir el proceso de migración una vez iniciado
3. **Verificar**: Revisar los mensajes de verificación al final

### Después de Aplicar

1. **Verificar Datos**: Comprobar que los datos iniciales se insertaron correctamente
2. **Probar Funcionalidad**: Verificar que el nuevo módulo funciona correctamente
3. **Revisar Logs**: Revisar los logs de MySQL/MariaDB por posibles advertencias

## 🔄 Rollback (Deshacer una Migración)

Si es necesario deshacer una migración:

```bash
# 1. Restaurar desde el backup
mysql -u usuario -p nombre_bd < backup.sql

# 2. Verificar que se restauró correctamente
mysql -u usuario -p nombre_bd -e "SHOW TABLES"
```

**Nota**: No existe un script automático de rollback. La única forma segura de deshacer una migración es restaurar desde un backup.

## 📊 Estado de las Migraciones

| Migración | Fecha | Estado | Notas |
|-----------|-------|--------|-------|
| 2025_12_accounting_module.sql | Dic 2025 | ✅ Actual | Módulo de contabilidad |

## 🆘 Solución de Problemas

### Error: "Table already exists"

**Causa**: La tabla ya existe en la base de datos  
**Solución**: Las migraciones usan `CREATE TABLE IF NOT EXISTS`, por lo que este error solo debería aparecer con índices únicos. Verificar manualmente si la tabla ya tiene los datos correctos.

### Error: "Foreign key constraint fails"

**Causa**: No existe la tabla referenciada o no hay datos que cumplan la restricción  
**Solución**: 
1. Verificar que todas las tablas base existen (especialmente `users`)
2. Asegurarse de que existe al menos un usuario en la tabla `users`

### Error: "Duplicate entry for key"

**Causa**: Los datos iniciales ya existen  
**Solución**: Las migraciones usan `INSERT IGNORE`, por lo que esto es normal y no causa problemas. Los datos duplicados simplemente se omiten.

### No se crean las cuentas contables

**Causa**: Posible error en la ejecución del INSERT  
**Solución**: 
```sql
-- Verificar cuántas cuentas hay
SELECT COUNT(*) FROM accounting_accounts;

-- Si es 0, ejecutar manualmente los INSERT desde el archivo de migración
```

## 📚 Documentación Adicional

- [ACCOUNTING_MODULE.md](../../ACCOUNTING_MODULE.md) - Documentación completa del módulo de contabilidad
- [README.md](../../README.md) - Documentación general de la aplicación
- [schema.sql](../schema.sql) - Schema completo de la base de datos

## 💡 Mejores Prácticas

1. **Aplicar en Orden**: Las migraciones deben aplicarse en orden cronológico (por fecha en el nombre)
2. **Una a la Vez**: Aplicar una migración a la vez y verificar antes de continuar
3. **Documentar**: Registrar qué migraciones se han aplicado en cada entorno
4. **Testing**: Siempre probar las migraciones en desarrollo antes de producción
5. **Backups Regulares**: Mantener backups regulares, no solo antes de migraciones

## 📞 Soporte

Si encuentras problemas al aplicar una migración:

1. Revisa los logs de MySQL/MariaDB
2. Verifica los requisitos previos
3. Consulta la sección de solución de problemas
4. Abre un issue en GitHub con detalles del error

---

**Nota**: Este sistema de migraciones es manual. Para instalaciones nuevas, usar directamente el archivo `schema.sql` que ya incluye todos los cambios de las migraciones.
