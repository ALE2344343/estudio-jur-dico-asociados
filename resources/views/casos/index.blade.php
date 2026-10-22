@extends('layouts.dashboard')
@section('title','Casos')
@section('page-title','Casos')

@section('top-bar-actions')
@if(session('usuario_rol') === 'administrador')
<a href="{{ route('casos.create') }}" class="btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
    Nuevo Caso
</a>
@endif
@endsection

@section('content')
<!-- Filtros -->
<div class="table-card" style="margin-bottom:20px;">
    <div style="padding:15px 20px;">
        <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
            <div class="search-box" style="flex:1;min-width:200px;">
                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                <input name="search" placeholder="Buscar por N° caso, título, NUREJ, cliente o abogado..." value="{{ request('search') }}">
            </div>
            <select name="tipo" class="form-control" style="width:140px;">
                <option value="">Tipo</option>
                @foreach(['Civil','Penal','Laboral','Familia','Comercial','Administrativo','Constitucional','Tributario','Otro'] as $t)
                <option value="{{ $t }}" {{ request('tipo')==$t?'selected':'' }}>{{ $t }}</option>
                @endforeach
            </select>
            <select name="estado" class="form-control" style="width:140px;">
                <option value="">Estado</option>
                @foreach(['activo'=>'Activo','en_proceso'=>'En Proceso','suspendido'=>'Suspendido','cerrado'=>'Cerrado','ganado'=>'Ganado','perdido'=>'Perdido'] as $v=>$l)
                <option value="{{ $v }}" {{ request('estado')==$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
            <select name="prioridad" class="form-control" style="width:130px;">
                <option value="">Prioridad</option>
                @foreach(['baja'=>'Baja','media'=>'Media','alta'=>'Alta','urgente'=>'Urgente'] as $v=>$l)
                <option value="{{ $v }}" {{ request('prioridad')==$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary">Filtrar</button>
            <a href="{{ route('casos.index') }}" class="btn-secondary">Actualizar</a>
        </form>
    </div>
</div>

<div class="table-card animate-in">
    <div class="table-card-header">
        <div class="table-card-title">
            <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
            EXPEDIENTES / CASOS — {{ $casos->total() }} registros
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>N° Caso</th>
                <th>Título</th>
                <th>Tipo</th>
                <th>Cliente</th>
                <th>Abogado</th>
                <th>Prioridad</th>
                <th>Estado</th>
                <th>Vencimiento</th>
                <th>Audiencias</th>
            </tr>
        </thead>
        <tbody>
            @forelse($casos as $caso)
            <tr>
                <td>
                    <a href="{{ route('casos.show',$caso->id) }}" style="font-family:'Cinzel',serif;font-size:0.78rem;color:var(--gold-dark);text-decoration:none;font-weight:600;">
                        {{ $caso->numero_caso }}
                    </a>
                </td>
                <td style="max-width:180px;">
                    <a href="{{ route('casos.show',$caso->id) }}" style="color:var(--navy);text-decoration:none;font-weight:500;">
                        {{ Str::limit($caso->titulo, 35) }}
                    </a>
                </td>
                <td><span class="badge badge-closed">{{ $caso->tipo }}</span></td>
                <td style="font-size:0.82rem;">{{ $caso->cliente?->nombre_completo ?? '—' }}</td>
                <td style="font-size:0.82rem;">{{ $caso->abogado?->nombre ?? '—' }}</td>
                <td>
                    @php $bp = $caso->badge_prioridad; @endphp
                    <span class="badge {{ $bp[0] }}">{{ $bp[1] }}</span>
                </td>
                <td>
                    @php $be = $caso->badge_estado; @endphp
                    <span class="badge {{ $be[0] }}">{{ $be[1] }}</span>
                </td>
                <td style="font-size:0.8rem;">
                    @if($caso->fecha_vencimiento)
                        @php
                            $diff = now()->diffInDays($caso->fecha_vencimiento, false);
                            $colorVenc = $diff < 0 ? '#dc3545' : ($diff < 7 ? '#856404' : 'var(--text-gray)');
                        @endphp
                        <span data-color="{{ $colorVenc }}" class="venc-span">
                            {{ $caso->fecha_vencimiento->format('d/m/Y') }}
                            @if($diff < 0) @elseif($diff < 7) @endif
                        </span>
                    @else —
                    @endif
                </td>
                <td>
                    @php
                       $proxima = App\Models\Audiencia::where('caso_id', $caso->id)
                           ->where('fecha_hora', '>=', now()->startOfDay())
                           ->orderBy('fecha_hora', 'asc')->first();
                    @endphp
                    @if($proxima)
                        <div style="font-size:0.7rem;margin-bottom:6px;line-height:1.2;background:#f8f9fa;padding:3px;border-radius:4px;border-left:2px solid var(--gold-dark);min-width:120px;">
                            <strong style="color:var(--navy);">{{ \Carbon\Carbon::parse($proxima->fecha_hora)->format('d/m/y H:i') }}</strong><br>
                            <span style="color:var(--text-gray);">{{ Str::limit($proxima->titulo, 20) }}</span>
                        </div>
                    @endif
                    <div style="display:flex;gap:6px;align-items:center;">
                        <a href="{{ route('casos.show',$caso->id) }}" class="btn-edit">Ver</a>
                        @if(session('usuario_rol') === 'administrador')
                        <a href="{{ route('casos.edit',$caso->id) }}" class="btn-edit" title="Editar" style="display:inline-flex;align-items:center;justify-content:center;">
                            <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                        </a>
                        <form action="{{ route('casos.destroy',$caso->id) }}" method="POST" style="display:inline;margin:0;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger" onclick="return confirm('¿Eliminar este caso?')" title="Eliminar" style="display:inline-flex;align-items:center;justify-content:center;">
                                <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-gray);">
                <div style="font-size:3rem;margin-bottom:10px;">📁</div>
                No hay casos registrados.<br>
                @if(session('usuario_rol') === 'administrador') <a href="{{ route('casos.create') }}" class="btn-primary" style="display:inline-flex;margin-top:15px;">+ Crear primer caso</a> @endif
            </td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:15px 20px;">{{ $casos->withQueryString()->links() }}</div>
</div>
<script>
document.querySelectorAll('.venc-span[data-color]').forEach(el => {
    el.style.color = el.getAttribute('data-color');
});
</script>
@endsection



