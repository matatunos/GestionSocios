#!/bin/bash

# Script de instalación para GestionSocios v0.5 beta
# Autor: Nacho (matatunos)

echo "=========================================="
echo "  Instalación de GestionSocios v0.5 beta"
echo "=========================================="
echo ""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función para mostrar errores
error() {
    echo -e "${RED}ERROR: $1${NC}"
    exit 1
}

# Función para mostrar advertencias
warning() {
    echo -e "${YELLOW}ADVERTENCIA: $1${NC}"
}

# Función para mostrar éxito
success() {
    echo -e "${GREEN}✓ $1${NC}"
}

# Verificar que estamos en el directorio correcto
if [ ! -f "schema.sql" ]; then
    error "No se encuentra schema.sql. Asegúrate de ejecutar este script desde el directorio 'database'"
fi

# Solicitar datos de configuración
echo "Por favor, proporciona la siguiente información:"
echo ""

read -p "Nombre de la base de datos [asociacion_db]: " DB_NAME
DB_NAME=${DB_NAME:-asociacion_db}

read -p "Usuario MySQL [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -sp "Contraseña MySQL: " DB_PASS
echo ""

read -p "Host MySQL [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}

echo ""
echo "Configuración:"
echo "  Base de datos: $DB_NAME"
echo "  Usuario: $DB_USER"
echo "  Host: $DB_HOST"
echo ""

read -p "¿Es correcta esta configuración? (s/n): " CONFIRM
if [ "$CONFIRM" != "s" ] && [ "$CONFIRM" != "S" ]; then
    error "Instalación cancelada por el usuario"
fi

echo ""
echo "Iniciando instalación..."
echo ""

# Verificar que MySQL está disponible
if ! command -v mysql &> /dev/null; then
    error "MySQL no está instalado o no está en el PATH"
fi

# Probar conexión a MySQL
echo "Probando conexión a MySQL..."
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "SELECT 1;" &> /dev/null
if [ $? -ne 0 ]; then
    error "No se puede conectar a MySQL. Verifica las credenciales."
fi
success "Conexión a MySQL exitosa"

# Crear base de datos
echo "Creando base de datos '$DB_NAME'..."
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
if [ $? -eq 0 ]; then
    success "Base de datos creada"
else
    warning "La base de datos ya existe o hubo un error al crearla"
fi

# Importar schema
echo "Importando estructura de la base de datos..."
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < schema.sql
if [ $? -eq 0 ]; then
    success "Schema importado correctamente"
else
    error "Error al importar el schema"
fi

# Preguntar si quiere importar datos de ejemplo
echo ""
read -p "¿Deseas importar datos de ejemplo? (s/n): " IMPORT_SAMPLE
if [ "$IMPORT_SAMPLE" = "s" ] || [ "$IMPORT_SAMPLE" = "S" ]; then
    if [ -f "sample_data_large.sql" ]; then
        echo "Importando datos de ejemplo..."
        mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < sample_data_large.sql
        if [ $? -eq 0 ]; then
            success "Datos de ejemplo importados"
        else
            warning "Error al importar datos de ejemplo"
        fi
    else
        warning "No se encuentra el archivo sample_data_large.sql"
    fi
fi

# Crear archivo de configuración
echo ""
echo "Creando archivo de configuración..."
CONFIG_DIR="../src/Config"
CONFIG_FILE="$CONFIG_DIR/config.php"

# Crear directorio si no existe
mkdir -p "$CONFIG_DIR"

# Generar contraseña segura para el usuario de la BD (opcional)
read -p "¿Deseas crear un usuario específico para la aplicación? (s/n): " CREATE_USER
if [ "$CREATE_USER" = "s" ] || [ "$CREATE_USER" = "S" ]; then
    read -p "Nombre del usuario de la aplicación [gestion_user]: " APP_USER
    APP_USER=${APP_USER:-gestion_user}
    
    # Generar contraseña aleatoria
    APP_PASS=$(openssl rand -base64 16 2>/dev/null || cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 16 | head -n 1)
    
    echo "Creando usuario '$APP_USER'..."
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "CREATE USER IF NOT EXISTS '$APP_USER'@'localhost' IDENTIFIED BY '$APP_PASS';" 2>/dev/null
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$APP_USER'@'localhost';" 2>/dev/null
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "FLUSH PRIVILEGES;" 2>/dev/null
    
    if [ $? -eq 0 ]; then
        success "Usuario de aplicación creado"
        echo "  Usuario: $APP_USER"
        echo "  Contraseña: $APP_PASS"
        echo "  ⚠️  GUARDA ESTA CONTRASEÑA EN UN LUGAR SEGURO"
        
        # Usar las credenciales del nuevo usuario para el config
        FINAL_USER="$APP_USER"
        FINAL_PASS="$APP_PASS"
    else
        warning "Error al crear usuario, usando credenciales proporcionadas"
        FINAL_USER="$DB_USER"
        FINAL_PASS="$DB_PASS"
    fi
else
    FINAL_USER="$DB_USER"
    FINAL_PASS="$DB_PASS"
fi

# Crear config.php
cat > "$CONFIG_FILE" << EOF
<?php
// Configuración de la base de datos
// Generado automáticamente por install.sh

define('DB_HOST', '$DB_HOST');
define('DB_NAME', '$DB_NAME');
define('DB_USER', '$FINAL_USER');
define('DB_PASS', '$FINAL_PASS');
?>
EOF

if [ $? -eq 0 ]; then
    success "Archivo de configuración creado en $CONFIG_FILE"
else
    error "Error al crear archivo de configuración"
fi

# Crear directorios de uploads si no existen
echo ""
echo "Creando directorios de uploads..."
UPLOAD_DIRS=(
    "../public/uploads"
    "../public/uploads/members"
    "../public/uploads/donors"
    "../public/uploads/organization"
    "../public/uploads/receipts"
    "../public/uploads/documents"
)

for dir in "${UPLOAD_DIRS[@]}"; do
    mkdir -p "$dir"
    chmod 775 "$dir" 2>/dev/null
done
success "Directorios de uploads creados"

# Resumen final
echo ""
echo "=========================================="
echo "  ✓ Instalación completada exitosamente"
echo "=========================================="
echo ""
echo "Credenciales de acceso a la aplicación:"
echo "  Usuario: admin"
echo "  Contraseña: admin123"
echo ""
echo "⚠️  IMPORTANTE:"
echo "  1. Cambia la contraseña de 'admin' inmediatamente"
echo "  2. Configura los permisos de archivos según tu servidor web"
echo "  3. Configura tu servidor web (Apache/Nginx) para apuntar a la carpeta 'public'"
echo ""
echo "Base de datos:"
echo "  Nombre: $DB_NAME"
echo "  Usuario: $FINAL_USER"
echo "  Host: $DB_HOST"
echo ""
echo "Próximos pasos:"
echo "  1. Configura tu servidor web (ver README.md)"
echo "  2. Accede a la aplicación desde tu navegador"
echo "  3. Cambia la contraseña por defecto"
echo ""
echo "Para más información, consulta el archivo README.md"
echo ""
