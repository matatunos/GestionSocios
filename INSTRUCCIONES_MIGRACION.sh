#!/bin/bash
# ============================================
# INSTRUCCIONES PARA APLICAR LA MIGRACIÓN
# ============================================
# 
# Este script contiene los comandos que debes ejecutar en el servidor
# de producción para solucionar el error de columnas faltantes.
#
# IMPORTANTE: Ejecuta estos comandos en el servidor de producción
# ============================================

# 1. Conectarse al servidor de producción
# ssh usuario@servidor

# 2. Ir al directorio del proyecto
cd /opt/GestionSocios

# 3. Hacer pull de los últimos cambios de la rama devel
git pull origin devel

# 4. Ir al directorio de migraciones
cd database/migrations

# 5. OPCIÓN A: Aplicar la migración usando el script bash
chmod +x apply_migration.sh
./apply_migration.sh

# 5. OPCIÓN B: Aplicar la migración manualmente
# mysql -u root -p gestion_socios < add_image_history_columns.sql

# 6. Verificar que las columnas se hayan añadido correctamente
# mysql -u root -p gestion_socios -e "DESCRIBE donor_image_history;"
# mysql -u root -p gestion_socios -e "DESCRIBE member_image_history;"

# 7. Verificar los logs de Apache para confirmar que el error ya no aparece
# tail -f /var/log/apache2/GestionSocios.log

# ============================================
# COMANDOS RESUMIDOS (copia y pega en el servidor)
# ============================================

# cd /opt/GestionSocios
# git pull origin devel
# mysql -u root -p gestion_socios < database/migrations/add_image_history_columns.sql

# ============================================
# FIN
# ============================================
