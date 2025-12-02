# Módulo de Contabilidad Avanzada

## 📋 Descripción General

Este módulo implementa un sistema completo de contabilidad avanzada con contabilidad por partida doble, compatible con el Plan General Contable (PGC) español.

## ✨ Características Principales

### 1. Plan de Cuentas (Chart of Accounts)
- Gestión completa de cuentas contables con estructura jerárquica
- Tipos de cuenta: Activo, Pasivo, Patrimonio, Ingresos, Gastos
- Tipo de saldo: Deudor o Acreedor
- Cuentas del sistema predefinidas según PGC español
- Niveles jerárquicos para organización de subcuentas

### 2. Libro Diario (Journal)
- Creación de asientos contables con partida doble
- Validación automática de que Debe = Haber
- Estados: Borrador, Contabilizado, Cancelado
- Numeración automática de asientos
- Referencia y descripción detallada
- Múltiples líneas por asiento

### 3. Libro Mayor (General Ledger)
- Consulta de movimientos por cuenta específica
- Saldo acumulado por transacción
- Filtrado por rango de fechas
- Visualización de débitos, créditos y saldos

### 4. Balance de Sumas y Saldos (Trial Balance)
- Resumen de todas las cuentas con movimiento
- Agrupación por tipo de cuenta
- Totales de Debe y Haber
- Saldos Deudores y Acreedores
- Verificación automática de cuadre contable

### 5. Períodos Contables
- Organización por ejercicio fiscal
- Períodos abiertos y cerrados
- Un período por año (extensible a mensual/trimestral)

## 🗂️ Estructura de Archivos

```
src/
├── Controllers/
│   └── AccountingController.php       # Controlador principal
├── Models/
│   ├── AccountingAccount.php          # Model de cuentas
│   ├── AccountingEntry.php            # Model de asientos
│   └── AccountingPeriod.php           # Model de períodos
└── Views/
    └── accounting/
        ├── dashboard.php              # Dashboard principal
        ├── accounts/
        │   ├── index.php             # Lista de cuentas
        │   ├── create.php            # Crear cuenta
        │   └── edit.php              # Editar cuenta
        ├── entries/
        │   ├── index.php             # Libro diario
        │   ├── create.php            # Nuevo asiento
        │   └── view.php              # Ver asiento
        └── reports/
            ├── general_ledger.php     # Libro mayor
            └── trial_balance.php      # Balance de sumas

database/
└── schema.sql                         # Incluye tablas contables
```

## 📊 Estructura de Base de Datos

### Tabla: accounting_periods
Almacena los períodos contables (ejercicios fiscales).

**Campos principales:**
- `name`: Nombre del período (ej: "Ejercicio 2025")
- `start_date`, `end_date`: Rango de fechas
- `fiscal_year`: Año fiscal
- `status`: open/closed/locked

### Tabla: accounting_accounts
Plan de cuentas contable.

**Campos principales:**
- `code`: Código de la cuenta (ej: "570", "700")
- `name`: Nombre descriptivo
- `account_type`: asset/liability/equity/income/expense
- `balance_type`: debit/credit
- `parent_id`: Para jerarquía de cuentas
- `level`: Nivel jerárquico (0-5)
- `is_active`: Si la cuenta está activa
- `is_system`: Si es cuenta del sistema (no editable)

### Tabla: accounting_entries
Cabecera de asientos contables.

**Campos principales:**
- `entry_number`: Número único del asiento
- `entry_date`: Fecha del asiento
- `period_id`: Período contable
- `description`: Descripción del asiento
- `reference`: Referencia opcional
- `status`: draft/posted/cancelled
- `entry_type`: manual/automatic
- `source_type`: origen del asiento
- `created_by`, `posted_by`: Usuarios

### Tabla: accounting_entry_lines
Líneas de los asientos (detalle).

**Campos principales:**
- `entry_id`: FK a accounting_entries
- `account_id`: FK a accounting_accounts
- `description`: Descripción de la línea
- `debit`: Importe en el Debe
- `credit`: Importe en el Haber
- `line_order`: Orden de la línea

## 🚀 Uso del Módulo

### Acceso
Menú principal → Contabilidad

### Flujo Típico de Trabajo

