@extends('layouts.dashboard')
@section('title','Editar Cliente')
@section('page-title','Editar Cliente')

@section('top-bar-actions')
<a href="{{ route('clientes.show', $cliente->id) }}" class="btn-secondary">← Ver Cliente</a>
@endsection

@section('content')
<div class="form-card animate-in">
    <div class="form-card-header">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="#C9A84C"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        <h3>EDITAR CLIENTE: {{ strtoupper($cliente->codigo) }}</h3>
    </div>
    <form action="{{ route('clientes.update', $cliente->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="form-body">
            <div class="form-grid">
                <div class="form-group"><label class="form-label">Nombre * <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Solo letras, se guardará con mayúscula inicial)</span></label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre',$cliente->nombre) }}" required>
                    @error('nombre')<span style="color:red;font-size:0.8rem;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group"><label class="form-label">Apellido * <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Solo letras, se guardará con mayúscula inicial)</span></label>
                    <input type="text" name="apellido" class="form-control" value="{{ old('apellido',$cliente->apellido) }}" required>
                    @error('apellido')<span style="color:red;font-size:0.8rem;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group"><label class="form-label">CI * <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Entre 5 y 9 dígitos reales)</span></label>
                    <input type="text" name="ci" class="form-control" value="{{ old('ci',$cliente->ci) }}" required>
                    @error('ci')<span style="color:red;font-size:0.8rem;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group"><label class="form-label">CI Expedido</label>
                    <select name="ci_expedido" class="form-control">
                        <option value="">Seleccione</option>
                        @foreach(['LP'=>'La Paz','CB'=>'Cochabamba','SC'=>'Santa Cruz','OR'=>'Oruro','PO'=>'Potosí','CH'=>'Chuquisaca','TJ'=>'Tarija','BE'=>'Beni','PN'=>'Pando'] as $k=>$v)
                        <option value="{{ $k }}" {{ $cliente->ci_expedido==$k?'selected':'' }}>{{ $k }} - {{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Fecha de Nacimiento <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Entre el año 1925 y la fecha actual)</span></label>
                    <input type="date" name="fecha_nacimiento" class="form-control" 
                           min="1925-01-01" max="{{ date('Y-m-d') }}"
                           value="{{ old('fecha_nacimiento', $cliente->fecha_nacimiento ? \Carbon\Carbon::parse($cliente->fecha_nacimiento)->format('Y-m-d') : '') }}">
                    @error('fecha_nacimiento')<span style="color:red;font-size:0.8rem;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group"><label class="form-label">Teléfono <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Opcional - 7 dígitos fijo u 8 dígitos móvil)</span></label>
                    <input type="tel" name="telefono" class="form-control" value="{{ old('telefono',$cliente->telefono) }}">
                    @error('telefono')<span style="color:red;font-size:0.8rem;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group"><label class="form-label">Celular <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Exactamente 8 dígitos iniciando en 6 o 7)</span></label>
                    <input type="tel" name="celular" class="form-control" value="{{ old('celular',$cliente->celular) }}">
                    @error('celular')<span style="color:red;font-size:0.8rem;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group"><label class="form-label">Email <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Formato correo válido)</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email',$cliente->email) }}">
                    @error('email')<span style="color:red;font-size:0.8rem;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group"><label class="form-label">Ciudad</label>
                    <input type="text" name="ciudad" class="form-control" value="{{ old('ciudad',$cliente->ciudad) }}"></div>
                <div class="form-group"><label class="form-label">Departamento</label>
                    <select name="departamento" class="form-control">
                        @foreach(['La Paz','Cochabamba','Santa Cruz','Oruro','Potosí','Chuquisaca','Tarija','Beni','Pando'] as $dep)
                        <option value="{{ $dep }}" {{ $cliente->departamento==$dep?'selected':'' }}>{{ $dep }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Ocupación</label>
                    <input type="text" name="ocupacion" class="form-control" value="{{ old('ocupacion',$cliente->ocupacion) }}"></div>
                <div class="form-group"><label class="form-label">Empresa</label>
                    <input type="text" name="empresa" class="form-control" value="{{ old('empresa',$cliente->empresa) }}"></div>
                <div class="form-group"><label class="form-label">Estado</label>
                    <select name="estado" class="form-control">
                        <option value="activo" {{ $cliente->estado=='activo'?'selected':'' }}>Activo</option>
                        <option value="inactivo" {{ $cliente->estado=='inactivo'?'selected':'' }}>Inactivo</option>
                    </select>
                </div>
                @if(session('usuario_rol') === 'administrador')
                <div class="form-group"><label class="form-label">Abogado Asignado</label>
                    <select name="usuario_id" class="form-control">
                        <option value="">Sin asignar</option>
                        @foreach($abogados as $ab)
                        <option value="{{ $ab->id }}" {{ $cliente->usuario_id==$ab->id?'selected':'' }}>{{ $ab->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="form-group span-2"><label class="form-label">Dirección</label>
                    <textarea name="direccion" class="form-control" rows="2">{{ old('direccion',$cliente->direccion) }}</textarea></div>
                <div class="form-group span-2"><label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones',$cliente->observaciones) }}</textarea></div>
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ route('clientes.show', $cliente->id) }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">Actualizar Cliente</button>
        </div>
    </form>
</div>
@endsection
