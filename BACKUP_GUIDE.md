# Sistema de Backup Automático - Gestión Socios

Sistema de respaldo automático de base de datos con rotación y limpieza.

## Características

- ✅ Backups comprimidos (gzip) con timestamp
- ✅ Verificación de integridad
- ✅ Rotación automática (30 días por defecto)
- ✅ Límite de backups máximos (60 por defecto)
- ✅ Registro de actividad (logs)
- ✅ Notificaciones opcionales por email
- ✅ Cron job para ejecución automática

## Instalación

### 1. Configurar el script

Edita el archivo `scripts/backup_database.sh`:

```bash
nano /opt/GestionSocios/scripts/backup_database.sh
```

Cambia la contraseña de la base de datos:

```bash
DB_PASS="tu_contraseña_real"  # Línea 12
```

### 2. Dar permisos de ejecución

```bash
chmod +x /opt/GestionSocios/scripts/backup_database.sh
```

### 3. Probar el script manualmente

```bash
cd /opt/GestionSocios
./scripts/backup_database.sh
```

Deberías ver un mensaje de éxito y el backup en `/opt/GestionSocios/backups/`

### 4. Configurar Cron Job

Edita el crontab:

```bash
crontab -e
```

Agrega una de estas líneas según la frecuencia deseada:

#### Opción 1: Backup diario a las 2:00 AM
```cron
0 2 * * * /opt/GestionSocios/scripts/backup_database.sh >> /opt/GestionSocios/backups/cron.log 2>&1
```

#### Opción 2: Backup cada 12 horas (2:00 AM y 2:00 PM)
```cron
0 2,14 * * * /opt/GestionSocios/scripts/backup_database.sh >> /opt/GestionSocios/backups/cron.log 2>&1
```

#### Opción 3: Backup cada 6 horas
```cron
0 */6 * * * /opt/GestionSocios/scripts/backup_database.sh >> /opt/GestionSocios/backups/cron.log 2>&1
```

#### Opción 4: Backup cada hora
```cron
0 * * * * /opt/GestionSocios/scripts/backup_database.sh >> /opt/GestionSocios/backups/cron.log 2>&1
```

Guarda y cierra el editor (Ctrl+X, luego Y, luego Enter)

### 5. Verificar que el cron está activo

```bash
crontab -l
```

Deberías ver tu línea de cron listada.

## Estructura de Backups

Los backups se guardan en:
```
/opt/GestionSocios/backups/
├── backup_asociacion_db_20241122_020000.sql.gz
├── backup_asociacion_db_20241123_020000.sql.gz
├── backup_asociacion_db_20241124_020000.sql.gz
├── backup.log
└── cron.log
```

### Formato del nombre de archivo:
```
backup_[nombre_bd]_[YYYYMMDD]_[HHMMSS].sql.gz
```

## Configuración Avanzada

### Cambiar retención de backups

Edita el script y modifica:

```bash
RETENTION_DAYS=30   # Días de retención (línea 17)
MAX_BACKUPS=60      # Número máximo de backups (línea 18)
```

### Activar notificaciones por email

1. Configura el servicio de correo en tu servidor (postfix/sendmail)

2. Edita el script y descomenta la línea 38:

```bash
# Antes:
# echo "$message" | mail -s "$subject" admin@example.com

# Después:
echo "$message" | mail -s "$subject" admin@example.com
```

3. Cambia `admin@example.com` por tu email real

## Restaurar desde Backup

### Restaurar backup completo:

```bash
# Descomprimir y restaurar
gunzip < /opt/GestionSocios/backups/backup_asociacion_db_20241122_020000.sql.gz | \
    mysql -h 192.168.1.22 -u root -p asociacion_db
```

### Restaurar solo una tabla específica:

```bash
# Extraer y buscar la tabla
gunzip < backup_asociacion_db_20241122_020000.sql.gz | \
    sed -n '/CREATE TABLE `members`/,/UNLOCK TABLES/p' | \
    mysql -h 192.168.1.22 -u root -p asociacion_db
```

