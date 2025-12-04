#!/bin/bash
# Script de aplicación de migraciones de base de datos
# Uso: ./apply_migrations.sh [host] [user] [database]

set -e  # Exit on error

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuración por defecto
DB_HOST="${1:-192.168.1.22}"
DB_USER="${2:-root}"
DB_NAME="${3:-asociacion}"
DB_PASS="satriani"

MIGRATIONS_DIR="database/migrations"
APPLIED_MIGRATIONS_TABLE="schema_migrations"

echo -e "${BLUE}=================================================="
echo "Sistema de Migraciones - GestionSocios"
echo -e "==================================================${NC}"
echo ""
echo "Host: $DB_HOST"
echo "Usuario: $DB_USER"
echo "Base de datos: $DB_NAME"
echo ""

# Verificar conexión
echo -e "${YELLOW}Verificando conexión...${NC}"
if ! mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME" 2>/dev/null; then
    echo -e "${RED}✗ Error: No se puede conectar a la base de datos${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Conexión exitosa${NC}"
echo ""

# Crear tabla de migraciones aplicadas si no existe
echo -e "${YELLOW}Inicializando sistema de migraciones...${NC}"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF
CREATE TABLE IF NOT EXISTS $APPLIED_MIGRATIONS_TABLE (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration_file VARCHAR(255) NOT NULL UNIQUE,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_file (migration_file)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
EOF
echo -e "${GREEN}✓ Sistema de migraciones listo${NC}"
echo ""

# Función para verificar si una migración ya fue aplicada
is_applied() {
    local file=$1
    local count=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -sN \
        -e "SELECT COUNT(*) FROM $APPLIED_MIGRATIONS_TABLE WHERE migration_file = '$file'")
    [ "$count" -gt 0 ]
}

# Función para registrar migración aplicada
register_migration() {
    local file=$1
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
        -e "INSERT INTO $APPLIED_MIGRATIONS_TABLE (migration_file) VALUES ('$file')"
}

# Buscar y aplicar migraciones pendientes
echo -e "${YELLOW}Buscando migraciones pendientes...${NC}"
echo ""

PENDING=0
APPLIED=0
ERRORS=0

for migration_file in $(ls -1 "$MIGRATIONS_DIR"/*.sql 2>/dev/null | sort); do
    filename=$(basename "$migration_file")
    
    if is_applied "$filename"; then
        echo -e "${BLUE}⊙${NC} $filename ${BLUE}(ya aplicada)${NC}"
        ((APPLIED++))
    else
        echo -e "${YELLOW}→${NC} Aplicando $filename..."
        
        if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$migration_file" 2>/dev/null; then
            register_migration "$filename"
            echo -e "${GREEN}✓${NC} $filename ${GREEN}aplicada exitosamente${NC}"
            ((PENDING++))
        else
            echo -e "${RED}✗${NC} $filename ${RED}ERROR al aplicar${NC}"
            ((ERRORS++))
        fi
        echo ""
    fi
done

# Resumen
echo ""
echo -e "${BLUE}=================================================="
echo "Resumen"
echo -e "==================================================${NC}"
echo -e "Migraciones ya aplicadas: ${BLUE}$APPLIED${NC}"
echo -e "Migraciones aplicadas ahora: ${GREEN}$PENDING${NC}"
echo -e "Errores: ${RED}$ERRORS${NC}"
echo ""

if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}✓ Todas las migraciones se aplicaron correctamente${NC}"
    exit 0
else
    echo -e "${RED}✗ Hubo errores al aplicar algunas migraciones${NC}"
    exit 1
fi
