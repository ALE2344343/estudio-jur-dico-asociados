<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estudio Jurídico & Asociados</title>
    <link rel="stylesheet" href="{{ asset('css/estudio.css') }}">
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    
    <!-- PWA / App Instalable -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0D1525">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">
    <script>
        const savedColor = localStorage.getItem('temaColor');
        if (savedColor) {
            document.documentElement.style.setProperty('--navy', savedColor);
            document.documentElement.style.setProperty('--gradient-navy', `linear-gradient(135deg, ${savedColor} 0%, #0D1525 100%)`);
        }
    </script>
</head>
<body>
<div class="page-inicio" style="background: url('{{ asset('img/fondo_login.jpg') }}') no-repeat center center fixed; background-size: cover; position: relative;">

    <div class="inicio-header animate-in" style="text-align: center; position:relative; z-index:1;">
        <img src="{{ asset('img/logo.png') }}" alt="Estudio Jurídico & Asociados" style="max-width: 380px; display: block; margin: 0 auto;">
    </div>

    <!-- Paneles de acceso -->
    <div class="panels-container animate-in" style="animation-delay:0.2s; position:relative; z-index:1;">

        <!-- Panel Administrador -->
        <div class="access-panel">
            <div class="panel-icon" style="text-align:center;">
                <img src="{{ asset('img/logo_admin.png') }}" alt="Administrador" style="width: 130px; height: auto; margin-bottom: 15px; filter: drop-shadow(0 8px 15px rgba(0,0,0,0.25));">
            </div>
            <div class="panel-title">ADMINISTRADOR</div>
            <a href="{{ route('login.admin') }}" class="btn-ingresar">INGRESAR</a>
        </div>

        <div class="center-statue">
            <!-- Espacio para mantener la separación en el medio -->
            <div style="width:170px;"></div>
        </div>

        <!-- Panel Abogado -->
        <div class="access-panel">
            <div class="panel-icon" style="text-align:center;">
                <img src="{{ asset('img/logo_abogado.png') }}" alt="Abogado" style="width: 130px; height: auto; margin-bottom: 15px; filter: drop-shadow(0 8px 15px rgba(0,0,0,0.25));">
            </div>
            <div class="panel-title">ABOGADO</div>
            <a href="{{ route('login.abogado') }}" class="btn-ingresar">INGRESAR</a>
        </div>
    </div>

    <p style="margin-top:40px;font-size:0.75rem;color:#FFFFFF;font-weight:bold;letter-spacing:2px;font-family:'Cinzel',serif; position:relative; z-index:1;">
    © {{ date('Y') }} ESTUDIO JURÍDICO & ASOCIADOS — TODOS LOS DERECHOS RESERVADOS
</p>
</div>
</body>
</html>
