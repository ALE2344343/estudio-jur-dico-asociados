@extends('layouts.dashboard')
@section('title','Detalle Cliente')
@section('page-title','Detalle de Cliente')

@section('top-bar-actions')
@if(session('usuario_rol') === 'administrador')
<a href="{{ route('clientes.edit', $cliente->id) }}" class="btn-edit" title="Editar" style="display:inline-flex;align-items:center;justify-content:center;">
    <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
</a>
<form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" style="display:inline;margin:0;">
    @csrf @method('DELETE')
    <button type="submit" class="btn-danger" onclick="return confirm('¿Eliminar este cliente?')" title="Eliminar" style="display:inline-flex;align-items:center;justify-content:center;">
        <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
    </button>
</form>
@endif
<a href="{{ route('clientes.index') }}" class="btn-secondary">← Volver</a>
@endsection

@section('content')
<div style="display:grid;grid-template-columns:1fr 2fr;gap:25px;">

    <!-- Info principal -->
    <div>
        <div style="background:var(--gradient-navy);border-radius:12px;padding:25px;text-align:center;margin-bottom:20px;">
            <div class="user-avatar" style="width:70px;height:70px;font-size:1.5rem;margin:0 auto 15px;">
                {{ strtoupper(substr($cliente->nombre,0,1).substr($cliente->apellido,0,1)) }}
            </div>
            <div style="font-family:'Cinzel',serif;font-size:1.1rem;color:var(--gold);margin-bottom:5px;">
                {{ $cliente->nombre_completo }}
            </div>
            <div style="font-size:0.82rem;color:rgba(255,255,255,0.6);letter-spacing:1px;">{{ $cliente->codigo }}</div>
            <div style="margin-top:12px;">
                <span class="badge {{ $cliente->estado==='activo'?'badge-active':'badge-closed' }}">{{ ucfirst($cliente->estado) }}</span>
            </div>
        </div>

        <div class="table-card" style="padding:0;">
            <div class="table-card-header"><div class="table-card-title">Información</div></div>
            <div style="padding:15px 20px;">
                @foreach([
                    ['CI','ci','ci_expedido'],
                    ['Teléfono','telefono',null],
                    ['Celular','celular',null],
                    ['Email','email',null],
                    ['Ciudad','ciudad','departamento'],
                    ['Ocupación','ocupacion',null],
                    ['Empresa','empresa',null],
                    ['Estado Civil','estado_civil',null],
                    ['Abogado','abogado.nombre',null],
                ] as [$label,$field,$extra])
                @php
                    $val = Str::contains($field,'.') ? data_get($cliente, $field) : $cliente->$field;
                    $extraVal = $extra ? $cliente->$extra : null;
                @endphp
                @if($val)
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(201,168,76,0.1);font-size:0.85rem;">
                    <span style="color:var(--text-gray);">{{ $label }}</span>
                    <span style="color:var(--navy);font-weight:500;">{{ $val }}{{ $extraVal ? ' ('.$extraVal.')' : '' }}</span>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Casos y documentos -->
    <div>
        <!-- Casos del cliente -->
        <div class="table-card" style="margin-bottom:20px;">
            <div class="table-card-header">
                <div class="table-card-title">📁 NUREJ / EXPEDIENTES ({{ $cliente->casos->count() }})</div>
                @if(session('usuario_rol') === 'administrador') <a href="{{ route('casos.create') }}?cliente={{ $cliente->id }}" class="btn-primary" style="font-size:0.72rem;padding:6px 12px;">+ Nuevo</a> @endif
            </div>
            <table>
                <thead><tr><th>NUREJ</th><th>Título</th><th>Tipo</th><th>Estado</th></tr></thead>
                <tbody>
                    @forelse($cliente->casos as $caso)
                    <tr>
                        <td><a href="{{ route('casos.show',$caso->id) }}" style="color:var(--gold-dark);text-decoration:none;font-family:'Cinzel',serif;font-size:0.78rem;">{{ $caso->numero_caso }}</a></td>
                        <td>{{ Str::limit($caso->titulo,40) }}</td>
                        <td><span class="badge badge-closed">{{ $caso->tipo }}</span></td>
                        <td>@php $b=$caso->badge_estado;@endphp<span class="badge {{ $b[0] }}">{{ $b[1] }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;padding:20px;color:var(--text-gray);">Sin casos registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Documentos -->
        <div class="table-card" style="margin-bottom:20px;">
            <div class="table-card-header">
                <div class="table-card-title">DOCUMENTOS ({{ $cliente->documentos->count() }})</div>
            </div>
            @if($cliente->documentos->count() > 0)
            <div style="padding:15px;display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:15px;">
                @foreach($cliente->documentos as $doc)
                <div class="doc-card" style="position:relative;padding:12px;display:flex;flex-direction:column;justify-content:space-between;border-radius:10px;background:#fff;border:1px solid rgba(201,168,76,0.2);box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                    <!-- Botones de Acción (Editar y Eliminar) -->
                    <div style="position:absolute; top:8px; right:8px; display:flex; gap:5px; z-index:3;">
                        <a href="{{ route('documentos.edit', $doc->id) }}" class="btn-edit" title="Editar / Modificar" style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;padding:0;background:rgba(201,168,76,0.15);border-radius:6px;">
                            <svg viewBox="0 0 24 24" style="width:13px;height:13px;fill:var(--gold-dark);"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                        </a>
                        <form action="{{ route('documentos.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('¿Seguro que desea eliminar este documento?');" style="display:inline;margin:0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger" title="Eliminar" style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;padding:0;border-radius:6px;">
                                <svg viewBox="0 0 24 24" style="width:13px;height:13px;fill:currentColor;"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                            </button>
                        </form>
                    </div>

                    <!-- Enlace y Vista Previa Real -->
                    <a href="{{ route('documentos.descargar', $doc->id) }}" target="_blank" style="text-decoration:none; color:inherit; display:block;">
                        <div style="height:110px;width:100%;border-radius:6px;overflow:hidden;background:#f8fafc;display:flex;align-items:center;justify-content:center;margin-bottom:10px;border:1px solid #e2e8f0;">
                            @if(!$doc->es_drive && in_array(strtolower($doc->extension), ['jpg','jpeg','png','webp','gif']))
                                <img src="{{ route('documentos.descargar', $doc->id) }}" alt="{{ $doc->nombre }}" style="width:100%;height:100%;object-fit:cover;">
                            @elseif(!$doc->es_drive && strtolower($doc->extension) === 'pdf')
                                <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                                    <svg viewBox="0 0 24 24" style="width:40px;height:40px;fill:#e11d48;"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9.5 8.5h-2V13h2c.55 0 1-.45 1-1s-.45-.5-1-.5zm0-2.5h-2v1.5h2c.55 0 1-.45 1-1s-.45-.5-1-.5zm7 5h-1.5V11h-1v2.5h-1.5V9h4v5zm-3.5-3.5v-1h-1v1h1zM9.5 8H6v8h3.5c1.38 0 2.5-1.12 2.5-2.5v-3c0-1.38-1.12-2.5-2.5-2.5z"/></svg>
                                    <span style="font-size:0.7rem;font-weight:700;color:#e11d48;letter-spacing:0.5px;">DOCUMENTO PDF</span>
                                </div>
                            @elseif($doc->es_drive)
                                <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                                    <svg viewBox="0 0 24 24" style="width:40px;height:40px;fill:#4285f4;"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
                                    <span style="font-size:0.7rem;font-weight:700;color:#4285f4;">GOOGLE DRIVE</span>
                                </div>
                            @else
                                <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                                    <svg viewBox="0 0 24 24" style="width:38px;height:38px;fill:#64748b;"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                    <span style="font-size:0.7rem;font-weight:700;color:#64748b;">{{ strtoupper($doc->extension ?: 'DOC') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="doc-name" style="font-size:0.82rem;font-weight:600;color:var(--navy);margin-bottom:4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $doc->nombre }}">
                            {{ $doc->nombre }}
                        </div>
                        <div class="doc-meta" style="font-size:0.72rem;color:var(--text-gray);display:flex;justify-content:space-between;align-items:center;">
                            <span style="background:rgba(201,168,76,0.15);color:var(--gold-dark);padding:2px 6px;border-radius:4px;font-weight:600;">{{ $doc->tipo }}</span>
                            <span>{{ $doc->es_drive ? 'Drive' : $doc->tamano_formateado }}</span>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
            @else
            <div style="padding:20px;text-align:center;color:var(--text-gray);font-size:0.85rem;">Sin documentos</div>
            @endif
        </div>

        <!-- Observaciones -->
        @if($cliente->observaciones)
        <div class="table-card">
            <div class="table-card-header"><div class="table-card-title">OBSERVACIONES</div></div>
            <div style="padding:20px;font-size:0.88rem;color:var(--text-gray);line-height:1.7;">{{ $cliente->observaciones }}</div>
        </div>
        @endif
    </div>
</div>
@endsection

