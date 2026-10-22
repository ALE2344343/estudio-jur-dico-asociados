@extends('layouts.dashboard')
@section('title', 'Historial de Auditoría')
@section('page-title', 'Historial de Modificaciones')

@section('content')
<div class="animate-in">

    <!-- Banner informativo de seguridad e inmutabilidad -->
    <div style="background:rgba(26,39,68,0.04);border:1px solid rgba(201,168,76,0.3);border-left:5px solid var(--gold-dark);padding:15px 20px;border-radius:8px;margin-bottom:25px;display:flex;align-items:center;gap:15px;">
        <div style="width:36px;height:36px;border-radius:50%;background:rgba(201,168,76,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg viewBox="0 0 24 24" style="width:20px;height:20px;fill:var(--gold-dark);"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
        </div>
        <div>
            <div style="font-weight:bold;color:var(--navy);font-size:0.95rem;">REGISTRO INMUTABLE DE AUDITORÍA Y TRAZABILIDAD</div>
            <div style="font-size:0.82rem;color:var(--text-gray);margin-top:2px;">
                Este módulo registra automáticamente cada creación, modificación, reasignación o eliminación realizada en cualquier panel del sistema. Por motivos de seguridad y transparencia legal, este registro es <strong>estrictamente de solo lectura</strong> y no puede ser alterado ni eliminado por ningún usuario.
            </div>
        </div>
    </div>

    <!-- Barra de Filtros y Búsqueda -->
    <div class="search-bar-container" style="background:white;padding:15px 20px;border-radius:12px;border:1px solid rgba(201,168,76,0.2);box-shadow:0 2px 8px rgba(0,0,0,0.03);margin-bottom:25px;">
        <form method="GET" action="{{ route('historial.index') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;width:100%;margin:0;">
            
            <div style="flex:1;min-width:200px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por descripción o usuario..." style="width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
            </div>

            <div style="min-width:160px;">
                <select name="modulo" style="width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;background:white;">
                    <option value="">-- Todos los Módulos --</option>
                    @foreach($modulos as $mod)
                        <option value="{{ $mod }}" {{ request('modulo') == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>

            <div style="min-width:160px;">
                <select name="accion" style="width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;background:white;">
                    <option value="">-- Todas las Acciones --</option>
                    @foreach($acciones as $acc)
                        <option value="{{ $acc }}" {{ request('accion') == $acc ? 'selected' : '' }}>{{ $acc }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <input type="date" name="fecha" value="{{ request('fecha') }}" style="padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
            </div>

            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn-primary" style="padding:8px 16px;font-size:0.85rem;">Filtrar</button>
                <a href="{{ route('historial.index') }}" class="btn-secondary" style="padding:8px 16px;font-size:0.85rem;">Actualizar</a>
            </div>

        </form>
    </div>

    <!-- Tabla del Historial -->
    <div class="table-card">
        <div class="table-card-header">
            <div class="table-card-title">
                <svg viewBox="0 0 24 24"><path d="M13 3a9 9 0 0 0-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42A8.954 8.954 0 0 0 13 21a9 9 0 0 0 0-18zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>
                REGISTRO CRONOLÓGICO DE MODIFICACIONES ({{ $logs->total() }})
            </div>
            <span style="font-size:0.8rem;color:var(--text-gray);">Ordenado del más reciente al más antiguo</span>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th style="white-space:nowrap;">Fecha y Hora</th>
                        <th>Usuario Responsable</th>
                        <th>Módulo</th>
                        <th>Acción Realizada</th>
                        <th>Detalle de la Modificación</th>
                        <th style="white-space:nowrap;">Dirección IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td style="font-weight:600;color:var(--navy);white-space:nowrap;font-size:0.82rem;">
                            {{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div class="user-avatar" style="width:28px;height:28px;font-size:0.75rem;background:rgba(26,39,68,0.1);color:var(--navy);">
                                    {{ substr($log->usuario_nombre, 0, 2) }}
                                </div>
                                <div>
                                    <div style="font-weight:600;font-size:0.85rem;color:var(--navy);">{{ $log->usuario_nombre }}</div>
                                    <div style="font-size:0.75rem;color:var(--text-gray);">{{ $log->usuario_rol }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="font-weight:500;font-size:0.82rem;color:var(--navy);">{{ $log->modulo }}</span>
                        </td>
                        <td>
                            @php
                                $badgeClass = match(true) {
                                    str_starts_with($log->accion, 'Creación')     => 'badge-active',
                                    str_starts_with($log->accion, 'Modificación') => 'badge-pending',
                                    str_starts_with($log->accion, 'Eliminación')  => 'badge-urgent',
                                    str_starts_with($log->accion, 'Reasignación') => 'badge-closed',
                                    str_starts_with($log->accion, 'Cambio')       => 'badge-pending',
                                    default                                       => 'badge-active'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}" style="white-space:nowrap;">{{ $log->accion }}</span>
                        </td>
                        <td style="font-size:0.85rem;color:#333;max-width:440px;line-height:1.45;">
                            @php
                                $descLimpia = str_replace(' 00:00:00', '', $log->descripcion);
                            @endphp
                            @if(str_contains($descLimpia, ' | ') || str_contains($descLimpia, ' :: '))
                                @php
                                    if (str_contains($descLimpia, ' :: ')) {
                                        $partes = explode(' :: ', $descLimpia, 2);
                                    } else {
                                        $partes = preg_split('/(?<=\))\:\s+/', $descLimpia, 2);
                                        if (count($partes) < 2) {
                                            $partes = explode(': ', $descLimpia, 2);
                                        }
                                    }
                                    $tituloEntidad = $partes[0] ?? '';
                                    $listaCambios = isset($partes[1]) ? explode(' | ', $partes[1]) : [];
                                @endphp
                                <div style="font-weight:600;color:var(--navy);margin-bottom:5px;">{{ $tituloEntidad }}:</div>
                                <div style="display:flex;flex-direction:column;gap:4px;">
                                    @foreach($listaCambios as $itemCambio)
                                        <div style="font-size:0.8rem;background:rgba(26,39,68,0.04);border-left:3px solid var(--gold-dark);padding:3px 8px;border-radius:3px;color:#2c3e50;">
                                            {{ str_replace(' 00:00:00', '', $itemCambio) }}
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                {{ $descLimpia }}
                            @endif
                        </td>
                        <td style="font-size:0.78rem;color:var(--text-gray);white-space:nowrap;">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:35px;color:var(--text-gray);">
                            No hay modificaciones registradas en el historial aún.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:15px 20px;">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
