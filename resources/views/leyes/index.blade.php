@extends('layouts.dashboard')
@section('title','Leyes & Normativa')
@section('page-title','Leyes & Normativa Bolivia')

@section('top-bar-actions')
<a href="{{ route('leyes.create') }}" class="btn-primary" style="display:inline-flex;align-items:center;gap:6px;">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
    + Nueva Ley
</a>
@endsection

@section('content')
<style>
.ley-accordion {
    background: white;
    border: 1px solid rgba(201,168,76,0.3);
    border-radius: 8px;
    margin-bottom: 10px;
    overflow: hidden;
}
.ley-accordion-header {
    width: 100%;
    padding: 15px 20px;
    background: white;
    border: none;
    text-align: left;
    display: flex;
    align-items: center;
    gap: 15px;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.2s;
}
.ley-accordion-header:hover {
    background: rgba(201,168,76,0.05);
}
.ley-accordion-header.open {
    background: rgba(201,168,76,0.1);
    border-bottom: 1px solid rgba(201,168,76,0.2);
}
.ley-title-text {
    flex: 1;
    font-weight: 600;
    color: var(--navy);
}
.ley-accordion-body {
    padding: 0 20px;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out, padding 0.3s ease;
    background: #fafafa;
}
.ley-accordion-body.open {
    padding: 20px;
    max-height: 1000px;
}
</style>
<!-- Filtros y categorías -->
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;" class="animate-in">
    @php $categorias = ['Todas','Laboral','Civil','Penal','Familia','Comercial','Administrativo','Constitucional','Tributario','Seguridad Social','Otro']; @endphp
    @foreach($categorias as $cat)
    <a href="{{ $cat === 'Todas' ? route('leyes.index', request('search') ? ['search' => request('search')] : []) : route('leyes.index', array_filter(['categoria' => $cat, 'search' => request('search')])) }}"
       style="
        padding:8px 18px;border-radius:20px;font-size:0.78rem;text-decoration:none;font-family:'Cinzel',serif;letter-spacing:1px;transition:all .2s;
        {{ (request('categoria')==$cat || ($cat==='Todas' && !request('categoria'))) ? 'background:var(--gradient-gold);color:var(--navy-dark);box-shadow:0 3px 10px rgba(201,168,76,0.3);' : 'background:white;color:var(--navy);border:1px solid rgba(201,168,76,0.3);' }}
       ">
        {{ $cat }}
    </a>
    @endforeach
</div>

