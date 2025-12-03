# Ubicación de Entradas de Navegación

## Resumen
Todas las entradas del menú de navegación están ubicadas en **DOS archivos principales**:

---

## 1. **Archivo de Rutas**: `public/index.php`
**Ubicación**: `/root/Documentos/github/GestionSocios/public/index.php`

Este archivo controla el **routing** (enrutamiento) de la aplicación. Contiene un `switch` statement que determina qué controlador y acción ejecutar según el parámetro `?page=` en la URL.

### Nuevos módulos añadidos (líneas 507-550):

```php
case 'grants':
    // Gestión de Subvenciones
    require_once __DIR__ . '/../src/Controllers/GrantController.php';
    $controller = new GrantController($db);
    // Acciones: index, scrape, view, track, updateStatus, delete, dashboard, calendar

case 'bank':
    // Gestión Bancaria
    require_once __DIR__ . '/../src/Controllers/BankController.php';
    $controller = new BankController($db);
    // Acciones: dashboard, accounts, transactions, import, reconciliation, etc.

case 'financial':
    // Dashboard Financiero Consolidado
    require_once __DIR__ . '/../src/Controllers/FinancialDashboardController.php';
    $controller = new FinancialDashboardController($db);
    // Acción única: index (dashboard)
```

---

## 2. **Archivo de Menú**: `src/Views/layout.php`
**Ubicación**: `/root/Documentos/github/GestionSocios/src/Views/layout.php`

Este archivo contiene la **estructura HTML del menú lateral** (sidebar). Tiene DOS versiones del menú:

### A) **Menú Móvil** (líneas 113-298)
Versión simplificada para dispositivos móviles. Entradas añadidas:

```php
<li>
    <a href="index.php?page=financial" class="nav-link">
        <i class="fas fa-chart-line"></i>
        <span>Dashboard Financiero</span>
    </a>
</li>
<li>
    <a href="index.php?page=bank" class="nav-link">
        <i class="fas fa-university"></i>
        <span>Gestión Bancaria</span>
    </a>
</li>
<li>
    <a href="index.php?page=grants" class="nav-link">
        <i class="fas fa-hand-holding-usd"></i>
        <span>Subvenciones</span>
    </a>
</li>
```

### B) **Menú Desktop** (líneas 300-595)
Versión completa con submenús desplegables. Entradas añadidas:

#### **Dashboard Financiero** (línea ~440)
```php
<li>
    <a href="index.php?page=financial" class="nav-link">
        <i class="fas fa-chart-line"></i>
        <span>Dashboard Financiero</span>
    </a>
</li>
```

#### **Gestión Bancaria** (líneas ~443-475) - CON SUBMENÚ
```php
<li class="nav-group">
    <a href="#" class="nav-link">
        <i class="fas fa-university"></i>
        <span>Gestión Bancaria</span>
        <i class="fas fa-chevron-down"></i>
    </a>
    <ul class="nav-submenu">
        <li><a href="index.php?page=bank">Panel Bancario</a></li>
        <li><a href="index.php?page=bank&action=accounts">Cuentas Bancarias</a></li>
        <li><a href="index.php?page=bank&action=transactions">Movimientos</a></li>
        <li><a href="index.php?page=bank&action=import">Importar CSV</a></li>
        <li><a href="index.php?page=bank&action=reconciliation">Conciliación</a></li>
    </ul>
</li>
```

#### **Subvenciones** (líneas ~478-505) - CON SUBMENÚ
```php
<li class="nav-group">
    <a href="#" class="nav-link">
        <i class="fas fa-hand-holding-usd"></i>
        <span>Subvenciones</span>
        <i class="fas fa-chevron-down"></i>
    </a>
    <ul class="nav-submenu">
        <li><a href="index.php?page=grants">Listado</a></li>
        <li><a href="index.php?page=grants&action=dashboard">Dashboard</a></li>
        <li><a href="index.php?page=grants&action=calendar">Calendario</a></li>
        <li><a href="index.php?page=grants&action=scrape">Actualizar BDNS</a></li>
    </ul>
</li>
```

---

## 3. Estructura Visual del Menú

```
📂 Dashboard
   ├─ Panel de Control
   ├─ Tesorería
   ├─ Contabilidad
   └─ ...

📂 Socios
📂 Donantes
📂 Libros Contables

✨ Dashboard Financiero          ← NUEVO

📂 Gestión Bancaria              ← NUEVO
   ├─ Panel Bancario
   ├─ Cuentas Bancarias
   ├─ Movimientos
   ├─ Importar CSV
   └─ Conciliación

📂 Subvenciones                  ← NUEVO
   ├─ Listado
   ├─ Dashboard
   ├─ Calendario
   └─ Actualizar BDNS

📂 Tareas
📂 Estadísticas
...
```

