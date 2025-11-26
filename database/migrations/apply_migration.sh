#!/bin/bash
# Script para aplicar la migración de columnas de historial de imágenes
# Fecha: 2025-11-26

echo "Aplicando migración: add_image_history_columns.sql"
echo "=========================================="

# Configuración de la base de datos
DB_NAME="gestion_socios"
DB_USER="root"

# Ejecutar la migración
mysql -u $DB_USER -p $DB_NAME < add_image_history_columns.sql

if [ $? -eq 0 ]; then
    echo "✓ Migración aplicada exitosamente"
else
    echo "✗ Error al aplicar la migración"
    exit 1
fi

echo "=========================================="
echo "Verificando estructura de las tablas..."

# Verificar estructura de donor_image_history
echo ""
echo "Estructura de donor_image_history:"
mysql -u $DB_USER -p $DB_NAME -e "DESCRIBE donor_image_history;"

# Verificar estructura de member_image_history
echo ""
echo "Estructura de member_image_history:"
mysql -u $DB_USER -p $DB_NAME -e "DESCRIBE member_image_history;"

echo ""
echo "Migración completada."
