@extends('layouts.dashboard')
@section('title','Ver Documento')
@section('page-title','Documento')

@section('top-bar-actions')
<a href="{{ route('documentos.index') }}" class="btn-secondary">← Volver</a>
@if($documento->archivo)
<a href="{{ asset('storage/'.$documento->archivo) }}" download class="btn-primary">⬇️ Descargar</a>
@elseif($documento->es_drive)
<a href="{{ $documento->drive_link }}" target="_blank" class="btn-primary">📁 Abrir en Drive</a>
@endif
@endsection

@section('content')
<div style="display:grid;grid-template-columns:1fr 2fr;gap:25px;" class="animate-in">

    <!-- Panel lateral -->
    <div>
        <div style="background:var(--gradient-navy);border-radius:12px;padding:30px;text-align:center;margin-bottom:20px;">
            <div style="font-size:5rem;margin-bottom:15px;">{{ $documento->icono }}</div>
            <div style="font-family:'Cinzel',serif;font-size:0.9rem;color:var(--gold);margin-bottom:5px;">{{ $documento->codigo }}</div>
            <div style="font-size:0.82rem;color:rgba(255,255,255,0.6);">{{ $documento->tipo }}</div>
            @if($documento->es_drive)
            <div style="margin-top:10px;"><span class="badge" style="background:rgba(66,133,244,0.2);color:#4285f4;border:1px solid rgba(66,133,244,0.3);">📁 Google Drive</span></div>
            @endif
        </div>

        <div class="table-card">
            <div class="table-card-header"><div class="table-card-title">Detalles</div></div>
            <div style="padding:15px 20px;">
                @foreach([
                    ['Nombre', $documento->nombre],
                    ['Tipo', $documento->tipo],
                    ['Extensión', strtoupper($documento->extension ?? 'Drive')],
                    ['Tamaño', $documento->tamano_formateado],
                    ['Versión', 'v'.$documento->version],
                    ['Subido por', $documento->autor?->nombre_completo],
                    ['Fecha', $documento->created_at->format('d/m/Y H:i')],
                    ['Caso', $documento->caso?->numero_caso],
                    ['Cliente', $documento->cliente?->nombre_completo],
                ] as [$l,$v])
                @if($v)
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(201,168,76,0.1);font-size:0.83rem;">
                    <span style="color:var(--text-gray);">{{ $l }}</span>
                    <span style="color:var(--navy);font-weight:500;max-width:60%;text-align:right;">{{ $v }}</span>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div>
        @if($documento->descripcion || $documento->notas)
        <div class="table-card" style="margin-bottom:20px;">
            <div class="table-card-header"><div class="table-card-title">📝 DESCRIPCIÓN</div></div>
            <div style="padding:20px;">
                @if($documento->descripcion)
                <p style="font-size:0.88rem;color:var(--text-gray);line-height:1.7;margin-bottom:12px;">{{ $documento->descripcion }}</p>
                @endif
                @if($documento->notas)
                <div class="ley-ref">{{ $documento->notas }}</div>
                @endif
            </div>
        </div>
        @endif

        <!-- Vista previa o acceso -->
        <div class="table-card">
            <div class="table-card-header"><div class="table-card-title">📄 ACCESO AL ARCHIVO</div></div>
            <div style="padding:25px;text-align:center;">
                @if($documento->es_drive)
                <div style="margin-bottom:20px;">
                    <div style="font-size:4rem;margin-bottom:15px;">📁</div>
                    <div style="font-family:'Cinzel',serif;font-size:0.9rem;color:var(--navy);margin-bottom:8px;">Documento en Google Drive</div>
                    <p style="font-size:0.85rem;color:var(--text-gray);margin-bottom:20px;">Este documento está almacenado en la Biblioteca de Expedientes de Google Drive.</p>
                    <a href="{{ $documento->drive_link }}" target="_blank" class="btn-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
                        Abrir en Google Drive
                    </a>
                </div>
                @elseif($documento->archivo)
                    @if(in_array($documento->extension, ['jpg','jpeg','png','gif','webp']))
                    <img src="{{ asset('storage/'.$documento->archivo) }}" style="max-width:100%;max-height:500px;border-radius:8px;box-shadow:0 4px 15px rgba(0,0,0,0.1);">
                    @elseif($documento->extension === 'pdf')
                    <iframe src="{{ asset('storage/'.$documento->archivo) }}" style="width:100%;height:500px;border:none;border-radius:8px;"></iframe>
                    @else
                    <div style="margin-bottom:20px;">
                        <div style="font-size:4rem;margin-bottom:10px;">{{ $documento->icono }}</div>
                        <div style="font-size:0.88rem;color:var(--text-gray);margin-bottom:20px;">
                            Archivo: <strong>{{ basename($documento->archivo) }}</strong> ({{ $documento->tamano_formateado }})
                        </div>
                        <a href="{{ asset('storage/'.$documento->archivo) }}" download class="btn-primary">⬇️ Descargar Archivo</a>
                    </div>
                    @endif
                @else
                <div style="color:var(--text-gray);font-size:0.88rem;">No hay archivo disponible</div>
                @endif
            </div>
        </div>

        <!-- Editar y Eliminar -->
        <div style="margin-top:20px;display:flex;justify-content:flex-end;gap:8px;">
            <a href="{{ route('documentos.edit',$documento->id) }}" class="btn-edit" title="Editar" style="display:inline-flex;align-items:center;justify-content:center;">
                <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
            </a>
            <form action="{{ route('documentos.destroy',$documento->id) }}" method="POST" style="display:inline;margin:0;">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger" onclick="return confirm('¿Eliminar este documento?')" title="Eliminar" style="display:inline-flex;align-items:center;justify-content:center;">
                    <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