<!-- Búsqueda + Enlace directo a la Gaceta Oficial debajo del buscador -->
<div class="table-card" style="margin-bottom:20px;">
    <div style="padding:15px 20px;">
        <form method="GET" action="{{ route('leyes.index') }}" style="display:flex;gap:12px;align-items:center;">
            <div class="search-box" style="flex:1;">
                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                <input type="text" id="inputBuscadorLeyes" name="search" placeholder="Buscar por número de ley, título, categoría o contenido..." value="{{ request('search') }}" oninput="filtrarLeyesEnVivo(this.value)" autocomplete="off">
            </div>
            @if(request('categoria'))
            <input type="hidden" name="categoria" value="{{ request('categoria') }}">
            @endif
            <button type="submit" class="btn-primary">Buscar</button>
            @if(request('search') || request('categoria'))
            <a href="{{ route('leyes.index') }}" class="btn-secondary" style="white-space:nowrap;">Limpiar</a>
            @endif
        </form>

        <!-- Enlaces oficiales visibles en botones justo debajo del buscador -->
        <div style="margin-top:14px;padding-top:14px;border-top:1px solid rgba(201,168,76,0.2);">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                <span style="font-size:1.1rem;"></span>
                <span style="font-weight:700;color:var(--navy);font-size:0.84rem;font-family:'Cinzel',serif;letter-spacing:0.5px;">PORTALES Y RECURSOS OFICIALES DE BOLIVIA (ACCESO DIRECTO):</span>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                <!-- 1. Gaceta Oficial (el que ya estaba anteriormente) -->
                <a href="http://www.gacetaoficialdebolivia.gob.bo/" target="_blank" rel="noopener noreferrer"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 13px;border-radius:6px;background:var(--gradient-gold);color:var(--navy-dark);font-weight:700;font-size:0.79rem;text-decoration:none;box-shadow:0 2px 6px rgba(201,168,76,0.25);transition:all 0.2s;">
                    Gaceta Oficial de Bolivia ↗
                </a>

                <!-- 2. Buscador de Causas y Resoluciones -->
                <a href="https://buscador.tcpbolivia.bo/" target="_blank" rel="noopener noreferrer"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 13px;border-radius:6px;background:rgba(201,168,76,0.14);border:1px solid var(--gold);color:var(--navy);font-weight:600;font-size:0.79rem;text-decoration:none;transition:all 0.2s;">
                    Buscador de Causas y Resoluciones ↗
                </a>

                <!-- 3. Tribunal Constitucional Plurinacional -->
                <a href="https://jurisprudencia.tcpbolivia.bo/" target="_blank" rel="noopener noreferrer"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 13px;border-radius:6px;background:rgba(201,168,76,0.14);border:1px solid var(--gold);color:var(--navy);font-weight:600;font-size:0.79rem;text-decoration:none;transition:all 0.2s;">
                    Tribunal Constitucional Plurinacional ↗
                </a>

                <!-- 4. SIREJ React -->
                <a href="https://sirej.organojudicial.gob.bo/" target="_blank" rel="noopener noreferrer"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 13px;border-radius:6px;background:rgba(201,168,76,0.14);border:1px solid var(--gold);color:var(--navy);font-weight:600;font-size:0.79rem;text-decoration:none;transition:all 0.2s;">
                    SIREJ Órgano Judicial ↗
                </a>

                <!-- 5. RPA Abogado -->
                <a href="https://sra.justicia.gob.bo/" target="_blank" rel="noopener noreferrer"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 13px;border-radius:6px;background:rgba(201,168,76,0.14);border:1px solid var(--gold);color:var(--navy);font-weight:600;font-size:0.79rem;text-decoration:none;transition:all 0.2s;">
                    RPA Abogado (SRA) ↗
                </a>

                <!-- 6. Aranceles PDF -->
                <a href="https://apirpa2.justicia.gob.bo/files/Aranceles.pdf" target="_blank" rel="noopener noreferrer"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 13px;border-radius:6px;background:rgba(26,39,68,0.08);border:1px solid var(--navy);color:var(--navy);font-weight:600;font-size:0.79rem;text-decoration:none;transition:all 0.2s;">
                    Aranceles.pdf ↗
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Mensaje cuando no hay coincidencias -->
<div id="sinResultadosLive" style="display:none;background:white;border:1px solid rgba(201,168,76,0.3);border-radius:10px;padding:25px;text-align:center;color:var(--text-gray);margin-bottom:20px;">
    No se encontraron leyes que coincidan con "<strong id="textoBuscadoLive"></strong>". Presione <strong>Buscar</strong> para consultar en toda la base de datos o limpie el filtro.
</div>

