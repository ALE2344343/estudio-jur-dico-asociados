@echo off
title Notificador en Segundo Plano - Estudio Juridico
color 0b
echo ====================================================================
echo    ESTUDIO JURIDICO - MOTOR DE ALERTAS CONTINUAS PARA TELEGRAM
echo ====================================================================
echo.
echo Este proceso se ejecuta en segundo plano.
echo Revisa cada minuto tus audiencias y citas para enviarlas a Telegram
echo aunque nadie tenga el navegador web abierto.
echo.
echo Para minimizar esta ventana, simplemente dejala abierta o minimizada.
echo ====================================================================
echo.

set "PHP_CMD=C:\nuevo xap\php\php.exe"
if not exist "%PHP_CMD%" (
    set "PHP_CMD=php"
)

:loop
"%PHP_CMD%" "c:\nuevo xap\php\proyectox\artisan" alertas:despachar
timeout /t 60 /nobreak >nul
goto loop
