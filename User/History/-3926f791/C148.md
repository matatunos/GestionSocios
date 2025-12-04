# Módulo de Proveedores Profesional - Guía de Implementación

## 🎯 Resumen de Mejoras

El módulo de proveedores ha sido completamente profesionalizado con las siguientes mejoras:

### ✅ Base de Datos
- **5 nuevas tablas** creadas
- **25+ campos nuevos** en tabla suppliers
- **10+ campos nuevos** en supplier_invoices
- **Índices optimizados** para consultas rápidas

### ✅ Modelos PHP
- **6 modelos nuevos/actualizados**
- Validación completa de datos
- Métodos de consulta avanzados
- Manejo de errores robusto

### ✅ Integración
- **Contabilidad**: Asientos automáticos en todas las operaciones
- **Gestor Documental**: Versionado, permisos y tags
- **Alertas**: Vencimientos de facturas y documentos

---

## 📋 Checklist de Implementación

### 1. Backup (OBLIGATORIO)
```bash
mysqldump -u root -p gestion_socios > backup_$(date +%Y%m%d).sql
```

### 2. Aplicar Migración
```bash
cd /root/Documentos/github/GestionSocios
./database/migrations/apply_supplier_migration.sh
```

### 3. Verificar Migración
```sql
-- Conectar a MySQL
mysql -u root -p gestion_socios

-- Verificar tablas nuevas
SHOW TABLES LIKE 'supplier_%';

-- Debería mostrar:
-- supplier_contacts
-- supplier_documents  
-- supplier_invoices (ya existía, pero modificada)
-- supplier_orders
-- supplier_order_lines

-- Verificar campos en suppliers
DESCRIBE suppliers;

-- Debería incluir nuevos campos:
-- tax_id, postal_code, city, province, country
-- tipo_proveedor, categoria, estado
-- payment_terms, default_payment_method
-- iban, swift, bank_name
-- default_discount, credit_limit
-- contact_person, contact_email, contact_phone, rating
```

### 4. Actualizar Controlador (Si es necesario)

El `SupplierController.php` actual ya tiene integración contable básica. 
Ahora necesitarás actualizar los métodos `store()` y `update()` para manejar los nuevos campos.

Ejemplo de actualización en `store()`:

```php
// Campos adicionales a añadir
$this->supplier->tax_id = $_POST['tax_id'] ?? '';
$this->supplier->postal_code = $_POST['postal_code'] ?? '';
$this->supplier->city = $_POST['city'] ?? '';
$this->supplier->province = $_POST['province'] ?? '';
$this->supplier->country = $_POST['country'] ?? 'España';
$this->supplier->tipo_proveedor = $_POST['tipo_proveedor'] ?? 'servicios';
$this->supplier->categoria = $_POST['categoria'] ?? '';
$this->supplier->estado = $_POST['estado'] ?? 'activo';
$this->supplier->payment_terms = intval($_POST['payment_terms'] ?? 30);
$this->supplier->default_payment_method = $_POST['default_payment_method'] ?? 'transfer';
$this->supplier->iban = $_POST['iban'] ?? '';
$this->supplier->swift = $_POST['swift'] ?? '';
$this->supplier->bank_name = $_POST['bank_name'] ?? '';
$this->supplier->default_discount = floatval($_POST['default_discount'] ?? 0.00);
$this->supplier->credit_limit = !empty($_POST['credit_limit']) ? floatval($_POST['credit_limit']) : null;
$this->supplier->contact_person = $_POST['contact_person'] ?? '';
$this->supplier->contact_email = $_POST['contact_email'] ?? '';
$this->supplier->contact_phone = $_POST['contact_phone'] ?? '';
$this->supplier->rating = !empty($_POST['rating']) ? intval($_POST['rating']) : null;
```

### 5. Actualizar Vistas

