@echo off
REM ================================================
REM  Sistema Consumo - instalador para Windows
REM  Ejecuta este archivo con doble clic o desde CMD
REM ================================================
setlocal
cd /d "%~dp0"

echo [1/5] Verificando PHP...
php -v >nul 2>&1
if errorlevel 1 (
  echo ERROR: PHP no esta en el PATH.
  echo Agrega C:\xampp\php al PATH o usa: C:\xampp\php\php.exe
  pause
  exit /b 1
)

echo [2/5] Instalando dependencias con Composer...
composer install --no-interaction
if errorlevel 1 (
  echo Reintentando ignorando avisos de seguridad...
  composer install --no-interaction --ignore-platform-reqs
)

echo [3/5] Preparando archivo .env...
if not exist ".env" copy ".env.example" ".env" >nul

echo [4/5] Generando llave y base de datos...
if not exist "database\database.sqlite" type nul > "database\database.sqlite"
php artisan key:generate
php artisan migrate --seed --force

echo [5/5] Iniciando servidor en http://localhost:8000
php artisan serve
pause
