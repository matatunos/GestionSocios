#!/bin/bash
# ============================================================================
# Script de Instalación v1.0 - Sistema de Gestión de Asociación
# ============================================================================
# Este script instala la base de datos completa de una sola vez
# No requiere ejecutar migraciones adicionales
# ============================================================================

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}============================================================================${NC}"
echo -e "${GREEN}  Sistema de Gestión de Asociación - Instalación v1.0${NC}"
echo -e "${GREEN}============================================================================${NC}"
echo ""

# Solicitar credenciales
read -p "Nombre de la base de datos [asociacion_db]: " DB_NAME
DB_NAME=${DB_NAME:-asociacion_db}

read -p "Usuario MySQL [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -sp "Contraseña MySQL: " DB_PASS
echo ""

read -p "Host MySQL [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}

echo ""
echo -e "${YELLOW}Configuración:${NC}"
echo "  Base de datos: $DB_NAME"
echo "  Usuario: $DB_USER"
echo "  Host: $DB_HOST"
echo ""

# Verificar conexión
echo -e "${YELLOW}Verificando conexión a MySQL...${NC}"
if ! mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1" &>/dev/null; then
    echo -e "${RED}✗ Error: No se pudo conectar a MySQL${NC}"
    echo "  Verifica las credenciales e intenta de nuevo"
    exit 1
fi
echo -e "${GREEN}✓ Conexión exitosa${NC}"
echo ""

# Crear base de datos si no existe
echo -e "${YELLOW}Creando base de datos '$DB_NAME'...${NC}"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Base de datos creada/verificada${NC}"
else
    echo -e "${RED}✗ Error al crear la base de datos${NC}"
    exit 1
fi
echo ""

# Importar schema
echo -e "${YELLOW}Instalando schema v1.0...${NC}"
if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "schema_v1.0.sql" 2>/dev/null; then
    echo -e "${GREEN}✓ Schema instalado correctamente${NC}"
else
    echo -e "${RED}✗ Error al instalar el schema${NC}"
    exit 1
fi
echo ""

# Verificar instalación
echo -e "${YELLOW}Verificando instalación...${NC}"
TABLE_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -sse "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME';" 2>/dev/null)
if [ "$TABLE_COUNT" -gt 20 ]; then
    echo -e "${GREEN}✓ Instalación verificada: $TABLE_COUNT tablas creadas${NC}"
else
    echo -e "${RED}✗ Advertencia: Solo se encontraron $TABLE_COUNT tablas${NC}"
fi
echo ""

# Mostrar tablas creadas
echo -e "${YELLOW}Tablas creadas:${NC}"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;" 2>/dev/null | tail -n +2 | sed 's/^/  - /'
echo ""

echo -e "${GREEN}============================================================================${NC}"
echo -e "${GREEN}  ¡Instalación completada exitosamente!${NC}"
echo -e "${GREEN}============================================================================${NC}"
echo ""
echo -e "${YELLOW}Credenciales por defecto:${NC}"
echo "  Usuario: admin"
echo "  Contraseña: admin123"
echo ""
echo -e "${YELLOW}⚠️  IMPORTANTE: Cambia la contraseña por defecto desde Configuración → Seguridad${NC}"
echo ""
echo "Accede al sistema en: http://tu-servidor/index.php"
echo ""