Deberás actualizar los formularios en:
- `src/Views/suppliers/create.php`
- `src/Views/suppliers/edit.php`
- `src/Views/suppliers/show.php`

Para incluir los nuevos campos organizados en secciones:

#### Sección: Datos Fiscales
- CIF/NIF
- Tax ID
- Tipo de Proveedor
- Categoría
- Estado

#### Sección: Dirección
- Dirección
- Código Postal
- Ciudad
- Provincia
- País

#### Sección: Datos Bancarios
- IBAN
- SWIFT
- Banco
- Forma de Pago por Defecto
- Plazos de Pago (días)

#### Sección: Comercial
- Descuento por Defecto (%)
- Límite de Crédito
- Valoración (1-5 estrellas)

#### Sección: Contacto Principal
- Persona de Contacto
- Email de Contacto
- Teléfono de Contacto

### 6. Crear Controladores Adicionales (Opcional pero Recomendado)

Para gestionar las nuevas entidades:

```php
// src/Controllers/SupplierContactController.php
// src/Controllers/SupplierDocumentController.php
// src/Controllers/SupplierOrderController.php
```

---

## 🔄 Flujo de Trabajo Recomendado

### Gestión de Proveedores

1. **Crear Proveedor**
   - Completar todos los datos maestros
   - Subir logo si está disponible
   - Guardar proveedor

2. **Añadir Contactos**
   - Acceder al detalle del proveedor
   - Sección "Contactos"
   - Añadir múltiples contactos (ventas, facturación, técnico)
   - Marcar uno como principal

3. **Subir Documentos**
   - Contratos
   - Certificados de seguro
   - Licencias
   - Configurar fechas de caducidad
   - Sistema alerta automáticamente 30 días antes

### Gestión de Compras

1. **Crear Orden de Compra** (Opcional)
   - Crear borrador
   - Añadir líneas de productos/servicios
   - Enviar para aprobación
   - Aprobar y enviar al proveedor
   - Marcar como recibida

2. **Registrar Factura**
   - Subir PDF de la factura
   - Completar datos (número, fecha, importes)
   - Vincular con orden de compra si existe
   - Estado inicial: "Pendiente"

3. **Pagar Factura**
   - Cambiar estado a "Pagada"
   - **Se crea automáticamente el asiento contable**
   - Se registra en auditoría

### Alertas Automáticas

El sistema genera alertas para:
- ✅ Facturas vencidas (overdue)
- ✅ Documentos próximos a caducar (30 días)
- ✅ Documentos caducados

---

## 📊 Nuevas Funcionalidades

### 1. Gestión de Contactos Múltiples

```php
require_once 'src/Models/SupplierContact.php';

$contact = new SupplierContact($db);
$contact->supplier_id = 123;
$contact->name = 'Juan Pérez';
$contact->position = 'Director Comercial';
$contact->email = 'juan@proveedor.com';
$contact->phone = '911234567';
$contact->is_primary = 1;
$contact->create();
```

### 2. Gestión Documental

```php
require_once 'src/Models/SupplierDocument.php';

$doc = new SupplierDocument($db);
$doc->supplier_id = 123;
$doc->document_type = 'seguro';
$doc->name = 'Seguro Responsabilidad Civil 2025';
$doc->file_path = 'public/uploads/suppliers/123/seguro_2025.pdf';
$doc->expiry_date = '2025-12-31';
$doc->tags = 'seguro,responsabilidad,anual';
$doc->uploaded_by = $_SESSION['user_id'];
$doc->create();

// Obtener documentos próximos a caducar
$expiring = $doc->getExpiringDocuments(30); // 30 días

// Obtener documentos ya caducados
$expired = $doc->getExpiredDocuments();
```

### 3. Órdenes de Compra

