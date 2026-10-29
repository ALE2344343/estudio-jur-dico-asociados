@extends('layouts.dashboard')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Estadi­sticas -->
<div class="stats-grid animate-in">
    <div class="stat-card">
        <div class="stat-icon gold">
            <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-num">{{ $totalClientes }}</div>
            <div class="stat-label">Total Clientes</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon navy">
            <svg viewBox="0 0 24 24"><path d="M20 6h-2.18c.07-.44.18-.88.18-1.34C18 2.54 15.8.5 13 .5c-1.72 0-3.24.84-4.17 2.12L8 4 7.17 2.62C6.24 1.34 4.72.5 3 .5.2.5-2 2.54-2 4.66c0 .46.11.9.18 1.34H-4v2h2v12h16V8h2V6z"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-num">{{ $totalCasos }}</div>
            <div class="stat-label">Total CASOS</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-num">{{ $casosActivos }}</div>
            <div class="stat-label">NUREJ Activos</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">
            <svg viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-num">{{ $casosUrgentes }}</div>
            <div class="stat-label">NUREJ Urgentes</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:25px;">
    <!-- Casos Recientes -->
    <div class="table-card animate-in" style="animation-delay:0.1s">
        <div class="table-card-header">
            <div class="table-card-title">
                <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                CASOS RECIENTES
            </div>
            <a href="{{ route('casos.index') }}" class="btn-primary" style="font-size:0.7rem;padding:6px 14px;">Ver todos</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>NUREJ</th>
                    <th>Titulo</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($casosRecientes as $caso)
                <tr>
                    <td><a href="{{ route('casos.show', $caso->id) }}" style="color:var(--gold-dark);text-decoration:none;font-family:'Cinzel',serif;font-size:0.78rem;">{{ $caso->numero_caso }}</a></td>
                    <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $caso->titulo }}</td>
                    <td><span class="badge badge-closed">{{ $caso->tipo }}</span></td>
                    <td>
                        @php $badge = $caso->badge_estado; @endphp
                        <span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:var(--text-gray);padding:25px;">No hay NUREJ registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Clientes Recientes -->
    <div class="table-card animate-in" style="animation-delay:0.2s">
        <div class="table-card-header">
            <div class="table-card-title">
                <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                CLIENTES RECIENTES
            </div>
            <a href="{{ route('clientes.index') }}" class="btn-primary" style="font-size:0.7rem;padding:6px 14px;">Ver todos</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Nombre</th>
                    <th>CI</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientesRecientes as $cliente)
                <tr>
                    <td><span style="font-family:'Cinzel',serif;font-size:0.78rem;color:var(--gold-dark);">{{ $cliente->codigo }}</span></td>
                    <td><a href="{{ route('clientes.show', $cliente->id) }}" style="color:var(--navy);text-decoration:none;">{{ $cliente->nombre_completo }}</a></td>
                    <td style="font-size:0.82rem;color:var(--text-gray);">{{ $cliente->ci }}</td>
                    <td><span class="badge {{ $cliente->estado === 'activo' ? 'badge-active' : 'badge-closed' }}">{{ ucfirst($cliente->estado) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:var(--text-gray);padding:25px;">No hay clientes registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Accesos rápidos -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-top:25px;" class="animate-in">
    @php
        $accesos = [
            ['route'=>'clientes.create','label'=>'Nuevo Cliente','color'=>'#C9A84C'],
            ['route'=>'casos.create','label'=>'Nuevo NUREJ','color'=>'#1A2744'],
            ['route'=>'documentos.index','label'=>'Documentos','color'=>'#28a745'],
            ['route'=>'leyes.index','label'=>'Leyes Bolivia','color'=>'#dc3545'],
        ];
    @endphp
    @foreach($accesos as $acc)
    <a href="{{ route($acc['route']) }}" data-border-color="{{ $acc['color'] }}" class="acceso-rapido-card" style="background:white;border-radius:12px;padding:20px;text-align:center;text-decoration:none;border:1px solid rgba(201,168,76,.15);box-shadow:0 4px 12px rgba(0,0,0,.06);transition:all .3s ease;display:block;border-top:3px solid #C9A84C;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
        <div style="font-family:'Cinzel',serif;font-size:0.9rem;font-weight:bold;color:var(--navy);letter-spacing:1px;text-transform:uppercase;padding:15px 0;">{{ $acc['label'] }}</div>
    </a>
    @endforeach
</div>


    <!-- Audiencias -->
    <div class="table-card">
        <div class="table-card-header">
            <div class="table-card-title">
                <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7v-5z"/></svg>
                PROXIMAS AUDIENCIAS
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Fecha y Hora</th>
                    <th>NUREJ</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                @forelse($audiencias as $aud)
                <tr>
                    <td style="font-weight:600;color:var(--gold-dark);">{{ \Carbon\Carbon::parse($aud->fecha_hora)->format('d/m/Y H:i') }}</td>
                    <td><a href="{{ route('casos.show', $aud->caso_id) }}" style="color:var(--navy);text-decoration:none;font-weight:bold;">{{ $aud->caso->numero_caso }}</a></td>
                    <td style="font-size:0.85rem;color:var(--text-gray);">{{ $aud->titulo }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center;color:var(--text-gray);padding:25px;">No hay audiencias programadas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Anotaciones de los Abogados (Supervisión del Administrador) -->
    <div class="table-card" style="margin-top:25px;">
        <div class="table-card-header">
            <div class="table-card-title">
                <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                ANOTACIONES Y RECORDATORIOS DE LOS ABOGADOS
            </div>
            <span style="font-size:0.8rem;color:var(--text-gray);">Supervisión de notas y tareas programadas por el cuerpo legal</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Abogado</th>
                    <th>Asunto / Recordatorio</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                @forelse($anotacionesAbogados as $nota)
                <tr>
                    <td style="font-weight:600;color:var(--navy);white-space:nowrap;font-size:0.85rem;">
                        {{ $nota->fecha ? $nota->fecha->format('d/m/Y') : '-' }} {{ $nota->hora ? ' - ' . $nota->hora : '' }}
                    </td>
                    <td>
                        <span style="font-weight:600;color:var(--gold-dark);">{{ $nota->usuario ? $nota->usuario->nombre_completo : 'Abogado' }}</span>
                    </td>
                    <td style="font-weight:500;">
                        <span data-color="{{ $nota->color ?? '#2563EB' }}" class="color-dot">●</span> {{ $nota->titulo }}
                        @php $recMin = $nota->recordatorio_minutos ?? 5; @endphp
                        <span style="background:#e0f2fe;color:#0369a1;padding:2px 6px;border-radius:4px;font-size:0.7rem;font-weight:600;margin-left:6px;">
                            {{ $recMin == 0 ? 'A la hora' : $recMin . ' min antes' }}
                        </span>
                    </td>
                    <td style="font-size:0.82rem;color:var(--text-gray);">
                        {{ $nota->descripcion ?? 'Sin detalle adicional' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:var(--text-gray);padding:25px;">No hay anotaciones registradas por los abogados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

@push('scripts')
<script>
    document.querySelectorAll('.color-dot[data-color]').forEach(el => {
        el.style.color = el.getAttribute('data-color');
    });
    document.querySelectorAll('.acceso-rapido-card[data-border-color]').forEach(el => {
        el.style.borderTopColor = el.getAttribute('data-border-color');
    });
    function cambiarColorTema(color) {
        document.documentElement.style.setProperty('--navy', color);
        document.documentElement.style.setProperty('--gradient-navy', `linear-gradient(135deg, ${color} 0%, #0D1525 100%)`);
        localStorage.setItem('temaColor', color);
    }
</script>
@endpush
@endsection








