#!/bin/bash

# Script para aplicar todas las migraciones pendientes
# Ejecutar como: bash database/apply_all_migrations.sh

# Colores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}=== Aplicando Migraciones de Base de Datos ===${NC}"
echo ""

# Configuración de base de datos
DB_NAME="asociacion_db"
DB_USER="root"

# Solicitar contraseña una sola vez
read -sp "Contraseña de MySQL: " DB_PASS
echo ""
echo ""

# Lista de migraciones en orden
MIGRATIONS=(
    "migration_tasks.sql"
    "migration_organization_settings.sql"
)

# Contador
SUCCESS=0
FAILED=0

# Aplicar cada migración
for migration in "${MIGRATIONS[@]}"; do
    MIGRATION_PATH="/opt/GestionSocios/database/$migration"
    
    if [ -f "$MIGRATION_PATH" ]; then
        echo -e "${YELLOW}Aplicando: $migration${NC}"
        
        # Ejecutar migración
        if mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$MIGRATION_PATH" 2>/dev/null; then
            echo -e "${GREEN}✓ $migration aplicada correctamente${NC}"
            ((SUCCESS++))
        else
            echo -e "${RED}✗ Error al aplicar $migration${NC}"
            echo -e "${YELLOW}  (Puede que ya esté aplicada)${NC}"
            ((FAILED++))
        fi
    else
        echo -e "${RED}✗ Archivo no encontrado: $MIGRATION_PATH${NC}"
        ((FAILED++))
    fi
    echo ""
done

# Resumen
echo -e "${YELLOW}=== Resumen ===${NC}"
echo -e "${GREEN}Exitosas: $SUCCESS${NC}"
echo -e "${RED}Fallidas/Ya aplicadas: $FAILED${NC}"
echo ""

# Verificar tablas creadas
echo -e "${YELLOW}Verificando tablas en la base de datos:${NC}"
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;" 2>/dev/null | grep -E "(tasks|organization_settings)" || echo "No se encontraron las nuevas tablas"
echo ""

echo -e "${GREEN}¡Proceso completado!${NC}"