```php
require_once 'src/Models/SupplierOrder.php';
require_once 'src/Models/SupplierOrderLine.php';

// Crear orden
$order = new SupplierOrder($db);
$order->supplier_id = 123;
$order->order_number = 'OC-2025-001';
$order->order_date = date('Y-m-d');
$order->status = 'draft';
$order->created_by = $_SESSION['user_id'];
$order->create();

// Añadir línea
$line = new SupplierOrderLine($db);
$line->order_id = $order->id;
$line->line_number = 1;
$line->description = 'Papelería oficina';
$line->quantity = 100;
$line->unit_price = 2.50;
$line->tax_rate = 21.00;
$line->create();

// Aprobar orden
$order->approve($_SESSION['user_id']);
```

### 4. Estadísticas Avanzadas

```php
// Total gastado en el año
$totalYear = $invoice->getTotalAmount(2025);

// Facturas pendientes
$pending = $invoice->getPendingAmount();

// Top 5 proveedores
$topSuppliers = $invoice->getTopSuppliers(5, 2025);

// Estadísticas mensuales
$monthlyStats = $invoice->getMonthlyStats(2025);

// Facturas vencidas
$overdueInvoices = $invoice->getOverdueInvoices();
```

---

## 🔗 Integración con Contabilidad

### Asientos Automáticos

El sistema crea automáticamente asientos contables cuando:

1. **Se marca una factura como pagada**
   ```
   DEBE: 629 Otros Servicios    €1,000.00
   HABER: 400 Proveedores        €1,000.00
   
   DEBE: 400 Proveedores         €1,000.00
   HABER: 570 Caja/Bancos        €1,000.00
   ```

2. **Se cancela una factura**
   - Se cancela automáticamente el asiento contable asociado

3. **Se elimina una factura**
   - Se cancela el asiento contable
   - Se registra en auditoría

### Verificación de Periodos

Todas las operaciones verifican que el periodo contable esté abierto antes de crear asientos.

---

## 🚨 Troubleshooting

### Error: "No se pudo crear el proveedor"
- Verificar que la migración se aplicó correctamente
- Verificar que todos los campos requeridos están presentes
- Revisar logs de MySQL

### Error: "Periodo contable cerrado"
- Abrir el periodo contable en el módulo de contabilidad
- Verificar que existe un periodo para el año actual

### Error: "No se puede subir el archivo"
- Verificar permisos en `public/uploads/suppliers/`
- Verificar tamaño máximo de upload en PHP (php.ini)
- Verificar extensiones permitidas

### Facturas no aparecen en contabilidad
- Verificar que la factura está marcada como "paid"
- Solo las facturas pagadas generan asientos contables
- Revisar logs en `logs/`

---

## 📈 Próximos Pasos

### Inmediatos
1. ✅ Aplicar migración
2. ✅ Actualizar formularios de vistas
3. ✅ Probar crear/editar proveedor
4. ✅ Probar subir factura y marcar como pagada

### A Corto Plazo
1. Crear vistas para gestión de contactos
2. Crear vistas para gestión de documentos
3. Crear vistas para órdenes de compra
4. Implementar alertas de caducidad en dashboard

### A Medio Plazo
1. API para proveedores (consultar estado de facturas)
2. Portal web para proveedores
3. Integración con factura electrónica
4. Pagos SEPA automáticos

---

## 📝 Notas Importantes

- ⚠️ **SIEMPRE hacer backup antes de migrar**
- ⚠️ Los proveedores existentes mantendrán sus datos, solo se añaden campos nuevos
- ⚠️ Las facturas existentes calcularán automáticamente subtotal e IVA
- ✅ La integración contable ya existente seguirá funcionando
- ✅ Los documentos existentes no se ven afectados

---

## 🆘 Soporte

Para cualquier duda o problema:
1. Revisar `SUPPLIER_MODULE.md` para documentación técnica completa
2. Revisar logs en `logs/`
3. Consultar código fuente en `src/Models/Supplier*.php`
4. Verificar asientos contables en módulo de contabilidad

---

**Última actualización: 3 de diciembre de 2025**
