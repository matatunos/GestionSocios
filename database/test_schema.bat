@echo off
REM Script para probar la importación del schema.sql
REM Asegúrate de tener MySQL instalado y configurado

echo ============================================
echo Prueba de importación de schema.sql
echo ============================================
echo.

REM Configuración - Ajusta estos valores según tu instalación
set MYSQL_PATH="C:\xampp\mysql\bin\mysql.exe"
set DB_NAME=gestion_socios_test
set DB_USER=root
set DB_PASS=

echo Paso 1: Eliminando base de datos de prueba si existe...
%MYSQL_PATH% -u%DB_USER% -p%DB_PASS% -e "DROP DATABASE IF EXISTS %DB_NAME%;"

echo Paso 2: Creando base de datos de prueba...
%MYSQL_PATH% -u%DB_USER% -p%DB_PASS% -e "CREATE DATABASE %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo Paso 3: Importando schema.sql...
%MYSQL_PATH% -u%DB_USER% -p%DB_PASS% %DB_NAME% < database\schema.sql

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ============================================
    echo ÉXITO: Schema importado correctamente
    echo ============================================
    echo.
    echo Verificando tablas creadas...
    %MYSQL_PATH% -u%DB_USER% -p%DB_PASS% %DB_NAME% -e "SHOW TABLES;"
    echo.
    echo Verificando foreign keys...
    %MYSQL_PATH% -u%DB_USER% -p%DB_PASS% %DB_NAME% -e "SELECT TABLE_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA='%DB_NAME%' AND REFERENCED_TABLE_NAME IS NOT NULL ORDER BY TABLE_NAME;"
) else (
    echo.
    echo ============================================
    echo ERROR: Falló la importación del schema
    echo ============================================
    echo Revisa los errores anteriores
)

echo.
pause
