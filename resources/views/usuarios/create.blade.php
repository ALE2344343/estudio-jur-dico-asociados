@extends('layouts.dashboard')
@section('title','Nuevo Usuario')
@section('page-title','Nuevo Usuario')

@section('top-bar-actions')
<a href="{{ route('usuarios.index') }}" class="btn-secondary">← Volver</a>
@endsection

@section('content')
<div class="form-card animate-in">
    <div class="form-card-header">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="#C9A84C"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        <h3>CREAR NUEVO USUARIO</h3>
    </div>
    @if($errors->any())
    <div style="background:#f8d7da;color:#721c24;padding:14px 20px;border-radius:8px;margin-bottom:20px;border-left:5px solid #dc3545;font-size:0.88rem;">
        <div style="font-weight:bold;margin-bottom:6px;">No se pudo registrar. Por favor corrija los siguientes datos:</div>
        <ul style="margin:0;padding-left:20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('usuarios.store') }}" method="POST">
        @csrf
        <div class="form-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre * <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Solo letras, se guardará con mayúscula inicial)</span></label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" placeholder="Ej: Carlos" required>
                    @error('nombre')<span style="color:#dc3545;font-size:0.78rem;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Apellido * <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Solo letras, se guardará con mayúscula inicial)</span></label>
                    <input type="text" name="apellido" class="form-control" value="{{ old('apellido') }}" placeholder="Ej: Mamani Quispe" required>
                    @error('apellido')<span style="color:#dc3545;font-size:0.78rem;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Usuario (username) * <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Mín. 3 letras/números sin espacios)</span></label>
                    <input type="text" name="username" class="form-control" value="{{ old('username') }}" placeholder="Ej: cmamani" required>
                    @error('username')<span style="color:#dc3545;font-size:0.78rem;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email * <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Correo corporativo o personal válido)</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="ejemplo@estudiojuridico.bo" required>
                    @error('email')<span style="color:#dc3545;font-size:0.78rem;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña * <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Mínimo 6 caracteres combinando letras y números/símbolos)</span></label>
                    <div style="position:relative;display:flex;align-items:center;">
                        <input type="password" id="createPasswordInput" name="password" class="form-control" required minlength="6" placeholder="Ej: Abc123#" style="padding-right:40px;">
                        <button type="button" onclick="togglePasswordVisibility('createPasswordInput', 'eyeCreate1Open', 'eyeCreate1Closed')" title="Mostrar/Ocultar"
                                style="position:absolute;right:10px;background:none;border:none;cursor:pointer;padding:4px;color:var(--navy);display:flex;align-items:center;">
                            <svg id="eyeCreate1Open" viewBox="0 0 24 24" style="width:18px;height:18px;fill:currentColor;"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                            <svg id="eyeCreate1Closed" viewBox="0 0 24 24" style="width:18px;height:18px;fill:currentColor;display:none;"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.44-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/></svg>
                        </button>
                    </div>
                    @error('password')<span style="color:#dc3545;font-size:0.78rem;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmar Contraseña * <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Debe ser idéntica a la contraseña)</span></label>
                    <div style="position:relative;display:flex;align-items:center;">
                        <input type="password" id="createPasswordConfirmInput" name="password_confirmation" class="form-control" required placeholder="Repita la contraseña" style="padding-right:40px;">
                        <button type="button" onclick="togglePasswordVisibility('createPasswordConfirmInput', 'eyeCreate2Open', 'eyeCreate2Closed')" title="Mostrar/Ocultar"
                                style="position:absolute;right:10px;background:none;border:none;cursor:pointer;padding:4px;color:var(--navy);display:flex;align-items:center;">
                            <svg id="eyeCreate2Open" viewBox="0 0 24 24" style="width:18px;height:18px;fill:currentColor;"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                            <svg id="eyeCreate2Closed" viewBox="0 0 24 24" style="width:18px;height:18px;fill:currentColor;display:none;"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.44-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/></svg>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Rol *</label>
                    <select name="rol" class="form-control" required>
                        <option value="abogado" {{ old('rol','abogado')=='abogado'?'selected':'' }}>Abogado</option>
                        <option value="administrador" {{ old('rol')=='administrador'?'selected':'' }}>Administrador</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Número boliviano de 7 u 8 dígitos)</span></label>
                    <input type="tel" name="telefono" class="form-control" value="{{ old('telefono') }}" placeholder="Ej: 72000001">
                    @error('telefono')<span style="color:#dc3545;font-size:0.78rem;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Telegram Chat ID <span style="font-size:0.75rem;font-weight:normal;color:var(--text-gray);">(Opcional, para recibir alertas al celular)</span></label>
                    <input type="text" name="telegram_chat_id" class="form-control" value="{{ old('telegram_chat_id') }}" placeholder="Ej: 123456789">
                </div>
                <div class="form-group" id="grupoEspecialidad">
                    <label class="form-label">Especialidad</label>
                    <input type="text" name="especialidad" class="form-control" value="{{ old('especialidad') }}" placeholder="Ej: Derecho Laboral">
                </div>
                <div class="form-group" id="grupoMatricula">
                    <label class="form-label">Matrícula de Abogado</label>
                    <input type="text" name="matricula" class="form-control" value="{{ old('matricula') }}" placeholder="Ej: MAT-001-2020">
                </div>
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ route('usuarios.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">Crear Usuario</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function togglePasswordVisibility(inputId, openIconId, closedIconId) {
        const input = document.getElementById(inputId);
        const openIcon = document.getElementById(openIconId);
        const closedIcon = document.getElementById(closedIconId);
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            if (openIcon) openIcon.style.display = 'none';
            if (closedIcon) closedIcon.style.display = 'block';
        } else {
            input.type = 'password';
            if (openIcon) openIcon.style.display = 'block';
            if (closedIcon) closedIcon.style.display = 'none';
        }
    }

    const rolSelect = document.querySelector('select[name="rol"]');
    const grupoEsp = document.getElementById('grupoEspecialidad');
    const grupoMat = document.getElementById('grupoMatricula');

    function toggleCamposAbogado() {
        if (rolSelect.value === 'administrador') {
            grupoEsp.style.display = 'none';
            grupoMat.style.display = 'none';
        } else {
            grupoEsp.style.display = 'block';
            grupoMat.style.display = 'block';
        }
    }
    if (rolSelect) {
        rolSelect.addEventListener('change', toggleCamposAbogado);
        toggleCamposAbogado();
    }
</script>
@endpush
@endsection
