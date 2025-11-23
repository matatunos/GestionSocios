# Gestión de Socios - Asociación

Sistema web integral para la gestión de socios, pagos, donaciones, tareas y geolocalización de una asociación. Desarrollado en PHP nativo con MySQL.

## 🚀 Características Principales

### Gestión de Socios
- Alta, baja, edición y listado de socios con foto de perfil
- Campo DNI/NIE para identificación oficial
- Geolocalización GPS con captura desde móvil o entrada manual
- Generación automática de número de socio
- Búsqueda avanzada y filtrado
- Exportación a Excel y PDF

### Gestión de Donantes
- Registro de empresas y particulares donantes
- Geolocalización de donantes con GPS
- Seguimiento de contactos y donaciones
- Gestión de logos y datos de contacto

### Certificados
- Generación de certificados de socio en PDF
- Certificados de pago con desglose de cuotas
- Nombres de archivo descriptivos (DNI-Nombre)

### Geolocalización y Mapas
- Mapa interactivo con Leaflet.js y OpenStreetMap
- Captura de coordenadas GPS desde dispositivos móviles
- Entrada manual de coordenadas (compatible con HTTP)
- Visualización combinada de socios y donantes
- Filtros: mostrar todos, solo socios, solo donantes
- Marcadores diferenciados por color (azul: socios, verde: donantes)
- Enlaces directos a Google Maps
- Diseño responsive para móvil y desktop

### Gestión de Tareas
- Crear, editar y asignar tareas
- Estados: pendiente, en progreso, completada, cancelada
- Prioridades: baja, normal, alta, urgente
- Fechas de vencimiento y seguimiento
- Historial de cambios

### Analíticas y Reportes
- Dashboard con estadísticas en tiempo real
- Gráficos de evolución de socios
- Reportes de pagos y morosos
- Exportación de datos

### Configuración
- Gestión centralizada de la organización
- Logo y datos institucionales personalizables
- Copias de seguridad de base de datos
- Interfaz moderna con modo claro/oscuro

## 📋 Requisitos del Sistema

### Servidor
- **Sistema Operativo**: Linux (Ubuntu 20.04+, Debian 10+, CentOS 7+) o Windows Server
- **Servidor Web**: Apache 2.4+ con `mod_rewrite` habilitado
- **PHP**: 7.4 o superior (recomendado PHP 8.0+)
- **Base de Datos**: MySQL 5.7+ o MariaDB 10.3+

### Extensiones PHP Requeridas
```bash
php-pdo
php-pdo-mysql
php-mbstring
php-json
php-curl
php-gd (para procesamiento de imágenes)
php-zip (para exportaciones)
```

### Permisos del Sistema
- El usuario del servidor web (típicamente `www-data` o `apache`) necesita permisos de escritura en:
  - `src/Config/` (configuración)
  - `public/uploads/` (archivos subidos)
  - `public/uploads/members/` (fotos de socios)
  - `public/uploads/donors/` (logos de donantes)
  - `public/uploads/organization/` (logo institucional)

### Navegadores Compatibles
- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Opera 76+

### Para Geolocalización GPS
- **HTTPS**: Requerido para captura automática de GPS (restricción de seguridad HTML5)
- **HTTP**: Solo permite entrada manual de coordenadas
- **Permisos**: El usuario debe autorizar acceso a la ubicación en el navegador

## 🛠️ Instalación

### Método Rápido (Recomendado para v1.0+)

#### 1. Clonar el Repositorio

```bash
git clone https://github.com/matatunos/GestionSocios.git
cd GestionSocios
```

#### 2. Instalar Base de Datos

```bash
cd database
chmod +x install_v1.0.sh
./install_v1.0.sh
```

El script te pedirá:
- Nombre de la base de datos (default: `asociacion_db`)
- Usuario MySQL (default: `root`)
- Contraseña MySQL
- Host MySQL (default: `localhost`)

**¡Importante!** Este script instala TODO el schema v1.0 de una vez. **NO necesitas ejecutar migraciones adicionales**.

#### 3. Configurar Servidor Web Apache

#### En Linux:
```bash
# Crear virtual host
sudo nano /etc/apache2/sites-available/gestion-socios.conf
```

Contenido del archivo:
```apache
<VirtualHost *:80>
    ServerName gestion-socios.local
    DocumentRoot /var/www/GestionSocios/public
    
    <Directory /var/www/GestionSocios/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/gestion-socios-error.log
    CustomLog ${APACHE_LOG_DIR}/gestion-socios-access.log combined
</VirtualHost>
```

```bash
# Habilitar sitio y mod_rewrite
sudo a2ensite gestion-socios.conf
sudo a2enmod rewrite
sudo systemctl restart apache2

# Configurar permisos
sudo chown -R www-data:www-data /var/www/GestionSocios
sudo chmod -R 755 /var/www/GestionSocios
sudo chmod -R 775 /var/www/GestionSocios/public/uploads
sudo chmod -R 775 /var/www/GestionSocios/src/Config
```

#### En Windows (XAMPP/WAMP):
1. Copiar la carpeta del proyecto a `C:\xampp\htdocs\GestionSocios`
2. Editar `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:
```apache
<VirtualHost *:80>
    ServerName gestion-socios.local
    DocumentRoot "C:/xampp/htdocs/GestionSocios/public"
    <Directory "C:/xampp/htdocs/GestionSocios/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
3. Agregar a `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 gestion-socios.local
```
4. Reiniciar Apache desde el panel de XAMPP

### 3. Crear Base de Datos MySQL

```bash
# Acceder a MySQL
mysql -u root -p

# Crear base de datos y usuario
CREATE DATABASE asociacion_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'gestion_user'@'localhost' IDENTIFIED BY 'tu_password_segura';
GRANT ALL PRIVILEGES ON asociacion_db.* TO 'gestion_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Configurar Aplicación

Edita `src/Config/config.php` con tus credenciales de base de datos:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'asociacion_db');
define('DB_USER', 'gestion_user');
define('DB_PASS', 'tu_password_segura');
?>
```

### 5. Acceder al Sistema

1. Abrir navegador: `http://gestion-socios.local`
2. **Usuario**: `admin`
3. **Contraseña**: `admin123`

**⚠️ IMPORTANTE**: Cambia la contraseña por defecto inmediatamente desde **Configuración → Seguridad**.

---

### 📦 Instalación Antigua (Solo para versiones < 1.0)

<details>
<summary>Ver instrucciones de migración desde versiones anteriores</summary>

Si vienes de una instalación anterior a v1.0, **NO uses `schema_v1.0.sql`**. En su lugar:

```bash
cd database
./apply_all_migrations.sh
```

Este script aplicará las migraciones incrementales una por una.

</details>

### 5. Aplicar Migraciones (Opcional - si ya existe BD)

Si ya tienes una base de datos existente y necesitas aplicar las nuevas funcionalidades:

```bash
# Aplicar todas las migraciones
mysql -u gestion_user -p asociacion_db < database/migration_add_dni.sql
mysql -u gestion_user -p asociacion_db < database/migration_add_geolocation.sql
mysql -u gestion_user -p asociacion_db < database/migration_add_donor_geolocation.sql
mysql -u gestion_user -p asociacion_db < database/migration_add_member_number.sql
mysql -u gestion_user -p asociacion_db < database/migration_member_profile_images.sql
mysql -u gestion_user -p asociacion_db < database/migration_organization_settings.sql
mysql -u gestion_user -p asociacion_db < database/migration_tasks.sql
```

O ejecutar todas de una vez (Linux):
```bash
cd database
chmod +x apply_all_migrations.sh
./apply_all_migrations.sh
```

### 6. Acceso Inicial

**Credenciales por defecto:**
- **Usuario**: `admin`
- **Contraseña**: `admin123`

⚠️ **IMPORTANTE**: Cambiar la contraseña inmediatamente después del primer acceso desde:
- Perfil de usuario → Cambiar contraseña
- O directamente en la base de datos con hash bcrypt

## 📂 Estructura del Proyecto

