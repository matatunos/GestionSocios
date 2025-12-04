# Módulo de Gestión de Proveedores - Documentación Técnica

## Índice
1. [Descripción General](#descripción-general)
2. [Arquitectura](#arquitectura)
3. [Modelo de Datos](#modelo-de-datos)
4. [Integración con Contabilidad](#integración-con-contabilidad)
5. [Integración con Gestor Documental](#integración-con-gestor-documental)
6. [Flujo de Trabajo](#flujo-de-trabajo)
7. [API y Endpoints](#api-y-endpoints)
8. [Migración](#migración)

---

## Descripción General

El módulo de **Gestión de Proveedores** profesionaliza completamente el manejo de proveedores, facturas, órdenes de compra y documentación asociada, con integración total a contabilidad y al sistema de gestión documental.

### Características Principales

- ✅ **Gestión completa de proveedores** con información fiscal, bancaria y comercial
- ✅ **Múltiples contactos** por proveedor
- ✅ **Órdenes de compra** con aprobación y seguimiento
- ✅ **Facturas profesionales** con desglose de impuestos y descuentos
- ✅ **Gestión documental** integrada (contratos, certificados, seguros)
- ✅ **Integración contable** automática en cada operación
- ✅ **Alertas** de vencimiento de facturas y documentos
- ✅ **Reportes avanzados** y análisis de gastos

---

## Arquitectura

### Estructura de Directorios

```
src/
├── Models/
│   ├── Supplier.php                 # Modelo principal de proveedores
│   ├── SupplierContact.php          # Contactos de proveedores
│   ├── SupplierDocument.php         # Documentos asociados
│   ├── SupplierOrder.php            # Órdenes de compra
│   ├── SupplierOrderLine.php        # Líneas de órdenes
│   └── SupplierInvoice.php          # Facturas de proveedores
├── Controllers/
│   └── SupplierController.php       # Controlador principal
├── Views/
│   └── suppliers/
│       ├── index.php                # Listado de proveedores
│       ├── create.php               # Formulario de creación
│       ├── edit.php                 # Formulario de edición
│       ├── show.php                 # Detalle de proveedor
│       └── dashboard.php            # Dashboard de estadísticas
└── Helpers/
    └── AccountingHelper.php         # Integración contable
```

### Componentes del Sistema

1. **Suppliers**: Datos maestros de proveedores
2. **Supplier Contacts**: Gestión de múltiples contactos
3. **Supplier Documents**: Documentos y certificados
4. **Supplier Orders**: Órdenes de compra pre-factura
5. **Supplier Invoices**: Facturas y pagos
6. **Accounting Integration**: Registro automático en contabilidad
7. **Document Manager Integration**: Versionado y permisos

---

## Modelo de Datos

### Tabla: `suppliers`

Información completa del proveedor con datos fiscales, bancarios y comerciales.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único |
| `name` | VARCHAR(255) | Nombre comercial * |
| `cif_nif` | VARCHAR(20) | CIF/NIF básico |
| `tax_id` | VARCHAR(50) | ID fiscal completo |
| `email` | VARCHAR(255) | Email principal |
| `phone` | VARCHAR(20) | Teléfono |
| `address` | TEXT | Dirección completa |
| `postal_code` | VARCHAR(10) | Código postal |
| `city` | VARCHAR(100) | Ciudad |
| `province` | VARCHAR(100) | Provincia |
| `country` | VARCHAR(100) | País (default: España) |
| `website` | VARCHAR(255) | Sitio web |
| `logo_path` | VARCHAR(255) | Ruta del logo |
| `tipo_proveedor` | ENUM | servicios, productos, mixto, profesional |
| `categoria` | VARCHAR(100) | Categoría personalizada |
| `estado` | ENUM | activo, inactivo, bloqueado |
| `payment_terms` | INT | Días de plazo de pago (default: 30) |
| `default_payment_method` | ENUM | transfer, cash, card, check, other |
| `iban` | VARCHAR(34) | IBAN bancario |
| `swift` | VARCHAR(11) | Código SWIFT/BIC |
| `bank_name` | VARCHAR(255) | Nombre del banco |
| `default_discount` | DECIMAL(5,2) | Descuento por defecto (%) |
| `credit_limit` | DECIMAL(10,2) | Límite de crédito |
| `contact_person` | VARCHAR(255) | Persona de contacto principal |
| `contact_email` | VARCHAR(255) | Email del contacto |
| `contact_phone` | VARCHAR(20) | Teléfono del contacto |
| `rating` | TINYINT | Valoración 1-5 |
| `notes` | TEXT | Notas adicionales |
| `created_at` | DATETIME | Fecha de creación |
| `updated_at` | DATETIME | Última actualización |

### Tabla: `supplier_contacts`

Múltiples contactos por proveedor para diferentes departamentos.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único |
| `supplier_id` | INT | Referencia a suppliers * |
| `name` | VARCHAR(255) | Nombre del contacto * |
| `position` | VARCHAR(100) | Cargo/Puesto |
| `email` | VARCHAR(255) | Email |
| `phone` | VARCHAR(20) | Teléfono fijo |
| `mobile` | VARCHAR(20) | Móvil |
| `is_primary` | BOOLEAN | Es contacto principal |
| `notes` | TEXT | Notas |
| `created_at` | DATETIME | Fecha de creación |

### Tabla: `supplier_documents`

Gestión de documentos asociados a proveedores con control de caducidad.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único |
| `supplier_id` | INT | Referencia a suppliers * |
| `document_id` | INT | Ref. al gestor documental (opcional) |
| `document_type` | ENUM | contrato, certificado, seguro, licencia, otro |
| `name` | VARCHAR(255) | Nombre del documento * |
| `file_path` | VARCHAR(255) | Ruta del archivo |
| `description` | TEXT | Descripción |
| `upload_date` | DATE | Fecha de subida |
| `expiry_date` | DATE | Fecha de caducidad |
| `status` | ENUM | vigente, caducado, renovado, cancelado |
| `tags` | VARCHAR(255) | Tags para búsqueda (separados por comas) |
| `uploaded_by` | INT | Usuario que subió el documento |
| `created_at` | DATETIME | Fecha de creación |

### Tabla: `supplier_orders`

Órdenes de compra antes de recibir la factura formal.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único |
| `supplier_id` | INT | Referencia a suppliers * |
| `order_number` | VARCHAR(50) | Número de orden * (único) |
| `order_date` | DATE | Fecha de orden * |
| `expected_delivery_date` | DATE | Fecha estimada de entrega |
| `status` | ENUM | draft, sent, confirmed, received, cancelled |
| `subtotal` | DECIMAL(10,2) | Subtotal sin IVA |
| `tax_amount` | DECIMAL(10,2) | Importe de IVA |
| `discount_amount` | DECIMAL(10,2) | Descuentos aplicados |
| `total_amount` | DECIMAL(10,2) | Total de la orden |
| `notes` | TEXT | Notas |
| `approved_by` | INT | Usuario que aprobó |
| `approved_at` | DATETIME | Fecha de aprobación |
| `created_by` | INT | Usuario creador |
| `created_at` | DATETIME | Fecha de creación |
| `updated_at` | DATETIME | Última actualización |

### Tabla: `supplier_order_lines`

Líneas de detalle de cada orden de compra.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único |
| `order_id` | INT | Referencia a supplier_orders * |
| `line_number` | INT | Número de línea * |
| `description` | VARCHAR(255) | Descripción del artículo * |
| `quantity` | DECIMAL(10,2) | Cantidad * |
| `unit_price` | DECIMAL(10,2) | Precio unitario * |
| `tax_rate` | DECIMAL(5,2) | % IVA (default: 21%) |
| `discount_rate` | DECIMAL(5,2) | % Descuento (default: 0%) |
| `line_total` | DECIMAL(10,2) | Total de la línea |
| `notes` | TEXT | Notas |

### Tabla: `supplier_invoices` (Mejorada)

Facturas de proveedores con desglose completo.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único |
| `supplier_id` | INT | Referencia a suppliers * |
| `order_id` | INT | Referencia a supplier_orders (opcional) |
| `invoice_number` | VARCHAR(50) | Número de factura * |
| `invoice_date` | DATE | Fecha de factura * |
| `due_date` | DATE | Fecha de vencimiento |
| `payment_date` | DATE | Fecha real de pago |
| `subtotal` | DECIMAL(10,2) | Base imponible |
| `tax_amount` | DECIMAL(10,2) | IVA |
| `discount_amount` | DECIMAL(10,2) | Descuentos |
| `amount` | DECIMAL(10,2) | Total * |
| `status` | ENUM | paid, pending, overdue, cancelled |
| `payment_method` | ENUM | transfer, cash, card, check, other |
| `bank_reference` | VARCHAR(100) | Referencia bancaria del pago |
| `tipo_factura` | ENUM | normal, rectificativa, abono |
| `file_path` | VARCHAR(255) | Ruta del PDF |
| `notes` | TEXT | Notas |
| `created_at` | DATETIME | Fecha de creación |
| `updated_at` | DATETIME | Última actualización |

---

## Integración con Contabilidad

### Cuentas Contables Utilizadas

- **400 - Proveedores**: Cuenta de pasivo para proveedores
- **570 - Caja/Bancos**: Cuenta de tesorería para pagos
- **629 - Otros Servicios**: Gastos de servicios
- **600 - Compras**: Compras de productos

### Flujo Contable Automático

#### 1. Al Subir una Factura (Estado: Pending)

**NO SE CREA ASIENTO** hasta que se marca como pagada.

#### 2. Al Marcar Factura como Pagada

```php
AccountingHelper::createEntryFromSupplierInvoice(
    $db,
    $invoice_id,
    $amount,
    $description,
    $invoice_date,
    $payment_date,
    $payment_method
);
```

**Asiento Generado:**
```
DEBE                                    HABER
629 Otros Servicios    €1,000.00
                                        400 Proveedores        €1,000.00

(Registro del gasto)

400 Proveedores        €1,000.00
                                        570 Caja/Bancos        €1,000.00

(Pago realizado)
```

#### 3. Al Cancelar/Eliminar Factura

El sistema **cancela automáticamente** el asiento contable asociado:

```php
// En SupplierController::deleteInvoice()
$stmt = $this->db->prepare("SELECT id FROM accounting_entries 
                            WHERE source_type = 'supplier_invoice' 
                            AND source_id = ?");
$stmt->execute([$invoice_id]);
$entry = $stmt->fetch(PDO::FETCH_ASSOC);

if ($entry) {
    $entryModel = new AccountingEntry($this->db);
    $entryModel->id = $entry['id'];
    $entryModel->cancel(); // Marca como cancelled
}
```

### Verificación de Periodo Contable

Todas las operaciones verifican que el periodo contable esté abierto:

```php
AccountingPeriod::getCurrentPeriod($db); // Verifica que esté 'open'
```

---

## Integración con Gestor Documental

### Almacenamiento de Documentos

Los documentos de proveedores se pueden gestionar de dos formas:

#### 1. Sistema de Archivos (Básico)

```
public/uploads/suppliers/
├── [supplier_id]/
│   ├── factura_001.pdf
│   ├── contrato_2025.pdf
│   └── certificado_seguro.pdf
└── logos/
    └── logo_proveedor_123.png
```

#### 2. Gestor Documental Integrado (Avanzado)

Los documentos se registran en `supplier_documents` con referencia a `document_id` del sistema de gestión documental general, que proporciona:

- **Versionado**: Múltiples versiones del mismo documento
- **Control de permisos**: Quién puede ver/editar
- **Búsqueda avanzada**: Por tags, tipo, fecha
- **Auditoría**: Historial completo de cambios
- **Firmas digitales**: Validación de documentos

### Uso de Supplier Documents

```php
require_once 'src/Models/SupplierDocument.php';

$doc = new SupplierDocument($db);
$doc->supplier_id = $supplier_id;
$doc->document_type = 'contrato';
$doc->name = 'Contrato Marco 2025';
$doc->file_path = 'public/uploads/suppliers/123/contrato.pdf';
$doc->expiry_date = '2025-12-31';
$doc->tags = 'contrato,marco,anual';
$doc->uploaded_by = $_SESSION['user_id'];
$doc->create();
```

### Alertas de Caducidad

El sistema detecta automáticamente documentos próximos a caducar:

```php
// Documentos que caducan en los próximos 30 días
$expiring = $doc->getExpiringDocuments(30);

// Documentos ya caducados
$expired = $doc->getExpiredDocuments();
```

---

## Flujo de Trabajo

### 1. Creación de Proveedor

```
Usuario completa formulario
    ↓
Validación de datos
    ↓
Creación en BD (suppliers)
    ↓
Creación de directorio de documentos
    ↓
[Opcional] Creación de contacto inicial
    ↓
Mensaje de éxito
```

### 2. Gestión de Contactos

```
Añadir nuevo contacto
    ↓
Marcar como principal (opcional)
    ↓
Si es principal: desmarcar otros contactos
    ↓
Guardar en supplier_contacts
```

### 3. Subida de Documentos

```
Seleccionar archivo
    ↓
Validación (tipo, tamaño, MIME)
    ↓
Subir a directorio del proveedor
    ↓
Registrar en supplier_documents
    ↓
[Opcional] Vincular con gestor documental
    ↓
Configurar alertas de caducidad
```

### 4. Crear Orden de Compra

```
Crear orden (estado: draft)
    ↓
Añadir líneas de productos/servicios
    ↓
Calcular subtotales, IVA, descuentos
    ↓
Enviar para aprobación
    ↓
Aprobar orden (estado: sent)
    ↓
Enviar al proveedor
    ↓
Confirmar recepción (estado: received)
```

### 5. Gestión de Facturas

```
Subir factura PDF
    ↓
Completar datos (número, fecha, importes)
    ↓
[Opcional] Vincular con orden de compra
    ↓
Estado: pending (sin asiento contable)
    ↓
Marcar como pagada
    ↓
CREAR ASIENTO CONTABLE automático
    ↓
Actualizar estado: paid
    ↓
Registrar en auditoría
```

### 6. Control de Vencimientos

```
Cron diario ejecuta verificación
    ↓
Detectar facturas vencidas
    ↓
Actualizar estado: overdue
    ↓
Enviar notificaciones
    ↓
Detectar documentos próximos a caducar
    ↓
Alertar a administradores
```

---

## API y Endpoints

### Proveedores

- `GET /index.php?page=suppliers` - Listado
- `GET /index.php?page=suppliers&action=create` - Formulario de creación
- `POST /index.php?page=suppliers&action=store` - Guardar proveedor
- `GET /index.php?page=suppliers&action=show&id={id}` - Detalle
- `GET /index.php?page=suppliers&action=edit&id={id}` - Formulario de edición
- `POST /index.php?page=suppliers&action=update` - Actualizar
- `POST /index.php?page=suppliers&action=delete&id={id}` - Eliminar

### Facturas

- `POST /index.php?page=suppliers&action=uploadInvoice` - Subir factura
- `POST /index.php?page=suppliers&action=updateInvoiceStatus&id={id}&status={status}` - Cambiar estado
- `POST /index.php?page=suppliers&action=deleteInvoice&id={id}` - Eliminar factura

### Dashboard

- `GET /index.php?page=suppliers&action=dashboard&year={year}` - Estadísticas

---

## Migración

### Prerequisitos

1. **Backup de la base de datos**
2. **PHP 7.4+** con extensiones: PDO, PDO_MySQL
3. **MySQL 5.7+** o **MariaDB 10.3+**

### Ejecutar Migración

```bash
cd /root/Documentos/github/GestionSocios
./database/migrations/apply_supplier_migration.sh
```

El script solicitará:
- Host de MySQL
- Usuario
- Contraseña
- Nombre de la base de datos

Automáticamente:
1. Crea un backup de seguridad
2. Aplica todas las modificaciones
3. Migra datos existentes
4. Verifica integridad

### Verificación Post-Migración

```sql
-- Verificar nuevas tablas
SHOW TABLES LIKE 'supplier_%';

-- Verificar nuevos campos en suppliers
DESCRIBE suppliers;

-- Verificar nuevos campos en supplier_invoices
DESCRIBE supplier_invoices;

-- Contar registros migrados
SELECT COUNT(*) FROM supplier_contacts;
```

### Rollback (Si es necesario)

```bash
mysql -h localhost -u root -p gestion_socios < Backups/backup_before_supplier_migration_YYYYMMDD_HHMMSS.sql
```

---

## Mejoras Futuras

1. **Órdenes de Compra Recurrentes**: Automatizar órdenes periódicas
2. **Integración con Proveedores**: API para recibir facturas electrónicas
3. **Análisis Predictivo**: Predicción de gastos futuros
4. **Comparativa de Proveedores**: Evaluación automática de precios
5. **Portal de Proveedores**: Acceso web para que proveedores consulten estado
6. **Aprobaciones Multi-Nivel**: Flujo de aprobación de órdenes/facturas
7. **Pagos Batch**: Generación de archivos SEPA para pagos masivos

---

## Soporte

Para dudas o problemas con el módulo de proveedores:
- Revisar logs en `logs/`
- Consultar `AccountingHelper.php` para debugging contable
- Verificar permisos de directorios `public/uploads/suppliers/`

---

**Documentación generada: 3 de diciembre de 2025**
**Versión del módulo: 2.0 Professional**