<!-- Leyes principales precargadas -->
@if($leyes->count() === 0)
<div style="background:rgba(201,168,76,0.06);border:1px solid rgba(201,168,76,0.2);border-radius:12px;padding:25px;margin-bottom:25px;" class="animate-in">
    @if(request('search') || request('categoria'))
    <div style="text-align:center;padding:15px 0 25px;border-bottom:1px solid rgba(201,168,76,0.2);margin-bottom:20px;">
        <div style="font-size:1rem;font-weight:600;color:var(--navy);margin-bottom:6px;">
            🔍 No se encontraron leyes registradas para "{{ request('search') ?: request('categoria') }}"
        </div>
        <div style="font-size:0.85rem;color:var(--text-gray);margin-bottom:12px;">
            Puede intentar con otro término, consultar la Gaceta Oficial arriba o ver todas las leyes registradas.
        </div>
        <a href="{{ route('leyes.index') }}" class="btn-secondary" style="font-size:0.82rem;">Ver todas las leyes</a>
    </div>
    @endif
    <div style="font-family:'Cinzel',serif;font-size:0.9rem;color:var(--gold-dark);margin-bottom:15px;display:flex;align-items:center;gap:8px;">
        📚 NORMATIVA LABORAL BOLIVIANA PRINCIPAL
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
        @foreach([
            ['Ley General del Trabajo (LGT)','8 de diciembre de 1942','La ley madre del derecho laboral boliviano. Regula el contrato de trabajo, jornada, salarios, desahucio e indemnización.','Laboral'],
            ['D.S. 28699','1 de mayo de 2006','Establece garantías laborales, prohibición de despidos injustificados y establece el procedimiento de reinserción laboral.','Laboral'],
            ['D.S. 23932 - Bono de Antigüedad','21 de diciembre de 1994','Establece la escala del bono de antigüedad basada en años de servicio y el salario mínimo nacional.','Laboral'],
            ['Ley 321 - Salud Ocupacional','18 de diciembre de 2012','Incorpora a los trabajadores del hogar al seguro de salud y pensiones del sistema estatal.','Seguridad Social'],
            ['D.S. 1802 - Aguinaldo','20 de noviembre de 2013','Regula el pago del aguinaldo de navidad y el aguinaldo extra o "segundo aguinaldo".','Laboral'],
            ['Ley 004 - Lucha Contra la Corrupción','31 de marzo de 2010','Ley Marcelo Quiroga Santa Cruz. Establece mecanismos de lucha contra la corrupción.','Administrativo'],
        ] as $l)
        <div class="ley-item-searchable" data-search="{{ strtolower($l[0].' '.$l[2].' '.$l[3]) }}" style="background:white;border-radius:8px;padding:15px;border:1px solid rgba(201,168,76,0.2);box-shadow:0 2px 8px rgba(0,0,0,0.05);">
            <div class="ley-num" style="display:inline-block;margin-bottom:8px;">{{ $l[2] ? substr($l[0],0,15).'...' : $l[0] }}</div>
            <div style="font-size:0.85rem;color:var(--navy);font-weight:500;margin-bottom:5px;">{{ $l[0] }}</div>
            <div style="font-size:0.75rem;color:var(--text-gray);margin-bottom:8px;">📅 {{ $l[1] }}</div>
            <div style="font-size:0.8rem;color:var(--text-gray);line-height:1.5;">{{ $l[2] }}</div>
            <span class="badge badge-active" style="margin-top:8px;">{{ $l[3] }}</span>
        </div>
        @endforeach
    </div>
    <div style="text-align:center;margin-top:20px;">
        <a href="{{ route('leyes.create') }}" class="btn-primary">+ Agregar Leyes a la Base de Datos</a>
    </div>
</div>
@endif

