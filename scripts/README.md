# Generación de Datos de Ejemplo

Este directorio contiene scripts para generar y cargar datos de ejemplo en la base de datos del sistema de gestión de socios.

## 📁 Archivos Disponibles

### Scripts de Generación
- **`generate_sample_data.php`** - Genera el archivo `sample_data_large.sql` con datos masivos

### Scripts de Carga
- **`load_sample_data_large.bat`** - Carga los datos masivos en la base de datos
- **`test_database_load.bat`** - Crea una base de datos de prueba y carga datos de ejemplo
- **`test_database_load.php`** - Versión PHP del script de verificación

## 🚀 Uso Rápido

### 1. Generar Datos de Ejemplo Large

Para generar un nuevo archivo `sample_data_large.sql`:

```bash
cd scripts
php generate_sample_data.php > ..\database\sample_data_large.sql
```

**Configuración por defecto:**
- 500 socios
- 50 donantes
- 30 eventos
- 1500 pagos
- 800 asistencias a eventos
- 200 gastos
- 50 tareas
- 100 donaciones
- 80 anuncios del libro

### 2. Cargar Datos en la Base de Datos

Para cargar los datos en tu base de datos:

```bash
cd scripts
.\load_sample_data_large.bat
```

El script te pedirá confirmación antes de cargar los datos.

### 3. Verificar la Carga

Para crear una base de datos de prueba y verificar que todo funciona:

```bash
cd scripts
.\test_database_load.bat
```

Esto creará una base de datos llamada `gestion_socios_test` con los datos de ejemplo básicos.

## ⚙️ Personalización

### Modificar la Cantidad de Datos

Edita el archivo `generate_sample_data.php` y modifica las constantes al inicio:

```php
$NUM_MEMBERS = 500;      // Número de socios
$NUM_DONORS = 50;        // Número de donantes
$NUM_EVENTS = 30;        // Número de eventos
$NUM_PAYMENTS = 1500;    // Número de pagos
$NUM_ATTENDANCE = 800;   // Número de asistencias
$NUM_EXPENSES = 200;     // Número de gastos
$NUM_TASKS = 50;         // Número de tareas
$NUM_DONATIONS = 100;    // Número de donaciones
$NUM_BOOK_ADS = 80;      // Número de anuncios
```

Luego regenera el archivo:

```bash
php generate_sample_data.php > ..\database\sample_data_large.sql
```

## 📊 Tipos de Datos Generados

### Configuración
- Configuración de la organización (20 settings)
- Cuotas anuales (2020-2026)
- Precios de anuncios por año y tipo

### Categorías
- 7 categorías de socios
- 7 categorías de gastos
- 5 categorías de tareas

### Datos Principales
- **Socios**: Nombres, DNI, emails, teléfonos, direcciones aleatorias
- **Donantes**: Empresas con nombres realistas
- **Eventos**: Títulos variados con fechas y ubicaciones
- **Pagos**: Cuotas anuales y pagos de eventos
- **Asistencias**: Registros de asistencia a eventos con diferentes estados
- **Gastos**: Gastos categorizados con facturas
- **Tareas**: Tareas administrativas con prioridades
- **Donaciones**: Donaciones monetarias y en especie
- **Anuncios**: Anuncios del libro de fiestas

## 🔧 Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Acceso a la base de datos con permisos de escritura

## ⚠️ Advertencias

1. **Datos de Prueba**: Los datos generados son ficticios y solo para pruebas
2. **Limpieza de Tablas**: Los scripts limpian las tablas existentes antes de insertar datos
3. **Backup**: Haz siempre un backup antes de cargar datos en producción
4. **Performance**: La carga de datos masivos puede tardar varios minutos

## 📝 Notas

- Los DNIs generados son válidos según el algoritmo español
- Los emails son ficticios pero con formato válido
- Las fechas se generan aleatoriamente en rangos específicos
- Los datos mantienen integridad referencial

## 🐛 Solución de Problemas

### Error: "Table doesn't exist"
Asegúrate de haber ejecutado primero el archivo `schema.sql`:
```bash
mysql -u root -proot gestion_socios < database\schema.sql
```

### Error: "Foreign key constraint fails"
Verifica que `SET FOREIGN_KEY_CHECKS = 0;` esté al inicio del archivo SQL.

### El archivo generado está vacío
Verifica que PHP esté correctamente instalado:
```bash
php --version
```

## 📚 Más Información

Para más detalles sobre la estructura de la base de datos, consulta:
- `database/schema.sql` - Esquema completo de la base de datos
- `database/sample_data.sql` - Datos de ejemplo básicos (más pequeños)