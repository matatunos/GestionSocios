# GestionSocios

Sistema de gestión integral para asociaciones desarrollado en PHP con arquitectura MVC. Permite administrar socios, pagos, eventos y cuotas anuales de forma eficiente y con una interfaz moderna.

![Dashboard](https://img.shields.io/badge/PHP-8.5-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-Unlicense-blue.svg)

## 📋 Características

- **Gestión de Socios**: Registro completo de miembros con datos de contacto, fotos y estados
- **Control de Pagos**: Seguimiento de cuotas, eventos y donaciones con estados (pagado/pendiente)
- **Gestión de Eventos**: Organización de eventos con precios y fechas
- **Cuotas Anuales**: Configuración y generación automática de cuotas por año
- **Sistema de Autenticación**: Control de acceso con roles (admin/readonly)
- **Dashboard Estadístico**: Visualización de métricas clave en tiempo real
- **Diseño Moderno**: Interfaz con glassmorphism, gradientes y animaciones suaves

## 🚀 Requisitos

### Software Necesario

- **PHP**: >= 8.0
  - Extensiones requeridas: PDO, pdo_mysql
- **MySQL**: >= 5.7 o MariaDB >= 10.2
- **Servidor Web**: Apache 2.4+ o PHP Built-in Server
- **Navegador**: Chrome, Firefox, Safari o Edge (versiones recientes)

### Requisitos Opcionales

- Git (para clonar el repositorio)
- Composer (si se añaden dependencias en el futuro)

## 📦 Instalación

### 1. Clonar el Repositorio

```bash
git clone https://github.com/matatunos/GestionSocios.git
cd GestionSocios
```

### 2. Configurar la Base de Datos

#### Opción A: Instalador Web (Recomendado)

1. Inicia el servidor web:
   ```bash
   php -S localhost:8085 -t public
   ```

2. Abre tu navegador en `http://localhost:8085`

3. El instalador se ejecutará automáticamente y te pedirá:
   - Host de la base de datos (ej: `localhost`)
   - Nombre de la base de datos (ej: `gestion_socios`)
   - Usuario de MySQL
   - Contraseña de MySQL

4. El instalador creará:
   - El archivo de configuración `src/Config/config.php`
   - Todas las tablas necesarias
   - Usuario administrador por defecto

#### Opción B: Instalación Manual

1. Crea la base de datos:
   ```sql
   CREATE DATABASE gestion_socios CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Importa el esquema:
   ```bash
   mysql -u tu_usuario -p gestion_socios < database/schema.sql
   ```

3. Crea el archivo de configuración `src/Config/config.php`:
   ```php
   <?php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'gestion_socios');
   define('DB_USER', 'tu_usuario');
   define('DB_PASS', 'tu_contraseña');
   ```

### 3. Iniciar el Servidor

#### Opción A: PHP Built-in Server (Desarrollo)

```bash
php -S localhost:8085 -t public
```

Accede a: `http://localhost:8085`

#### Opción B: Apache

1. Configura un VirtualHost apuntando a la carpeta `public/`
2. Asegúrate de que `mod_rewrite` esté habilitado
3. Reinicia Apache

### 4. Acceder al Sistema

**Credenciales por defecto:**
- Usuario: `admin`
- Contraseña: `admin123`

> ⚠️ **Importante**: Cambia la contraseña del administrador después del primer acceso.

## 🏗️ Estructura del Proyecto

```
GestionSocios/
├── database/
│   └── schema.sql              # Esquema de base de datos
├── public/
│   ├── css/
│   │   └── style.css           # Estilos principales
│   ├── uploads/                # Archivos subidos (fotos, etc.)
│   └── index.php               # Punto de entrada
├── src/
│   ├── Config/
│   │   ├── Database.php        # Conexión PDO
│   │   └── config.php          # Configuración (generado)
│   ├── Controllers/            # Controladores MVC
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── MemberController.php
│   │   ├── PaymentController.php
│   │   ├── EventController.php
│   │   ├── FeeController.php
│   │   └── SettingsController.php
│   ├── Models/                 # Modelos de datos
│   │   ├── User.php
│   │   ├── Member.php
│   │   ├── Payment.php
│   │   ├── Event.php
│   │   └── Fee.php
│   └── Views/                  # Vistas PHP
│       ├── layout.php          # Layout principal
│       ├── dashboard.php
│       ├── members/
│       ├── payments/
│       ├── events/
│       └── fees/
├── tests/
│   └── verify.php              # Script de verificación
├── LICENSE
└── README.md
```

## 💻 Uso

### Gestión de Socios

