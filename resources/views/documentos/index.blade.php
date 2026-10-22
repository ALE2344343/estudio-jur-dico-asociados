@extends('layouts.dashboard')
@section('title','Documentos')
@section('page-title','Documentos')

@section('top-bar-actions')
<a href="{{ $driveLink }}" target="_blank" class="btn-secondary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
    📁 Biblioteca Drive
</a>
<button onclick="document.getElementById('modalDoc').style.display='flex'" class="btn-primary">
    + Nuevo Documento
</button>
@endsection

@section('content')
<!-- Biblioteca Google Drive destacada -->
<div style="background:linear-gradient(135deg,var(--navy),var(--navy-light));border-radius:12px;padding:20px 25px;margin-bottom:25px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 4px 15px rgba(26,39,68,0.3);" class="animate-in">
    <div style="display:flex;align-items:center;gap:16px;">
        <div style="font-size:2.5rem;">📁</div>
        <div>
            <div style="font-family:'Cinzel',serif;font-size:1rem;color:var(--gold);letter-spacing:2px;margin-bottom:4px;">BIBLIOTECA DE EXPEDIENTES</div>
            <div style="font-size:0.85rem;color:rgba(255,255,255,0.7);">Repositorio de documentos en Google Drive — Estudio Jurídico & Asociados</div>
        </div>
    </div>
    <a href="{{ $driveLink }}" target="_blank" class="btn-ingresar">ABRIR CARPETA</a>
</div>

