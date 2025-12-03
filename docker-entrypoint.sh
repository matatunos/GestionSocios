#!/bin/bash
set -e

# Esperar a que la base de datos esté lista
echo "Esperando a que la base de datos esté lista..."
until mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1" >/dev/null 2>&1; do
    echo "Base de datos no disponible, esperando..."
    sleep 3
done

echo "Base de datos lista!"

# Crear archivo de configuración si no existe
if [ ! -f /var/www/html/src/Config/config.php ]; then
    echo "Creando archivo de configuración..."
    cat > /var/www/html/src/Config/config.php <<EOF
<?php
// Configuración de la base de datos
define('DB_HOST', '${DB_HOST}');
define('DB_NAME', '${DB_NAME}');
define('DB_USER', '${DB_USER}');
define('DB_PASS', '${DB_PASS}');

// Configuración de la aplicación
define('APP_NAME', 'Gestión de Socios');
define('APP_URL', 'http://localhost:8080');

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Errores (en producción cambiar a 0)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
EOF
    echo "Archivo de configuración creado!"
fi

# Verificar permisos
chown -R www-data:www-data /var/www/html/public/uploads
chown -R www-data:www-data /var/www/html/src/Config
chmod -R 775 /var/www/html/public/uploads
chmod -R 775 /var/www/html/src/Config

echo "Iniciando Apache..."
exec apache2-foreground
