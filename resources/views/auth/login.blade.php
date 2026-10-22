<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $rol === 'administrador' ? 'Administrador' : 'Abogado' }} — Estudio Jurídico</title>
    <link rel="stylesheet" href="{{ asset('css/estudio.css') }}">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'></text></svg>">
    <script>
        const savedColor = localStorage.getItem('temaColor');
        if (savedColor) {
            document.documentElement.style.setProperty('--navy', savedColor);
            document.documentElement.style.setProperty('--gradient-navy', `linear-gradient(135deg, ${savedColor} 0%, #0D1525 100%)`);
        }
    </script>
</head>
<body>
<div class="page-login" style="background: url('/img/fondo_login.jpg') no-repeat center center fixed; background-size: cover; position: relative;">

    <!-- Header con logo -->
    <div class="login-header animate-in" style="text-align: center; position:relative; z-index:1; padding-top: 20px;">
        @if($rol === 'administrador')
            <img src="{{ asset('img/logo_admin.png') }}" alt="Logo Administrador" style="max-width: 250px; display: block; margin: 0 auto; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.3));">
        @else
            <img src="{{ asset('img/logo_abogado.png') }}" alt="Logo Abogado" style="max-width: 250px; display: block; margin: 0 auto; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.3));">
        @endif
    </div>

    <!-- Contenedor del formulario -->
    <div class="login-statue-container animate-in" style="animation-delay:0.2s; position:relative; z-index:1;">

        <!-- Formulario de login -->
        <div class="login-form-card">
            <div class="login-role-title">
                {{ $rol === 'administrador' ? 'ADMINISTRADOR' : 'ABOGADO' }}
            </div>

            @if(session('error'))
            <div class="error-msg">{{ session('error') }}</div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <input type="hidden" name="rol" value="{{ $rol }}">

                <div class="login-field">
                    <label>USUARIO:</label>
                    <input type="text" name="username" value="{{ old('username') }}"
                           placeholder="Ingrese su usuario" required autocomplete="username">
                </div>

                <div class="login-field">
                    <label>CONTRASEÑA:</label>
                    <div style="position:relative;display:flex;align-items:center;">
                        <input type="password" id="passwordInput" name="password" style="padding-right:42px;width:100%;"
                               placeholder="Ingrese su contraseña" required autocomplete="current-password">
                        <button type="button" id="togglePasswordBtn" onclick="togglePasswordVisibility()" title="Mostrar u ocultar contraseña"
                                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:var(--navy-dark);display:flex;align-items:center;justify-content:center;opacity:0.75;transition:opacity 0.2s;"
                                onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.75'">
                            <!-- Icono Ojo (Mostrar) -->
                            <svg id="eyeIconOpen" viewBox="0 0 24 24" style="width:20px;height:20px;fill:currentColor;">
                                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            </svg>
                            <!-- Icono Ojo Tachado (Ocultar) -->
                            <svg id="eyeIconClosed" viewBox="0 0 24 24" style="width:20px;height:20px;fill:currentColor;display:none;">
                                <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.44-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">INGRESAR</button>
            </form>

            <div class="login-back">
                <a href="{{ route('inicio') }}">← Volver al inicio</a>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const input = document.getElementById('passwordInput');
        const eyeOpen = document.getElementById('eyeIconOpen');
        const eyeClosed = document.getElementById('eyeIconClosed');
        
        if (input.type === 'password') {
            input.type = 'text';
            eyeOpen.style.display = 'none';
            eyeClosed.style.display = 'block';
        } else {
            input.type = 'password';
            eyeOpen.style.display = 'block';
            eyeClosed.style.display = 'none';
        }
    }
</script>
</body>
</html>
