@extends('layouts.dashboard')
@section('title','Nueva Ley')
@section('page-title','Agregar Ley')

@section('top-bar-actions')
<a href="{{ route('leyes.index') }}" class="btn-secondary">← Volver</a>
@endsection

@section('content')
<div class="form-card animate-in">
    <div class="form-card-header">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="#C9A84C"><path d="M11.5 2C6.81 2 3 5.81 3 10.5S6.81 19 11.5 19h.5v3c4.86-2.34 8-7 8-11.5C20 5.81 16.19 2 11.5 2z"/></svg>
        <h3>REGISTRAR NUEVA LEY / NORMATIVA</h3>
    </div>
    <form action="{{ route('leyes.store') }}" method="POST" enctype="multipart/form-data" onsubmit="const b=this.querySelector('button[type=submit]'); if(b){ b.disabled=true; b.innerHTML='Guardando Ley...'; }">
        @csrf
        <div class="form-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Número / Identificador *</label>
                    <input type="text" name="numero" class="form-control" value="{{ old('numero') }}"
                           required placeholder="Ej: Ley 321 | D.S. 28699 | Art. 13 LGT">
                    @error('numero')<span style="color:#dc3545;font-size:0.78rem;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Categoría *</label>
                    <select name="categoria" class="form-control" required>
                        @foreach(['Laboral','Civil','Penal','Familia','Comercial','Administrativo','Constitucional','Tributario','Seguridad Social','Otro'] as $cat)
                        <option value="{{ $cat }}" {{ old('categoria')==$cat?'selected':'' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Título Completo *</label>
                    <input type="text" name="titulo" class="form-control" value="{{ old('titulo') }}"
                           required placeholder="Ej: Ley General del Trabajo — 8 de diciembre de 1942">
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha de Promulgación</label>
                    <input type="date" name="fecha_promulgacion" class="form-control" value="{{ old('fecha_promulgacion') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Gaceta Oficial</label>
                    <input type="text" name="gaceta_oficial" class="form-control" value="{{ old('gaceta_oficial') }}"
                           placeholder="Ej: G.O. N° 2341">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="vigente" class="form-control">
                        <option value="1" selected>🟢 Vigente</option>
                        <option value="0">🔴 Derogada</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Enlace Texto Oficial</label>
                    <input type="url" name="link_oficial" class="form-control" value="{{ old('link_oficial') }}"
                           placeholder="https://...">
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Subir PDF Local de la Ley</label>
                    <input type="file" name="archivo_pdf" class="form-control" accept=".pdf">
                    <small style="color:var(--text-gray);">Sube la ley en PDF para verla sin internet.</small>
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Descripción / Contenido Principal *</label>
                    <textarea name="descripcion" class="form-control" rows="6" required
                              placeholder="Descripción detallada de la ley, sus alcances, artículos relevantes...">{{ old('descripcion') }}</textarea>
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Notas / Artículos Clave</label>
                    <textarea name="notas" class="form-control" rows="3"
                              placeholder="Ej: Art. 12 — Desahucio. Art. 13 — Indemnización...">{{ old('notas') }}</textarea>
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Texto Completo de la Ley (Opcional)</label>
                    <textarea name="articulos" class="form-control" rows="12" placeholder="Pegue aquí el texto de los artículos si desea leerlos sin abrir el PDF...">{{ old('articulos') }}</textarea>
                </div>
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ route('leyes.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">Guardar Ley</button>
        </div>
    </form>
</div>
@endsection