<!-- Filtros -->
<div class="table-card" style="margin-bottom:20px;">
    <div style="padding:15px 20px;">
        <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
            <div class="search-box" style="flex:1;min-width:200px;">
                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                <input name="search" placeholder="Buscar por nombre, código, caso o cliente..." value="{{ request('search') }}">
            </div>
            <select name="tipo" class="form-control" style="width:180px;">
                <option value="">Todos los tipos</option>
                @foreach(['Demanda','Contrato','Acuerdo','Sentencia','Auto','Memorial','Poder Notarial','Certificado','Resolución','Recurso','Apelación','Denuncia','Otro'] as $t)
                <option value="{{ $t }}" {{ request('tipo')==$t?'selected':'' }}>{{ $t }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary">Buscar</button>
            <a href="{{ route('documentos.index') }}" class="btn-secondary">Actualizar</a>
        </form>
    </div>
</div>

<!-- Grid de documentos -->
@if($documentos->count() > 0)
<div class="docs-grid animate-in">
    @foreach($documentos as $doc)
    <div class="doc-card" style="position:relative;">
        <div style="position:absolute; top:10px; right:10px; display:flex; gap:6px;">
            <a href="{{ route('documentos.edit', $doc->id) }}" class="btn-edit" title="Editar" style="display:inline-flex;align-items:center;justify-content:center;">
                <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
            </a>
            <form action="{{ route('documentos.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('¿Seguro que desea eliminar este documento?');" style="display:inline;margin:0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger" title="Eliminar" style="display:inline-flex;align-items:center;justify-content:center;">
                    <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                </button>
            </form>
        </div>
        
        <a href="{{ $doc->es_drive ? $doc->drive_link : asset('storage/'.$doc->archivo) }}"
           target="_blank" style="text-decoration:none; color:inherit; display:block;" title="{{ $doc->descripcion }}">
            <span class="doc-icon">{{ $doc->icono }}</span>
            <div class="doc-name">{{ $doc->nombre }}</div>
            <div class="doc-meta">
                {{ $doc->tipo }}<br>
                @if($doc->es_drive)
                <span style="color:#4285f4;font-size:0.7rem;">📁 Google Drive</span>
                @else
                {{ $doc->tamano_formateado }}
                @endif
            </div>
            <div style="margin-top:8px;font-size:0.72rem;color:var(--text-gray);">
                {{ $doc->created_at->format('d/m/Y') }}
            </div>
            @if($doc->caso)
            <div style="margin-top:5px;">
                <span class="badge badge-pending" style="font-size:0.65rem;">{{ $doc->caso->numero_caso }}</span>
            </div>
            @endif
        </a>
    </div>
    @endforeach
</div>
<div style="margin-top:20px;">{{ $documentos->links() }}</div>
@else
<div style="text-align:center;padding:60px;background:white;border-radius:12px;border:1px solid rgba(201,168,76,0.15);">
    <div style="font-size:4rem;margin-bottom:15px;">📂</div>
    <div style="font-family:'Cinzel',serif;font-size:1rem;color:var(--navy);margin-bottom:8px;">No hay documentos registrados</div>
    <p style="color:var(--text-gray);font-size:0.88rem;margin-bottom:20px;">Puede subir archivos o vincular documentos de Google Drive</p>
    <div style="display:flex;gap:12px;justify-content:center;">
        <button onclick="document.getElementById('modalDoc').style.display='flex'" class="btn-primary">+ Agregar Documento</button>
        <a href="{{ $driveLink }}" target="_blank" class="btn-secondary">📁 Ver Drive</a>
    </div>
</div>
@endif

<!-- Modal para agregar documento -->
<div id="modalDoc" data-open="{{ $errors->any() ? '1' : '0' }}" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:12px;width:600px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div style="background:var(--gradient-navy);padding:20px 25px;display:flex;align-items:center;justify-content:space-between;border-radius:12px 12px 0 0;">
            <h3 style="font-family:'Cinzel',serif;color:var(--gold);letter-spacing:2px;font-size:0.95rem;">REGISTRAR DOCUMENTO</h3>
            <button onclick="document.getElementById('modalDoc').style.display='none'" style="background:none;border:none;color:white;font-size:1.5rem;cursor:pointer;">✕</button>
        </div>
        <form action="{{ route('documentos.store') }}" method="POST" enctype="multipart/form-data" style="padding:25px;">
            @csrf
            
            @if ($errors->any())
                <div style="background:rgba(220,53,69,0.1);color:#d9534f;padding:10px;border-radius:8px;margin-bottom:15px;font-size:0.85rem;">
                    <strong>Errores detectados:</strong>
                    <ul style="margin:5px 0 0 20px;padding:0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-grid" style="gap:15px;">
                <div class="form-group span-2">
                    <label class="form-label">Nombre del Documento *</label>
                    <input type="text" name="nombre" class="form-control" required placeholder="Ej: Demanda por despido injustificado">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo *</label>
                    <select name="tipo" class="form-control" required>
                        @foreach(['Demanda','Contrato','Acuerdo','Sentencia','Auto','Memorial','Poder Notarial','Certificado','Resolución','Recurso','Apelación','Denuncia','Otro'] as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Caso Relacionado</label>
                    <select name="caso_id" class="form-control">
                        <option value="">Sin caso</option>
                        @foreach($casos as $c)
                        <option value="{{ $c->id }}">{{ $c->numero_caso }} — {{ $c->titulo }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Toggle: Archivo o Drive -->
                <div class="form-group span-2">
                    <label class="form-label">Tipo de Fuente</label>
                    <div style="display:flex;gap:10px;">
                        <button type="button" onclick="toggleFuente('local')" id="btnLocal"
                                style="flex:1;padding:10px;border-radius:8px;border:2px solid var(--gold);background:rgba(201,168,76,0.1);color:var(--navy);cursor:pointer;font-family:'Cinzel',serif;font-size:0.8rem;letter-spacing:1px;">
                            📄 ARCHIVO LOCAL
                        </button>
                        <button type="button" onclick="toggleFuente('drive')" id="btnDrive"
                                style="flex:1;padding:10px;border-radius:8px;border:2px solid #ccc;background:white;color:var(--text-gray);cursor:pointer;font-family:'Cinzel',serif;font-size:0.8rem;letter-spacing:1px;">
                            📁 GOOGLE DRIVE
                        </button>
                    </div>
                </div>

                <div id="secLocal" class="form-group span-2">
                    <label class="form-label">Subir Archivo</label>
                    <input type="file" name="archivo" class="form-control"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip">
                    <small style="color:var(--text-gray);font-size:0.72rem;">Formatos: PDF, Word, Excel, Imágenes, ZIP</small>
                </div>

                <div id="secDrive" class="form-group span-2" style="display:none;">
                    <label class="form-label">Enlace de Google Drive</label>
                    <input type="hidden" name="es_drive" id="esDrive" value="0">
                    <input type="url" name="drive_link" class="form-control"
                           placeholder="https://drive.google.com/file/d/..."
                           value="https://drive.google.com/drive/folders/1m6zAmvyumCIE_eChPpWaKxUjk-lFe89J">
                    <small style="color:#4285f4;font-size:0.72rem;">📁 Carpeta: Biblioteca de Expedientes</small>
                </div>

                <div class="form-group span-2">
                    <label class="form-label">Descripción / Notas</label>
                    <textarea name="notas" class="form-control" rows="3" placeholder="Observaciones sobre el documento..."></textarea>
                </div>
                <!-- Barra de Progreso Oculta -->
                <div id="uploadProgressContainer" class="form-group span-2" style="display:none; margin-top: 5px;">
                    <div style="display:flex; justify-content:space-between; font-size:0.85rem; font-weight:bold; color:var(--navy); margin-bottom:8px;">
                        <span id="uploadStatusText">Subiendo documento...</span>
                        <span id="uploadPercentage">0%</span>
                    </div>
                    <div style="width:100%; background:#e0e0e0; border-radius:8px; height:14px; overflow:hidden;">
                        <div id="uploadProgressBar" style="width:0%; background:var(--gold); height:100%; transition: width 0.3s ease;"></div>
                    </div>
                </div>
            </div>
            
            <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;">
                <button type="button" onclick="document.getElementById('modalDoc').style.display='none'" class="btn-secondary">Cancelar</button>
                <button type="submit" id="btnSubmitDoc" class="btn-primary">💾 Guardar Documento</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleFuente(tipo) {
    const local = document.getElementById('secLocal');
    const drive = document.getElementById('secDrive');
    const btnL  = document.getElementById('btnLocal');
    const btnD  = document.getElementById('btnDrive');
    const esDriveIn = document.getElementById('esDrive');

    if (tipo === 'local') {
        local.style.display = ''; drive.style.display = 'none';
        btnL.style.borderColor = 'var(--gold)'; btnL.style.background = 'rgba(201,168,76,0.1)'; btnL.style.color = 'var(--navy)';
        btnD.style.borderColor = '#ccc'; btnD.style.background = 'white'; btnD.style.color = 'var(--text-gray)';
        esDriveIn.value = '0';
    } else {
        local.style.display = 'none'; drive.style.display = '';
        btnD.style.borderColor = '#4285f4'; btnD.style.background = 'rgba(66,133,244,0.1)'; btnD.style.color = '#4285f4';
        btnL.style.borderColor = '#ccc'; btnL.style.background = 'white'; btnL.style.color = 'var(--text-gray)';
        esDriveIn.value = '1';
    }
}

document.getElementById('formDocumento').addEventListener('submit', function(e) {
    const isDrive = document.getElementById('esDrive').value === "1";
    const fileInput = document.getElementById('archivoInput');
    
    if (!isDrive && fileInput.files.length > 0) {
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);
        const progressContainer = document.getElementById('uploadProgressContainer');
        const progressBar = document.getElementById('uploadProgressBar');
        const percentageText = document.getElementById('uploadPercentage');
        const submitBtn = document.getElementById('btnSubmitDoc');
        
        progressContainer.style.display = 'block';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '⏳ Subiendo...';
        submitBtn.style.opacity = '0.7';
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', form.action, true);
        xhr.setRequestHeader('Accept', 'application/json');
        
        xhr.upload.onprogress = function(event) {
            if (event.lengthComputable) {
                const percentComplete = Math.round((event.loaded / event.total) * 100);
                progressBar.style.width = percentComplete + '%';
                percentageText.innerText = percentComplete + '%';
                
                if(percentComplete === 100) {
                    document.getElementById('uploadStatusText').innerText = "Guardando...";
                }
            }
        };
        
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                window.location.reload();
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '💾 Guardar Documento';
                submitBtn.style.opacity = '1';
                progressContainer.style.display = 'none';
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    let errMsg = response.message || 'Error de validación';
                    if(response.errors) {
                        errMsg += "\n" + Object.values(response.errors).join("\n");
                    }
                    alert("Error al subir: " + errMsg);
                } catch(err) {
                    if(xhr.status === 413) {
                        alert("Error: El documento supera la capacidad máxima de tu servidor.");
                    } else {
                        alert("Ocurrió un error (Código: " + xhr.status + ").");
                    }
                }
            }
        };
        
        xhr.onerror = function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '💾 Guardar Documento';
            submitBtn.style.opacity = '1';
            progressContainer.style.display = 'none';
            alert("Error de conexión con el servidor local.");
        };
        
        xhr.send(formData);
    }
});
if (document.getElementById('modalDoc')?.getAttribute('data-open') === '1') {
    document.getElementById('modalDoc').style.display = 'flex';
}
</script>
@endpush
