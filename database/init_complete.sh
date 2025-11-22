#!/bin/bash
# Script para inicializar completamente la base de datos
# Uso: ./init_complete.sh

DB_NAME="asociacion_db"
DB_USER="root"

echo "Iniciando instalación completa de la base de datos..."
echo ""

# Schema principal
echo "1. Ejecutando schema principal..."
mysql -u $DB_USER -p $DB_NAME < schema.sql

# Migraciones en orden
echo "2. Ejecutando migración: add_deactivated_at..."
mysql -u $DB_USER -p $DB_NAME < migration_add_deactivated_at.sql 2>/dev/null || echo "   (Ya existe)"

echo "3. Ejecutando migración: add_donor_logo..."
mysql -u $DB_USER -p $DB_NAME < migration_add_donor_logo.sql 2>/dev/null || echo "   (Ya existe)"

echo "4. Ejecutando migración: fix_donations_schema..."
mysql -u $DB_USER -p $DB_NAME < migration_fix_donations_schema.sql 2>/dev/null || echo "   (Ya existe)"

echo "5. Ejecutando migración: events_donations..."
mysql -u $DB_USER -p $DB_NAME < migration_2025_11_21_events_donations.sql 2>/dev/null || echo "   (Ya existe)"

echo "6. Ejecutando migración: fiesta_book..."
mysql -u $DB_USER -p $DB_NAME < migration_2025_11_21_fiesta_book.sql 2>/dev/null || echo "   (Ya existe)"

echo "7. Ejecutando migración: ad_prices..."
mysql -u $DB_USER -p $DB_NAME < migration_ad_prices.sql 2>/dev/null || echo "   (Ya existe)"

echo "8. Ejecutando migración: member_image_history..."
mysql -u $DB_USER -p $DB_NAME < migration_member_image_history.sql 2>/dev/null || echo "   (Ya existe)"

echo "9. Ejecutando migración: donor_image_history..."
mysql -u $DB_USER -p $DB_NAME < migration_donor_image_history.sql 2>/dev/null || echo "   (Ya existe)"

echo ""
echo "✓ Instalación completa finalizada!"
echo ""
echo "Verificando tablas creadas:"
mysql -u $DB_USER -p $DB_NAME -e "SHOW TABLES;"
