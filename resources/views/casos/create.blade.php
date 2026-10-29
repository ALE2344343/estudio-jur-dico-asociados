@extends('layouts.dashboard')
@section('title','Nuevo Caso')
@section('page-title','Nuevo Caso')

@section('top-bar-actions')
<a href="{{ route('casos.index') }}" class="btn-secondary">← Volver</a>
@endsection

@section('content')
<div class="form-card animate-in">
    <div class="form-card-header">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="#C9A84C"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
        <h3>REGISTRAR NUEVO CASO / EXPEDIENTE</h3>
    </div>
    <form action="{{ route('casos.store') }}" method="POST">
        @csrf
        <div class="form-body">
            <!-- Info básica -->
            <div style="margin-bottom:20px;">
                <div style="font-family:'Cinzel',serif;font-size:0.8rem;color:var(--gold-dark);letter-spacing:2px;margin-bottom:15px;border-bottom:1px solid rgba(201,168,76,0.2);padding-bottom:8px;">INFORMACIÓN DEL CASO</div>
                <div class="form-grid">
                    <div class="form-group span-2">
                        <label class="form-label">Título del Proceso *</label>
                        <input type="text" name="titulo" class="form-control" value="{{ old('titulo') }}" required placeholder="Ej: Demanda por despido injustificado — Juan Pérez vs. Empresa XYZ">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipo de Materia *</label>
                        <select name="tipo" class="form-control" required>
                            @foreach(['Civil','Penal','Laboral','Familia','Comercial','Administrativo','Constitucional','Tributario','Otro'] as $t)
                            <option value="{{ $t }}" {{ old('tipo')==$t?'selected':'' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Prioridad</label>
                        <select name="prioridad" class="form-control">
                            <option value="baja" {{ old('prioridad')=='baja'?'selected':'' }}>🔵 Baja</option>
                            <option value="media" {{ old('prioridad','media')=='media'?'selected':'' }}>🟡 Media</option>
                            <option value="alta" {{ old('prioridad')=='alta'?'selected':'' }}>🟠 Alta</option>
                            <option value="urgente" {{ old('prioridad')=='urgente'?'selected':'' }}>🔴 Urgente</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-control">
                            <option value="activo" {{ old('estado','activo')=='activo'?'selected':'' }}>Activo</option>
                            <option value="en_proceso" {{ old('estado')=='en_proceso'?'selected':'' }}>En Proceso</option>
                            <option value="suspendido" {{ old('estado')=='suspendido'?'selected':'' }}>Suspendido</option>
                        </select>
                    </div>
                    <div class="form-group span-2">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción general del caso...">{{ old('descripcion') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Partes -->
            <div style="margin-bottom:20px;">
                <div style="font-family:'Cinzel',serif;font-size:0.8rem;color:var(--gold-dark);letter-spacing:2px;margin-bottom:15px;border-bottom:1px solid rgba(201,168,76,0.2);padding-bottom:8px;">PARTES</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Cliente * <span style="font-weight:normal;font-size:0.75rem;color:var(--text-gray);">(Busque por CI o Nombre)</span></label>
                        <input type="text" id="buscarClienteInput" class="form-control" placeholder="🔍 Escriba N° de Carnet (CI), nombre o código para filtrar..." style="margin-bottom:6px;font-size:0.82rem;padding:7px 10px;border-style:dashed;" oninput="filtrarSelectorClientes(this.value)">
                        <select name="cliente_id" id="selectClienteId" class="form-control" required>
                            <option value="">Seleccione cliente</option>
                            @foreach($clientes as $cl)
                            <option value="{{ $cl->id }}" data-search="{{ strtolower($cl->ci . ' ' . $cl->nombre_completo . ' ' . $cl->codigo) }}" {{ old('cliente_id')==$cl->id?'selected':'' }}>
                                CI: {{ $cl->ci }} {{ $cl->ci_expedido }} — {{ $cl->nombre_completo }} ({{ $cl->codigo }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Abogado Responsable *</label>
                        <select name="abogado_id" class="form-control" required>
                            @foreach($abogados as $ab)
                            <option value="{{ $ab->id }}" {{ old('abogado_id')==$ab->id || (old('abogado_id')=='' && $ab->id==session('usuario_id'))?'selected':'' }}>
                                {{ $ab->nombre_completo }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group span-2">
                        <label class="form-label">Contraparte</label>
                        <input type="text" name="contraparte" class="form-control" value="{{ old('contraparte') }}" placeholder="Nombre de la parte contraria">
                    </div>
                </div>
            </div>

            <!-- Juzgado -->
            <div style="margin-bottom:20px;">
                <div style="font-family:'Cinzel',serif;font-size:0.8rem;color:var(--gold-dark);letter-spacing:2px;margin-bottom:15px;border-bottom:1px solid rgba(201,168,76,0.2);padding-bottom:8px;">DATOS DEL JUZGADO</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Juzgado</label>
                        <input type="text" name="juzgado" class="form-control" value="{{ old('juzgado') }}" placeholder="Ej: Juzgado de Partido en lo Laboral N° 1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Juez</label>
                        <input type="text" name="juez" class="form-control" value="{{ old('juez') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">N° Expediente en Juzgado</label>
                        <input type="text" name="numero_expediente" class="form-control" value="{{ old('numero_expediente') }}">
                    </div>
                </div>
            </div>

            <!-- Fechas y honorarios -->
            <div style="margin-bottom:20px;">
                <div style="font-family:'Cinzel',serif;font-size:0.8rem;color:var(--gold-dark);letter-spacing:2px;margin-bottom:15px;border-bottom:1px solid rgba(201,168,76,0.2);padding-bottom:8px;">FECHAS Y HONORARIOS</div>
                <div class="form-grid cols-3">
                    <div class="form-group">
                        <label class="form-label">Fecha de Inicio *</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ old('fecha_inicio', date('Y-m-d')) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha de Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" class="form-control" value="{{ old('fecha_vencimiento') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Honorarios (Bs.)</label>
                        <input type="number" name="honorarios" class="form-control" value="{{ old('honorarios') }}" step="0.01" min="0" placeholder="0.00">
                    </div>
                </div>
            </div>

            <!-- Notas privadas -->
            <div class="form-grid cols-1">
                <div class="form-group">
                    <label class="form-label">Notas Privadas (solo visible para abogados)</label>
                    <textarea name="notas_privadas" class="form-control" rows="4" placeholder="Notas internas, estrategia legal, observaciones privadas...">{{ old('notas_privadas') }}</textarea>
                </div>
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ route('casos.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/></svg>
                Registrar Caso
            </button>
        </div>
    </form>
</div>
<script>
function filtrarSelectorClientes(termino) {
    const q = termino.toLowerCase().trim();
    const select = document.getElementById('selectClienteId');
    let primeraCoincidencia = null;
    Array.from(select.options).forEach(opt => {
        if (!opt.value) return;
        const texto = (opt.getAttribute('data-search') || opt.textContent).toLowerCase();
        const coincide = texto.includes(q);
        opt.hidden = !coincide;
        opt.style.display = coincide ? '' : 'none';
        if (coincide && !primeraCoincidencia) {
            primeraCoincidencia = opt;
        }
    });
    if (q !== '' && primeraCoincidencia) {
        select.value = primeraCoincidencia.value;
    }
}
</script>
@endsection
