@extends('layouts.dashboard')
@section('title','Clientes')
@section('page-title','Clientes')

@section('top-bar-actions')
@if(session('usuario_rol') === 'administrador')
<a href="{{ route('clientes.create') }}" class="btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
    Nuevo Cliente
</a>
@endif
@endsection

@section('content')
<!-- Filtros -->
<div class="table-card" style="margin-bottom:20px;">
    <div style="padding:15px 20px;">
        <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
            <div class="search-box" style="flex:1;min-width:200px;">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                <input name="search" placeholder="Buscar por nombre, CI o código..." value="{{ request('search') }}">
            </div>
            <select name="estado" class="form-control" style="width:150px;">
                <option value="">Todos</option>
                <option value="activo" {{ request('estado')=='activo'?'selected':'' }}>Activo</option>
                <option value="inactivo" {{ request('estado')=='inactivo'?'selected':'' }}>Inactivo</option>
            </select>
            <button type="submit" class="btn-primary">Buscar</button>
            <a href="{{ route('clientes.index') }}" class="btn-secondary">Actualizar</a>
        </form>
    </div>
</div>

<div class="table-card animate-in">
    <div class="table-card-header">
        <div class="table-card-title">
            <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            REGISTRO DE CLIENTES — {{ $clientes->total() }} registros
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>CÓDIGO<br><span style="font-size:0.65rem;color:var(--text-gray);font-weight:normal;">FECHA REGISTRO</span></th>
                <th>Nombre Completo</th>
                <th>C.I.</th>
                <th>Teléfono</th>
                <th>Ciudad</th>
                <th>Abogado</th>
                <th>Estado</th>
                <th>Audiencias</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clientes as $cliente)
            <tr>
                <td>
                    <span style="font-family:'Cinzel',serif;font-size:0.78rem;color:var(--gold-dark);font-weight:600;display:block;margin-bottom:2px;">{{ $cliente->codigo }}</span>
                    <span style="font-size:0.75rem;color:var(--text-gray);">{{ $cliente->created_at->format('d/m/Y') }}</span>
                </td>
                <td>
                    <a href="{{ route('clientes.show',$cliente->id) }}" style="color:var(--navy);text-decoration:none;font-weight:500;">
                        {{ $cliente->nombre_completo }}
                    </a>
                </td>
                <td style="font-size:0.85rem;">{{ $cliente->ci }} {{ $cliente->ci_expedido ? '('.$cliente->ci_expedido.')' : '' }}</td>
                <td style="font-size:0.85rem;">{{ $cliente->telefono }}</td>
                <td style="font-size:0.82rem;color:var(--text-gray);">{{ $cliente->ciudad }}</td>
                <td style="font-size:0.82rem;">{{ $cliente->abogado?->nombre ?? '—' }}</td>
                <td><span class="badge {{ $cliente->estado==='activo'?'badge-active':'badge-closed' }}">{{ ucfirst($cliente->estado) }}</span></td>
                <td>
                    @php
                       $proxima = App\Models\Audiencia::whereHas('caso', function($q) use ($cliente) {
                           $q->where('cliente_id', $cliente->id);
                       })->where('fecha_hora', '>=', now()->startOfDay())->orderBy('fecha_hora', 'asc')->first();
                    @endphp
                    @if($proxima)
                        <div style="font-size:0.75rem;margin-bottom:6px;line-height:1.2;background:#f8f9fa;padding:4px;border-radius:4px;border-left:2px solid var(--gold-dark);">
                            <strong style="color:var(--navy);">{{ \Carbon\Carbon::parse($proxima->fecha_hora)->format('d/m/Y H:i') }}</strong><br>
                            <span style="color:var(--text-gray);">{{ Str::limit($proxima->titulo, 25) }}</span>
                        </div>
                    @endif
                    <div style="display:flex;gap:6px;align-items:center;">
                        <a href="{{ route('clientes.show',$cliente->id) }}" class="btn-edit">Ver</a>
                        @if(session('usuario_rol') === 'administrador')
                        <a href="{{ route('clientes.edit',$cliente->id) }}" class="btn-edit" title="Editar" style="display:inline-flex;align-items:center;justify-content:center;">
                            <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                        </a>
                        <form action="{{ route('clientes.destroy',$cliente->id) }}" method="POST" style="display:inline;margin:0;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger" onclick="return confirm('¿Eliminar este cliente?')" title="Eliminar" style="display:inline-flex;align-items:center;justify-content:center;">
                                <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-gray);">
                <div style="font-size:3rem;margin-bottom:10px;"></div>
                No hay clientes registrados.<br>
                @if(session('usuario_rol') === 'administrador') <a href="{{ route('clientes.create') }}" class="btn-primary" style="display:inline-flex;margin-top:15px;">+ Agregar primer cliente</a> @endif
            </td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:15px 20px;">{{ $clientes->withQueryString()->links() }}</div>
</div>
@endsection




