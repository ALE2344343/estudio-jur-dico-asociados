@echo off
title Instalar Servicio de Alertas Telegram
echo ====================================================================
echo        INSTALADOR DE SERVICIO AUTOMATICO DE ALERTAS
echo ====================================================================
echo.
echo Se creara una Tarea Programada de Windows para chequear las alertas
echo cada minuto y enviarlas directamente a Telegram aunque la PC este
echo con la pantalla bloqueada o el navegador cerrado.
echo.

schtasks /create /tn "BufeteAlertasTelegram" /tr "\"C:\nuevo xap\php\php.exe\" \"c:\nuevo xap\php\proyectox\artisan\" alertas:despachar" /sc minute /mo 1 /f

if %errorlevel% equ 0 (
    echo.
    echo [EXITO] Tarea programada instalada correctamente.
    echo A partir de ahora tus alertas llegaran a tu celular a la hora exacta.
) else (
    echo.
    echo [AVISO] Si fallo, por favor haz clic derecho sobre este archivo y elige:
    echo "Ejecutar como administrador".
)

echo.
pause
