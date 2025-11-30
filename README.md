# ESTA APLICACION ESTA DESAROLLADA POR UN TECNICO INFORMATICO CON POCOS CONOCIMIENTOS DE PROGRAMACION, DESARROLLADA INTEGRAMENTE USANDO IA SIN EDITAR A MANO NI UNA SOLA LINEA DE CODIGO
# HA EMPEZADO SIENDO UN EXPERIMENTO, Y HA CONTINUADO INTENTANDO HACER UNA APLICACION FUNCIONAL
# PODRIA VALER PARA SU PROPOSITO, ESTA RELATIVAMENTE TESTEADA PERO, COMO SE PODRA OBSERVAR, NO ESTA LIMPIA DE FICHEROS INUTILES QUE SE HAN IDO GENERANDO

# AL QUE LE PUEDA VALER, LIBRE ES DE HACER CON ELLA LO QUE DESEE, Y YO ESTARIA ENCANTADO DE RECIBIR FEEDBACK, AUNQUE SOLO SEA POR CURIOSIDAD DE QUE A ALGUIEN LE HA PODIDO SER UTIL
## NACHO@FAVALA.ES

# Gestión de Socios - Asociación

Sistema web integral para la gestión completa de asociaciones y entidades sin ánimo de lucro. Desarrollado en PHP nativo con MySQL, incluye gestión de socios, donantes, eventos, finanzas, proveedores, libro de fiestas y mucho más.

## 🚀 Características Principales

### 👥 Gestión de Socios
- **Categorías de socios** con cuotas personalizadas por categoría
- Alta, baja, edición y listado con foto de perfil
- Campo DNI/NIE para identificación oficial
- **Geolocalización GPS** con captura desde móvil o entrada manual
- Generación automática de número de socio
- **Historial de imágenes** con comparación y restauración
- Búsqueda avanzada y filtrado por categoría, estado, etc.
- Exportación a Excel y PDF
- **Generación masiva de pagos** por categoría y año

### 💰 Gestión Financiera
- **Dashboard de Tesorería** con KPIs en tiempo real
- **Gestión de Pagos** con estados (pendiente, pagado, vencido)
- **Gestión de Gastos** con categorías personalizables
- **Proveedores** con seguimiento de facturas y pagos
- Gráficos de evolución de ingresos y gastos
- Reportes financieros exportables
- Control de morosos y pagos vencidos

### 🎁 Gestión de Donantes
- Registro de empresas y particulares donantes
- **Galería de imágenes** con historial y comparación
- Geolocalización de donantes con GPS
- Seguimiento de contactos y donaciones
- Gestión de logos y datos de contacto
- Exportación de datos

### 📅 Gestión de Eventos
- Creación y edición de eventos
- **Dashboard de eventos** con estadísticas
- Control de asistencia y participantes
- Gestión de pagos por evento
- Calendario interactivo
- Reportes de eventos

### 📖 Libro de Fiestas
- **Gestión de anunciantes** con diferentes tipos de anuncios
- **Precios por año** configurables (media página, página completa, portada, contraportada)
- **Actividades** del programa de fiestas
- **Dashboard del libro** con estadísticas de ventas
- **Maquetación y exportación** a PDF y DOCX
- **Sistema de versiones** para guardar diferentes ediciones
- **Editor visual** con arrastrar y soltar páginas
- Generación automática de contenido

### 🗺️ Geolocalización y Mapas
- **Mapa interactivo** con Leaflet.js y OpenStreetMap
- Captura de coordenadas GPS desde dispositivos móviles
- Entrada manual de coordenadas (compatible con HTTP)
- Visualización combinada de socios y donantes
- **Etiquetas con nombres** al hacer zoom
- Filtros: mostrar todos, solo socios, solo donantes
- Marcadores diferenciados por color
- Enlaces directos a Google Maps
- Diseño responsive para móvil y desktop

### 📋 Gestión de Tareas
- Crear, editar y asignar tareas
- Estados: pendiente, en progreso, completada, cancelada
- Prioridades: baja, normal, alta, urgente
- Fechas de vencimiento y seguimiento
- Historial de cambios
- Comentarios y notas

