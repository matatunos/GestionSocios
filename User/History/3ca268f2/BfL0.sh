#!/bin/bash

# Script para aplicar la migración del módulo de proveedores profesional
# Fecha: 2025-12-03

echo "=================================================="
echo "Migración: Módulo de Proveedores Profesional"
echo "=================================================="
echo ""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verificar que existe el archivo de migración
MIGRATION_FILE="database/migrations/2025_12_03_professional_suppliers.sql"

if [ ! -f "$MIGRATION_FILE" ]; then
    echo -e "${RED}ERROR: No se encuentra el archivo de migración${NC}"
    echo "Ruta esperada: $MIGRATION_FILE"
    exit 1
fi

echo -e "${YELLOW}ADVERTENCIA: Esta migración modificará la estructura de la base de datos.${NC}"
echo -e "${YELLOW}Asegúrese de haber hecho un backup antes de continuar.${NC}"
echo ""
read -p "¿Desea continuar? (s/n): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Ss]$ ]]; then
    echo "Migración cancelada."
    exit 0
fi

# Solicitar credenciales de MySQL
echo ""
echo "Ingrese las credenciales de MySQL:"
read -p "Host (default: localhost): " DB_HOST
DB_HOST=${DB_HOST:-localhost}

read -p "Usuario (default: root): " DB_USER
DB_USER=${DB_USER:-root}

read -sp "Contraseña: " DB_PASS
echo ""

read -p "Base de datos: " DB_NAME

if [ -z "$DB_NAME" ]; then
    echo -e "${RED}ERROR: Debe especificar el nombre de la base de datos${NC}"
    exit 1
fi

# Crear backup antes de migrar
BACKUP_DIR="Backups"
BACKUP_FILE="${BACKUP_DIR}/backup_before_supplier_migration_$(date +%Y%m%d_%H%M%S).sql"

echo ""
echo "Creando backup de seguridad..."
mkdir -p "$BACKUP_DIR"

mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Backup creado exitosamente: $BACKUP_FILE${NC}"
else
    echo -e "${RED}✗ Error al crear el backup${NC}"
    echo "¿Desea continuar sin backup? (NO RECOMENDADO)"
    read -p "(s/n): " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Ss]$ ]]; then
        echo "Migración cancelada."
        exit 1
    fi
fi

# Aplicar migración
echo ""
echo "Aplicando migración..."
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$MIGRATION_FILE" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Migración aplicada exitosamente${NC}"
    echo ""
    echo "Nuevas tablas creadas:"
    echo "  - supplier_contacts"
    echo "  - supplier_documents"
    echo "  - supplier_orders"
    echo "  - supplier_order_lines"
    echo ""
    echo "Tablas modificadas:"
    echo "  - suppliers (nuevos campos profesionales)"
    echo "  - supplier_invoices (nuevos campos de gestión)"
    echo ""
    echo -e "${GREEN}¡Módulo de proveedores profesionalizado exitosamente!${NC}"
else
    echo -e "${RED}✗ Error al aplicar la migración${NC}"
    echo ""
    echo "Si necesita restaurar el backup:"
    echo "  mysql -h $DB_HOST -u $DB_USER -p $DB_NAME < $BACKUP_FILE"
    exit 1
fi

echo ""
echo "=================================================="
echo "Migración completada"
echo "=================================================="
