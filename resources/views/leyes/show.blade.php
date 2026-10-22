@extends('layouts.dashboard')
@section('title', $ley->numero)
@section('page-title', 'Detalle de Ley')

@section('top-bar-actions')
@if(session('usuario_rol') === 'administrador')
<a href="{{ route('leyes.edit', $ley->id) }}" class="btn-edit" title="Editar" style="display:inline-flex;align-items:center;justify-content:center;">
    <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
</a>
<form action="{{ route('leyes.destroy', $ley->id) }}" method="POST" style="display:inline;margin:0;">
    @csrf @method('DELETE')
    <button type="submit" class="btn-danger" onclick="return confirm('¿Eliminar esta ley del registro?')" title="Eliminar" style="display:inline-flex;align-items:center;justify-content:center;">
        <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
    </button>
</form>
@endif
<a href="{{ route('leyes.index') }}" class="btn-secondary">← Volver</a>
@endsection

@section('content')
<div style="max-width:860px;margin:0 auto;" class="animate-in">

    <!-- Encabezado -->
    <div style="background:var(--gradient-navy);border-radius:14px;padding:30px;margin-bottom:20px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:20px;">
            <div>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                    <span style="background:var(--gradient-gold);color:var(--navy-dark);font-family:'Cinzel',serif;font-size:0.78rem;font-weight:700;padding:5px 14px;border-radius:5px;letter-spacing:1px;">
                        {{ $ley->numero }}
                    </span>
                    <span class="badge {{ $ley->vigente ? 'badge-active' : 'badge-closed' }}">
                        {{ $ley->vigente ? '✅ Vigente' : '❌ Derogada' }}
                    </span>
                    <span class="badge badge-pending">{{ $ley->categoria }}</span>
                </div>
                <h2 style="font-family:'Cinzel',serif;font-size:1.2rem;color:white;line-height:1.5;margin-bottom:10px;">
                    {{ $ley->titulo }}
                </h2>
                @if($ley->fecha_promulgacion)
                <div style="font-size:0.82rem;color:rgba(255,255,255,0.5);">
                    Promulgada el {{ $ley->fecha_promulgacion->format('d/m/Y') }}
                    @if($ley->gaceta_oficial)
                    — Gaceta Oficial: {{ $ley->gaceta_oficial }}
                    @endif
                </div>
                @endif
            </div>
            @if($ley->link_oficial)
            <a href="{{ $ley->link_oficial }}" target="_blank" class="btn-primary" style="flex-shrink:0;">
                Texto Oficial
            </a>
            @endif
        </div>
    </div>

    <!-- Descripción -->
    <div class="table-card" style="margin-bottom:20px;">
        <div class="table-card-header">
            <div class="table-card-title">CONTENIDO / DESCRIPCIÓN</div>
        </div>
        <div style="padding:25px;font-size:0.9rem;line-height:1.8;color:var(--text-gray);white-space:pre-line;">{{ $ley->descripcion }}</div>
    </div>

    <!-- Notas / Artículos clave -->
    @if($ley->notas)
    <div class="table-card" style="margin-bottom:20px;">
        <div class="table-card-header">
            <div class="table-card-title">ARTÍCULOS CLAVE / NOTAS</div>
        </div>
        <div style="padding:20px;">
            <div class="ley-ref" style="font-style:normal;font-size:0.88rem;line-height:1.8;white-space:pre-line;">{{ $ley->notas }}</div>
        </div>
    </div>
    @endif

    <!-- Acciones admin -->
    @if(session('usuario_rol') === 'administrador')
    <div style="text-align:right;margin-top:10px;">
        <form action="{{ route('leyes.destroy', $ley->id) }}" method="POST" style="display:inline;">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger" title="Eliminar" style="display:inline-flex;align-items:center;justify-content:center;" onclick="return confirm('¿Eliminar esta ley del registro?')">
                <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
            </button>
        </form>
    </div>
    @endif
</div>
@endsection
