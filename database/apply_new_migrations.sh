#!/bin/bash
# Script para aplicar migraciones de nuevas funcionalidades
# Ejecutar desde el directorio del proyecto

echo "=== Aplicando Migraciones GestionSocios ==="
echo ""

# Solicitar credenciales de BD
read -p "Host de BD [192.168.1.22]: " DB_HOST
DB_HOST=${DB_HOST:-192.168.1.22}

read -p "Usuario de BD [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -sp "Contraseña de BD: " DB_PASS
echo ""

read -p "Nombre de BD [asociacion_db]: " DB_NAME
DB_NAME=${DB_NAME:-asociacion_db}

echo ""
echo "Conectando a $DB_USER@$DB_HOST/$DB_NAME..."
echo ""

# Función para ejecutar migración
apply_migration() {
    local file=$1
    local description=$2
    
    echo ">> Aplicando: $description"
    if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$file" 2>/dev/null; then
        echo "   ✓ Completado"
    else
        echo "   ✗ Error (puede que ya esté aplicada)"
    fi
    echo ""
}

# Aplicar migraciones en orden
cd database

apply_migration "migration_member_categories.sql" "Categorías de Socios"
apply_migration "migration_roles_permissions.sql" "Sistema de Roles y Permisos"

echo "=== Migraciones Completadas ==="
echo ""
echo "Nuevas funcionalidades disponibles:"
echo "  - Categorías de Socios (Juvenil, Senior, Familiar, etc.)"
echo "  - Sistema de Roles (Admin, Tesorero, Secretario, Consulta)"
echo "  - Control de Permisos granular por módulo"
echo ""
echo "Accede a 'Categorías' en el menú lateral para configurar."