```
GestionSocios/
├── database/                      # Migraciones SQL
│   ├── migration_*.sql           # Archivos de migración
│   └── apply_all_migrations.sh   # Script para aplicar todas
├── public/                        # Archivos públicos (punto de entrada)
│   ├── index.php                 # Router principal
│   ├── css/                      # Estilos CSS
│   │   └── style.css            # Estilos principales
│   ├── js/                       # JavaScript
│   └── uploads/                  # Archivos subidos
│       ├── members/              # Fotos de socios
│       ├── donors/               # Logos de donantes
│       └── organization/         # Logo institucional
├── src/
│   ├── Config/                   # Configuración
│   │   └── config.php           # Archivo de configuración (generado)
│   ├── Controllers/              # Controladores MVC
│   │   ├── MemberController.php
│   │   ├── DonorController.php
│   │   ├── CertificateController.php
│   │   ├── TaskController.php
│   │   ├── SettingsController.php
│   │   ├── DashboardController.php
│   │   ├── AnalyticsController.php
│   │   ├── SearchController.php
│   │   └── ExportController.php
│   ├── Models/                   # Modelos de datos
│   │   ├── Member.php
│   │   ├── Donor.php
│   │   ├── Certificate.php
│   │   ├── Task.php
│   │   ├── Analytics.php
│   │   └── OrganizationSettings.php
│   ├── Views/                    # Vistas (plantillas PHP)
│   │   ├── layout.php           # Plantilla principal
│   │   ├── dashboard.php        # Dashboard
│   │   ├── members/             # Vistas de socios
│   │   │   ├── list.php
│   │   │   ├── create.php
│   │   │   ├── edit.php
│   │   │   └── map.php          # Mapa interactivo
│   │   ├── donors/              # Vistas de donantes
│   │   ├── tasks/               # Vistas de tareas
│   │   ├── analytics/           # Vistas de analíticas
│   │   └── settings/            # Vistas de configuración
│   └── Helpers/                  # Utilidades
│       └── AvatarHelper.php     # Generador de avatares
└── README.md                     # Este archivo
```

## 🗺️ Uso del Sistema de Geolocalización

### Captura de GPS desde Móvil (requiere HTTPS)

1. **En formulario de crear/editar socio o donante:**
   - Localizar el campo "Dirección"
   - Hacer clic en el botón **📍 GPS** (esquina inferior derecha del campo)
   - Autorizar acceso a ubicación cuando el navegador lo solicite
   - Las coordenadas se capturarán automáticamente

2. **Entrada Manual (funciona en HTTP):**
   - Si no tienes HTTPS o prefieres introducir coordenadas manualmente:
   - Buscar la ubicación en Google Maps
   - Hacer clic derecho en el punto exacto → "¿Qué hay aquí?"
   - Copiar las coordenadas (formato: 40.4168, -3.7038)
   - Pegar en los campos "Latitud" y "Longitud"

### Ver Mapa de Ubicaciones

1. **Acceder al mapa:**
   - Menú lateral → "Socios" → "Mapa"
   - O directamente: `index.php?page=members&action=map`

2. **Filtros disponibles:**
   - **Todos**: Muestra socios (azul) y donantes (verde)
   - **Solo Socios**: Muestra únicamente marcadores azules
   - **Solo Donantes**: Muestra únicamente marcadores verdes

3. **Interacción:**
   - Clic en marcador: ver información detallada
   - Botón "Editar": ir al formulario de edición
   - Botón "Maps": abrir ubicación en Google Maps
   - Botón "🎯": centrar mapa en esa ubicación
   - Zoom con scroll o botones +/-
   - Arrastrar para mover el mapa

## 📜 Generación de Certificados

### Certificado de Socio
1. Ir a "Socios" → Lista de socios
2. Clic en el icono **📄** junto al socio
3. Se genera PDF con formato: `certificado_socio_DNI_Nombre.pdf`

### Certificado de Pagos
1. Acceder al perfil del socio
2. Sección "Pagos" → Botón "Generar Certificado"
3. PDF incluye desglose de todas las cuotas pagadas

Los certificados se descargan automáticamente al navegador.

## 👥 Gestión de Socios

### Crear Nuevo Socio
1. Menú → "Socios" → "Nuevo Socio"
2. Completar formulario:
   - Nombre y apellidos (obligatorio)
   - DNI/NIE (opcional pero recomendado)
   - Email, teléfono
   - Dirección + GPS (opcional)
   - Foto (opcional, formatos: JPG, PNG, GIF, máx 5MB)
3. El número de socio se asigna automáticamente
4. Guardar

### Editar Socio
- Lista de socios → Clic en icono **✏️**
- Modificar campos necesarios
- Si se sube nueva foto, reemplaza la anterior
- Actualizar coordenadas GPS si ha cambiado de ubicación

### Búsqueda Avanzada
- Campo de búsqueda en lista de socios
- Busca por: nombre, apellidos, DNI, email, teléfono
- Filtros adicionales: estado, tipo de cuota

## 🎯 Gestión de Tareas

### Crear Tarea
1. Menú → "Tareas" → "Nueva Tarea"
2. Completar:
   - Título descriptivo
   - Descripción detallada
   - Asignar a usuario
   - Prioridad (baja, normal, alta, urgente)
   - Fecha de vencimiento
3. Estado inicial: "Pendiente"

### Estados de Tarea
- **Pendiente**: Recién creada, sin empezar
- **En Progreso**: Se está trabajando en ella
- **Completada**: Finalizada exitosamente
- **Cancelada**: Descartada o ya no aplica

