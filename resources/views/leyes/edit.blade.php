@extends('layouts.dashboard')
@section('title','Editar Ley')
@section('page-title','Editar Ley')

@section('top-bar-actions')
<a href="{{ route('leyes.index') }}" class="btn-secondary">← Volver</a>
@endsection

@section('content')
<div class="form-card animate-in">
    <div class="form-card-header">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="#C9A84C"><path d="M11.5 2C6.81 2 3 5.81 3 10.5S6.81 19 11.5 19h.5v3c4.86-2.34 8-7 8-11.5C20 5.81 16.19 2 11.5 2z"/></svg>
        <h3>EDITAR: {{ $ley->numero }}</h3>
    </div>
    <form action="{{ route('leyes.update', $ley->id) }}" method="POST" enctype="multipart/form-data" onsubmit="const b=this.querySelector('button[type=submit]'); if(b){ b.disabled=true; b.innerHTML='Actualizando Ley...'; }">
        @csrf @method('PUT')
        <div class="form-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Número / Identificador *</label>
                    <input type="text" name="numero" class="form-control" value="{{ old('numero',$ley->numero) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Categoría *</label>
                    <select name="categoria" class="form-control" required>
                        @foreach(['Laboral','Civil','Penal','Familia','Comercial','Administrativo','Constitucional','Tributario','Seguridad Social','Otro'] as $cat)
                        <option value="{{ $cat }}" {{ $ley->categoria==$cat?'selected':'' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Título Completo *</label>
                    <input type="text" name="titulo" class="form-control" value="{{ old('titulo',$ley->titulo) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha de Promulgación</label>
                    <input type="date" name="fecha_promulgacion" class="form-control"
                           value="{{ old('fecha_promulgacion',$ley->fecha_promulgacion?->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Gaceta Oficial</label>
                    <input type="text" name="gaceta_oficial" class="form-control" value="{{ old('gaceta_oficial',$ley->gaceta_oficial) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="vigente" class="form-control">
                        <option value="1" {{ $ley->vigente?'selected':'' }}>🟢 Vigente</option>
                        <option value="0" {{ !$ley->vigente?'selected':'' }}>🔴 Derogada</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Enlace Texto Oficial</label>
                    <input type="url" name="link_oficial" class="form-control" value="{{ old('link_oficial',$ley->link_oficial) }}">
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Subir PDF Local de la Ley</label>
                    <input type="file" name="archivo_pdf" class="form-control" accept=".pdf">
                    <small style="color:var(--text-gray);">Si subes un PDF aquí, no dependerás de internet para leer la ley.</small>
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Descripción *</label>
                    <textarea name="descripcion" class="form-control" rows="6" required>{{ old('descripcion',$ley->descripcion) }}</textarea>
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Notas / Artículos Clave</label>
                    <textarea name="notas" class="form-control" rows="3">{{ old('notas',$ley->notas) }}</textarea>
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Texto Completo de la Ley (Opcional)</label>
                    <textarea name="articulos" class="form-control" rows="12" placeholder="Pegue aquí el texto completo de la ley (artículos, capítulos, etc.) si desea que se lea dentro del sistema...">{{ old('articulos',$ley->articulos) }}</textarea>
                    <small style="color:var(--text-gray);">Si deja esto vacío, los usuarios tendrán que hacer clic en 'Ver Texto Oficial' para leer la ley completa.</small>
                </div>
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ route('leyes.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">Actualizar Ley</button>
        </div>
    </form>
</div>
@endsection
