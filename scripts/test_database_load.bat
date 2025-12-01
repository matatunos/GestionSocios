@echo off
REM Script para verificar la carga del esquema y datos de ejemplo
REM en la base de datos MySQL local

echo ============================================== 
echo   VERIFICACION DE CARGA DE BASE DE DATOS
echo ==============================================
echo.

set DB_HOST=localhost
set DB_USER=root
set DB_PASS=root
set DB_NAME=gestion_socios_test

echo [1/6] Eliminando base de datos de prueba si existe...
mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% -e "DROP DATABASE IF EXISTS %DB_NAME%;" 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: No se pudo conectar a MySQL
    echo Verifica que MySQL este ejecutandose y las credenciales sean correctas
    pause
    exit /b 1
)
echo OK - Base de datos eliminada

echo.
echo [2/6] Creando base de datos de prueba...
mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% -e "CREATE DATABASE %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: No se pudo crear la base de datos
    pause
    exit /b 1
)
echo OK - Base de datos creada

echo.
echo [3/6] Cargando esquema desde schema.sql...
mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% %DB_NAME% < ..\database\schema.sql
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Fallo al cargar el esquema
    pause
    exit /b 1
)
echo OK - Esquema cargado

echo.
echo [4/6] Verificando tablas creadas...
mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% %DB_NAME% -e "SHOW TABLES;" > temp_tables.txt
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: No se pudieron listar las tablas
    pause
    exit /b 1
)
type temp_tables.txt
del temp_tables.txt
echo OK - Tablas verificadas

echo.
echo [5/6] Cargando datos de ejemplo desde sample_data.sql...
mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% %DB_NAME% < ..\database\sample_data.sql
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Fallo al cargar los datos de ejemplo
    pause
    exit /b 1
)
echo OK - Datos de ejemplo cargados

echo.
echo [6/6] Verificando datos cargados...
echo.
echo Registros por tabla:
echo -------------------
mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% %DB_NAME% -e "SELECT 'organization_settings' as Tabla, COUNT(*) as Registros FROM organization_settings UNION ALL SELECT 'annual_fees', COUNT(*) FROM annual_fees UNION ALL SELECT 'ad_prices', COUNT(*) FROM ad_prices UNION ALL SELECT 'member_categories', COUNT(*) FROM member_categories UNION ALL SELECT 'expense_categories', COUNT(*) FROM expense_categories UNION ALL SELECT 'task_categories', COUNT(*) FROM task_categories UNION ALL SELECT 'members', COUNT(*) FROM members UNION ALL SELECT 'events', COUNT(*) FROM events UNION ALL SELECT 'event_attendance', COUNT(*) FROM event_attendance UNION ALL SELECT 'payments', COUNT(*) FROM payments UNION ALL SELECT 'donors', COUNT(*) FROM donors UNION ALL SELECT 'book_ads', COUNT(*) FROM book_ads UNION ALL SELECT 'donations', COUNT(*) FROM donations UNION ALL SELECT 'expenses', COUNT(*) FROM expenses UNION ALL SELECT 'tasks', COUNT(*) FROM tasks UNION ALL SELECT 'category_fee_history', COUNT(*) FROM category_fee_history;"

echo.
echo ==============================================
echo   VERIFICACION COMPLETADA EXITOSAMENTE
echo ==============================================
echo.
echo La base de datos de prueba '%DB_NAME%' ha sido creada
echo y los datos de ejemplo se han cargado correctamente.
echo.
echo Para eliminar la base de datos de prueba, ejecuta:
echo   mysql -u root -proot -e "DROP DATABASE %DB_NAME%;"
echo.
pause
