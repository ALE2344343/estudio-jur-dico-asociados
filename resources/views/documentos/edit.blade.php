@extends('layouts.dashboard')

@section('title', 'Editar Documento')
@section('page-title', 'Editar Documento')

@section('top-bar-actions')
<a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('documentos.index') }}" class="btn-secondary">← Volver</a>
@endsection

@section('content')
<div class="card" style="max-width:750px;margin:15px auto;padding:30px;background:#ffffff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.06);border:1px solid rgba(201,168,76,0.2);">

    <div style="margin-bottom:25px;border-bottom:1px solid #f0f0f0;padding-bottom:15px;">
        <h3 style="font-family:'Cinzel',serif;color:var(--navy);margin:0 0 6px 0;font-size:1.3rem;">
            MODIFICAR DOCUMENTO
        </h3>
        <span style="font-size:0.82rem;color:var(--text-gray);">
            Código: <strong style="color:var(--gold-dark);">{{ $documento->codigo }}</strong>
            @if($documento->caso) | Expediente: <strong>{{ $documento->caso->numero_caso }}</strong> @endif
        </span>
    </div>

    <form action="{{ route('documentos.update', $documento->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        @if ($errors->any())
            <div style="background:rgba(220,53,69,0.1);color:#d9534f;padding:12px 15px;border-radius:8px;margin-bottom:20px;font-size:0.85rem;">
                <strong>Por favor revise los siguientes campos:</strong>
                <ul style="margin:5px 0 0 15px;padding:0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-grid" style="display:flex;flex-direction:column;gap:20px;">
            <!-- 1. Nombre del Documento * -->
            <div class="form-group">
                <label class="form-label" style="font-weight:600;color:var(--navy);margin-bottom:6px;display:block;">Nombre del Documento *</label>
                <input type="text" name="nombre" class="form-control" required value="{{ old('nombre', $documento->nombre) }}" placeholder="Ej: Contrato de Arrendamiento">
            </div>

            <!-- 2. Tipo de Documento * -->
            <div class="form-group">
                <label class="form-label" style="font-weight:600;color:var(--navy);margin-bottom:6px;display:block;">Tipo de Documento *</label>
                <select name="tipo" class="form-control" required>
                    @foreach(['Demanda','Contrato','Acuerdo','Sentencia','Auto','Memorial','Poder Notarial','Prueba','Certificado','Resolución','Recurso','Apelación','Denuncia','Otro'] as $t)
                    <option value="{{ $t }}" {{ (old('tipo', $documento->tipo) == $t) ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 3. Reemplazar Archivo (Opcional) con dos opciones -->
            <div class="form-group" style="background:#f8fafc;padding:18px;border-radius:10px;border:1px solid #e2e8f0;">
                <label class="form-label" style="font-weight:600;color:var(--navy);margin-bottom:8px;display:block;">
                    Reemplazar Archivo (Opcional)
                </label>
                
                <div style="font-size:0.8rem;color:var(--text-gray);margin-bottom:12px;">
                    Archivo actual: 
                    @if($documento->es_drive)
                        <strong style="color:#4285f4;">📁 Enlace de Google Drive</strong>
                    @elseif($documento->archivo)
                        <strong style="color:var(--navy);">📄 {{ basename($documento->archivo) }}</strong> ({{ $documento->tamano_formateado }})
                    @else
                        <em>Sin archivo</em>
                    @endif
                </div>

                <!-- Botones de alternancia -->
                <div style="display:flex;gap:10px;margin-bottom:15px;">
                    <button type="button" onclick="toggleFuenteE('local')" id="btnLocalE"
                            style="flex:1;padding:10px 14px;border-radius:8px;border:2px solid {{ $documento->es_drive ? '#ccc' : 'var(--gold)' }};background:{{ $documento->es_drive ? 'white' : 'rgba(201,168,76,0.1)' }};color:{{ $documento->es_drive ? 'var(--text-gray)' : 'var(--navy)' }};cursor:pointer;font-weight:600;font-size:0.82rem;display:flex;align-items:center;justify-content:center;gap:6px;">
                        <span>💻</span> SUBIR ARCHIVO DESDE LA PC
                    </button>
                    <button type="button" onclick="toggleFuenteE('drive')" id="btnDriveE"
                            style="flex:1;padding:10px 14px;border-radius:8px;border:2px solid {{ $documento->es_drive ? '#4285f4' : '#ccc' }};background:{{ $documento->es_drive ? 'rgba(66,133,244,0.1)' : 'white' }};color:{{ $documento->es_drive ? '#4285f4' : 'var(--text-gray)' }};cursor:pointer;font-weight:600;font-size:0.82rem;display:flex;align-items:center;justify-content:center;gap:6px;">
                        <span>📁</span> NUEVO LINK DE GOOGLE DRIVE
                    </button>
                </div>

                <!-- Opción 1: Archivo local desde PC -->
                <div id="secLocalE" style="{{ $documento->es_drive ? 'display:none;' : '' }}">
                    <input type="file" name="archivo" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp,.gif,.zip">
                    <small style="color:var(--text-gray);font-size:0.75rem;display:block;margin-top:5px;">
                        Selecciona un nuevo archivo si deseas reemplazar el actual (PDF, Word, Excel, Imágenes, ZIP).
                    </small>
                </div>

                <!-- Opción 2: Enlace de Google Drive -->
                <div id="secDriveE" style="{{ $documento->es_drive ? '' : 'display:none;' }}">
                    <input type="hidden" name="es_drive" id="esDriveE" value="{{ $documento->es_drive ? '1' : '0' }}">
                    <input type="url" name="drive_link" class="form-control" placeholder="https://drive.google.com/file/d/..." value="{{ old('drive_link', $documento->drive_link) }}">
                    <small style="color:#4285f4;font-size:0.75rem;display:block;margin-top:5px;">
                        Pega el enlace público o compartido de Google Drive.
                    </small>
                </div>
            </div>

            <!-- 4. Descripción / Notas -->
            <div class="form-group">
                <label class="form-label" style="font-weight:600;color:var(--navy);margin-bottom:6px;display:block;">Descripción / Notas</label>
                <textarea name="notas" class="form-control" rows="4" placeholder="Agregue observaciones o detalles adicionales sobre este documento...">{{ old('notas', $documento->notas ?? $documento->descripcion) }}</textarea>
            </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:25px;border-top:1px solid #f0f0f0;padding-top:20px;">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('documentos.index') }}" class="btn-secondary" style="padding:10px 20px;">Cancelar</a>
            <button type="submit" class="btn-primary" style="padding:10px 24px;">💾 Guardar Cambios</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function toggleFuenteE(tipo) {
    const local = document.getElementById('secLocalE');
    const drive = document.getElementById('secDriveE');
    const btnL  = document.getElementById('btnLocalE');
    const btnD  = document.getElementById('btnDriveE');
    const esDriveIn = document.getElementById('esDriveE');

    if (tipo === 'local') {
        local.style.display = 'block'; 
        drive.style.display = 'none';
        btnL.style.borderColor = 'var(--gold)'; 
        btnL.style.background = 'rgba(201,168,76,0.1)'; 
        btnL.style.color = 'var(--navy)';
        btnD.style.borderColor = '#ccc'; 
        btnD.style.background = 'white'; 
        btnD.style.color = 'var(--text-gray)';
        esDriveIn.value = '0';
    } else {
        local.style.display = 'none'; 
        drive.style.display = 'block';
        btnD.style.borderColor = '#4285f4'; 
        btnD.style.background = 'rgba(66,133,244,0.1)'; 
        btnD.style.color = '#4285f4';
        btnL.style.borderColor = '#ccc'; 
        btnL.style.background = 'white'; 
        btnL.style.color = 'var(--text-gray)';
        esDriveIn.value = '1';
    }
}
</script>
@endpush