---

## 4. Cómo Agregar Nuevas Entradas

### Paso 1: Agregar Routing en `public/index.php`
Buscar la línea del `default:` case y añadir antes:

```php
case 'nuevo_modulo':
    require_once __DIR__ . '/../src/Controllers/NuevoController.php';
    $controller = new NuevoController($db);
    if ($action === 'accion1') $controller->accion1();
    else if ($action === 'accion2') $controller->accion2();
    else $controller->index();
    break;
```

### Paso 2: Agregar Entrada en Menú `src/Views/layout.php`

**Para menú simple** (una sola página):
```php
<li>
    <a href="index.php?page=nuevo_modulo" class="nav-link <?php echo ($page === 'nuevo_modulo') ? 'active' : ''; ?>">
        <i class="fas fa-icono"></i>
        <span>Nombre Módulo</span>
    </a>
</li>
```

**Para menú con submenú**:
```php
<li class="nav-group">
    <a href="#" class="nav-link <?php echo ($page === 'nuevo_modulo') ? 'active' : ''; ?>">
        <i class="fas fa-icono"></i>
        <span>Nombre Módulo</span>
        <i class="fas fa-chevron-down" style="margin-left:auto;font-size:0.8em;"></i>
    </a>
    <ul class="nav-submenu">
        <li>
            <a href="index.php?page=nuevo_modulo&action=accion1" class="nav-link">
                <i class="fas fa-icono-sub"></i>
                <span>Sub-opción 1</span>
            </a>
        </li>
        <!-- Más sub-opciones... -->
    </ul>
</li>
```

### Paso 3: Agregar en AMBAS versiones del menú
- **Menú móvil** (líneas 113-298): versión simplificada
- **Menú desktop** (líneas 300-595): versión completa

---

## 5. Iconos de Font Awesome Utilizados

| Módulo | Icono | Clase CSS |
|--------|-------|-----------|
| Dashboard Financiero | 📈 | `fas fa-chart-line` |
| Gestión Bancaria | 🏛️ | `fas fa-university` |
| Cuentas Bancarias | 🐷 | `fas fa-piggy-bank` |
| Movimientos | 🔄 | `fas fa-exchange-alt` |
| Importar CSV | 📄 | `fas fa-file-csv` |
| Conciliación | ✔️✔️ | `fas fa-check-double` |
| Subvenciones | 💵 | `fas fa-hand-holding-usd` |
| Dashboard | 📊 | `fas fa-chart-bar` |
| Calendario | 📅 | `fas fa-calendar-check` |
| Actualizar BDNS | 🔄 | `fas fa-sync` |

---

## 6. Cambios Realizados en este Commit

**Commit**: `6c1c5dd` - "feat: Add routing and navigation for grants, bank, and financial modules"

### Archivos modificados:
- ✅ `public/index.php` (+42 líneas)
- ✅ `src/Views/layout.php` (+97 líneas)

### Total: **139 líneas añadidas**

---

## 7. URLs de Acceso a los Nuevos Módulos

### Dashboard Financiero
- `index.php?page=financial`

### Gestión Bancaria
- Panel: `index.php?page=bank`
- Cuentas: `index.php?page=bank&action=accounts`
- Movimientos: `index.php?page=bank&action=transactions`
- Importar CSV: `index.php?page=bank&action=import`
- Conciliación: `index.php?page=bank&action=reconciliation`

### Subvenciones
- Listado: `index.php?page=grants`
- Dashboard: `index.php?page=grants&action=dashboard`
- Calendario: `index.php?page=grants&action=calendar`
- Scraper BDNS: `index.php?page=grants&action=scrape`

---

## 8. Próximos Pasos

Para hacer **push** de estos cambios:

```bash
cd /root/Documentos/github/GestionSocios
git push origin devel
```

---

## Notas Importantes

⚠️ **IMPORTANTE**: Cuando agregues nuevas entradas:
1. Actualizar **AMBAS** secciones del menú (móvil y desktop)
2. Mantener coherencia en iconos y nombres
3. Agregar el routing correspondiente en `index.php`
4. Verificar que el controlador y las vistas existan

📝 **Mantenimiento**: El archivo `layout.php` es extenso (1051 líneas). Considera dividirlo en partials si crece más.

🔍 **Búsqueda rápida**:
- En `index.php`: Buscar `case 'page_name':`
- En `layout.php`: Buscar `page=page_name`
