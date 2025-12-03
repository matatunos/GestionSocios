@echo off
REM Script para cargar datos de ejemplo LARGE en la base de datos

echo ============================================== 
echo   CARGA DE DATOS DE EJEMPLO LARGE
echo ==============================================
echo.

set DB_HOST=localhost
set DB_USER=root
set DB_PASS=root
set DB_NAME=gestion_socios

echo ADVERTENCIA: Este script cargara datos de ejemplo masivos en la base de datos.
echo.
echo Configuracion:
echo   - 500 socios
echo   - 50 donantes
echo   - 30 eventos
echo   - 1500 pagos
echo   - 800 asistencias
echo   - 200 gastos
echo   - 50 tareas
echo.
echo Base de datos: %DB_NAME%
echo.
set /p CONFIRM="Deseas continuar? (S/N): "

if /i not "%CONFIRM%"=="S" (
    echo Operacion cancelada.
    pause
    exit /b 0
)

echo.
echo [1/2] Cargando datos de ejemplo...
mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% %DB_NAME% < ..\database\sample_data_large.sql
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Fallo al cargar los datos de ejemplo
    pause
    exit /b 1
)
echo OK - Datos cargados

echo.
echo [2/2] Verificando datos cargados...
echo.
mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% %DB_NAME% -e "SELECT 'members' as Tabla, COUNT(*) as Registros FROM members UNION ALL SELECT 'donors', COUNT(*) FROM donors UNION ALL SELECT 'events', COUNT(*) FROM events UNION ALL SELECT 'payments', COUNT(*) FROM payments UNION ALL SELECT 'event_attendance', COUNT(*) FROM event_attendance UNION ALL SELECT 'expenses', COUNT(*) FROM expenses UNION ALL SELECT 'tasks', COUNT(*) FROM tasks UNION ALL SELECT 'donations', COUNT(*) FROM donations UNION ALL SELECT 'book_ads', COUNT(*) FROM book_ads;"

echo.
echo ==============================================
echo   DATOS CARGADOS EXITOSAMENTE
echo ==============================================
echo.
pause
