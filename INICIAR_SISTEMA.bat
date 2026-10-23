@echo on
title Estudio Juridico - Arranque del Sistema

rem -------------------------------------------------------------------
rem Cambia al directorio del script
rem -------------------------------------------------------------------
cd /d "%~dp0"

rem -------------------------------------------------------------------
rem Mensaje de inicio
rem -------------------------------------------------------------------
echo ====================================================================
echo        ESTUDIO JURIDICO - ARRANQUE DEL SISTEMA
echo ====================================================================

rem -------------------------------------------------------------------
rem 1. Detectar ejecutable PHP
rem -------------------------------------------------------------------
set "PHP_CMD=php"
if exist "C:\nuevo xap\php\php.exe" (
    set "PHP_CMD=C:\nuevo xap\php\php.exe"
) else if exist "..\php.exe" (
    set "PHP_CMD=..\php.exe"
) else if exist "C:\xampp\php\php.exe" (
    set "PHP_CMD=C:\xampp\php\php.exe"
)

echo Usando ejecutable: %PHP_CMD%

rem -------------------------------------------------------------------
rem 2. Abrir puerto 8000 en el firewall (requiere ejecutar como Administrador)
rem -------------------------------------------------------------------
netsh advfirewall firewall add rule name="Laravel Bufete" dir=in action=allow protocol=TCP localport=8000 >nul 2>&1
if %errorlevel% neq 0 (
    echo [AVISO] No se pudo crear regla de firewall (¿se ejecuta como Administrador?)
) else (
    echo [OK] Regla de firewall para el puerto 8000 creada/confirmada.
)

rem -------------------------------------------------------------------
rem 3. Verificar que MySQL está corriendo
rem -------------------------------------------------------------------
%PHP_CMD% -r "try { $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', ''); $pdo->query('SELECT 1'); echo '[OK] MySQL está activo.' . PHP_EOL; } catch (Exception $e) { echo '[AVISO] MySQL no responde. Inicie MySQL en XAMPP antes de continuar.' . PHP_EOL; exit(1); }"
if %errorlevel% neq 0 (
    pause
    exit /b 1
)

rem -------------------------------------------------------------------
rem 4. Crear .env si no existe
rem -------------------------------------------------------------------
if not exist ".env" (
    echo Creando archivo .env inicial...
    copy ".env.example" ".env" >nul
    "%PHP_CMD%" artisan key:generate --force >nul
)

rem -------------------------------------------------------------------
rem 5. Crear base de datos si no existe
rem -------------------------------------------------------------------
%PHP_CMD% -r "try { $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', ''); $pdo->exec('CREATE DATABASE IF NOT EXISTS bufete_abogados CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'); echo '[OK] Base de datos lista en MySQL.' . PHP_EOL; } catch (Exception $e) { echo '[AVISO] No se pudo crear/validar la base de datos.' . PHP_EOL; }"

rem -------------------------------------------------------------------
rem 6. Ejecutar migraciones
rem -------------------------------------------------------------------
echo Verificando tablas del sistema...
"%PHP_CMD%" artisan migrate --force

rem -------------------------------------------------------------------
rem 6.5 Instalar dependencias npm y compilar assets (si existen)
rem -------------------------------------------------------------------
if exist "package.json" (
    echo Instalando dependencias npm...
    npm install
    if %errorlevel% neq 0 (
        echo [AVISO] npm install falló. Verifique la instalación de Node.js.
    ) else (
        echo Compilando assets con npm run dev...
        npm run dev
        if %errorlevel% neq 0 (
            echo [AVISO] npm run dev falló.
        )
    )
)

rem -------------------------------------------------------------------
rem 7. Crear enlace de storage
rem -------------------------------------------------------------------
"%PHP_CMD%" artisan storage:link >nul 2>&1

rem -------------------------------------------------------------------
rem 8. Abrir navegador local
rem -------------------------------------------------------------------
start "" http://127.0.0.1:8000

rem -------------------------------------------------------------------
rem 9. Iniciar servidor Laravel escuchando en todas las interfaces
rem -------------------------------------------------------------------
echo.
echo ====================================================================
echo    SERVIDOR INICIADO EN: http://127.0.0.1:8000
echo    Acceda desde otro dispositivo usando la IP LAN del equipo.
echo ====================================================================
echo.
"%PHP_CMD%" artisan serve --host=0.0.0.0 --port=8000

pause