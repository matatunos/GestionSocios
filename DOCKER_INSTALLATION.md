# 🐳 Instalación con Docker

Esta guía te permitirá instalar y ejecutar la aplicación de Gestión de Socios usando Docker y Docker Compose, sin necesidad de instalar manualmente Apache, PHP o MySQL.

## 📋 Requisitos Previos

### Instalar Docker y Docker Compose

#### En Ubuntu/Debian:
```bash
# Actualizar paquetes
sudo apt update

# Instalar Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Añadir usuario al grupo docker (para no usar sudo)
sudo usermod -aG docker $USER

# Instalar Docker Compose
sudo apt install docker-compose-plugin

# Verificar instalación
docker --version
docker compose version
```

#### En Windows:
1. Descargar e instalar [Docker Desktop for Windows](https://www.docker.com/products/docker-desktop)
2. Iniciar Docker Desktop
3. Docker Compose viene incluido

#### En macOS:
1. Descargar e instalar [Docker Desktop for Mac](https://www.docker.com/products/docker-desktop)
2. Iniciar Docker Desktop
3. Docker Compose viene incluido

## 🚀 Instalación Rápida

### 1. Clonar el Repositorio

```bash
git clone https://github.com/matatunos/GestionSocios.git
cd GestionSocios
```

### 2. Configurar Variables de Entorno (Opcional)

Por defecto, la aplicación usa las credenciales definidas en `docker-compose.yml`. Si deseas cambiarlas:

```bash
# Editar docker-compose.yml y cambiar:
# - MYSQL_ROOT_PASSWORD
# - MYSQL_PASSWORD / DB_PASS
# - Puertos si hay conflictos
```

### 3. Construir y Levantar los Contenedores

```bash
# Construir las imágenes y levantar los contenedores
docker compose up -d

# Ver el progreso
docker compose logs -f
```

Este comando:
- ✅ Descarga las imágenes necesarias (PHP, MariaDB, phpMyAdmin)
- ✅ Construye el contenedor de la aplicación
- ✅ Crea la base de datos automáticamente
- ✅ Importa el schema completo
- ✅ Configura todos los servicios

### 4. Acceder a la Aplicación

Espera unos segundos a que todo inicie y luego accede:

- **Aplicación principal**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081 (para administrar la base de datos)

**Credenciales por defecto:**
- **Usuario**: admin
- **Contraseña**: admin123

⚠️ **IMPORTANTE**: Cambia la contraseña del administrador inmediatamente después del primer acceso.

## 🔧 Comandos Útiles

### Gestión de Contenedores

```bash
# Ver estado de los contenedores
docker compose ps

# Ver logs en tiempo real
docker compose logs -f web

# Ver logs de la base de datos
docker compose logs -f db

# Detener los contenedores
docker compose stop

# Iniciar los contenedores (si están detenidos)
docker compose start

# Reiniciar los contenedores
docker compose restart

# Detener y eliminar contenedores (los datos persisten)
docker compose down

# Detener y eliminar TODO (incluyendo volúmenes de datos)
docker compose down -v
```

### Acceso al Contenedor

```bash
# Acceder al contenedor de la aplicación
docker compose exec web bash

# Acceder al contenedor de la base de datos
docker compose exec db bash

# Ejecutar comandos MySQL directamente
docker compose exec db mysql -u root -p asociacion
```

### Backups y Restauración

```bash
# Crear backup de la base de datos
docker compose exec db mysqldump -u gestion_user -pgestion_password_2025 asociacion > backup_$(date +%Y%m%d_%H%M%S).sql

# Restaurar backup
docker compose exec -T db mysql -u gestion_user -pgestion_password_2025 asociacion < backup_20251203_120000.sql

# Backup con compresión
docker compose exec db mysqldump -u gestion_user -pgestion_password_2025 asociacion | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz

# Restaurar backup comprimido
gunzip < backup_20251203_120000.sql.gz | docker compose exec -T db mysql -u gestion_user -pgestion_password_2025 asociacion
```

## 📁 Estructura de Volúmenes

Los datos persistentes se guardan en volúmenes Docker:

```
Volúmenes:
├── db-data (base de datos MySQL)
├── ./public/uploads (archivos subidos - mapeado al host)
└── ./src/Config (configuración - mapeado al host)
```

**Ventaja**: Los archivos subidos y la configuración están mapeados a tu sistema local, por lo que puedes acceder a ellos directamente desde tu carpeta del proyecto.

## 🔒 Seguridad para Producción

Si vas a usar esto en producción, es **CRÍTICO** que:

### 1. Cambies las Contraseñas

Edita `docker-compose.yml` antes de hacer `docker compose up`:

```yaml
environment:
  MYSQL_ROOT_PASSWORD: tu_password_super_segura_root
  MYSQL_PASSWORD: tu_password_super_segura_user
  # ...
```

### 2. Configures HTTPS

Para habilitar HTTPS, necesitarás un proxy reverso como Nginx o Traefik. Ejemplo con Nginx:

```bash
# Instalar certbot
sudo apt install certbot python3-certbot-nginx

# Obtener certificado SSL
sudo certbot --nginx -d tu-dominio.com
```

### 3. Desactives phpMyAdmin

En producción, elimina o comenta el servicio de phpMyAdmin en `docker-compose.yml`:

```yaml
# phpmyadmin:
#   image: phpmyadmin:latest
#   ...
```

### 4. Configures Firewall

```bash
# Solo permitir puertos HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

## 🐛 Solución de Problemas

### Puerto 8080 ya en uso

Si el puerto 8080 está ocupado, cámbialo en `docker-compose.yml`:

```yaml
web:
  ports:
    - "8888:80"  # Cambiar 8080 por otro puerto
```

### La base de datos no inicia

```bash
# Ver logs detallados
docker compose logs db

# Si hay problemas de permisos en volúmenes
docker compose down -v
docker compose up -d
```

### No se pueden subir archivos

```bash
# Verificar permisos dentro del contenedor
docker compose exec web ls -la /var/www/html/public/uploads

# Corregir permisos
docker compose exec web chown -R www-data:www-data /var/www/html/public/uploads
docker compose exec web chmod -R 775 /var/www/html/public/uploads
```

### Errores de PHP o configuración

```bash
# Ver logs de Apache
docker compose logs web

# Acceder al contenedor para investigar
docker compose exec web bash
tail -f /var/log/apache2/error.log
```

### Resetear todo y empezar de cero

```bash
# CUIDADO: Esto borra TODO (base de datos incluida)
docker compose down -v
docker compose up -d
```

## 🔄 Actualización

Para actualizar la aplicación a una nueva versión:

```bash
# 1. Hacer backup
docker compose exec db mysqldump -u gestion_user -pgestion_password_2025 asociacion > backup_pre_update.sql

# 2. Detener contenedores
docker compose down

# 3. Actualizar código
git pull origin main

# 4. Reconstruir imágenes
docker compose build --no-cache

# 5. Levantar contenedores
docker compose up -d

# 6. Aplicar migraciones si existen
docker compose exec web bash
cd database/migrations
# Ejecutar migraciones manualmente si es necesario
```

## 📊 Monitoreo

### Ver recursos usados

```bash
# Ver uso de CPU/RAM/Red
docker stats

# Ver espacio usado por volúmenes
docker system df -v
```

### Logs centralizados

```bash
# Ver todos los logs
docker compose logs

# Seguir logs en tiempo real
docker compose logs -f

# Últimas 100 líneas
docker compose logs --tail=100
```

## 🌐 Acceso desde Otros Dispositivos

Para acceder desde otros dispositivos en tu red local:

1. Averigua tu IP local:
```bash
# Linux/Mac
ip addr show | grep inet

# Windows
ipconfig
```

2. Accede desde otro dispositivo usando:
```
http://TU_IP_LOCAL:8080
```

Por ejemplo: `http://192.168.1.100:8080`

## 📝 Notas Adicionales

### Datos de Ejemplo

Si quieres cargar datos de ejemplo después de la instalación:

```bash
# Copiar archivo de datos de ejemplo al contenedor
docker compose cp database/sample_data_large.sql db:/tmp/

# Importar datos
docker compose exec db mysql -u gestion_user -pgestion_password_2025 asociacion < /tmp/sample_data_large.sql
```

### Composer

Si necesitas instalar o actualizar dependencias PHP:

```bash
docker compose exec web composer install
# o
docker compose exec web composer update
```

### Desarrollo

Para desarrollo activo, puedes mapear todo el código al host editando `docker-compose.yml`:

```yaml
web:
  volumes:
    - .:/var/www/html  # Mapear todo el código
    - ./public/uploads:/var/www/html/public/uploads
```

Esto permite editar archivos en tu editor local y ver cambios inmediatamente.

---

## 📞 Soporte

Si encuentras problemas con Docker:

1. Revisa los logs: `docker compose logs`
2. Verifica que Docker esté corriendo: `docker ps`
3. Consulta la [documentación oficial de Docker](https://docs.docker.com/)
4. Abre un issue en GitHub: https://github.com/matatunos/GestionSocios/issues

---

**¡Listo!** Tu aplicación debería estar funcionando en http://localhost:8080 🎉
