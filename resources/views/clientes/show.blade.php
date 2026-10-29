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
            <div style="padding:15px 20px;display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
                @foreach($cliente->documentos as $doc)
                <a href="{{ $doc->es_drive ? $doc->drive_link : asset('storage/'.$doc->archivo) }}"
                   target="_blank" class="doc-card" style="padding:12px;">
                    <span class="doc-icon" style="font-size:2rem;">{{ $doc->icono }}</span>
                    <div class="doc-name" style="font-size:0.78rem;">{{ Str::limit($doc->nombre,20) }}</div>
                    <div class="doc-meta">{{ $doc->tipo }}</div>
                </a>
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