### Ver Historial
- Cada tarea registra automáticamente:
  - Cambios de estado
  - Modificaciones de campos
  - Fecha y hora de cada cambio

## ⚙️ Configuración del Sistema

### Datos de la Organización
1. Menú → "Configuración"
2. Pestaña "Organización"
3. Configurar:
   - Nombre de la asociación
   - CIF/NIF
   - Dirección, teléfono, email
   - Subir logo (aparecerá en certificados y cabecera)

### Copias de Seguridad
1. Menú → "Configuración"
2. Pestaña "Copias de Seguridad"
3. Clic en "Generar Copia de Seguridad"
4. Descarga archivo SQL completo de la base de datos
5. Guardar en lugar seguro

### Restaurar Copia de Seguridad
```bash
mysql -u gestion_user -p asociacion_db < backup_20251122_120000.sql
```

## 🔒 Seguridad

### Recomendaciones
- ✅ Cambiar contraseña de `admin` inmediatamente
- ✅ Usar contraseñas fuertes (mínimo 12 caracteres)
- ✅ Configurar HTTPS para proteger datos (especialmente para GPS)
- ✅ Realizar copias de seguridad semanales
- ✅ Mantener PHP y MySQL actualizados
- ✅ Revisar logs de Apache regularmente
- ✅ Limitar acceso SSH solo a IPs conocidas

### Permisos de Archivos (Linux)
```bash
# Archivos: 644 (lectura general, escritura propietario)
find /var/www/GestionSocios -type f -exec chmod 644 {} \;

# Directorios: 755 (lectura/ejecución general, escritura propietario)
find /var/www/GestionSocios -type d -exec chmod 755 {} \;

# Uploads y Config: 775 (escritura para grupo www-data)
chmod -R 775 /var/www/GestionSocios/public/uploads
chmod -R 775 /var/www/GestionSocios/src/Config
```

## 🐛 Solución de Problemas

### Error "No se puede conectar a la base de datos"
- Verificar credenciales en `src/Config/config.php`
- Comprobar que MySQL está corriendo: `systemctl status mysql`
- Verificar permisos del usuario en MySQL

### Las fotos no se suben
- Verificar permisos de escritura en `public/uploads/`
- Comprobar tamaño máximo en `php.ini`:
  ```ini
  upload_max_filesize = 10M
  post_max_size = 10M
  ```
- Reiniciar Apache tras cambios

### GPS no funciona
- **Requiere HTTPS**: La API de Geolocalización HTML5 está bloqueada en HTTP
- Solución temporal: usar entrada manual de coordenadas
- Solución permanente: configurar certificado SSL (Let's Encrypt)

### Error 500 en Apache
- Revisar logs: `tail -f /var/log/apache2/error.log`
- Verificar que mod_rewrite está habilitado
- Comprobar sintaxis de `.htaccess`

### El mapa no carga
- Verificar conexión a Internet (usa OpenStreetMap tiles)
- Comprobar consola del navegador (F12) para errores JavaScript
- Verificar que Leaflet.js se carga correctamente

## 🚀 Actualización del Sistema

### Desde Git
```bash
cd /var/www/GestionSocios

# Hacer backup antes de actualizar
mysqldump -u gestion_user -p asociacion_db > backup_pre_update.sql

# Actualizar código
git pull origin main

# Aplicar nuevas migraciones si existen
cd database
./apply_all_migrations.sh

# Verificar permisos
sudo chown -R www-data:www-data /var/www/GestionSocios
```

## 📞 Soporte y Contribución

### Reportar Problemas
- Abrir issue en GitHub: https://github.com/matatunos/GestionSocios/issues
- Incluir:
  - Descripción del problema
  - Pasos para reproducirlo
  - Versión de PHP y MySQL
  - Logs relevantes

### Contribuir
1. Fork del repositorio
2. Crear rama para feature: `git checkout -b feature/nueva-funcionalidad`
3. Commit de cambios: `git commit -m "feat: descripción"`
4. Push a la rama: `git push origin feature/nueva-funcionalidad`
5. Abrir Pull Request

## 📄 Licencia

Este proyecto está bajo licencia MIT. Ver archivo `LICENSE` para más detalles.

## 🙏 Créditos

- **Desarrollador**: Nacho (matatunos)
- **Mapas**: Leaflet.js + OpenStreetMap
- **Iconos**: Font Awesome
- **PDF**: TCPDF

---

**Versión**: 1.0.0  
**Última actualización**: Noviembre 2025
