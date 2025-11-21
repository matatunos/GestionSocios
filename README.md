# Gestión de Socios - Asociación

Sistema web integral para la gestión de socios, pagos, eventos, donaciones y libro de fiestas de una asociación. Desarrollado en PHP nativo con MySQL.

## 🚀 Características

*   **Gestión de Socios**: Altas, bajas, edición y listado de socios con foto.
*   **Control de Pagos**: Generación de cuotas anuales, registro de pagos y seguimiento de morosos.
*   **Eventos**: Creación de eventos, gestión de participantes y control de pagos específicos por evento.
*   **Donaciones y Donantes**: Registro de donantes (empresas/particulares) y sus donaciones.
*   **Libro de Fiestas**: Gestión de anuncios publicitarios (media página, completa, portada, etc.) con precios configurables por año.
*   **Informes**: Reportes ejecutivos con gráficas de ingresos, altas y estado de la asociación.
*   **Configuración Centralizada**: Panel para gestionar datos de la asociación, precios de anuncios, cuotas y copias de seguridad.
*   **Interfaz Moderna**: Diseño responsive, modo oscuro/claro y fácil de usar.

## 📋 Requisitos del Sistema

*   **Servidor Web**: Apache (con mod_rewrite habilitado).
*   **PHP**: Versión 7.4 o superior.
*   **Base de Datos**: MySQL 5.7+ o MariaDB 10.2+.
*   **Extensiones PHP**: `pdo`, `pdo_mysql`.

## 🛠️ Instalación

1.  **Clonar el Repositorio**
    ```bash
    git clone https://github.com/matatunos/GestionSocios.git
    ```

2.  **Configurar el Servidor Web**
    *   Apunta el `DocumentRoot` de tu servidor a la carpeta `public/` del proyecto.
    *   Asegúrate de que el usuario del servidor web tenga permisos de escritura en la carpeta `src/Config/` (para crear el archivo `config.php`).

3.  **Instalación Automática**
    *   Abre tu navegador y accede a la URL de la aplicación (ej. `http://localhost/GestionSocios/public`).
    *   El sistema detectará que no está instalado y te redirigirá al asistente de instalación.
    *   Introduce los datos de conexión a tu base de datos (Host, Usuario, Contraseña, Nombre de la BD).
    *   El instalador creará la base de datos, las tablas y el usuario administrador por defecto.

4.  **Acceso Inicial**
    *   **Usuario**: `admin`
    *   **Contraseña**: `admin123`
    *   ⚠️ **Importante**: Cambia la contraseña inmediatamente desde el perfil de usuario o la base de datos.

## 📂 Estructura del Proyecto

```
GestionSocios/
├── public/             # Archivos públicos (index.php, css, js, uploads)
│   ├── css/            # Estilos CSS
│   ├── js/             # Scripts JavaScript
│   ├── uploads/        # Imágenes subidas (fotos socios, logos)
│   └── index.php       # Punto de entrada único (Router)
├── src/                # Código fuente
│   ├── Config/         # Configuración (Database.php, config.php)
│   ├── Controllers/    # Controladores (Lógica de negocio)
│   ├── Models/         # Modelos (Acceso a datos)
│   └── Views/          # Vistas (Plantillas HTML/PHP)
├── database/           # Scripts SQL (schema.sql, migraciones)
└── README.md           # Documentación
```

## 🔧 Solución de Problemas

### Error de Conexión a la Base de Datos
Si ves una pantalla roja de "Error de Conexión":
1.  Verifica que el servidor MySQL esté corriendo.
2.  Haz clic en "Reconfigurar" para volver a introducir las credenciales.
3.  Si el problema persiste, revisa manualmente el archivo `src/Config/config.php`.

### Error 500 o Pantalla en Blanco
*   Revisa los logs de error de Apache/PHP.
*   Asegúrate de que la carpeta `src/` tiene permisos de lectura.

### Imágenes no cargan
*   Verifica que la carpeta `public/uploads` tenga permisos de escritura (`chmod 777` o `755` según tu configuración).

## 📄 Licencia

Este proyecto es de uso privado para la gestión de la asociación.