## Monitoreo

### Ver el log de backups:

```bash
tail -f /opt/GestionSocios/backups/backup.log
```

### Ver el log de cron:

```bash
tail -f /opt/GestionSocios/backups/cron.log
```

### Ver último backup:

```bash
ls -lht /opt/GestionSocios/backups/backup_*.sql.gz | head -1
```

### Ver estadísticas:

```bash
echo "Número total de backups:"
ls -1 /opt/GestionSocios/backups/backup_*.sql.gz | wc -l

echo "Tamaño total:"
du -sh /opt/GestionSocios/backups/
```

## Solución de Problemas

### Error: "mysqldump command not found"

Instala mysql-client:

```bash
# Debian/Ubuntu
sudo apt-get install mysql-client

# Red Hat/CentOS
sudo yum install mysql
```

### Error: "Access denied for user"

Verifica que la contraseña en el script sea correcta:

```bash
mysql -h 192.168.1.22 -u root -p asociacion_db
```

### Los backups no se ejecutan automáticamente

1. Verifica que cron esté activo:
```bash
sudo systemctl status cron
```

2. Verifica los permisos del script:
```bash
ls -l /opt/GestionSocios/scripts/backup_database.sh
# Debe mostrar: -rwxr-xr-x
```

3. Revisa el log de cron del sistema:
```bash
sudo tail -f /var/log/syslog | grep CRON
```

### El backup falla con "gzip: stdout: Broken pipe"

Aumenta el espacio en disco o reduce el número de backups retenidos.

## Backup Manual

Para crear un backup manualmente en cualquier momento:

```bash
cd /opt/GestionSocios
./scripts/backup_database.sh
```

## Seguridad

⚠️ **IMPORTANTE**:

1. **Protege el script**: Contiene credenciales de base de datos
   ```bash
   chmod 700 /opt/GestionSocios/scripts/backup_database.sh
   ```

2. **Protege el directorio de backups**:
   ```bash
   chmod 700 /opt/GestionSocios/backups
   ```

3. **Considera usar `.my.cnf`** para almacenar credenciales de forma más segura:
   ```bash
   # /root/.my.cnf
   [client]
   user=root
   password=tu_contraseña
   host=192.168.1.22
   ```

   Luego modifica el script para usar:
   ```bash
   mysqldump --defaults-file=/root/.my.cnf ...
   ```

4. **Backups offsite**: Considera copiar los backups a otra ubicación:
   ```bash
   # Agregar al script después del backup exitoso:
   rsync -avz "$BACKUP_FILE" user@remote-server:/backups/
   ```

## Mantenimiento

### Limpiar backups manualmente:

```bash
# Eliminar backups más antiguos que 30 días
find /opt/GestionSocios/backups -name "backup_*.sql.gz" -mtime +30 -delete

# Eliminar todos los backups excepto los 10 más recientes
cd /opt/GestionSocios/backups
ls -t backup_*.sql.gz | tail -n +11 | xargs rm -f
```

### Verificar integridad de un backup:

```bash
gzip -t /opt/GestionSocios/backups/backup_asociacion_db_20241122_020000.sql.gz
echo $?  # 0 = OK, cualquier otro valor = corrupto
```

## Ejemplos de Uso

### Backup antes de actualización importante:

```bash
# Crear backup manual con etiqueta
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
mysqldump -h 192.168.1.22 -u root -p asociacion_db | \
    gzip > /opt/GestionSocios/backups/backup_pre_update_${TIMESTAMP}.sql.gz
```

### Comparar dos backups:

```bash
diff <(gunzip < backup1.sql.gz) <(gunzip < backup2.sql.gz)
```

### Extraer solo el esquema (sin datos):

```bash
mysqldump -h 192.168.1.22 -u root -p --no-data asociacion_db | \
    gzip > schema_only.sql.gz
```

---

## Contacto y Soporte

Para problemas o mejoras, consulta la documentación del proyecto o contacta al administrador del sistema.
