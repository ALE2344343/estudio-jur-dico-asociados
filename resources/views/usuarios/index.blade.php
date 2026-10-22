@extends('layouts.dashboard')
@section('title','Gestión de Usuarios')
@section('page-title','Gestión de Usuarios')

@section('top-bar-actions')
<a href="{{ route('usuarios.create') }}" class="btn-primary">+ Nuevo Usuario</a>
@endsection

@section('content')
<div class="animate-in">

    <!-- Mensajes de sesión -->
    @if(session('success'))
    <div style="background:#d4edda;color:#155724;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:5px solid #28a745;">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background:#f8d7da;color:#721c24;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:5px solid #dc3545;">
        {{ session('error') }}
    </div>
    @endif

    <!-- Buscador de Usuarios -->
    <div class="table-card" style="margin-bottom:20px;">
        <div style="padding:15px 20px;">
            <form method="GET" action="{{ route('usuarios.index') }}" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                <div class="search-box" style="flex:1;min-width:200px;">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, usuario, matrícula, especialidad o email...">
                </div>
                <button type="submit" class="btn-primary">Buscar</button>
                <a href="{{ route('usuarios.index') }}" class="btn-secondary">Actualizar</a>
            </form>
        </div>
    </div>

    <!-- TABLA 1: CUERPO DE ABOGADOS -->
    <div class="table-card" style="margin-bottom:30px;">
        <div class="table-card-header">
            <div class="table-card-title">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                ABOGADOS DEL BUFETE ({{ $abogados->count() }})
            </div>
            <span style="font-size:0.8rem;color:var(--text-gray);">Profesionales jurídicos con casos y clientes asignados</span>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Especialidad</th>
                        <th>Matrícula</th>
                        <th>Estado</th>
                        <th>Último Acceso</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($abogados as $u)
                    <tr>
                        <td>
                            <span style="font-family:'Cinzel',serif;font-size:0.82rem;color:var(--gold-dark);font-weight:bold;">{{ $u->username }}</span>
                        </td>
                        <td style="font-weight:600;color:var(--navy);">{{ $u->nombre_completo }}</td>
                        <td style="font-size:0.82rem;color:var(--text-gray);">{{ $u->email }}</td>
                        <td style="font-size:0.82rem;">{{ $u->telefono ?? '—' }}</td>
                        <td>
                            <span style="font-weight:500;color:var(--navy);font-size:0.85rem;">{{ $u->especialidad ?? 'General' }}</span>
                        </td>
                        <td style="font-size:0.82rem;color:var(--gold-dark);font-weight:600;">
                            {{ $u->matricula ? $u->matricula : '—' }}
                        </td>
                        <td>
                            <span class="badge {{ $u->activo ? 'badge-active' : 'badge-closed' }}">
                                {{ $u->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td style="font-size:0.78rem;color:var(--text-gray);">
                            {{ $u->ultimo_acceso ? \Carbon\Carbon::parse($u->ultimo_acceso)->format('d/m/Y H:i') : 'Nunca' }}
                        </td>
                        <td style="text-align:center;">
                            <div style="display:inline-flex;gap:6px;">
                                <a href="{{ route('usuarios.edit', $u->id) }}" class="btn-edit" title="Editar" style="display:inline-flex;align-items:center;justify-content:center;">
                                    <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                </a>
                                @if($u->id != session('usuario_id'))
                                <form action="{{ route('usuarios.destroy', $u->id) }}" method="POST" style="display:inline;margin:0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-danger" onclick="return confirm('¿Eliminar al abogado {{ $u->nombre_completo }}?')" title="Eliminar" style="display:inline-flex;align-items:center;justify-content:center;">
                                        <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-gray);">No hay abogados registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TABLA 2: ADMINISTRADORES DEL SISTEMA -->
    <div class="table-card">
        <div class="table-card-header">
            <div class="table-card-title">
                <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
                ADMINISTRADORES DEL SISTEMA ({{ $administradores->count() }})
            </div>
            <span style="font-size:0.8rem;color:var(--text-gray);">Personal con control total sobre clientes, casos y configuraciones</span>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th>Último Acceso</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($administradores as $u)
                    <tr>
                        <td>
                            <span style="font-family:'Cinzel',serif;font-size:0.82rem;color:var(--navy);font-weight:bold;">{{ $u->username }}</span>
                        </td>
                        <td style="font-weight:600;color:var(--navy);">
                            {{ $u->nombre_completo }}
                            @if($u->id == session('usuario_id'))
                                <span style="font-size:0.75rem;color:var(--gold-dark);font-weight:normal;margin-left:5px;">(Tú)</span>
                            @endif
                        </td>
                        <td style="font-size:0.82rem;color:var(--text-gray);">{{ $u->email }}</td>
                        <td style="font-size:0.82rem;">{{ $u->telefono ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $u->activo ? 'badge-active' : 'badge-closed' }}">
                                {{ $u->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td style="font-size:0.78rem;color:var(--text-gray);">
                            {{ $u->ultimo_acceso ? \Carbon\Carbon::parse($u->ultimo_acceso)->format('d/m/Y H:i') : 'Nunca' }}
                        </td>
                        <td style="text-align:center;">
                            <div style="display:inline-flex;gap:6px;">
                                <a href="{{ route('usuarios.edit', $u->id) }}" class="btn-edit" title="Editar" style="display:inline-flex;align-items:center;justify-content:center;">
                                    <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                </a>
                                @if($u->id != session('usuario_id'))
                                <form action="{{ route('usuarios.destroy', $u->id) }}" method="POST" style="display:inline;margin:0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-danger" onclick="return confirm('¿Eliminar al administrador {{ $u->nombre_completo }}?')" title="Eliminar" style="display:inline-flex;align-items:center;justify-content:center;">
                                        <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-gray);">No hay administradores registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
