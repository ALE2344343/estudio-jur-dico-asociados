@extends('layouts.dashboard')
@section('title','Editar CASO')
@section('page-title','Editar CASO')

@section('top-bar-actions')
<a href="{{ route('casos.show', $caso->id) }}" class="btn-secondary">← Ver CASO</a>
@endsection

@section('content')
<div class="form-card animate-in">
    <div class="form-card-header">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="#C9A84C"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6z"/></svg>
        <h3>EDITAR CASO: {{ $caso->numero_caso }}</h3>
    </div>
    <form action="{{ route('casos.update', $caso->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="form-body">

            <div style="margin-bottom:20px;">
                <div style="font-family:'Cinzel',serif;font-size:0.8rem;color:var(--gold-dark);letter-spacing:2px;margin-bottom:15px;border-bottom:1px solid rgba(201,168,76,0.2);padding-bottom:8px;">INFORMACIÓN DEL CASO</div>
                <div class="form-grid">
                    <div class="form-group span-2">
                        <label class="form-label">Título del Proceso *</label>
                        <input type="text" name="titulo" class="form-control" value="{{ old('titulo',$caso->titulo) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipo *</label>
                        <select name="tipo" class="form-control" required>
                            @foreach(['Civil','Penal','Laboral','Familia','Comercial','Administrativo','Constitucional','Tributario','Otro'] as $t)
                            <option value="{{ $t }}" {{ $caso->tipo==$t?'selected':'' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Prioridad</label>
                        <select name="prioridad" class="form-control">
                            @foreach(['baja'=>'🔵 Baja','media'=>'🟡 Media','alta'=>'🟠 Alta','urgente'=>'🔴 Urgente'] as $v=>$l)
                            <option value="{{ $v }}" {{ $caso->prioridad==$v?'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-control">
                            @foreach(['activo'=>'Activo','en_proceso'=>'En Proceso','suspendido'=>'Suspendido','cerrado'=>'Cerrado','ganado'=>'Ganado','perdido'=>'Perdido'] as $v=>$l)
                            <option value="{{ $v }}" {{ $caso->estado==$v?'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group span-2">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion',$caso->descripcion) }}</textarea>
                    </div>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <div style="font-family:'Cinzel',serif;font-size:0.8rem;color:var(--gold-dark);letter-spacing:2px;margin-bottom:15px;border-bottom:1px solid rgba(201,168,76,0.2);padding-bottom:8px;">PARTES</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Cliente * <span style="font-weight:normal;font-size:0.75rem;color:var(--text-gray);">(Busque por CI o Nombre)</span></label>
                        <input type="text" id="buscarClienteInput" class="form-control" placeholder="🔍 Escriba N° de Carnet (CI), nombre o código para filtrar..." style="margin-bottom:6px;font-size:0.82rem;padding:7px 10px;border-style:dashed;" oninput="filtrarSelectorClientes(this.value)">
                        <select name="cliente_id" id="selectClienteId" class="form-control" required>
                            @foreach($clientes as $cl)
                            <option value="{{ $cl->id }}" data-search="{{ strtolower($cl->ci . ' ' . $cl->nombre_completo . ' ' . $cl->codigo) }}" {{ $caso->cliente_id==$cl->id?'selected':'' }}>
                                CI: {{ $cl->ci }} {{ $cl->ci_expedido }} — {{ $cl->nombre_completo }} ({{ $cl->codigo }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Abogado *</label>
                        <select name="abogado_id" class="form-control" required>
                            @foreach($abogados as $ab)
                            <option value="{{ $ab->id }}" {{ $caso->abogado_id==$ab->id?'selected':'' }}>{{ $ab->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group span-2">
                        <label class="form-label">Contraparte</label>
                        <input type="text" name="contraparte" class="form-control" value="{{ old('contraparte',$caso->contraparte) }}">
                    </div>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <div style="font-family:'Cinzel',serif;font-size:0.8rem;color:var(--gold-dark);letter-spacing:2px;margin-bottom:15px;border-bottom:1px solid rgba(201,168,76,0.2);padding-bottom:8px;">DATOS DEL JUZGADO</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Juzgado</label>
                        <input type="text" name="juzgado" class="form-control" value="{{ old('juzgado',$caso->juzgado) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Juez</label>
                        <input type="text" name="juez" class="form-control" value="{{ old('juez',$caso->juez) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">N° Expediente</label>
                        <input type="text" name="numero_expediente" class="form-control" value="{{ old('numero_expediente',$caso->numero_expediente) }}">
                    </div>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <div style="font-family:'Cinzel',serif;font-size:0.8rem;color:var(--gold-dark);letter-spacing:2px;margin-bottom:15px;border-bottom:1px solid rgba(201,168,76,0.2);padding-bottom:8px;">FECHAS Y HONORARIOS</div>
                <div class="form-grid cols-3">
                    <div class="form-group">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ old('fecha_inicio',$caso->fecha_inicio?->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" class="form-control" value="{{ old('fecha_vencimiento',$caso->fecha_vencimiento?->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha Cierre</label>
                        <input type="date" name="fecha_cierre" class="form-control" value="{{ old('fecha_cierre',$caso->fecha_cierre?->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Honorarios (Bs.)</label>
                        <input type="number" name="honorarios" class="form-control" step="0.01" value="{{ old('honorarios',$caso->honorarios) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado Honorarios</label>
                        <select name="estado_honorarios" class="form-control">
                            <option value="pendiente" {{ $caso->estado_honorarios=='pendiente'?'selected':'' }}>Pendiente</option>
                            <option value="parcial"   {{ $caso->estado_honorarios=='parcial'?'selected':'' }}>Parcial</option>
                            <option value="pagado"    {{ $caso->estado_honorarios=='pagado'?'selected':'' }}>Pagado</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-grid cols-1">
                <div class="form-group">
                    <label class="form-label">Notas Privadas</label>
                    <textarea name="notas_privadas" class="form-control" rows="4">{{ old('notas_privadas',$caso->notas_privadas) }}</textarea>
                </div>
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ route('casos.show', $caso->id) }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">Actualizar Caso</button>
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