1. **Configuración Inicial**
   - Verificar que existe un período contable abierto
   - Revisar el plan de cuentas predefinido
   - Añadir cuentas adicionales si es necesario

2. **Registro de Operaciones**
   - Crear asiento contable (Libro Diario → Nuevo Asiento)
   - Añadir líneas con cuentas y importes
   - Verificar que cuadre (Debe = Haber)
   - Guardar como borrador para revisión
   - Contabilizar cuando esté verificado

3. **Consultas y Reportes**
   - Libro Mayor: Ver movimientos de una cuenta específica
   - Balance de Sumas y Saldos: Verificar cuadre general
   - Exportar reportes si es necesario

## 🔒 Seguridad

- **XSS Protection**: Todos los outputs usan `htmlspecialchars()`
- **SQL Injection**: Uso de prepared statements en todas las consultas
- **Validación**: Validación en cliente y servidor
- **Auditoría**: Todas las acciones se registran en audit_log
- **Autorización**: Solo usuarios autenticados pueden acceder

## 🎨 Interfaz de Usuario

- **Diseño Responsivo**: Compatible con desktop y móvil
- **Tema Consistente**: Usa los estilos del sistema
- **Iconos Font Awesome**: Para mejor UX
- **Validación en Tiempo Real**: En formularios de asientos
- **Feedback Visual**: Colores para estados y saldos

## 📝 Cuentas Predefinidas (PGC Español)

El sistema incluye estas cuentas iniciales:

### Patrimonio
- 100: Capital Social
- 129: Resultados del Ejercicio

### Activo
- 430: Clientes
- 440: Deudores
- 470: Hacienda Pública, Deudora
- 570: Caja
- 572: Bancos e Instituciones de Crédito

### Pasivo
- 400: Proveedores
- 410: Acreedores
- 475: Hacienda Pública, Acreedora

### Ingresos
- 700: Ventas de Mercaderías
- 705: Prestaciones de Servicios
- 720: Cuotas de Socios
- 721: Subvenciones
- 722: Donaciones
- 759: Otros Ingresos

### Gastos
- 600: Compras
- 621: Arrendamientos
- 622: Reparaciones y Conservación
- 623: Servicios de Profesionales Independientes
- 624: Transportes
- 625: Primas de Seguros
- 626: Servicios Bancarios
- 627: Publicidad y Propaganda
- 628: Suministros
- 629: Otros Servicios
- 640: Sueldos y Salarios
- 642: Seguridad Social a cargo de la Empresa
- 649: Otros Gastos Sociales
- 678: Gastos Excepcionales

## 🔄 Integración Futura

El módulo está preparado para:
- Generar asientos automáticos desde otros módulos (gastos, pagos, donaciones)
- Exportar a formatos contables estándar
- Integración con software de contabilidad externo
- Reportes fiscales y declaraciones

## 📚 Referencias

- [Plan General Contable Español](https://www.icac.gob.es/)
- [Contabilidad por Partida Doble](https://es.wikipedia.org/wiki/Contabilidad_por_partida_doble)

## 🐛 Solución de Problemas

### El período contable no aparece
- Verificar que existe un registro en `accounting_periods` con `status='open'`
- Crear manualmente si es necesario

### No puedo contabilizar un asiento
- Verificar que el asiento está en estado 'draft'
- Verificar que los totales de Debe y Haber cuadran exactamente

### Mensaje "Descuadrado" en Balance
- Revisar todos los asientos contabilizados
- Buscar asientos con diferencias en Debe/Haber
- Puede ser error de redondeo (< 0.01 €)

## 📊 Mejores Prácticas

1. **Nunca eliminar cuentas con movimientos**: Desactivar en su lugar
2. **Revisar asientos antes de contabilizar**: Una vez contabilizados no se pueden editar
3. **Realizar balance mensual**: Para detectar errores temprano
4. **Documentar bien los asientos**: Descripción y referencia claras
5. **Respetar períodos**: No contabilizar en períodos cerrados
6. **Backup regular**: Exportar datos contables periódicamente

## 📅 Versión

- **Versión**: 1.0
- **Fecha**: Diciembre 2025
- **Estado**: Producción
- **Compatibilidad**: PHP 7.4+, MySQL 5.7+

---

**Desarrollado para GestionSocios**  
Sistema de Gestión de Asociaciones
