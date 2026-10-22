@extends('layouts.dashboard')

@section('content')
<div class="content-header">
    <div style="display:flex;align-items:center;gap:15px;">
        <a href="{{ route('documentos.index') }}" style="color:var(--text-gray);text-decoration:none;font-size:1.5rem;">←</a>
        <h2 style="font-family:'Cinzel',serif;color:var(--navy);margin:0;">EDITAR DOCUMENTO</h2>
    </div>
</div>

<div class="card" style="max-width:800px;margin:20px auto;padding:30px;">
    <form action="{{ route('documentos.update', $documento->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        @if ($errors->any())
            <div style="background:rgba(220,53,69,0.1);color:#d9534f;padding:10px;border-radius:8px;margin-bottom:15px;">
                <strong>Errores detectados:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-grid" style="gap:20px;">
            <div class="form-group span-2">
                <label class="form-label">Nombre del Documento *</label>
                <input type="text" name="nombre" class="form-control" required value="{{ old('nombre', $documento->nombre) }}">
            </div>

            <div class="form-group">
                <label class="form-label">Vincular a Caso</label>
                <select name="caso_id" class="form-control">
                    <option value="">Sin vincular...</option>
                    @foreach($casos as $c)
                    <option value="{{ $c->id }}" {{ $documento->caso_id == $c->id ? 'selected' : '' }}>{{ $c->numero_caso }} - {{ $c->titulo }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Vincular a Cliente</label>
                <select name="cliente_id" class="form-control">
                    <option value="">Sin vincular...</option>
                    @foreach(App\Models\Usuario::where('rol','cliente')->get() as $cli)
                    <option value="{{ $cli->id }}" {{ $documento->cliente_id == $cli->id ? 'selected' : '' }}>{{ $cli->nombre }} {{ $cli->apellido }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group span-2">
                <label class="form-label">Tipo de Documento *</label>
                <select name="tipo" class="form-control" required>
                    @foreach(['Demanda','Contrato','Acuerdo','Sentencia','Auto','Memorial','Poder Notarial','Certificado','Resolución','Recurso','Apelación','Denuncia','Otro'] as $t)
                    <option value="{{ $t }}" {{ $documento->tipo == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group span-2">
                <label class="form-label">Reemplazar Archivo (Opcional)</label>
                <div style="display:flex;gap:10px;margin-bottom:10px;">
                    <button type="button" onclick="toggleFuenteE('local')" id="btnLocalE"
                            style="flex:1;padding:10px;border-radius:8px;border:2px solid var(--gold);background:rgba(201,168,76,0.1);color:var(--navy);cursor:pointer;font-family:'Cinzel',serif;font-size:0.8rem;letter-spacing:1px;">
                        📄 SUBIR NUEVO ARCHIVO
                    </button>
                    <button type="button" onclick="toggleFuenteE('drive')" id="btnDriveE"
                            style="flex:1;padding:10px;border-radius:8px;border:2px solid #ccc;background:white;color:var(--text-gray);cursor:pointer;font-family:'Cinzel',serif;font-size:0.8rem;letter-spacing:1px;">
                        📁 NUEVO LINK GOOGLE DRIVE
                    </button>
                </div>
            </div>

            <div id="secLocalE" class="form-group span-2">
                <input type="file" name="archivo" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip">
                <small style="color:var(--text-gray);">Si no selecciona nada, se mantendrá el archivo original.</small>
            </div>

            <div id="secDriveE" class="form-group span-2" style="display:none;">
                <input type="hidden" name="es_drive" id="esDriveE" value="0">
                <input type="url" name="drive_link" class="form-control" placeholder="https://drive.google.com/..." value="{{ $documento->es_drive ? $documento->drive_link : '' }}">
            </div>

            <div class="form-group span-2">
                <label class="form-label">Descripción / Notas</label>
                <textarea name="notas" class="form-control" rows="4">{{ old('notas', $documento->notas) }}</textarea>
            </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:25px;">
            <a href="{{ route('documentos.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">💾 Actualizar Documento</button>
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
</script>
@endpush
