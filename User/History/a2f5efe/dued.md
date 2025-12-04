# Sistema de Migraciones de Base de Datos

## Resumen

Sistema automatizado de migraciones para mantener sincronizada la base de datos con el código de la aplicación.

---

## 📋 Migraciones Disponibles

### 1. `2025_12_03_professional_suppliers_SAFE.sql`
**Descripción**: Profesionaliza el módulo de proveedores

**Cambios**:
- ✅ 19 nuevas columnas en `suppliers` (tax_id, postal_code, city, province, country, etc.)
- ✅ 3 índices optimizados (tipo, estado, categoría)
- ✅ Tabla `supplier_contacts` (múltiples contactos por proveedor)
- ✅ Tabla `supplier_documents` (gestión documental)
- ✅ Tabla `supplier_categories` (10 categorías predefinidas)
- ✅ Tabla `supplier_ratings` (sistema de valoración)

**Estado**: ✅ Aplicada

---

### 2. `2025_12_04_create_grants_tables.sql`
**Descripción**: Crea tablas para el módulo de subvenciones

**Cambios**:
- ✅ Tabla `grants` (gestión completa de subvenciones)
- ✅ Tabla `grant_documents` (documentación asociada)
- ✅ Tabla `grant_activities` (seguimiento de actividades)
- ✅ Tabla `grant_reminders` (recordatorios automáticos)
- ✅ 3 subvenciones de ejemplo insertadas

**Estado**: ✅ Aplicada

**Registros**: 3 subvenciones ejemplo

---

### 3. `2025_12_04_create_bank_tables.sql`
**Descripción**: Crea tablas para el módulo bancario

**Cambios**:
- ✅ Tabla `bank_accounts` (cuentas bancarias)
- ✅ Tabla `bank_transactions` (movimientos bancarios)
- ✅ Tabla `bank_reconciliations` (conciliaciones)
- ✅ Tabla `bank_transaction_matches` (matching automático)
- ✅ Tabla `bank_import_rules` (reglas de categorización)
- ✅ 1 cuenta bancaria ejemplo
- ✅ 4 reglas de categorización predefinidas

**Estado**: ✅ Aplicada

**Registros**: 1 cuenta + 4 reglas

---

## 🚀 Uso del Sistema de Migraciones

### Opción 1: Script Automático (Recomendado)

```bash
# Aplicar todas las migraciones pendientes
./database/apply_migrations.sh

# Con parámetros personalizados
./database/apply_migrations.sh [host] [user] [database]

# Ejemplo:
./database/apply_migrations.sh 192.168.1.22 root asociacion
```

**Ventajas**:
- ✅ Detecta automáticamente migraciones pendientes
- ✅ Evita aplicar migraciones duplicadas
- ✅ Registro de migraciones aplicadas en `schema_migrations`
- ✅ Reporte de estado con colores
- ✅ Safe to run múltiples veces (idempotente)

---

## 📊 Estado de las Tablas

### Verificar Migraciones Aplicadas

```bash
mysql -u root -psatriani -h 192.168.1.22 asociacion -e \
  "SELECT * FROM schema_migrations ORDER BY applied_at DESC;"
```

---

## 🗂️ Estructura de Tablas Actual

### Módulo de Proveedores (8 tablas)
```
suppliers (30 columnas)
├── supplier_contacts
├── supplier_documents
├── supplier_categories
├── supplier_ratings
├── supplier_invoices
├── supplier_orders
└── supplier_order_lines
```

### Módulo de Subvenciones (4 tablas)
```
grants
├── grant_documents
├── grant_activities
└── grant_reminders
```

### Módulo Bancario (5 tablas)
```
bank_accounts
├── bank_transactions
├── bank_reconciliations
├── bank_transaction_matches
└── bank_import_rules
```

---

## 📈 Estadísticas

| Módulo | Tablas | Migraciones | Estado |
|--------|--------|-------------|--------|
| Proveedores | 8 | 1 | ✅ Completo |
| Subvenciones | 4 | 1 | ✅ Completo |
| Bancario | 5 | 1 | ✅ Completo |
| **Total** | **17** | **3** | ✅ **100%** |
