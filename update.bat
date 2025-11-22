@echo off
cd /d c:\Users\nacho\Documents\GitHub\GestionSocios
git add .
git commit -m "feat: Auto-assign member_number as ID on creation and update existing members"
git push origin devel
echo.
echo Cambios enviados correctamente
pause