### 📊 Analíticas y Reportes
- **Dashboard principal** con estadísticas en tiempo real
- **Dashboard de tesorería** con KPIs financieros
- **Dashboard de eventos** con métricas de participación
- **Dashboard de proveedores** con estado de facturas
- Gráficos de evolución de socios, ingresos y gastos
- Reportes de pagos y morosos
- Exportación de datos a Excel y PDF

### 📢 Anuncios Públicos
- Creación de anuncios para la web pública
- Gestión de visibilidad (activo/inactivo)
- Fechas de publicación y caducidad
- Editor de contenido enriquecido

### 🖼️ Galería de Imágenes
- Galería unificada de fotos de socios y logos de donantes
- Navegación por pestañas
- Vista en cuadrícula responsive
- Visualización ampliada de imágenes

### 📄 Documentos y Certificados
- **Gestión de documentos** con categorías
- Generación de certificados de socio en PDF
- Certificados de pago con desglose de cuotas
- Nombres de archivo descriptivos (DNI-Nombre)
- Almacenamiento organizado

### 🔔 Sistema de Notificaciones
- Notificaciones en tiempo real
- Contador de notificaciones no leídas
- Marcado como leído individual o masivo
- Tipos: info, éxito, advertencia, error

### 💬 Mensajería Interna
- Sistema de mensajes entre usuarios
- Conversaciones directas
- Contador de mensajes no leídos
- Historial de conversaciones

### 🗳️ Votaciones
- Creación de encuestas y votaciones
- Opciones múltiples
- Resultados en tiempo real
- Control de cierre de votaciones

### 🔍 Búsqueda Global
- Búsqueda unificada en toda la aplicación
- Resultados categorizados
- Búsqueda rápida desde cualquier página

### 🔐 Seguridad y Auditoría
- **Registro de auditoría** completo de todas las acciones
- Exportación de logs a Excel y PDF
- Control de acceso por roles (admin, usuario)
- **Política de contraseñas** configurable
- Protección CSRF en formularios
- Sesiones seguras

### ⚙️ Configuración Avanzada
- **Gestión de la organización** (nombre, CIF, dirección, contacto)
- **Logo y colores corporativos** personalizables
- **Junta directiva** configurable
- **Administración de usuarios** del sistema
- **Configuración de notificaciones**
- **Copias de seguridad** de base de datos
- **Configuración de base de datos**
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
php-xml (para exportación DOCX)
```

### Permisos del Sistema
- El usuario del servidor web (típicamente `www-data` o `apache`) necesita permisos de escritura en:
  - `src/Config/` (configuración)
  - `public/uploads/` (archivos subidos)
  - `public/uploads/members/` (fotos de socios)
  - `public/uploads/donors/` (logos de donantes)
  - `public/uploads/organization/` (logo institucional)
  - `public/uploads/receipts/` (comprobantes de gastos)
  - `public/uploads/documents/` (documentos)

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

##### En Linux:
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

##### En Windows (XAMPP/WAMP):
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

#### 4. Acceder al Sistema

1. Abrir navegador: `http://gestion-socios.local`
2. **Usuario**: `admin`
3. **Contraseña**: `admin123`

**⚠️ IMPORTANTE**: Cambia la contraseña por defecto inmediatamente desde **Configuración → Administración de Usuarios**.

## 📂 Estructura del Proyecto