<!-- Lista de leyes desde BD -->
@forelse($leyes as $ley)
<details class="ley-accordion ley-item-searchable animate-in"
         data-search="{{ strtolower($ley->numero . ' ' . $ley->titulo . ' ' . $ley->categoria . ' ' . $ley->descripcion . ' ' . ($ley->gaceta_oficial ?? '')) }}"
         style="background:white; border:1px solid rgba(201,168,76,0.3); border-radius:8px; margin-bottom:10px;">
    <summary class="ley-accordion-header" style="padding:15px 20px; cursor:pointer; list-style:none; display:flex; align-items:center; gap:15px;">
        <span class="ley-num">{{ $ley->numero }}</span>
        <span class="ley-title-text" style="flex:1; font-weight:600; color:var(--navy);">{{ $ley->titulo }}</span>
        @if($ley->link_oficial)
        <a href="{{ $ley->link_oficial }}" target="_blank" onclick="event.stopPropagation();" style="font-size:0.74rem;padding:4px 10px;border-radius:14px;background:rgba(201,168,76,0.15);border:1px solid var(--gold);color:var(--navy);text-decoration:none;font-weight:600;">
            🔗 Link Oficial
        </a>
        @endif
        <span class="badge {{ $ley->vigente ? 'badge-active' : 'badge-closed' }}">{{ $ley->vigente ? 'Vigente' : 'Derogada' }}</span>
        <span class="badge badge-pending">{{ $ley->categoria }}</span>
        <span style="font-size:0.8rem;">▼</span>
    </summary>
    <div class="ley-accordion-body" style="padding:20px; background:#fafafa; border-top:1px solid rgba(201,168,76,0.2);">
        @if($ley->fecha_promulgacion)
        <div style="font-size:0.78rem;color:var(--gold-dark);margin-bottom:10px;">
            📅 Promulgada: {{ $ley->fecha_promulgacion->format('d/m/Y') }}
            @if($ley->gaceta_oficial) | 📰 Gaceta: {{ $ley->gaceta_oficial }} @endif
        </div>
        @endif
        <p>{{ $ley->descripcion }}</p>
        
        @if($ley->articulos)
        <div style="margin:15px 0;">
            <h4 style="font-family:'Cinzel',serif; color:var(--navy); font-size:0.9rem; margin-bottom:8px;">📖 TEXTO COMPLETO DE LA LEY</h4>
            <div style="background:white; border:1px solid rgba(201,168,76,0.3); border-radius:8px; padding:15px; max-height:400px; overflow-y:auto; font-size:0.85rem; line-height:1.6; color:#444;">
                {!! nl2br(e($ley->articulos)) !!}
            </div>
        </div>
        @endif

        @if($ley->notas)
        <div class="ley-ref">📝 {{ $ley->notas }}</div>
        @endif
        <div style="margin-top:14px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            @if($ley->archivo_pdf)
            <a href="{{ route('leyes.descargar_archivo', $ley->archivo_pdf) }}" target="_blank" class="btn-primary" style="font-size:0.78rem;display:inline-flex;align-items:center;gap:6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                Abrir PDF de la Ley
            </a>
            @endif
            @if($ley->link_oficial)
            <a href="{{ $ley->link_oficial }}" target="_blank" class="btn-primary" style="font-size:0.78rem;display:inline-flex;align-items:center;gap:6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg>
                Abrir Enlace Oficial
            </a>
            @endif

            <form action="{{ route('leyes.upload_pdf', $ley->id) }}" method="POST" enctype="multipart/form-data" style="display:inline; margin:0; padding:0;">
                @csrf
                <input type="file" name="archivo_pdf" id="pdf-{{ $ley->id }}" style="display:none;" accept=".pdf" onchange="subirPdfLey(this, {{ $ley->id }})">
                <button type="button" class="btn-secondary" id="btn-pdf-{{ $ley->id }}" style="font-size:0.78rem;display:inline-flex;align-items:center;gap:6px;" onclick="document.getElementById('pdf-{{ $ley->id }}').click()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5v-2z"/></svg>
                    {{ $ley->archivo_pdf ? 'Cambiar PDF' : 'Subir Archivo PDF' }}
                </button>
            </form>

            <a href="{{ route('leyes.edit',$ley->id) }}" class="btn-edit" title="Editar" style="display:inline-flex;align-items:center;justify-content:center;">
                <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
            </a>

            @if(session('usuario_rol') === 'administrador')
            <form action="{{ route('leyes.destroy',$ley->id) }}" method="POST" style="display:inline;margin:0;">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger" title="Eliminar" style="display:inline-flex;align-items:center;justify-content:center;" onclick="return confirm('¿Eliminar esta ley?')">
                    <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                </button>
            </form>
            @endif
        </div>
    </div>
</details>
@empty
@endforelse

<div style="margin-top:20px;">{{ $leyes->links() }}</div>

<script>
function filtrarLeyesEnVivo(valor) {
    const termino = (valor || '').toLowerCase().trim();
    const items = document.querySelectorAll('.ley-item-searchable');
    let visibles = 0;

    items.forEach(item => {
        const texto = (item.getAttribute('data-search') || item.innerText || '').toLowerCase();
        if (!termino || texto.includes(termino)) {
            item.style.display = '';
            visibles++;
        } else {
            item.style.display = 'none';
        }
    });

    const aviso = document.getElementById('sinResultadosLive');
    const txtBuscado = document.getElementById('textoBuscadoLive');
    if (aviso) {
        if (items.length > 0 && visibles === 0 && termino.length > 0) {
            if (txtBuscado) txtBuscado.textContent = valor;
            aviso.style.display = 'block';
        } else {
            aviso.style.display = 'none';
        }
    }
}

function subirPdfLey(input, id) {
    if (input.files && input.files.length > 0) {
        const file = input.files[0];
        if (!file.name.toLowerCase().endsWith('.pdf')) {
            alert('El archivo seleccionado debe ser un documento PDF (.pdf).');
            input.value = '';
            return;
        }
        const btn = document.getElementById('btn-pdf-' + id);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span style="display:inline-block;animation:spin 1s linear infinite;">⏳</span> Subiendo PDF...';
            btn.style.opacity = '0.7';
        }
        input.form.submit();
    }
}
</script>

@endsection

