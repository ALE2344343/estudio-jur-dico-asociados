@extends('layouts.dashboard')
@section('title','Detalle Caso')
@section('page-title','Expediente')

@section('top-bar-actions')
@if(session('usuario_rol') === 'administrador')
<a href="{{ route('casos.edit', $caso->id) }}" class="btn-edit" title="Editar" style="display:inline-flex;align-items:center;justify-content:center;">
    <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
</a>
<form action="{{ route('casos.destroy', $caso->id) }}" method="POST" style="display:inline;margin:0;">
    @csrf @method('DELETE')
    <button type="submit" class="btn-danger" onclick="return confirm('¿Eliminar este caso?')" title="Eliminar" style="display:inline-flex;align-items:center;justify-content:center;">
        <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
    </button>
</form>
@endif
<a href="{{ route('casos.index') }}" class="btn-secondary">← Volver</a>
@endsection

@section('content')
<div style="display:grid;grid-template-columns:1fr 2fr;gap:25px;">

    <!-- Panel lateral -->
    <div>
        <div style="background:var(--gradient-navy);border-radius:12px;padding:25px;margin-bottom:20px;">
            <div style="font-family:'Cinzel',serif;font-size:0.75rem;color:var(--gold);letter-spacing:3px;margin-bottom:8px;">EXPEDIENTE</div>
            <div style="font-family:'Cinzel',serif;font-size:1.3rem;color:white;margin-bottom:12px;">{{ $caso->numero_caso }}</div>
            @php $be = $caso->badge_estado; $bp = $caso->badge_prioridad; @endphp
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <span class="badge {{ $be[0] }}">{{ $be[1] }}</span>
                <span class="badge {{ $bp[0] }}">{{ $bp[1] }}</span>
                <span class="badge badge-closed">{{ $caso->tipo }}</span>
            </div>
        </div>

        <div class="table-card">
            <div class="table-card-header"><div class="table-card-title">Detalles</div></div>
            <div style="padding:15px 20px;">
                @foreach([
                    ['Cliente',$caso->cliente?->nombre_completo],
                    ['Abogado',$caso->abogado?->nombre_completo],
                    ['Contraparte',$caso->contraparte],
                    ['Inicio',$caso->fecha_inicio?->format('d/m/Y')],
                    ['Vencimiento',$caso->fecha_vencimiento?->format('d/m/Y')],
                    ['Juzgado',$caso->juzgado],
                    ['N° Expediente',$caso->numero_expediente],
                    ['Honorarios', $caso->honorarios ? 'Bs. '.number_format($caso->honorarios,2) : null],
                ] as [$l,$v])
                @if($v)
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(201,168,76,0.1);font-size:0.83rem;">
                    <span style="color:var(--text-gray);">{{ $l }}</span>
                    <span style="color:var(--navy);font-weight:500;text-align:right;max-width:60%;">{{ $v }}</span>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Contenido principal -->
    <div>
        <!-- Descripción -->
        @if($caso->descripcion)
        <div class="table-card" style="margin-bottom:20px;">
            <div class="table-card-header"><div class="table-card-title">DESCRIPCIÓN</div></div>
            <div style="padding:20px;font-size:0.88rem;line-height:1.7;color:var(--text-gray);">{{ $caso->descripcion }}</div>
        </div>
        @endif

        <!-- Audiencias Programadas -->
        <div class="table-card" style="margin-bottom:20px;">
            <div class="table-card-header">
                <div class="table-card-title">AUDIENCIAS PROGRAMADAS ({{ $caso->audiencias->count() }})</div>
                @if(session('usuario_rol') === 'administrador' || session('usuario_id') === $caso->abogado_id)
                <button onclick="document.getElementById('modalAud').style.display='flex'" class="btn-primary" style="font-size:0.72rem;padding:6px 12px;">+ Programar</button>
                @endif
            </div>
            @if($caso->audiencias->count() > 0)
            <div style="padding:15px;">
                @foreach($caso->audiencias as $aud)
                <div style="padding:12px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <strong style="color:var(--gold-dark);display:block;margin-bottom:4px;">{{ \Carbon\Carbon::parse($aud->fecha_hora)->format('d/m/Y H:i') }}</strong>
                        <span style="color:var(--navy);font-weight:600;">{{ $aud->titulo }}</span><br>
                        <span style="color:var(--text-gray);font-size:0.8rem;">Lugar: {{ $aud->lugar ?? 'N/A' }} | Estado: {{ ucfirst($aud->estado) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div style="padding:25px;text-align:center;color:var(--text-gray);">Sin audiencias programadas</div>
            @endif
        </div>

        <!-- Documentos del caso -->
        <div class="table-card" style="margin-bottom:20px;">
            <div class="table-card-header">
                <div class="table-card-title">DOCUMENTOS ({{ $caso->documentos->count() }})</div>
                <button onclick="document.getElementById('modalDoc').style.display='flex'" class="btn-primary" style="font-size:0.72rem;padding:6px 12px;">+ Agregar</button>
            </div>
            @if($caso->documentos->count() > 0)
            <div style="padding:15px;display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
                @foreach($caso->documentos as $doc)
                <a href="{{ $doc->es_drive ? $doc->drive_link : asset('storage/'.$doc->archivo) }}"
                   target="_blank" class="doc-card" style="padding:12px;">
                    <span class="doc-icon" style="font-size:2rem;">{{ $doc->icono }}</span>
                    <div class="doc-name" style="font-size:0.78rem;">{{ Str::limit($doc->nombre,20) }}</div>
                    <div class="doc-meta">{{ $doc->tipo }}</div>
                </a>
                @endforeach
            </div>
            @else
            <div style="padding:25px;text-align:center;color:var(--text-gray);">Sin documentos adjuntos</div>
            @endif
        </div>

        <!-- Notas privadas -->
        @if($caso->notas_privadas)
        <div class="table-card">
            <div class="table-card-header"><div class="table-card-title">NOTAS PRIVADAS</div></div>
            <div style="padding:20px;font-size:0.88rem;line-height:1.7;color:var(--text-gray);font-style:italic;">{{ $caso->notas_privadas }}</div>
        </div>
        @endif
    </div>
</div>

<!-- Modal Documento -->
<div id="modalDoc" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:9999;">
    <div style="background:white;padding:25px;border-radius:12px;width:100%;max-width:450px;">
        <h3 style="margin-top:0;color:var(--navy);font-family:'Cinzel',serif;">Subir Documento</h3>
        <form action="{{ route('documentos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="caso_id" value="{{ $caso->id }}">
            <input type="hidden" name="cliente_id" value="{{ $caso->cliente_id }}">
            <div class="form-group" style="margin-bottom:15px;">
                <label class="form-label">Nombre del Documento *</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="form-group" style="margin-bottom:15px;">
                <label class="form-label">Tipo *</label>
                <select name="tipo" class="form-control" required>
                    <option value="Memorial">Memorial</option>
                    <option value="Prueba">Prueba</option>
                    <option value="Poder">Poder</option>
                    <option value="Contrato">Contrato</option>
                    <option value="Resolución">Resolución</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:15px;">
                <label class="form-label">Archivo (PDF, Word, Imágenes)</label>
                <input type="file" name="archivo" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('modalDoc').style.display='none'">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Audiencia -->
<div id="modalAud" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:9999;">
    <div style="background:white;padding:25px;border-radius:12px;width:100%;max-width:400px;">
        <h3 style="margin-top:0;color:var(--navy);font-family:'Cinzel',serif;">Programar Audiencia</h3>
        <form action="{{ route('audiencias.store', $caso->id) }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom:15px;">
                <label class="form-label">Título de la Audiencia</label>
                <input type="text" name="titulo" class="form-control" required>
            </div>
            <div class="form-group" style="margin-bottom:15px;">
                <label class="form-label">Fecha y Hora</label>
                <input type="datetime-local" name="fecha_hora" class="form-control" required>
            </div>
            <div class="form-group" style="margin-bottom:15px;">
                <label class="form-label">Lugar / Enlace</label>
                <input type="text" name="lugar" class="form-control">
            </div>
            <div class="form-group" style="margin-bottom:15px;">
                <label class="form-label">Avisarme con anticipación</label>
                <select name="recordatorio_minutos" class="form-control" style="width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;background:white;">
                    <option value="5">5 minutos antes</option>
                    <option value="10">10 minutos antes</option>
                    <option value="15" selected>15 minutos antes (Recomendado)</option>
                    <option value="30">30 minutos antes</option>
                    <option value="60">1 hora antes</option>
                    <option value="0">A la hora exacta</option>
                </select>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('modalAud').style.display='none'">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection



