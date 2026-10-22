@extends('layouts.dashboard')
@section('title', 'Control, Asignaciones y Plazos')
@section('page-title', 'Centro de Control y Asignaciones')

@section('content')
<div class="animate-in">

    <!-- Mensajes de sesión -->
    @if(session('success'))
    <div style="background:#d4edda;color:#155724;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:5px solid #28a745;">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background:#f8d7da;color:#721c24;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:5px solid #dc3545;">
        {{ session('error') }}
    </div>
    @endif

    <!-- Resumen Superior / Métricas de Control -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-bottom:25px;">
        <div style="background:white;padding:18px;border-radius:10px;border:1px solid rgba(201,168,76,0.2);box-shadow:0 2px 8px rgba(0,0,0,0.04);text-align:center;">
            <div style="font-size:1.8rem;font-weight:bold;color:var(--navy);">{{ $casos->count() }}</div>
            <div style="font-size:0.8rem;color:var(--text-gray);text-transform:uppercase;letter-spacing:1px;margin-top:4px;">Total Casos</div>
        </div>
        <div style="background:white;padding:18px;border-radius:10px;border:1px solid rgba(201,168,76,0.2);box-shadow:0 2px 8px rgba(0,0,0,0.04);text-align:center;">
            <div style="font-size:1.8rem;font-weight:bold;color:var(--gold-dark);">{{ $audiencias->count() }}</div>
            <div style="font-size:0.8rem;color:var(--text-gray);text-transform:uppercase;letter-spacing:1px;margin-top:4px;">Próximas Audiencias</div>
        </div>
        <div style="background:white;padding:18px;border-radius:10px;border:1px solid rgba(201,168,76,0.2);box-shadow:0 2px 8px rgba(0,0,0,0.04);text-align:center;">
            <div style="font-size:1.8rem;font-weight:bold;color:#e67e22;">{{ $plazos->count() }}</div>
            <div style="font-size:0.8rem;color:var(--text-gray);text-transform:uppercase;letter-spacing:1px;margin-top:4px;">Plazos Pendientes</div>
        </div>
        <div style="background:white;padding:18px;border-radius:10px;border:1px solid rgba(201,168,76,0.2);box-shadow:0 2px 8px rgba(0,0,0,0.04);text-align:center;">
            <div style="font-size:1.8rem;font-weight:bold;color:#e74c3c;">{{ $notificaciones->where('estado','pendiente')->count() }}</div>
            <div style="font-size:0.8rem;color:var(--text-gray);text-transform:uppercase;letter-spacing:1px;margin-top:4px;">Notificaciones Activas</div>
        </div>
    </div>

    <!-- Buscador en tiempo real del Centro de Control -->
    <div class="table-card" style="margin-bottom:25px;">
        <div style="padding:15px 20px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
            <div class="search-box" style="flex:1;min-width:220px;">
                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                <input type="text" id="inputFiltroGestion" placeholder="Filtrar casos, clientes, abogados, audiencias o notificaciones..." oninput="filtrarTablasGestion(this.value)">
            </div>
            <button type="button" class="btn-secondary" onclick="document.getElementById('inputFiltroGestion').value=''; filtrarTablasGestion('');">Limpiar</button>
        </div>
    </div>

    <!-- SECCIÓN 1: ASIGNACIÓN DE CASOS A ABOGADOS -->
    <div class="table-card" style="margin-bottom:30px;">
        <div class="table-card-header">
            <div class="table-card-title">
                <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                ASIGNACIÓN DE CASOS A ABOGADOS
            </div>
            <span style="font-size:0.8rem;color:var(--text-gray);">Asigna o reasigna cada proceso en tiempo real</span>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>N° Caso</th>
                        <th>Título</th>
                        <th>Cliente</th>
                        <th>Abogado Asignado</th>
                        <th>Estado</th>
                        <th style="text-align:center;">Acción de Asignación</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($casos as $caso)
                    <tr>
                        <td>
                            <a href="{{ route('casos.show', $caso->id) }}" style="color:var(--navy);font-weight:bold;text-decoration:none;">
                                {{ $caso->numero_caso }}
                            </a>
                        </td>
                        <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $caso->titulo }}
                        </td>
                        <td>{{ $caso->cliente ? $caso->cliente->nombre_completo : 'Sin cliente' }}</td>
                        <td>
                            @if($caso->abogado)
                                <span style="font-weight:600;color:var(--navy);">{{ $caso->abogado->nombre_completo }}</span>
                            @else
                                <span style="color:#e74c3c;font-style:italic;font-weight:bold;">¡Sin Asignar!</span>
                            @endif
                        </td>
                        <td>
                            @php $badge = $caso->badge_estado; @endphp
                            <span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span>
                        </td>
                        <td style="text-align:center;">
                            <form action="{{ route('gestion.casos.asignar', $caso->id) }}" method="POST" style="display:inline-flex;gap:6px;align-items:center;margin:0;">
                                @csrf
                                <select name="abogado_id" style="padding:4px 8px;font-size:0.8rem;border:1px solid #ccc;border-radius:4px;background:white;">
                                    <option value="">-- Sin asignar --</option>
                                    @foreach($abogados as $abg)
                                        <option value="{{ $abg->id }}" {{ $caso->abogado_id == $abg->id ? 'selected' : '' }}>
                                            {{ $abg->nombre_completo }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn-primary" style="padding:4px 10px;font-size:0.75rem;">Guardar</button>
                            </form>
                        </td>
                        <td style="text-align:center;">
                            <div style="display:inline-flex;gap:6px;align-items:center;">
                                <a href="{{ route('casos.edit', $caso->id) }}" class="btn-edit" title="Editar Caso" style="display:inline-flex;align-items:center;justify-content:center;">
                                    <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                </a>
                                <form action="{{ route('casos.destroy', $caso->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este caso?')" style="display:inline;margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger" title="Eliminar Caso" style="display:inline-flex;align-items:center;justify-content:center;">
                                        <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:20px;color:var(--text-gray);">No hay casos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECCIÓN 2: RECORDATORIOS DE AUDIENCIAS Y PLAZOS PROCESALES -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:25px;margin-bottom:30px;">
        
        <!-- Próximas Audiencias -->
        <div class="table-card">
            <div class="table-card-header">
                <div class="table-card-title">
                    <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7v-5z"/></svg>
                    RECORDATORIOS DE AUDIENCIAS
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Caso</th>
                        <th>Abogado a Cargo</th>
                        <th style="text-align:center;">Notificar</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audiencias as $aud)
                    <tr>
                        <td style="font-weight:bold;color:var(--gold-dark);white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($aud->fecha_hora)->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            <a href="{{ route('casos.show', $aud->caso_id) }}" style="color:var(--navy);text-decoration:none;font-weight:bold;">
                                {{ $aud->caso->numero_caso }}
                            </a>
                            <div style="font-size:0.78rem;color:var(--text-gray);">{{ $aud->titulo }}</div>
                        </td>
                        <td>
                            <span style="font-size:0.85rem;">{{ $aud->caso->abogado ? $aud->caso->abogado->nombre_completo : 'Sin asignar' }}</span>
                        </td>
                        <td style="text-align:center;">
                            @if($aud->caso && $aud->caso->abogado)
                            <form action="{{ route('gestion.audiencias.notificar', $aud->id) }}" method="POST" style="margin:0;display:inline-block;">
                                @csrf
                                <button type="submit" title="Notificar recordatorio de audiencia por Telegram (@Bethg_bot)"
                                        style="background:#0284c7;color:white;border:none;border-radius:6px;padding:5px 10px;font-size:0.75rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:4px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                                    <span></span> Telegram
                                </button>
                            </form>
                            @else
                                <span style="font-size:0.75rem;color:var(--text-gray);font-style:italic;">Sin abogado</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <div style="display:inline-flex;gap:6px;align-items:center;">
                                <button type="button" class="btn-edit" title="Editar Audiencia"
                                        data-id="{{ $aud->id }}"
                                        data-titulo="{{ $aud->titulo }}"
                                        data-fecha="{{ \Carbon\Carbon::parse($aud->fecha_hora)->format('Y-m-d\TH:i') }}"
                                        data-lugar="{{ $aud->lugar ?? '' }}"
                                        data-recordatorio="{{ (int)($aud->recordatorio_minutos ?? 15) }}"
                                        onclick="abrirModalEditarAudienciaDesdeBtn(this)"
                                        style="display:inline-flex;align-items:center;justify-content:center;border:none;cursor:pointer;">
                                    <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                </button>
                                <form action="{{ route('gestion.audiencias.destroy', $aud->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta audiencia?')" style="display:inline;margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger" title="Eliminar Audiencia" style="display:inline-flex;align-items:center;justify-content:center;border:none;cursor:pointer;">
                                        <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:20px;color:var(--text-gray);">No hay audiencias programadas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Plazos Procesales Perentorios -->
        <div class="table-card">
            <div class="table-card-header">
                <div class="table-card-title">
                    <svg viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-8-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                    PLAZOS PROCESALES (VENCIMIENTOS)
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Fecha Límite</th>
                        <th>Caso</th>
                        <th>Abogado</th>
                        <th>Estado Plazo</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plazos as $plazo)
                    @php
                        $diasRestantes = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($plazo->fecha_vencimiento)->startOfDay(), false);
                    @endphp
                    <tr>
                        <td style="font-weight:bold;color:var(--navy);white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($plazo->fecha_vencimiento)->format('d/m/Y') }}
                        </td>
                        <td>
                            <a href="{{ route('casos.show', $plazo->id) }}" style="color:var(--navy);text-decoration:none;font-weight:bold;">
                                {{ $plazo->numero_caso }}
                            </a>
                        </td>
                        <td style="font-size:0.85rem;">
                            {{ $plazo->abogado ? $plazo->abogado->nombre_completo : 'Sin asignar' }}
                        </td>
                        <td>
                            @if($diasRestantes < 0)
                                <span class="badge badge-urgent">Vencido hace {{ abs($diasRestantes) }} d</span>
                            @elseif($diasRestantes == 0)
                                <span class="badge badge-urgent">¡VENCE HOY!</span>
                            @elseif($diasRestantes <= 3)
                                <span class="badge badge-pending">Vence en {{ $diasRestantes }} d</span>
                            @else
                                <span class="badge badge-active">{{ $diasRestantes }} días</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <div style="display:inline-flex;gap:6px;align-items:center;">
                                <a href="{{ route('casos.edit', $plazo->id) }}" class="btn-edit" title="Editar Plazo / Caso" style="display:inline-flex;align-items:center;justify-content:center;">
                                    <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                </a>
                                <form action="{{ route('casos.destroy', $plazo->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este caso y su plazo?')" style="display:inline;margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger" title="Eliminar Caso" style="display:inline-flex;align-items:center;justify-content:center;border:none;cursor:pointer;">
                                        <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:20px;color:var(--text-gray);">No hay plazos de vencimiento registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <!-- SECCIÓN 3: LLEGADA DE NOTIFICACIONES JUDICIALES -->
    <div class="table-card" style="margin-bottom:30px;">
        <div class="table-card-header">
            <div class="table-card-title">
                <svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"/></svg>
                REGISTRO DE NOTIFICACIONES JUDICIALES
            </div>
            <button onclick="document.getElementById('modalNotificacion').style.display='flex'" class="btn-primary" style="font-size:0.8rem;padding:6px 16px;">
                + Registrar Nueva Notificación
            </button>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Fecha Recepción</th>
                        <th>Notificación / Asunto</th>
                        <th>Caso</th>
                        <th>Juzgado</th>
                        <th>Abogado Notificado</th>
                        <th>Plazo Límite</th>
                        <th>Estado</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notificaciones as $notif)
                    <tr>
                        <td style="font-weight:bold;color:var(--navy);white-space:nowrap;">
                            {{ $notif->fecha_recepcion ? $notif->fecha_recepcion->format('d/m/Y') : '-' }}
                        </td>
                        <td>
                            <strong style="color:var(--navy);font-size:0.95rem;">{{ $notif->titulo }}</strong>
                            @if($notif->descripcion)
                                <div style="font-size:0.75rem;color:#777;font-style:italic;margin-top:2px;">{{ Str::limit($notif->descripcion, 60) }}</div>
                            @endif
                        </td>
                        <td>
                            @if($notif->caso)
                                <a href="{{ route('casos.show', $notif->caso_id) }}" style="color:var(--navy);text-decoration:none;font-weight:bold;">
                                    {{ $notif->caso->numero_caso }}
                                </a>
                            @else
                                <span style="color:var(--text-gray);">General</span>
                            @endif
                        </td>
                        <td style="font-size:0.85rem;">{{ $notif->juzgado ?? '-' }}</td>
                        <td style="font-size:0.85rem;">
                            {{ $notif->usuario ? $notif->usuario->nombre_completo : ($notif->caso && $notif->caso->abogado ? $notif->caso->abogado->nombre_completo : 'Sin asignar') }}
                        </td>
                    <td style="white-space:nowrap;">
                        @if($notif->fecha_vencimiento)
                            @php
                                $hoy = now()->startOfDay();
                                $venc = \Carbon\Carbon::parse($notif->fecha_vencimiento)->startOfDay();
                                $diasCalendario = (int)$hoy->diffInDays($venc, false);
                                $materiaNotif = $notif->tipo ?? 'civil';
                            @endphp
                            <div style="font-weight:bold;color:{{ $diasCalendario <= 2 ? '#e74c3c' : '#0369a1' }};">
                                {{ $notif->fecha_vencimiento->format('d/m/Y') }}
                            </div>
                            <div style="font-size:0.72rem;color:var(--text-gray);">
                                @if($diasCalendario > 0)
                                    <span>{{ $diasCalendario }} {{ $diasCalendario == 1 ? 'día restante' : 'días restantes' }}</span>
                                @elseif($diasCalendario === 0)
                                    <span style="color:#e74c3c;font-weight:bold;">¡VENCE HOY!</span>
                                @else
                                    <span style="color:#e74c3c;font-weight:bold;">Venció hace {{ abs($diasCalendario) }} d</span>
                                @endif
                            </div>
                        @else
                            <span style="color:var(--text-gray);">-</span>
                        @endif
                    </td>
                        <td>
                            <form action="{{ route('gestion.notificaciones.toggle', $notif->id) }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" style="background:none;border:none;cursor:pointer;padding:0;">
                                    @if($notif->estado === 'pendiente')
                                         <span class="badge badge-urgent" title="Clic para marcar como atendida">Pendiente</span>
                                    @else
                                        <span class="badge badge-active" title="Clic para marcar como pendiente">Atendida</span>
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td style="text-align:center;">
                            <div style="display:inline-flex;gap:6px;align-items:center;">
                                <button type="button" class="btn-edit" title="Editar Notificación"
                                        data-id="{{ $notif->id }}"
                                        data-titulo="{{ $notif->titulo }}"
                                        data-materia="{{ $notif->tipo ?? 'civil' }}"
                                        data-recepcion="{{ $notif->fecha_recepcion ? $notif->fecha_recepcion->format('Y-m-d') : '' }}"
                                        data-vencimiento="{{ $notif->fecha_vencimiento ? $notif->fecha_vencimiento->format('Y-m-d') : '' }}"
                                        data-caso="{{ $notif->caso_id ?? '' }}"
                                        data-usuario="{{ $notif->usuario_id ?? '' }}"
                                        data-juzgado="{{ $notif->juzgado ?? '' }}"
                                        data-descripcion="{{ $notif->descripcion ?? '' }}"
                                        onclick="abrirModalEditarNotificacionDesdeBtn(this)"
                                        style="display:inline-flex;align-items:center;justify-content:center;border:none;cursor:pointer;">
                                    <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                </button>
                                <form action="{{ route('gestion.notificaciones.destroy', $notif->id) }}" method="POST" onsubmit="return confirm('¿Eliminar notificación?')" style="display:inline;margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger" style="display:inline-flex;align-items:center;justify-content:center;border:none;cursor:pointer;" title="Eliminar">
                                        <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center;padding:25px;color:var(--text-gray);">No hay notificaciones judiciales registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECCIÓN 4: ASIGNACIÓN DE PERMISOS Y USUARIOS (Abogados y Administradores) -->
    <div class="table-card">
        <div class="table-card-header">
            <div class="table-card-title">
                <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                CONTROL DE PERMISOS Y ROLES DE USUARIOS
            </div>
            <a href="{{ route('usuarios.create') }}" class="btn-primary" style="font-size:0.8rem;padding:6px 16px;">
                + Registrar Nuevo Abogado/Admin
            </a>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Nombre y Apellido</th>
                        <th>Email / Usuario</th>
                        <th>Especialidad / Matrícula</th>
                        <th>Rol Asignado</th>
                        <th>Estado</th>
                        <th style="text-align:center;">Cambiar Permiso de Rol</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $usr)
                    <tr>
                        <td style="font-weight:bold;color:var(--navy);">
                            {{ $usr->nombre_completo }}
                        </td>
                        <td>
                            <div style="font-size:0.85rem;">{{ $usr->email }}</div>
                            <div style="font-size:0.75rem;color:var(--text-gray);">Usuario: {{ $usr->username }}</div>
                        </td>
                        <td style="font-size:0.85rem;">
                            {{ $usr->especialidad ?? 'General' }}
                            @if($usr->matricula)
                                <div style="font-size:0.75rem;color:var(--text-gray);">Mat: {{ $usr->matricula }}</div>
                            @endif
                        </td>
                        <td>
                            @if($usr->rol === 'administrador')
                                <span class="badge badge-active" style="background:#1A2744;color:#C9A84C;border:1px solid #C9A84C;">ADMINISTRADOR</span>
                            @else
                                <span class="badge badge-pending">ABOGADO</span>
                            @endif
                        </td>
                        <td>
                            @if($usr->activo)
                                <span style="color:#28a745;font-weight:bold;font-size:0.85rem;">● Activo</span>
                            @else
                                <span style="color:#e74c3c;font-weight:bold;font-size:0.85rem;">● Inactivo</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($usr->id != session('usuario_id'))
                            <form action="{{ route('gestion.usuarios.rol', $usr->id) }}" method="POST" style="display:inline-flex;gap:6px;align-items:center;margin:0;">
                                @csrf
                                <select name="rol" style="padding:4px 8px;font-size:0.8rem;border:1px solid #ccc;border-radius:4px;background:white;">
                                    <option value="abogado" {{ $usr->rol === 'abogado' ? 'selected' : '' }}>Abogado</option>
                                    <option value="administrador" {{ $usr->rol === 'administrador' ? 'selected' : '' }}>Administrador</option>
                                </select>
                                <button type="submit" class="btn-primary" style="padding:4px 8px;font-size:0.75rem;">Aplicar</button>
                            </form>
                            @else
                                <span style="font-size:0.75rem;color:var(--text-gray);font-style:italic;">(Tu cuenta)</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <div style="display:inline-flex;gap:8px;align-items:center;">
                                <a href="{{ route('usuarios.edit', $usr->id) }}" class="btn-edit" style="display:inline-flex;align-items:center;justify-content:center;" title="Editar">
                                    <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                </a>
                                @if($usr->id != session('usuario_id'))
                                <form action="{{ route('gestion.usuarios.activo', $usr->id) }}" method="POST" style="margin:0;">
                                    @csrf
                                    @if($usr->activo)
                                    <button type="submit" class="btn-primary" style="padding:4px 8px;font-size:0.75rem;background:#e74c3c;">Desactivar</button>
                                    @else
                                    <button type="submit" class="btn-primary" style="padding:4px 8px;font-size:0.75rem;background:#28a745;">Activar</button>
                                    @endif
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL PARA REGISTRAR NOTIFICACIÓN JUDICIAL -->
<div id="modalNotificacion" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:12px;width:95%;max-width:600px;padding:25px;box-shadow:0 10px 30px rgba(0,0,0,0.3);border-top:5px solid var(--navy);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 style="font-family:'Cinzel',serif;color:var(--navy);margin:0;font-size:1.1rem;">Registrar Notificación Judicial</h3>
            <button onclick="document.getElementById('modalNotificacion').style.display='none'" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-gray);">&times;</button>
        </div>
        <form action="{{ route('gestion.notificaciones.store') }}" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Caso Relacionado</label>
                    <select name="caso_id" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
                        <option value="">-- Seleccionar Caso (Opcional) --</option>
                        @foreach($casos as $c)
                            <option value="{{ $c->id }}">{{ $c->numero_caso }} - {{ Str::limit($c->titulo, 25) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Abogado Responsable</label>
                    <select name="usuario_id" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
                        <option value="">-- Asignar Abogado --</option>
                        @foreach($abogados as $abg)
                            <option value="{{ $abg->id }}">{{ $abg->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Juzgado / Tribunal</label>
                <input type="text" name="juzgado" placeholder="Ej. Juzgado 4to de Partido en lo Civil / Sala Constitucional" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Título / Anotación de la Notificación *</label>
                <input type="text" name="titulo" required placeholder="Ej. Cédula de Notificación con Auto de Admisión de Demanda" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Materia Jurídica Procesal (Cómputo de Plazos) *</label>
                <select name="materia" id="inputMateriaNotificacion" onchange="calcularVencimientoSegunReglas()" required style="width:100%;padding:8px;border:1px solid #0284c7;border-radius:6px;font-size:0.85rem;background:#f0f9ff;font-weight:600;color:var(--navy);">
                    <option value="civil">Materia Civil (Ley 439 - Art. 90: Plazos ≤ 15 días hábiles; > 15 días corridos)</option>
                    <option value="penal">Materia Penal (Ley 1970 - Art. 130: Días corridos ininterrumpidos; prorroga a hábil si vence en festivo)</option>
                    <option value="laboral">Materia Laboral (CPT - Art. 43: Días hábiles judiciales de lunes a viernes)</option>
                    <option value="accion_libertad">Acción de Libertad / Constitucional (Plazo corrido fatal 24/7 ininterrumpido)</option>
                </select>
                <span id="ayudaMateria" style="font-size:0.73rem;color:#0369a1;display:block;margin-top:3px;font-style:italic;">
                    Empieza al día hábil siguiente. Plazos ≤ 15 días no cuentan sábados ni domingos.
                </span>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:15px;background:#f8fafc;padding:12px;border-radius:8px;border:1px dashed #cbd5e1;">
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Fecha de Notificación *</label>
                    <input type="date" id="inputFechaRecepcion" name="fecha_recepcion" value="{{ date('Y-m-d') }}" required onchange="calcularVencimientoSegunReglas()" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;background:white;">
                </div>
                <div>
                    <label id="lblPlazoDias" style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:#0369a1;">
                        Plazo (Días)
                    </label>
                    <input type="number" id="inputDiasHabiles" name="dias_habiles" min="1" max="365" placeholder="Ej: 10" oninput="calcularVencimientoSegunReglas()" style="width:100%;padding:8px;border:1px solid #0284c7;border-radius:6px;font-size:0.85rem;background:white;">
                    <span id="subtextoDias" style="font-size:0.7rem;color:#64748b;display:block;margin-top:2px;">Días según materia</span>
                </div>
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:#b91c1c;">
                        Fecha Vencimiento
                    </label>
                    <input type="date" id="inputFechaVencimiento" name="fecha_vencimiento" onchange="calcularDiasDesdeVencimiento()" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;background:white;">
                    <span id="textoCalculoHabiles" style="font-size:0.72rem;color:#0284c7;display:block;margin-top:2px;font-weight:600;"></span>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Detalle u Observaciones</label>
                <textarea name="descripcion" rows="3" placeholder="Indicar términos del plazo, fojas o instrucciones especiales..." style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;resize:vertical;"></textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" onclick="document.getElementById('modalNotificacion').style.display='none'" class="btn-secondary">Cancelar</button>
                <button type="submit" class="btn-primary">Registrar Notificación</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL PARA EDITAR NOTIFICACIÓN JUDICIAL -->
<div id="modalEditarNotificacion" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:12px;width:95%;max-width:600px;padding:25px;box-shadow:0 10px 30px rgba(0,0,0,0.3);border-top:5px solid var(--navy);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 style="font-family:'Cinzel',serif;color:var(--navy);margin:0;font-size:1.1rem;">Modificar Notificación Judicial</h3>
            <button onclick="document.getElementById('modalEditarNotificacion').style.display='none'" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-gray);">&times;</button>
        </div>
        <form id="formEditarNotificacion" method="POST">
            @csrf
            @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Caso Relacionado</label>
                    <select name="caso_id" id="editNotifCasoId" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
                        <option value="">-- Seleccionar Caso (Opcional) --</option>
                        @foreach($casos as $c)
                            <option value="{{ $c->id }}">{{ $c->numero_caso }} - {{ Str::limit($c->titulo, 25) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Abogado Responsable</label>
                    <select name="usuario_id" id="editNotifUsuarioId" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
                        <option value="">-- Asignar Abogado --</option>
                        @foreach($abogados as $abg)
                            <option value="{{ $abg->id }}">{{ $abg->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Juzgado / Tribunal</label>
                <input type="text" name="juzgado" id="editNotifJuzgado" placeholder="Ej. Juzgado 4to de Partido en lo Civil / Sala Constitucional" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Título / Anotación de la Notificación *</label>
                <input type="text" name="titulo" id="editNotifTitulo" required placeholder="Ej. Cédula de Notificación con Auto de Admisión" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Materia Jurídica Procesal (Cómputo de Plazos) *</label>
                <select name="materia" id="editNotifMateria" onchange="calcularVencimientoSegunReglasEdit()" required style="width:100%;padding:8px;border:1px solid #0284c7;border-radius:6px;font-size:0.85rem;background:#f0f9ff;font-weight:600;color:var(--navy);">
                    <option value="civil">Materia Civil (Ley 439 - Art. 90: Plazos ≤ 15 días hábiles; > 15 días corridos)</option>
                    <option value="penal">Materia Penal Ley 1970-Art. 130 Días corridos ininterrumpidos; prorroga a hábil si vence en festivo)</option>
                    <option value="laboral">Materia Lab Art. 43 Días hábiles judiciales de lunes a viernes)</option>
                    <option value="accion_libertad">Acción de Libertad / Constitucional Plazo corrido fatal 24/7 ininterrumpido)</option>
                </select>
                <span id="editAyudaMateria" style="font-size:0.73rem;color:#0369a1;display:block;margin-top:3px;font-style:italic;">
                    Empieza al día hábil siguiente. Plazos ≤ 15 días no cuentan sábados ni domingos.
                </span>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:15px;background:#f8fafc;padding:12px;border-radius:8px;border:1px dashed #cbd5e1;">
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Fecha de Notificación *</label>
                    <input type="date" id="editNotifFechaRecepcion" name="fecha_recepcion" required onchange="calcularVencimientoSegunReglasEdit()" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;background:white;">
                </div>
                <div>
                    <label id="editLblPlazoDias" style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:#0369a1;">
                        Plazo (Días)
                    </label>
                    <input type="number" id="editNotifDiasHabiles" name="dias_habiles" min="1" max="365" placeholder="Ej: 10" oninput="calcularVencimientoSegunReglasEdit()" style="width:100%;padding:8px;border:1px solid #0284c7;border-radius:6px;font-size:0.85rem;background:white;">
                    <span id="editSubtextoDias" style="font-size:0.7rem;color:#64748b;display:block;margin-top:2px;">Días según materia</span>
                </div>
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:#b91c1c;">
                        Fecha Vencimiento
                    </label>
                    <input type="date" id="editNotifFechaVencimiento" name="fecha_vencimiento" onchange="calcularDiasDesdeVencimientoEdit()" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;background:white;">
                    <span id="textoCalculoHabilesEdit" style="font-size:0.72rem;color:#0284c7;display:block;margin-top:2px;font-weight:600;"></span>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Detalle u Observaciones</label>
                <textarea name="descripcion" id="editNotifDescripcion" rows="3" placeholder="Indicar términos del plazo..." style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;resize:vertical;"></textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" onclick="document.getElementById('modalEditarNotificacion').style.display='none'" class="btn-secondary">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL PARA EDITAR AUDIENCIA JUDICIAL -->
<div id="modalEditarAudiencia" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:12px;width:95%;max-width:520px;padding:25px;box-shadow:0 10px 30px rgba(0,0,0,0.3);border-top:5px solid var(--navy);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 style="font-family:'Cinzel',serif;color:var(--navy);margin:0;font-size:1.1rem;">Modificar Audiencia Judicial</h3>
            <button onclick="document.getElementById('modalEditarAudiencia').style.display='none'" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-gray);">&times;</button>
        </div>
        <form id="formEditarAudiencia" method="POST">
            @csrf
            @method('PUT')
            <div style="margin-bottom:15px;">
                <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Título de la Audiencia *</label>
                <input type="text" name="titulo" id="editAudTitulo" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Fecha y Hora *</label>
                <input type="datetime-local" name="fecha_hora" id="editAudFechaHora" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Lugar / Enlace Virtual</label>
                <input type="text" name="lugar" id="editAudLugar" placeholder="Ej. Sala de Audiencias 3 / Google Meet" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:5px;color:var(--navy);">Recordatorio Previo (Minutos)</label>
                <input type="number" name="recordatorio_minutos" id="editAudRecordatorio" min="5" step="5" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" onclick="document.getElementById('modalEditarAudiencia').style.display='none'" class="btn-secondary">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Motor de cálculo de plazos según legislación procesal boliviana
function calcularFechaProcesal(fechaBaseStr, dias, materia) {
    if (!fechaBaseStr || isNaN(dias) || dias <= 0) return null;

    const partes = fechaBaseStr.split('-');
    let fecha = new Date(parseInt(partes[0]), parseInt(partes[1]) - 1, parseInt(partes[2]));

    if (materia === 'civil') {
        // Ley 439 Art. 90:
        // Empieza al día hábil siguiente.
        // Si plazo <= 15 días: sólo días hábiles (lunes a viernes).
        // Si plazo > 15 días: días corridos calendario.
        if (dias <= 15) {
            let cont = 0;
            while (cont < dias) {
                fecha.setDate(fecha.getDate() + 1);
                const ds = fecha.getDay();
                if (ds !== 0 && ds !== 6) cont++;
            }
        } else {
            fecha.setDate(fecha.getDate() + dias);
        }
    } else if (materia === 'penal') {
        // Ley 1970 Art. 130:
        // Días corridos ininterrumpidos desde las 00:00 del día siguiente.
        // Si el último día cae en sábado o domingo, se traslada al primer día hábil siguiente.
        fecha.setDate(fecha.getDate() + dias);
        while (fecha.getDay() === 0 || fecha.getDay() === 6) {
            fecha.setDate(fecha.getDate() + 1);
        }
    } else if (materia === 'laboral') {
        // CPT Art. 43: Días hábiles judiciales (lunes a viernes).
        let cont = 0;
        while (cont < dias) {
            fecha.setDate(fecha.getDate() + 1);
            const ds = fecha.getDay();
            if (ds !== 0 && ds !== 6) cont++;
        }
    } else if (materia === 'accion_libertad') {
        // Materia Constitucional / Acción de Libertad:
        // Plazo perentorio y continuo 24/7 (días corridos sin trasladar).
        fecha.setDate(fecha.getDate() + dias);
    } else {
        // Default: días hábiles
        let cont = 0;
        while (cont < dias) {
            fecha.setDate(fecha.getDate() + 1);
            const ds = fecha.getDay();
            if (ds !== 0 && ds !== 6) cont++;
        }
    }

    return fecha;
}

// Cálculo inverso: contar cuántos días hábiles/procesales hay entre dos fechas
function contarDiasProcesales(fechaInicioStr, fechaFinStr, materia) {
    if (!fechaInicioStr || !fechaFinStr) return 0;
    const p1 = fechaInicioStr.split('-');
    const p2 = fechaFinStr.split('-');
    let f1 = new Date(parseInt(p1[0]), parseInt(p1[1]) - 1, parseInt(p1[2]));
    let f2 = new Date(parseInt(p2[0]), parseInt(p2[1]) - 1, parseInt(p2[2]));

    if (f2 <= f1) return 0;

    if (materia === 'penal' || materia === 'accion_libertad') {
        // Días corridos
        const diffTime = Math.abs(f2 - f1);
        return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    }

    // Civil / Laboral: contar días hábiles entre ambas fechas
    let cont = 0;
    let cursor = new Date(f1.getTime());
    while (cursor < f2) {
        cursor.setDate(cursor.getDate() + 1);
        const ds = cursor.getDay();
        if (ds !== 0 && ds !== 6) {
            cont++;
        }
    }
    return cont;
}

function calcularVencimientoSegunReglas() {
    const selMateria = document.getElementById('inputMateriaNotificacion');
    const inputFecha = document.getElementById('inputFechaRecepcion');
    const inputDias = document.getElementById('inputDiasHabiles');
    const inputVenc = document.getElementById('inputFechaVencimiento');
    const textoInfo = document.getElementById('textoCalculoHabiles');
    const ayudaMateria = document.getElementById('ayudaMateria');

    if (!selMateria || !inputFecha || !inputDias || !inputVenc) return;

    const materia = selMateria.value;
    const fechaVal = inputFecha.value;
    const dias = parseInt(inputDias.value, 10);

    // Actualizar texto explicativo según materia
    if (ayudaMateria) {
        if (materia === 'civil') {
            ayudaMateria.textContent = 'Ley 439 Si el plazo es ≤ 15 días son hábiles (Lun-Vie); si es > 15 días son corridos.';
        } else if (materia === 'penal') {
            ayudaMateria.textContent = 'Ley 1970 Días corridos ininterrumpidos. Si vence en sábado/domingo, se traslada al primer día hábil.';
        } else if (materia === 'laboral') {
            ayudaMateria.textContent = 'Días hábiles judiciales (de lunes a viernes, sin fines de semana).';
        } else if (materia === 'accion_libertad') {
            ayudaMateria.textContent = 'Constitucional: Plazo fatal ininterrumpido 24/7 de día a día sin excepciones.';
        }
    }

    if (!fechaVal || isNaN(dias) || dias <= 0) {
        if (textoInfo) textoInfo.textContent = '';
        return;
    }

    const fechaVenc = calcularFechaProcesal(fechaVal, dias, materia);
    if (!fechaVenc) return;

    const y = fechaVenc.getFullYear();
    const m = String(fechaVenc.getMonth() + 1).padStart(2, '0');
    const d = String(fechaVenc.getDate()).padStart(2, '0');
    inputVenc.value = `${y}-${m}-${d}`;

    const diasSemanaNombres = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    if (textoInfo) {
        textoInfo.textContent = `Vence: ${diasSemanaNombres[fechaVenc.getDay()]} ${d}/${m}/${y}`;
    }
}

function calcularDiasDesdeVencimiento() {
    const selMateria = document.getElementById('inputMateriaNotificacion');
    const inputFecha = document.getElementById('inputFechaRecepcion');
    const inputDias = document.getElementById('inputDiasHabiles');
    const inputVenc = document.getElementById('inputFechaVencimiento');
    const textoInfo = document.getElementById('textoCalculoHabiles');

    if (!inputFecha.value || !inputVenc.value) return;
    const materia = selMateria ? selMateria.value : 'civil';
    const dias = contarDiasProcesales(inputFecha.value, inputVenc.value, materia);
    if (dias > 0) {
        inputDias.value = dias;
        const partes = inputVenc.value.split('-');
        const fv = new Date(parseInt(partes[0]), parseInt(partes[1]) - 1, parseInt(partes[2]));
        const diasSemanaNombres = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        if (textoInfo) {
            textoInfo.textContent = `Vence: ${diasSemanaNombres[fv.getDay()]} ${partes[2]}/${partes[1]}/${partes[0]} (${dias} d)`;
        }
    }
}

// Modal Editar Notificación
function calcularVencimientoSegunReglasEdit() {
    const selMateria = document.getElementById('editNotifMateria');
    const inputFecha = document.getElementById('editNotifFechaRecepcion');
    const inputDias = document.getElementById('editNotifDiasHabiles');
    const inputVenc = document.getElementById('editNotifFechaVencimiento');
    const textoInfo = document.getElementById('textoCalculoHabilesEdit');
    const ayudaMateria = document.getElementById('editAyudaMateria');

    if (!selMateria || !inputFecha || !inputDias || !inputVenc) return;

    const materia = selMateria.value;
    const fechaVal = inputFecha.value;
    const dias = parseInt(inputDias.value, 10);

    if (ayudaMateria) {
        if (materia === 'civil') {
            ayudaMateria.textContent = 'Ley 439: Empieza al día hábil siguiente. Si es ≤ 15 días son hábiles (Lun-Vie); si es > 15 días son corridos.';
        } else if (materia === 'penal') {
            ayudaMateria.textContent = 'Ley 1970: Días corridos ininterrumpidos. Si vence en sábado/domingo, se traslada al primer hábil.';
        } else if (materia === 'laboral') {
            ayudaMateria.textContent = 'CPT Art. 43: Días hábiles judiciales (de lunes a viernes).';
        } else if (materia === 'accion_libertad') {
            ayudaMateria.textContent = 'Constitucional: Plazo fatal ininterrumpido 24/7 sin excepciones.';
        }
    }

    if (!fechaVal || isNaN(dias) || dias <= 0) {
        if (textoInfo) textoInfo.textContent = '';
        return;
    }

    const fechaVenc = calcularFechaProcesal(fechaVal, dias, materia);
    if (!fechaVenc) return;

    const y = fechaVenc.getFullYear();
    const m = String(fechaVenc.getMonth() + 1).padStart(2, '0');
    const d = String(fechaVenc.getDate()).padStart(2, '0');
    inputVenc.value = `${y}-${m}-${d}`;

    const diasSemanaNombres = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    if (textoInfo) {
        textoInfo.textContent = `Vence: ${diasSemanaNombres[fechaVenc.getDay()]} ${d}/${m}/${y}`;
    }
}

function calcularDiasDesdeVencimientoEdit() {
    const selMateria = document.getElementById('editNotifMateria');
    const inputFecha = document.getElementById('editNotifFechaRecepcion');
    const inputDias = document.getElementById('editNotifDiasHabiles');
    const inputVenc = document.getElementById('editNotifFechaVencimiento');
    const textoInfo = document.getElementById('textoCalculoHabilesEdit');

    if (!inputFecha.value || !inputVenc.value) return;
    const materia = selMateria ? selMateria.value : 'civil';
    const dias = contarDiasProcesales(inputFecha.value, inputVenc.value, materia);
    if (dias > 0) {
        inputDias.value = dias;
        const partes = inputVenc.value.split('-');
        const fv = new Date(parseInt(partes[0]), parseInt(partes[1]) - 1, parseInt(partes[2]));
        const diasSemanaNombres = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        if (textoInfo) {
            textoInfo.textContent = `Vence: ${diasSemanaNombres[fv.getDay()]} ${partes[2]}/${partes[1]}/${partes[0]} (${dias} d)`;
        }
    }
}

function abrirModalEditarNotificacionDesdeBtn(btn) {
    const d = btn.dataset;
    abrirModalEditarNotificacion(d.id, d.titulo, d.materia, d.recepcion, d.vencimiento, d.caso, d.usuario, d.juzgado, d.descripcion);
}

function abrirModalEditarAudienciaDesdeBtn(btn) {
    const d = btn.dataset;
    abrirModalEditarAudiencia(d.id, d.titulo, d.fecha, d.lugar, d.recordatorio);
}

function abrirModalEditarNotificacion(id, titulo, materia, fechaRecepcion, fechaVencimiento, casoId, usuarioId, juzgado, descripcion) {
    const form = document.getElementById('formEditarNotificacion');
    form.action = '/gestion-legal/notificaciones/' + id;

    const selectMateria = document.getElementById('editNotifMateria');
    if (selectMateria) {
        selectMateria.value = materia || 'civil';
    }

    document.getElementById('editNotifTitulo').value = titulo || '';
    document.getElementById('editNotifFechaRecepcion').value = fechaRecepcion || '';
    document.getElementById('editNotifFechaVencimiento').value = fechaVencimiento || '';
    document.getElementById('editNotifCasoId').value = casoId || '';
    document.getElementById('editNotifUsuarioId').value = usuarioId || '';
    document.getElementById('editNotifJuzgado').value = juzgado || '';
    document.getElementById('editNotifDescripcion').value = descripcion || '';

    // Si ya tiene fecha de recepción y fecha de vencimiento, calcular los días automáticamente
    if (fechaRecepcion && fechaVencimiento) {
        const d = contarDiasProcesales(fechaRecepcion, fechaVencimiento, materia || 'civil');
        document.getElementById('editNotifDiasHabiles').value = d > 0 ? d : '';
    } else {
        document.getElementById('editNotifDiasHabiles').value = '';
    }

    calcularVencimientoSegunReglasEdit();
    document.getElementById('modalEditarNotificacion').style.display = 'flex';
}

function abrirModalEditarAudiencia(id, titulo, fechaHora, lugar, recordatorio) {
    const form = document.getElementById('formEditarAudiencia');
    form.action = '/gestion-legal/audiencias/' + id;

    document.getElementById('editAudTitulo').value = titulo || '';
    document.getElementById('editAudFechaHora').value = fechaHora || '';
    document.getElementById('editAudLugar').value = lugar || '';
    document.getElementById('editAudRecordatorio').value = recordatorio || 15;

    document.getElementById('modalEditarAudiencia').style.display = 'flex';
}

function filtrarTablasGestion(texto) {
    const q = (texto || '').toLowerCase().trim();
    document.querySelectorAll('.table-card table tbody tr').forEach(function(tr) {
        if (tr.querySelector('td[colspan]')) return;
        const contenido = tr.innerText.toLowerCase();
        tr.style.display = (!q || contenido.includes(q)) ? '' : 'none';
    });
}
</script>
@endpush
@endsection