1. **Listar Socios**: Navega a "Socios" en el menú lateral
2. **Agregar Socio**: Click en "Nuevo Socio"
   - Completa los datos: nombre, apellidos, email, teléfono, dirección
   - Opcionalmente sube una foto de perfil
   - Selecciona el estado (activo/inactivo)
3. **Editar Socio**: Click en "Editar" en la lista de socios
4. **Ver Detalles**: Click en el nombre del socio

### Gestión de Pagos

1. **Registrar Pago**: Click en "Nuevo Pago"
   - Selecciona el socio
   - Ingresa el monto y fecha
   - Especifica el concepto
   - Selecciona el tipo: cuota, evento o donación
   - Define el estado: pagado o pendiente
2. **Editar/Eliminar**: Usa los botones en la lista de pagos

### Gestión de Eventos

1. **Crear Evento**: Click en "Nuevo Evento"
   - Nombre y descripción del evento
   - Fecha y precio
   - Estado activo/inactivo
2. **Gestionar Eventos**: Edita o desactiva eventos desde la lista

### Cuotas Anuales

1. **Configurar Cuota**: Ingresa el año y el monto
2. **Generar Pagos**: Click en "Generar Pagos" para crear automáticamente pagos pendientes para todos los socios activos

### Configuración

- **General**: Cambia el nombre de la asociación
- **Base de Datos**: Actualiza credenciales de conexión

## 🔒 Seguridad

- Contraseñas hasheadas con bcrypt
- Protección contra SQL Injection mediante PDO prepared statements
- Validación de sesiones en cada request
- Control de acceso basado en roles
- Sanitización de inputs del usuario

## 🧪 Verificación

Ejecuta el script de verificación para comprobar que todo funciona correctamente:

```bash
php tests/verify.php
```

Deberías ver:
```
[PASS] Database Connection
[PASS] Admin User Exists
[PASS] Member Creation
[PASS] Payment Creation
Verification Complete.
```

## 🎨 Personalización

### Cambiar Colores

Edita las variables CSS en `public/css/style.css`:

```css
:root {
    --primary-500: #6366f1;    /* Color principal */
    --secondary-500: #10b981;  /* Color secundario */
    --danger-500: #ef4444;     /* Color de alerta */
}
```

### Modificar el Logo

Reemplaza el icono en `src/Views/layout.php`:

```php
<i class="fas fa-users-rectangle"></i>  <!-- Cambia esta clase -->
```

## 🛠️ Desarrollo

### Añadir un Nuevo Módulo

1. Crea el controlador en `src/Controllers/`
2. Crea el modelo en `src/Models/`
3. Crea las vistas en `src/Views/`
4. Añade la ruta en `public/index.php`
5. Añade el enlace en el menú lateral (`src/Views/layout.php`)

### Estructura de Base de Datos

Las tablas principales son:

- `users`: Usuarios del sistema
- `members`: Socios de la asociación
- `payments`: Pagos y transacciones
- `events`: Eventos organizados
- `annual_fees`: Cuotas anuales configuradas
- `settings`: Configuración general

## 📝 Solución de Problemas

### El menú lateral no muestra los enlaces

**Solución**: Reinicia el servidor PHP para limpiar el caché:

```bash
# Detener el servidor (Ctrl+C)
# Reiniciar
php -S localhost:8085 -t public
```

### Error de conexión a la base de datos

**Solución**: Verifica las credenciales en `src/Config/config.php`

### Las imágenes no se suben

**Solución**: Verifica los permisos de la carpeta `public/uploads/`:

```bash
chmod -R 755 public/uploads/
```

### Error "Session already started"

**Solución**: Asegúrate de que `session_start()` solo se llame una vez en `public/index.php`

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Unlicense (dominio público). Ver el archivo `LICENSE` para más detalles.

Esto significa que puedes usar, copiar, modificar, distribuir y vender este software libremente sin ninguna restricción.

## 👥 Autor

**matatunos**
- GitHub: [@matatunos](https://github.com/matatunos)

## 🙏 Agradecimientos

- [Font Awesome](https://fontawesome.com/) - Iconos
- [Google Fonts](https://fonts.google.com/) - Tipografía Inter
- Comunidad PHP por las mejores prácticas y patrones

## 📞 Soporte

Si encuentras algún problema o tienes sugerencias:

1. Abre un [Issue](https://github.com/matatunos/GestionSocios/issues)
2. Describe el problema detalladamente
3. Incluye capturas de pantalla si es posible

---

**Desarrollado con ❤️ para facilitar la gestión de asociaciones**
