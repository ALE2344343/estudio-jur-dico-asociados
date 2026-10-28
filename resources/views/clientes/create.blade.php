@extends('layouts.dashboard')
@section('title','Nuevo Cliente')
@section('page-title','Nuevo Cliente')

@section('top-bar-actions')
<a href="{{ route('clientes.index') }}" class="btn-secondary">← Volver</a>
@endsection

@section('content')
<div class="form-card animate-in">
    <div class="form-card-header">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="#C9A84C"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        <h3>REGISTRAR NUEVO CLIENTE</h3>
    </div>
    <form action="{{ route('clientes.store') }}" method="POST">
        @csrf
        <div class="form-body">

            <!-- Información Personal -->
            <div style="margin-bottom:20px;">
                <div style="font-family:'Cinzel',serif;font-size:0.8rem;color:var(--gold-dark);letter-spacing:2px;margin-bottom:15px;border-bottom:1px solid rgba(201,168,76,0.2);padding-bottom:8px;">
                    INFORMACIÓN PERSONAL
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nombre * <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Solo letras, se guardará con mayúscula inicial)</span></label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" placeholder="Ej: Roberto" required>
                        @error('nombre')<span style="color:#dc3545;font-size:0.78rem;">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellido * <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Solo letras, se guardará con mayúscula inicial)</span></label>
                        <input type="text" name="apellido" class="form-control" value="{{ old('apellido') }}" placeholder="Ej: Perez Gomez" required>
                        @error('apellido')<span style="color:#dc3545;font-size:0.78rem;">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cédula de Identidad * <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Entre 5 y 9 dígitos reales)</span></label>
                        <input type="text" name="ci" class="form-control" value="{{ old('ci') }}" required placeholder="Ej: 4892341">
                        @error('ci')<span style="color:#dc3545;font-size:0.78rem;">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">CI Expedido en</label>
                        <select name="ci_expedido" class="form-control">
                            <option value="">Seleccione departamento</option>
                            @foreach(['LP'=>'La Paz','CB'=>'Cochabamba','SC'=>'Santa Cruz','OR'=>'Oruro','PO'=>'Potosí','CH'=>'Chuquisaca','TJ'=>'Tarija','BE'=>'Beni','PN'=>'Pando'] as $k=>$v)
                            <option value="{{ $k }}" {{ old('ci_expedido')==$k?'selected':'' }}>{{ $k }} - {{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha de Nacimiento <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Entre el año 1925 y la fecha actual)</span></label>
                        <input type="date" name="fecha_nacimiento" class="form-control" 
                               min="1925-01-01" max="{{ date('Y-m-d') }}"
                               value="{{ old('fecha_nacimiento') }}">
                        @error('fecha_nacimiento')<span style="color:#dc3545;font-size:0.78rem;">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Género</label>
                        <select name="genero" class="form-control">
                            <option value="">Seleccione</option>
                            <option value="M" {{ old('genero')=='M'?'selected':'' }}>Masculino</option>
                            <option value="F" {{ old('genero')=='F'?'selected':'' }}>Femenino</option>
                            <option value="Otro" {{ old('genero')=='Otro'?'selected':'' }}>Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado Civil</label>
                        <select name="estado_civil" class="form-control">
                            <option value="">Seleccione</option>
                            @foreach(['Soltero/a','Casado/a','Divorciado/a','Viudo/a','Conviviente'] as $ec)
                            <option value="{{ $ec }}" {{ old('estado_civil')==$ec?'selected':'' }}>{{ $ec }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Contacto -->
            <div style="margin-bottom:20px;">
                <div style="font-family:'Cinzel',serif;font-size:0.8rem;color:var(--gold-dark);letter-spacing:2px;margin-bottom:15px;border-bottom:1px solid rgba(201,168,76,0.2);padding-bottom:8px;">
                    INFORMACIÓN DE CONTACTO
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Teléfono <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Opcional - 7 dígitos fijo u 8 dígitos móvil)</span></label>
                        <input type="tel" name="telefono" class="form-control" value="{{ old('telefono') }}" placeholder="Ej: 22441234 o 72000001">
                        @error('telefono')<span style="color:#dc3545;font-size:0.78rem;">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Celular <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Exactamente 8 dígitos iniciando en 6 o 7)</span></label>
                        <input type="tel" name="celular" class="form-control" value="{{ old('celular') }}" placeholder="Ej: 71234567 o 60045788">
                        @error('celular')<span style="color:#dc3545;font-size:0.78rem;">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Opcional, formato correo válido)</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="cliente@correo.com">
                        @error('email')<span style="color:#dc3545;font-size:0.78rem;">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ciudad</label>
                        <input type="text" name="ciudad" class="form-control" value="{{ old('ciudad','La Paz') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Departamento</label>
                        <select name="departamento" class="form-control">
                            @foreach(['La Paz','Cochabamba','Santa Cruz','Oruro','Potosí','Chuquisaca','Tarija','Beni','Pando'] as $dep)
                            <option value="{{ $dep }}" {{ old('departamento','La Paz')==$dep?'selected':'' }}>{{ $dep }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group span-2">
                        <label class="form-label">Dirección</label>
                        <textarea name="direccion" class="form-control" rows="2" placeholder="Calle, N°, Zona/Barrio">{{ old('direccion') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Laboral -->
            <div style="margin-bottom:20px;">
                <div style="font-family:'Cinzel',serif;font-size:0.8rem;color:var(--gold-dark);letter-spacing:2px;margin-bottom:15px;border-bottom:1px solid rgba(201,168,76,0.2);padding-bottom:8px;">
                    INFORMACIÓN LABORAL
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Ocupación</label>
                        <input type="text" name="ocupacion" class="form-control" value="{{ old('ocupacion') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Empresa/Empleador</label>
                        <input type="text" name="empresa" class="form-control" value="{{ old('empresa') }}">
                    </div>
                </div>
            </div>

            <!-- Asignación -->
            <div style="margin-bottom:10px;">
                <div style="font-family:'Cinzel',serif;font-size:0.8rem;color:var(--gold-dark);letter-spacing:2px;margin-bottom:15px;border-bottom:1px solid rgba(201,168,76,0.2);padding-bottom:8px;">
                    ASIGNACIÓN
                </div>
                <div class="form-grid">
                    @if(session('usuario_rol') === 'administrador')
                    <div class="form-group">
                        <label class="form-label">Abogado Asignado</label>
                        <select name="usuario_id" class="form-control">
                            <option value="">Sin asignar</option>
                            @foreach($abogados as $ab)
                            <option value="{{ $ab->id }}" {{ old('usuario_id')==$ab->id?'selected':'' }}>
                                {{ $ab->nombre_completo }} - {{ $ab->especialidad ?? 'General' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-control">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div class="form-group span-2">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3" placeholder="Notas adicionales sobre el cliente...">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ route('clientes.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/></svg>
                Guardar Cliente
            </button>
        </div>
    </form>
</div>
@endsection