```
GestionSocios/
├── database/                      # Migraciones SQL
│   ├── schema_v1.0.sql           # Schema completo v1.0
│   ├── migration_*.sql           # Archivos de migración
│   ├── sample_data_large.sql     # Datos de ejemplo
│   └── install_v1.0.sh           # Script de instalación
├── public/                        # Archivos públicos (punto de entrada)
│   ├── index.php                 # Router principal
│   ├── css/                      # Estilos CSS
│   │   ├── style.css            # Estilos principales
│   │   ├── mobile.css           # Estilos móvil
│   │   └── nav-submenu.css      # Navegación
│   ├── js/                       # JavaScript
│   └── uploads/                  # Archivos subidos
│       ├── members/              # Fotos de socios
│       ├── donors/               # Logos de donantes
│       ├── organization/         # Logo institucional
│       ├── receipts/             # Comprobantes
│       └── documents/            # Documentos
├── src/
│   ├── Config/                   # Configuración
│   │   ├── config.php           # Configuración DB
│   │   └── database.php         # Clase Database
│   ├── Controllers/              # Controladores MVC
│   │   ├── MemberController.php
│   │   ├── MemberCategoryController.php
│   │   ├── DonorController.php
│   │   ├── PaymentController.php
│   │   ├── ExpenseController.php
│   │   ├── SupplierController.php
│   │   ├── EventController.php
│   │   ├── BookAdController.php
│   │   ├── BookActivityController.php
│   │   ├── BookExportController.php
│   │   ├── BookDashboardController.php
│   │   ├── CertificateController.php
│   │   ├── TaskController.php
│   │   ├── SettingsController.php
│   │   ├── DashboardController.php
│   │   ├── TreasuryController.php
│   │   ├── AnalyticsController.php
│   │   ├── AnnouncementController.php
│   │   ├── GalleryController.php
│   │   ├── NotificationsController.php
│   │   ├── MessageController.php
│   │   ├── PollController.php
│   │   ├── DocumentController.php
│   │   ├── AuditLogController.php
│   │   ├── SearchController.php
│   │   └── ExportController.php
│   ├── Models/                   # Modelos de datos
│   │   ├── Member.php
│   │   ├── MemberCategory.php
│   │   ├── Donor.php
│   │   ├── Payment.php
│   │   ├── Expense.php
│   │   ├── ExpenseCategory.php
│   │   ├── Supplier.php
│   │   ├── SupplierInvoice.php
│   │   ├── Event.php
│   │   ├── BookAd.php
│   │   ├── BookActivity.php
│   │   ├── BookPage.php
│   │   ├── AdPrice.php
│   │   ├── Task.php
│   │   ├── Analytics.php
│   │   ├── PublicAnnouncement.php
│   │   ├── Notification.php
│   │   ├── Message.php
│   │   ├── Poll.php
│   │   ├── Document.php
│   │   ├── AuditLog.php
│   │   └── OrganizationSettings.php
│   ├── Views/                    # Vistas (plantillas PHP)
│   │   ├── layout.php           # Plantilla principal
│   │   ├── dashboard.php        # Dashboard principal
│   │   ├── members/             # Vistas de socios
│   │   ├── donors/              # Vistas de donantes
│   │   ├── payments/            # Vistas de pagos
│   │   ├── expenses/            # Vistas de gastos
│   │   ├── suppliers/           # Vistas de proveedores
│   │   ├── events/              # Vistas de eventos
│   │   ├── book/                # Vistas del libro de fiestas
│   │   ├── tasks/               # Vistas de tareas
│   │   ├── analytics/           # Vistas de analíticas
│   │   ├── announcements/       # Vistas de anuncios
│   │   ├── gallery/             # Galería de imágenes
│   │   ├── notifications/       # Notificaciones
│   │   ├── messages/            # Mensajería
│   │   ├── polls/               # Votaciones
│   │   ├── documents/           # Documentos
│   │   ├── audit_log/           # Auditoría
│   │   └── settings/            # Configuración
│   └── Helpers/                  # Utilidades
│       ├── AvatarHelper.php     # Generador de avatares
│       ├── CsrfHelper.php       # Protección CSRF
│       └── Lang.php             # Internacionalización
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
   - Menú lateral → "Mapa"
   - O directamente: `index.php?page=map`

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
   - **Etiquetas con nombres** aparecen al hacer zoom nivel 14+

## 📖 Gestión del Libro de Fiestas

### Configurar Precios
1. Menú → "Configuración" → Pestaña "Precios Anuncios"
2. Definir precios para cada tipo de anuncio:
   - Media página
   - Página completa
   - Portada
   - Contraportada
3. Los precios se guardan por año

### Gestionar Anunciantes
1. Menú → "Libro Fiestas" → "Anunciantes"
2. Crear nuevo anuncio con:
   - Anunciante (nombre de la empresa/persona)
   - Tipo de anuncio
   - Año
   - Estado de pago
3. El precio se asigna automáticamente según el tipo

### Gestionar Actividades
1. Menú → "Libro Fiestas" → "Actividades"
2. Crear actividades del programa:
   - Título
   - Descripción
   - Fecha y hora
   - Ubicación

### Maquetación y Exportación
1. Menú → "Libro Fiestas" → "Maquetación"
2. **Editor visual** con arrastrar y soltar
3. **Sistema de versiones** para guardar diferentes ediciones
4. Exportar a PDF o DOCX
5. Vista previa antes de exportar

## 💰 Gestión Financiera

### Dashboard de Tesorería
- Acceso: Menú → "Dashboard" → "Tesorería"
- KPIs: Ingresos totales, gastos totales, balance
- Gráficos de evolución mensual
- Desglose por tipo de ingreso/gasto

### Generar Pagos de Cuotas
1. Menú → "Configuración" → Pestaña "Socios"
2. Sección "Generar Pagos de Cuotas"
3. Seleccionar año
4. Clic en "Generar Pagos"
5. Se crearán pagos pendientes para todos los socios activos según la cuota de su categoría

### Gestión de Gastos
1. Menú → "Gastos"
2. Crear nuevo gasto:
   - Categoría
   - Descripción
   - Importe
   - Proveedor
   - Número de factura
   - Comprobante (opcional)
3. Filtrar por año, mes, categoría
4. Exportar a Excel

### Gestión de Proveedores
1. Menú → "Proveedores"
2. Crear proveedor con datos de contacto
3. Subir facturas asociadas
4. Dashboard con estado de facturas y pagos

## 📊 Analíticas y Reportes

### Dashboard Principal
- Estadísticas de socios (total, activos, inactivos)
- Últimos pagos y próximos vencimientos
- Eventos próximos
- Tareas pendientes
- Gráficos de evolución

### Dashboard de Eventos
- Menú → "Dashboard" → "Eventos"
- Estadísticas de asistencia
- Ingresos por eventos
- Eventos más populares

### Exportaciones
- **Excel**: Socios, donantes, pagos, gastos, eventos
- **PDF**: Certificados, reportes
- Todos los listados tienen botón de exportación

## 🔐 Seguridad

### Recomendaciones
- ✅ Cambiar contraseña de `admin` inmediatamente
- ✅ Usar contraseñas fuertes (mínimo 12 caracteres)
- ✅ Configurar HTTPS para proteger datos (especialmente para GPS)
- ✅ Realizar copias de seguridad semanales
- ✅ Mantener PHP y MySQL actualizados
- ✅ Revisar logs de auditoría regularmente
- ✅ Configurar política de contraseñas desde Configuración
- ✅ Limitar acceso SSH solo a IPs conocidas

### Auditoría
- Menú → "Auditoría"
- Registro completo de todas las acciones
- Filtros por usuario, acción, fecha
- Exportación a Excel y PDF

### Política de Contraseñas
- Menú → "Configuración" → "Política de Contraseñas"
- Configurar longitud mínima
- Requerir mayúsculas, minúsculas, números, símbolos
- Expiración de contraseñas

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

- **Desarrollador**: Nacho (matatunos) - nacho@favala.es
- **Mapas**: Leaflet.js + OpenStreetMap
- **Iconos**: Font Awesome
- **PDF**: TCPDF
- **Gráficos**: Chart.js

---

**Versión**: 0.5 beta  
**Última actualización**: Noviembre 2025  
**Estado**: En desarrollo activo - No se recomienda para producción sin pruebas exhaustivas

## 🎯 Roadmap

### Próximas Funcionalidades
- [ ] API REST para integración con otras aplicaciones
- [ ] App móvil nativa (Android/iOS)
- [ ] Sistema de reservas online
- [ ] Pasarela de pago integrada
- [ ] Firma digital de documentos
- [ ] Integración con redes sociales
- [ ] Sistema de newsletters
- [ ] Módulo de contabilidad avanzada
