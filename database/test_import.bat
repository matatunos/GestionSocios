@echo off
REM Script para probar la importación completa (schema + sample_data)
REM Asegúrate de tener MySQL instalado y configurado

echo ============================================
echo Prueba de importación completa
echo ============================================
echo.

REM Configuración - Ajusta estos valores según tu instalación
set MYSQL_PATH="C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe"
set DB_NAME=gestion_socios_test
set DB_USER=root
set DB_PASS=root

echo Paso 1: Eliminando base de datos de prueba si existe...
%MYSQL_PATH% -u%DB_USER% -p%DB_PASS% -e "DROP DATABASE IF EXISTS %DB_NAME%;"

echo Paso 2: Creando base de datos de prueba...
%MYSQL_PATH% -u%DB_USER% -p%DB_PASS% -e "CREATE DATABASE %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo Paso 3: Importando schema.sql...
%MYSQL_PATH% -u%DB_USER% -p%DB_PASS% %DB_NAME% < database\schema.sql

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ============================================
    echo ERROR: Falló la importación del schema
    echo ============================================
    pause
    exit /b 1
)

echo Paso 4: Importando sample_data.sql...
%MYSQL_PATH% -u%DB_USER% -p%DB_PASS% %DB_NAME% < database\sample_data.sql

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ============================================
    echo ÉXITO: Datos importados correctamente
    echo ============================================
    echo.
    echo Verificando datos insertados...
    echo.
    echo --- Tablas y registros ---
    %MYSQL_PATH% -u%DB_USER% -p%DB_PASS% %DB_NAME% -e "SELECT 'member_categories' as tabla, COUNT(*) as registros FROM member_categories UNION ALL SELECT 'members', COUNT(*) FROM members UNION ALL SELECT 'events', COUNT(*) FROM events UNION ALL SELECT 'event_attendance', COUNT(*) FROM event_attendance UNION ALL SELECT 'donors', COUNT(*) FROM donors UNION ALL SELECT 'payments', COUNT(*) FROM payments UNION ALL SELECT 'expenses', COUNT(*) FROM expenses UNION ALL SELECT 'tasks', COUNT(*) FROM tasks;"
    echo.
    echo --- Usuario admin ---
    %MYSQL_PATH% -u%DB_USER% -p%DB_PASS% %DB_NAME% -e "SELECT id, email, name, role, status FROM users WHERE role='admin';"
) else (
    echo.
    echo ============================================
    echo ERROR: Falló la importación de sample_data
    echo ============================================
    echo Revisa los errores anteriores
)

echo.
pause
