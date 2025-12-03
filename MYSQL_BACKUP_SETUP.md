# Configuración de Permisos MySQL para Backups

## Problema
El usuario 'root' no tiene permisos para conectarse desde 192.168.1.7 al servidor MySQL en 192.168.1.22.

## Solución Recomendada: Crear Usuario Específico para Backups

### Opción 1: Crear usuario dedicado para backups (RECOMENDADO)

Conectarse al servidor MySQL (192.168.1.22):

```bash
ssh root@192.168.1.22
mysql -u root -p
```

Dentro de MySQL ejecutar:

```sql
-- Crear usuario para backups
CREATE USER 'backup_user'@'192.168.1.7' IDENTIFIED BY 'contraseña_segura_aqui';

-- Dar permisos de lectura para backup
GRANT SELECT, LOCK TABLES, SHOW VIEW, EVENT, TRIGGER ON asociacion_db.* TO 'backup_user'@'192.168.1.7';

-- Aplicar cambios
FLUSH PRIVILEGES;

-- Verificar permisos
SHOW GRANTS FOR 'backup_user'@'192.168.1.7';

EXIT;
```

Luego editar el archivo config.php para agregar credenciales de backup:

```php
// /opt/GestionSocios/src/Config/config.php

define('DB_HOST', '192.168.1.22');
define('DB_NAME', 'asociacion_db');
define('DB_USER', 'root');
define('DB_PASS', 'tu_password');

// Credenciales para backups (opcional, si usa usuario diferente)
define('DB_BACKUP_USER', 'backup_user');
define('DB_BACKUP_PASS', 'contraseña_segura_aqui');
```

### Opción 2: Dar permisos al usuario root existente

**MENOS SEGURO** pero más simple:

```sql
-- Conectarse a MySQL en 192.168.1.22
mysql -u root -p

-- Dar permisos al root desde 192.168.1.7
GRANT ALL PRIVILEGES ON asociacion_db.* TO 'root'@'192.168.1.7' IDENTIFIED BY 'tu_password_root';

FLUSH PRIVILEGES;
EXIT;
```

### Opción 3: Ejecutar backup localmente en el servidor MySQL

Mover el script al servidor MySQL:

```bash
# En 192.168.1.7
scp /opt/GestionSocios/scripts/backup_database.sh root@192.168.1.22:/root/

# En 192.168.1.22
ssh root@192.168.1.22

# Editar el script para usar localhost
nano /root/backup_database.sh
# Cambiar DB_HOST de 192.168.1.22 a localhost

# Configurar cron en 192.168.1.22
crontab -e
0 2 * * * /root/backup_database.sh >> /var/backups/backup.log 2>&1
```

## Verificar Configuración

Después de configurar, probar la conexión:

```bash
# Desde 192.168.1.7
mysqldump -h 192.168.1.22 -u backup_user -p asociacion_db > /tmp/test_backup.sql

# Verificar que el archivo tiene contenido
ls -lh /tmp/test_backup.sql
# Debería ser varios MB, no solo KB

# Si funciona, ejecutar el script completo
/opt/GestionSocios/scripts/backup_database.sh
```

## Solución al Problema Actual

Mientras configuras MySQL, ejecuta el backup manualmente con un método alternativo:

```bash
# Método 1: SSH al servidor MySQL y backup local
ssh root@192.168.1.22 "mysqldump -u root -p asociacion_db | gzip > /tmp/backup_$(date +%Y%m%d).sql.gz"

# Luego copiar el backup de vuelta
scp root@192.168.1.22:/tmp/backup_*.sql.gz /opt/GestionSocios/backups/

# Método 2: Usar MySQL del servidor web local (si existe)
# Solo si tienes MySQL instalado en 192.168.1.7 con acceso a 192.168.1.22
```

## Aplicar Migración Pendiente

Para aplicar la migración de book_ad_payments:

```bash
# Conectarse a MySQL
mysql -h 192.168.1.22 -u root -p

# Dentro de MySQL:
USE asociacion_db;

ALTER TABLE payments 
ADD COLUMN IF NOT EXISTS book_ad_id INT DEFAULT NULL;

ALTER TABLE payments
ADD CONSTRAINT fk_payments_book_ad 
    FOREIGN KEY (book_ad_id) 
    REFERENCES book_ads(id) 
    ON DELETE CASCADE;

CREATE INDEX IF NOT EXISTS idx_payments_book_ad ON payments(book_ad_id);

EXIT;
```
