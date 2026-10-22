@extends('layouts.dashboard')
@section('title','Procesos Laborales')
@section('page-title','Procesos Laborales / Liquidación')

@section('top-bar-actions')
<span class="badge" style="background:rgba(201,168,76,0.15);color:var(--gold-dark);border:1px solid var(--gold);font-size:0.75rem;padding:6px 12px;">
    Ley General del Trabajo 
</span>
@endsection

@section('content')
<div class="calc-container animate-in">

    <!-- Panel de entrada -->
    <div class="calc-input-card">
        <div class="calc-header">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="#C9A84C"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14h-2v-4H8v-2h4V7h2v4h4v2h-4v4z"/></svg>
            <h3>DATOS DE LA LIQUIDACIÓN</h3>
        </div>
        <div class="calc-body">
            <form id="formCalc">
                <div class="form-grid" style="grid-template-columns:1fr 1fr;gap:15px;">
                    <div class="form-group">
                        <label class="form-label">Fecha de Ingreso *</label>
                        <input type="date" id="fecha_ingreso" name="fecha_ingreso" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha de Retiro *</label>
                        <input type="date" id="fecha_retiro" name="fecha_retiro" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Salario Mensual (Bs.) *</label>
                        <input type="number" id="salario_mensual" name="salario_mensual" class="form-control"
                               min="1000" step="0.01" placeholder="Ej: 5000.00" required>
                        <small style="color:var(--text-gray);font-size:0.72rem;">SMN Bolivia: Bs. 3,300 (SPI Automático)</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipo de Retiro *</label>
                        <select id="tipo_retiro" name="tipo_retiro" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="despido_injustificado">Despido Injustificado (Paga Desahucio)</option>
                            <option value="despido_justificado">Despido Justificado</option>
                            <option value="voluntario">Retiro Voluntario</option>
                            <option value="mutuo_acuerdo">Mutuo Acuerdo</option>
                        </select>
                    </div>
                </div>

                <hr style="border:0;border-top:1px dashed rgba(26,39,68,0.2);margin:20px 0;">

                <h4 style="font-family:'Cinzel',serif;font-size:0.9rem;color:var(--navy);margin-bottom:15px;">Conceptos Adicionales y Multas</h4>
                
                <div class="form-grid" style="grid-template-columns:1fr 1fr;gap:15px;">
                    <div class="form-group">
                        <label class="form-label">1. Reintegro (Monto en Bs.)</label>
                        <input type="number" id="reintegro" name="reintegro" class="form-control" min="0" step="0.01" value="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">2. Sueldos Devengados (Meses adeudados)</label>
                        <input type="number" id="sueldos_devengados_meses" name="sueldos_devengados_meses" class="form-control" min="0" step="1" value="0">
                    </div>
                </div>

                <div class="form-group" style="margin-top:15px;">
                    <label style="display:flex;align-items:center;gap:10px;font-size:0.9rem;color:var(--navy);cursor:pointer;">
                        <input type="checkbox" id="tomo_vacaciones" name="tomo_vacaciones" style="width:18px;height:18px;">
                        <strong>El trabajador ya tomó sus vacaciones</strong> (No se pagan en la liquidación)
                    </label>
                </div>

                <div class="form-group" style="margin-top:10px;">
                    <label style="display:flex;align-items:center;gap:10px;font-size:0.9rem;color:var(--navy);cursor:pointer;">
                        <input type="checkbox" id="multa_aguinaldo" name="multa_aguinaldo" style="width:18px;height:18px;">
                        <strong>Aplicar Multa de Aguinaldo (100%)</strong> (Por falta de pago oportuno)
                    </label>
                </div>

                <div class="form-group" style="margin-top:10px;">
                    <label style="display:flex;align-items:center;gap:10px;font-size:0.9rem;color:var(--navy);cursor:pointer;">
                        <input type="checkbox" id="multa_30" name="multa_30" style="width:18px;height:18px;">
                        <strong>Aplicar Multa del 30% (D.S. 28699)</strong> (Vencimiento de 15 días plazo)
                    </label>
                </div>

                <button type="button" onclick="calcular()" class="btn-primary" style="width:100%;margin-top:20px;justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    CALCULAR LIQUIDACIÓN
                </button>
            </form>
        </div>
    </div>

    <!-- Panel de resultados -->
    <div class="calc-result-card" id="resultCard" style="opacity:0.4;">
        <div class="calc-header">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="#C9A84C"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
            <h3>RESULTADO</h3>
        </div>
        <div class="calc-body">
            <div id="infoTiempo" style="background:rgba(26,39,68,0.06);border-radius:8px;padding:12px;margin-bottom:15px;font-size:0.85rem;">
                <span id="txtTiempo" style="color:var(--text-gray);">Complete los datos y presione Calcular</span>
            </div>

            <div class="calc-result-item" id="row_reintegro">
                <span class="calc-result-label">1. Reintegro</span>
                <span class="calc-result-value" id="r_reintegro">Bs. 0.00</span>
            </div>
            <div class="calc-result-item" id="row_sueldos_devengados">
                <span class="calc-result-label">2. Sueldos Devengados</span>
                <span class="calc-result-value" id="r_sueldos_devengados">Bs. 0.00</span>
            </div>
            <div class="calc-result-item" id="row_desahucio">
                <span class="calc-result-label">3. Desahucio (Art. 12 LGT)</span>
                <span class="calc-result-value" id="r_desahucio">Bs. 0.00</span>
            </div>
            <div class="calc-result-item">
                <span class="calc-result-label">4. Indemnización (Art. 13 LGT)</span>
                <span class="calc-result-value" id="r_indemnizacion">Bs. 0.00</span>
            </div>
            <div class="calc-result-item">
                <span class="calc-result-label">5. Aguinaldo (+Multa si aplica)</span>
                <span class="calc-result-value" id="r_aguinaldo">Bs. 0.00</span>
            </div>
            <div class="calc-result-item">
                <span class="calc-result-label">6. Vacaciones Proporcionales</span>
                <span class="calc-result-value" id="r_vacaciones">Bs. 0.00</span>
            </div>
            
            <div class="calc-result-item" style="margin-top:10px; padding-top:10px; border-top:1px dashed #ccc;">
                <span class="calc-result-label">Prima Anual Proporcional</span>
                <span class="calc-result-value" id="r_prima">Bs. 0.00</span>
            </div>
            <div class="calc-result-item">
                <span class="calc-result-label">Bono de Antigüedad</span>
                <span class="calc-result-value" id="r_antiguedad">Bs. 0.00</span>
            </div>
        </div>

        <div class="calc-total" style="background:var(--gradient-gold); padding:10px; border-radius:8px;">
            <span class="calc-total-label" style="color:var(--navy-dark)">TOTAL BENEFICIOS SOCIALES</span>
            <span class="calc-total-amount" id="r_total_beneficios" style="color:var(--navy-dark)">Bs. 0.00</span>
        </div>

        <div class="calc-result-item" id="row_multa_30" style="margin-top:15px;">
            <span class="calc-result-label" style="color:#d9534f; font-weight:bold;">Multa 30% (D.S. 28699)</span>
            <span class="calc-result-value" id="r_multa_30" style="color:#d9534f; font-weight:bold;">Bs. 0.00</span>
        </div>

        <div class="calc-total" style="margin-top:15px;">
            <span class="calc-total-label">TOTAL GENERAL</span>
            <span class="calc-total-amount" id="r_total_general">Bs. 0.00</span>
        </div>

        <!-- Guardar cálculo -->
        <div style="padding:20px;">
            <div style="font-family:'Cinzel',serif;font-size:0.75rem;color:var(--gold-dark);margin-bottom:12px;letter-spacing:1px;">GUARDAR PROCESO LABORAL</div>
            <form action="{{ route('calculadora.guardar') }}" method="POST" id="formGuardar">
                @csrf
                <input type="hidden" name="fecha_ingreso" id="hf_ingreso">
                <input type="hidden" name="fecha_retiro" id="hf_retiro">
                <input type="hidden" name="salario_mensual" id="hf_salario">
                <input type="hidden" name="tipo_retiro" id="hf_tipo">
                
                <input type="hidden" name="reintegro" id="hf_reintegro">
                <input type="hidden" name="sueldos_devengados_meses" id="hf_sueldos_devengados_meses">
                <input type="hidden" name="tomo_vacaciones" id="hf_tomo_vacaciones">
                <input type="hidden" name="multa_aguinaldo" id="hf_multa_aguinaldo">
                <input type="hidden" name="multa_30" id="hf_multa_30">

                <div class="form-grid" style="gap:10px;margin-bottom:10px;">
                    <div class="form-group">
                        <label class="form-label">Nombre Trabajador</label>
                        <input type="text" name="trabajador_nombre" class="form-control" placeholder="Nombre completo">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Empresa / Empleador</label>
                        <input type="text" name="empresa" class="form-control" placeholder="Nombre empresa">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cliente (opcional)</label>
                        <select name="cliente_id" class="form-control">
                            <option value="">Sin vincular</option>
                            @foreach($clientes as $cl)
                            <option value="{{ $cl->id }}">{{ $cl->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Caso (opcional)</label>
                        <select name="caso_id" class="form-control">
                            <option value="">Sin vincular</option>
                            @foreach($casos as $ca)
                            <option value="{{ $ca->id }}">{{ $ca->numero_caso }} - {{ $ca->titulo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;" id="btnGuardar" disabled>
                    GUARDAR PARA IMPRIMIR
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Historial -->
@if($historial->count() > 0)
<div class="table-card animate-in" style="margin-top:25px;">
    <div class="table-card-header">
        <div class="table-card-title">
            HISTORIAL DE PROCESOS LABORALES
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Trabajador</th>
                <th>Empresa</th>
                <th>Período</th>
                <th>Tipo Retiro</th>
                <th>Total General</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($historial as $h)
            <tr>
                <td style="font-weight:500;">{{ $h->trabajador_nombre }}</td>
                <td>{{ $h->empresa }}</td>
                <td style="font-size:0.8rem;color:var(--text-gray);">
                    {{ $h->fecha_ingreso->format('d/m/Y') }} — {{ $h->fecha_retiro->format('d/m/Y') }}
                </td>
                <td><span class="badge badge-pending">{{ str_replace('_',' ',ucfirst($h->tipo_retiro)) }}</span></td>
                <td style="font-family:'Cinzel',serif;font-size:1rem;color:var(--gold-dark);font-weight:700;">
                    Bs. {{ number_format($h->total_general, 2) }}
                </td>
                <td style="font-size:0.8rem;color:var(--text-gray);">{{ $h->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    <a href="{{ route('calculadora.show', $h->id) }}" class="btn-primary" style="padding:4px 10px;font-size:0.75rem;">Ver / Imprimir</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@push('scripts')
<script>
function fmt(n) {
    return 'Bs. ' + parseFloat(n).toLocaleString('es-BO', {minimumFractionDigits:2, maximumFractionDigits:2});
}

async function calcular() {
    const fi = document.getElementById('fecha_ingreso').value;
    const fr = document.getElementById('fecha_retiro').value;
    const sal = document.getElementById('salario_mensual').value;
    const tipo = document.getElementById('tipo_retiro').value;

    const reintegro = document.getElementById('reintegro').value || 0;
    const s_meses = document.getElementById('sueldos_devengados_meses').value || 0;
    const t_vacaciones = document.getElementById('tomo_vacaciones').checked;
    const m_aguinaldo = document.getElementById('multa_aguinaldo').checked;
    const m_30 = document.getElementById('multa_30').checked;

    if (!fi || !fr || !sal || !tipo) {
        alert('Por favor complete todos los campos obligatorios (*).');
        return;
    }

    try {
        const payload = {
            fecha_ingreso: fi, 
            fecha_retiro: fr, 
            salario_mensual: sal, 
            tipo_retiro: tipo,
            reintegro: reintegro,
            sueldos_devengados_meses: s_meses,
            tomo_vacaciones: t_vacaciones,
            multa_aguinaldo: m_aguinaldo,
            multa_30: m_30
        };

        const res = await fetch('{{ route("calculadora.calcular") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (data.errors) {
            alert('Verifique los datos: ' + Object.values(data.errors).join(', '));
            return;
        }

        // Mostrar resultados
        document.getElementById('r_reintegro').textContent = fmt(data.reintegro);
        document.getElementById('r_sueldos_devengados').textContent = fmt(data.sueldos_devengados_monto) + (s_meses > 0 ? ` (${s_meses} meses)` : '');
        document.getElementById('r_desahucio').textContent = fmt(data.desahucio);
        document.getElementById('r_indemnizacion').textContent = fmt(data.indemnizacion);
        document.getElementById('r_aguinaldo').textContent = fmt(data.aguinaldo) + (m_aguinaldo ? ' (Doble por multa)' : '');
        document.getElementById('r_vacaciones').textContent = fmt(data.vacaciones) + (t_vacaciones ? ' (Ya tomadas)' : '');
        document.getElementById('r_prima').textContent = fmt(data.prima);
        document.getElementById('r_antiguedad').textContent = fmt(data.bono_antiguedad);
        
        document.getElementById('r_total_beneficios').textContent = fmt(data.total_finiquito);
        document.getElementById('r_multa_30').textContent = fmt(data.monto_multa_30);
        document.getElementById('r_total_general').textContent = fmt(data.total_general);

        document.getElementById('txtTiempo').innerHTML =
            `⏱️ <strong>Tiempo de servicios:</strong> ${data.anos_trabajados} año(s), ${data.meses_trabajados} mes(es) y ${data.dias_trabajados} día(s) | ` +
            `💵 SPI: Bs. ${data.spi_aplicado} | ` +
            `🔹 SMN: Bs. ${data.smn_aplicado}`;

        // Resaltar
        document.getElementById('row_desahucio').style.background = data.desahucio > 0 ? 'rgba(220,53,69,0.05)' : '';
        document.getElementById('row_multa_30').style.display = m_30 ? 'flex' : 'none';

        document.getElementById('resultCard').style.opacity = '1';
        document.getElementById('resultCard').style.transition = 'opacity 0.5s';

        // Habilitar guardado
        document.getElementById('hf_ingreso').value = fi;
        document.getElementById('hf_retiro').value = fr;
        document.getElementById('hf_salario').value = sal;
        document.getElementById('hf_tipo').value = tipo;
        document.getElementById('hf_reintegro').value = reintegro;
        document.getElementById('hf_sueldos_devengados_meses').value = s_meses;
        document.getElementById('hf_tomo_vacaciones').value = t_vacaciones ? 1 : 0;
        document.getElementById('hf_multa_aguinaldo').value = m_aguinaldo ? 1 : 0;
        document.getElementById('hf_multa_30').value = m_30 ? 1 : 0;

        document.getElementById('btnGuardar').disabled = false;

    } catch(e) {
        alert('Error al calcular. Intente nuevamente.');
        console.error(e);
    }
}

// Auto-calcular al cambiar campos
['fecha_ingreso','fecha_retiro','salario_mensual','tipo_retiro', 'reintegro', 'sueldos_devengados_meses', 'tomo_vacaciones', 'multa_aguinaldo', 'multa_30'].forEach(id => {
    document.getElementById(id).addEventListener('change', () => {
        const allFilled = ['fecha_ingreso','fecha_retiro','salario_mensual','tipo_retiro']
            .every(f => document.getElementById(f).value);
        if (allFilled) calcular();
    });
});
</script>
@endpush
@endsection
