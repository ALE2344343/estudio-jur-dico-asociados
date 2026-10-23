<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — Estudio Jurídico & Asociados</title>
    <link rel="stylesheet" href="{{ asset('css/estudio.css') }}">
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    
    <!-- PWA / Modo Instalable en PC y Celular -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0D1525">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Estudio Jurídico">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') { document.documentElement.classList.add('dark-mode-active'); }
        const savedColor = localStorage.getItem('temaColor');
        if (savedColor) {
            document.documentElement.style.setProperty('--navy', savedColor);
            document.documentElement.style.setProperty('--gradient-navy', `linear-gradient(135deg, ${savedColor} 0%, #0D1525 100%)`);
        }
    </script>
    @stack('styles')
</head>
<body>
<div class="dashboard-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header" style="text-align: center; padding: 20px 10px;">
            <img src="{{ asset('img/logo.png') }}" alt="Estudio Jurídico & Asociados" style="max-width: 170px; display: block; margin: 0 auto 15px;">
            <div class="sidebar-role">
                {{ session('usuario_rol') === 'administrador' ? 'Administrador' : 'Abogado' }}
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-title">MENÚ PRINCIPAL</div>

            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('clientes.index') }}"
               class="nav-item {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                </svg>
                Clientes
            </a>

            <a href="{{ route('casos.index') }}"
               class="nav-item {{ request()->routeIs('casos.*') ? 'active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M20 6h-2.18c.07-.44.18-.88.18-1.34C18 2.54 15.8.5 13 .5c-1.72 0-3.24.84-4.17 2.12L8 4 7.17 2.62C6.24 1.34 4.72.5 3 .5.2.5-2 2.54-2 4.66c0 .46.11.9.18 1.34H-4v2h2v12h16V8h2V6zm-7.83-3.37c.51-.68 1.3-1.13 2.2-1.13 1.46 0 2.63 1.17 2.63 2.5 0 .46-.14.88-.36 1.26-.04.07-.08.13-.12.2-.1.17-.22.32-.35.45A2.6 2.6 0 0112.7 6H11L12.17 2.63zm-4.34 0L10 6H8.3a2.6 2.6 0 01-1.47-.55c-.13-.13-.25-.28-.35-.45-.04-.07-.08-.13-.12-.2A2.47 2.47 0 016 3.16C6 1.83 7.17.66 8.63.66c.9 0 1.69.45 2.2 1.13zM16 18H6V8h10v10z"/>
                </svg>
                Casos
            </a>

            <a href="{{ route('documentos.index') }}"
               class="nav-item {{ request()->routeIs('documentos.*') ? 'active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                </svg>
                Documentos
            </a>

            @if(session('usuario_rol') !== 'administrador')
            <a href="{{ route('calculadora.index') }}"
               class="nav-item {{ request()->routeIs('calculadora.*') ? 'active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14h-2v-4H8v-2h4V7h2v4h4v2h-4v4z"/>
                </svg>
                Procesos Laborales
            </a>
            @endif

            @if(session('usuario_rol') === 'administrador')
            <a href="{{ route('gestion.index') }}"
               class="nav-item {{ request()->routeIs('gestion.*') ? 'active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm6 12H6v-1.4c0-2 4-3.1 6-3.1s6 1.1 6 3.1V18z"/>
                </svg>
                Control y Asignaciones
            </a>
            @endif

            <a href="{{ route('leyes.index') }}"
               class="nav-item {{ request()->routeIs('leyes.*') ? 'active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.5 2C6.81 2 3 5.81 3 10.5S6.81 19 11.5 19h.5v3c4.86-2.34 8-7 8-11.5C20 5.81 16.19 2 11.5 2zm1 14.5h-2v-2h2v2zm0-4h-2c0-3.25 3-3 3-5 0-1.1-.9-2-2-2s-2 .9-2 2h-2c0-2.21 1.79-4 4-4s4 1.79 4 4c0 2.5-3 2.75-3 5z"/>
                </svg>
                Leyes
            </a>

            @if(session('usuario_rol') === 'administrador')
            <div class="nav-section-title" style="margin-top:10px;">ADMINISTRACIÓN</div>
            <a href="{{ route('usuarios.index') }}"
               class="nav-item {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                Usuarios
            </a>
            <a href="{{ route('historial.index') }}"
               class="nav-item {{ request()->routeIs('historial.*') ? 'active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M13 3a9 9 0 0 0-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42A8.954 8.954 0 0 0 13 21a9 9 0 0 0 0-18zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
                </svg>
                Historial de Cambios
            </a>
            @endif
        </nav>

        <!-- Footer del sidebar -->
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">{{ session('usuario_inicial', 'US') }}</div>
                <div class="user-details">
                    <div class="user-name">{{ session('usuario_nombre', 'Usuario') }}</div>
                    <div class="user-role-label">{{ ucfirst(session('usuario_rol', 'usuario')) }}</div>
                </div>
            </div>
            <a href="{{ route('logout') }}" class="btn-logout"
               onclick="return confirm('¿Desea cerrar sesión?')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                </svg>
                Cerrar Sesión
            </a>
        </div>
    </aside>

    <!-- SIDEBAR OVERLAY FOR MOBILE -->
    <div id="sidebarOverlay" onclick="toggleSidebarMobile()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:95;backdrop-filter:blur(2px);transition:all 0.3s ease;"></div>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <!-- Top bar -->
        <div class="top-bar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button type="button" class="btn-toggle-sidebar" onclick="toggleSidebarMobile()" title="Abrir/Cerrar Menú"
                        style="display:none;background:none;border:none;cursor:pointer;padding:6px;color:var(--navy);border-radius:6px;">
                    <svg viewBox="0 0 24 24" style="width:24px;height:24px;fill:currentColor;">
                        <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
                    </svg>
                </button>
                <div class="page-title">
                    <span></span> @yield('page-title', 'Dashboard')
                </div>
            </div>
            <div class="top-bar-right" style="display:flex;align-items:center;gap:15px;flex-wrap:wrap;">
                @yield('top-bar-actions')

                <!-- Campana de Notificaciones y Recordatorios de Escritorio -->
                <div id="contenedorCampanaNotificaciones" style="position:relative;">
                    <button type="button" onclick="toggleCampanaNotificaciones(event)" title="Recordatorios y Notificaciones"
                            style="background:white; border:1px solid rgba(201,168,76,0.3); border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; cursor:pointer; position:relative; box-shadow:0 2px 6px rgba(0,0,0,0.06); transition:all 0.2s;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="#C9A84C">
                            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"/>
                        </svg>
                        <span id="campanaNotificacionesBadge" style="display:none; position:absolute; top:-4px; right:-4px; background:#e02424; color:white; font-size:0.65rem; font-weight:bold; border-radius:10px; min-width:18px; height:18px; padding:0 4px; align-items:center; justify-content:center; border:2px solid white;">0</span>
                    </button>

                    <!-- Dropdown de Notificaciones -->
                    <div id="dropdownCampanaNotificaciones" style="display:none; position:absolute; right:0; top:45px; width:340px; background:white; border:1px solid rgba(201,168,76,0.3); border-radius:10px; box-shadow:0 12px 30px rgba(0,0,0,0.18); z-index:9999; overflow:hidden;">
                        <div style="background:var(--navy); color:white; padding:12px 16px; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-family:'Cinzel',serif; font-size:0.85rem; font-weight:600; color:var(--gold);">Agenda y Notificaciones</span>
                            <span id="badgeEstadoNotificaciones" style="font-size:0.7rem;"></span>
                        </div>
                        <div style="padding:10px 14px; background:#fbfbfb; border-bottom:1px solid #eee; display:flex; flex-direction:column; gap:8px;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:0.75rem; color:#666;">Alertas en escritorio:</span>
                                <button id="btnPermisoNotificaciones" type="button" style="font-size:0.72rem; padding:4px 10px; border-radius:12px; border:1px solid #C9A84C; background:var(--gradient-gold); color:var(--navy-dark); font-weight:600; cursor:pointer;">
                                    Probar Notificación
                                </button>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding-top:4px; border-top:1px dashed #e5e5e5;">
                                <label style="font-size:0.75rem; color:#555; font-weight:600;">Recordarme con:</label>
                                <select id="selectAnticipacionGlobal" onchange="cambiarAnticipacionGlobal(this.value)" style="font-size:0.73rem; padding:3px 6px; border-radius:6px; border:1px solid #ccc; background:white; font-family:inherit;">
                                    <option value="5">5 minutos antes</option>
                                    <option value="10">10 minutos antes</option>
                                    <option value="15">15 minutos antes</option>
                                    <option value="30">30 minutos antes</option>
                                    <option value="60">1 hora antes</option>
                                    <option value="0">A la hora exacta</option>
                                </select>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding-top:6px; border-top:1px dashed #e5e5e5;">
                                <span style="font-size:0.74rem; color:#0D1525; font-weight:600;">Telegram (@Bethg_bot):</span>
                                <button type="button" onclick="abrirModalTelegramGmail()" style="font-size:0.72rem; padding:4px 10px; border-radius:12px; border:1px solid #0D1525; background:#0D1525; color:#C9A84C; font-weight:600; cursor:pointer;">
                                    Configurar Telegram
                                </button>
                            </div>
                        </div>
                        <div id="listaAlertasDropdown" style="max-height:280px; overflow-y:auto;">
                            <div style="padding:15px; text-align:center; color:#888; font-size:0.85rem;">
                                Cargando agenda de hoy...
                            </div>
                        </div>
                        <div style="padding:8px 12px; background:#f9f9f9; text-align:center; border-top:1px solid #eee;">
                            <a href="{{ route('dashboard') }}" style="font-size:0.75rem; color:var(--navy); text-decoration:none; font-weight:600;">Ver Calendario Completo &rarr;</a>
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:10px;align-items:center;">
                    <button id="btnInstalarApp" style="display:none;background:var(--gradient-gold);color:var(--navy-dark);border:none;border-radius:20px;padding:6px 14px;font-size:0.75rem;font-weight:700;cursor:pointer;box-shadow:0 2px 6px rgba(201,168,76,0.3);align-items:center;gap:6px;" title="Instalar como aplicación en este dispositivo">
                        📲 Instalar App
                    </button>
                    <button onclick="cambiarColorTema('#2a4365')" style="width:24px;height:24px;border-radius:50%;background:#2a4365;border:2px solid #fff;box-shadow:0 0 4px rgba(0,0,0,0.3);cursor:pointer;" title="Tema Celeste"></button>
                    <button onclick="cambiarColorTema('#1b4332')" style="width:24px;height:24px;border-radius:50%;background:#1b4332;border:2px solid #fff;box-shadow:0 0 4px rgba(0,0,0,0.3);cursor:pointer;" title="Tema Verde Oscuro"></button>
                </div>

                <label class="theme-switch" for="theme-checkbox" title="Cambiar modo oscuro/claro">
                    <input type="checkbox" id="theme-checkbox" onchange="toggleTheme()">
                    <div class="slider">
                        <span class="sun">☀️</span>
                        <span class="moon">🌙</span>
                    </div>
                </label>

                <div style="font-size:0.78rem;color:var(--text-gray);">
                    {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </div>
            </div>
        </div>

        <!-- Alertas globales -->
        <div style="padding: 0 30px;">
            @if(session('success'))
            <div class="alert alert-success animate-in">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-error animate-in">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                {{ session('error') }}
            </div>
            @endif
            @if(isset($errors) && $errors->any())
            <div class="alert alert-error animate-in" style="background:#fde8e8;border-left:4px solid #e02424;color:#9b1c1c;padding:12px 16px;border-radius:6px;margin-bottom:15px;">
                <div style="font-weight:600;margin-bottom:4px;display:flex;align-items:center;gap:6px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    Por favor verifique los siguientes campos:
                </div>
                <ul style="margin:0;padding-left:22px;font-size:0.85rem;">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <!-- Content -->
        <div class="content-area">
            @yield('content')
        </div>
    </main>
</div>

<!-- MODAL DE CONFIGURACIÓN Y PRUEBA DE NOTIFICACIONES POR TELEGRAM (@Bethg_bot) -->
<div id="modalTelegramGmail" style="display:none;position:fixed;inset:0;background:rgba(13,21,37,0.65);z-index:99999;align-items:center;justify-content:center;padding:15px;backdrop-filter:blur(3px);">
    <div style="background:white;border-radius:12px;max-width:560px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 50px rgba(0,0,0,0.4);border:1px solid rgba(201,168,76,0.4);">
        <div style="background:var(--gradient-navy);color:white;padding:18px 24px;display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid var(--gold);">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:1.3rem;"></span>
                <div>
                    <h3 style="margin:0;font-family:'Cinzel',serif;font-size:1rem;color:var(--gold);">NOTIFICACIONES A TELEGRAM (@Bethg_bot)</h3>
                    <div style="font-size:0.75rem;color:rgba(255,255,255,0.75);">Recibe alertas instantáneas de tus anotaciones, audiencias y plazos en tu Telegram</div>
                </div>
            </div>
            <button type="button" onclick="cerrarModalTelegramGmail()" style="background:none;border:none;color:var(--gold);font-size:1.5rem;cursor:pointer;line-height:1;">&times;</button>
        </div>

        <div style="padding:20px 24px;">
            <!-- PASO 1: ABRIR EL BOT EN TELEGRAM -->
            <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:14px;margin-bottom:16px;">
                <div style="font-weight:700;color:#0369a1;font-size:0.86rem;margin-bottom:6px;">
                    Paso 1: Activa tu chat con el Bot
                </div>
                <div style="font-size:0.79rem;color:#0c4a6e;line-height:1.45;margin-bottom:10px;">
                    Haz clic en el siguiente botón para abrir tu bot <strong>@Bethg_bot</strong> en Telegram y presiona <strong>INICIAR (Start)</strong> o envíale un mensaje diciendo <strong>Hola</strong>:
                </div>
                <a href="https://t.me/Bethg_bot" target="_blank"
                   style="display:inline-flex;align-items:center;gap:8px;background:#0284c7;color:white;text-decoration:none;padding:8px 16px;border-radius:6px;font-size:0.82rem;font-weight:600;">
                    Abrir t.me/Bethg_bot en Telegram ↗
                </a>
            </div>

            <!-- PASO 2: DETECTAR CHAT ID DEL USUARIO -->
            <div style="background:rgba(201,168,76,0.08);border:1px solid rgba(201,168,76,0.3);border-radius:8px;padding:15px;margin-bottom:16px;">
                <div style="font-weight:700;color:var(--navy);font-size:0.86rem;margin-bottom:8px;">
                    Paso 2: Tu ID de Telegram ({{ session('usuario_nombre') }})
                </div>
                <div>
                    <label style="display:block;font-size:0.78rem;font-weight:600;color:var(--navy);margin-bottom:4px;">
                        Mi Telegram Chat ID (se detecta solo al pulsar el botón):
                    </label>
                    <div style="display:flex;gap:8px;">
                        <input type="text" id="cfgMiTelegramId" class="form-control" placeholder="Pulsa '🔍 Detectar mi Chat ID'..." style="flex:1;padding:8px 10px;font-size:0.85rem;">
                        <button type="button" onclick="detectarMiChatIdTelegram()"
                                style="padding:8px 14px;border-radius:6px;background:var(--navy);color:var(--gold);border:none;font-size:0.78rem;font-weight:600;cursor:pointer;white-space:nowrap;">
                            Detectar mi Chat ID
                        </button>
                    </div>
                </div>
                <div id="listaChatsDetectados" style="display:none;margin-top:10px;padding:8px 12px;background:white;border:1px dashed var(--gold);border-radius:6px;font-size:0.78rem;"></div>
            </div>

            <!-- PASO 3: TOKEN DEL BOT -->
            <div style="border:1px solid #e2e8f0;border-radius:8px;padding:15px;margin-bottom:16px;background:#f8fafc;">
                <div style="font-weight:700;color:var(--navy);font-size:0.86rem;margin-bottom:8px;">
                    Paso 3: Token de tu Bot (@Bethg_bot)
                </div>
                <div>
                    <label style="display:block;font-size:0.77rem;font-weight:600;color:var(--navy);margin-bottom:4px;">
                        Token HTTP API entregado por <a href="https://t.me/BotFather" target="_blank" style="color:#2563eb;">@BotFather ↗</a>:
                    </label>
                    <input type="text" id="cfgTelegramToken" class="form-control" value="8899958203:AAEVzM46rWrE1PgkAFCEbYd6BInHYSUekdw" style="width:100%;padding:8px 10px;font-size:0.83rem;font-family:monospace;">
                    <div style="font-size:0.72rem;color:#64748b;margin-top:5px;">
                        Ya está cargado el token de <strong>@Bethg_bot</strong>. Si alguna vez generas uno nuevo en <code>@BotFather</code>, pégalo aquí.
                    </div>
                </div>
            </div>

            <!-- Estado / Respuesta -->
            <div id="estadoConfigExterna" style="display:none;padding:10px 14px;border-radius:6px;font-size:0.82rem;margin-bottom:15px;"></div>

            <!-- Botones de Acción y Prueba en Vivo -->
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;border-top:1px solid #eee;padding-top:14px;">
                <div>
                    <button type="button" onclick="probarEnvioExterno('telegram')" class="btn-secondary" style="font-size:0.82rem;padding:8px 16px;border-color:#0284c7;color:white;background:#0284c7;font-weight:600;">
                        Probar Envío a mi Telegram
                    </button>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="button" onclick="cerrarModalTelegramGmail()" class="btn-secondary" style="font-size:0.82rem;padding:8px 14px;">Cerrar</button>
                    <button type="button" onclick="guardarConfigTelegramGmail()" class="btn-primary" style="font-size:0.82rem;padding:8px 16px;">💾 Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

@stack('scripts')
<script src="{{ asset('js/estudio.js') }}"></script>
<script src="{{ asset('js/notificaciones.js') }}"></script>
<script>
    function toggleTheme() {
        const html = document.documentElement;
        if (html.classList.contains('dark-mode-active')) {
            html.classList.remove('dark-mode-active');
            localStorage.setItem('theme', 'light');
        } else {
            html.classList.add('dark-mode-active');
            localStorage.setItem('theme', 'dark');
        }
    }

    // Initialize theme on load
    document.addEventListener('DOMContentLoaded', () => {
        const savedTheme = localStorage.getItem('theme');
        const checkbox = document.getElementById('theme-checkbox');
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark-mode-active');
            if (checkbox) checkbox.checked = true;
        }
    });

    function cambiarColorTema(color) {
        document.documentElement.style.setProperty('--navy', color);
        document.documentElement.style.setProperty('--gradient-navy', `linear-gradient(135deg, ${color} 0%, #0D1525 100%)`);
        localStorage.setItem('temaColor', color);
    }

    function toggleSidebarMobile() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (!sidebar) return;
        const isOpen = sidebar.classList.toggle('sidebar-mobile-open');
        if (overlay) {
            overlay.style.display = isOpen ? 'block' : 'none';
        }
    }

    // Registro de PWA y botón de instalación
    let deferredPrompt = null;
    const btnInstalar = document.getElementById('btnInstalarApp');

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        if (btnInstalar) {
            btnInstalar.style.display = 'inline-flex';
        }
    });

    if (btnInstalar) {
        btnInstalar.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    btnInstalar.style.display = 'none';
                }
                deferredPrompt = null;
            }
        });
    }

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }
</script>
</body>
</html>

