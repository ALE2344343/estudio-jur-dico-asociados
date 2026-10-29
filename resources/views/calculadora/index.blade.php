@extends('layouts.dashboard')
@section('title','Calculadora de beneficios sociales - Procesos Laborales')
@section('page-title','Calculadora de beneficios sociales - Procesos Laborales')



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
                        <label class="form-label">1. Reintegro Manual (Opcional)</label>
                        <input type="number" id="reintegro" name="reintegro" class="form-control" min="0" step="0.01" value="0">
                        <small style="color:var(--text-gray);font-size:0.7rem;">Dejar en 0 para cálculo automático si el sueldo es menor a Bs. 3.300</small>
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
            <h3>LIQUIDACIÓN FINAL (RESULTADO DETALLADO)</h3>
        </div>
        <div class="calc-body">
            <!-- Datos imprescindibles estilo diapositiva -->
            <div id="infoTiempo" style="background:#e8f6f3;border:1px solid #a3e4d7;border-radius:8px;padding:14px;margin-bottom:16px;font-size:0.84rem;color:#0e6251;">
                <span id="txtTiempo" style="color:var(--text-gray);">Complete los datos y presione Calcular</span>
            </div>

            <div class="calc-result-item" id="row_reintegro" style="flex-direction:column;align-items:stretch;">
                <div style="display:flex;justify-content:space-between;width:100%;">
                    <span class="calc-result-label" id="lbl_reintegro">1. Reintegro de sueldos</span>
                    <span class="calc-result-value" id="r_reintegro">Bs. --------</span>
                </div>
                <div id="det_reintegro" style="font-size:0.74rem;color:#555;margin-top:4px;padding-left:10px;border-left:2px solid var(--gold);"></div>
            </div>

            <div class="calc-result-item" id="row_sueldos_devengados" style="flex-direction:column;align-items:stretch;">
                <div style="display:flex;justify-content:space-between;width:100%;">
                    <span class="calc-result-label" id="lbl_devengados">2. Sueldos devengados</span>
                    <span class="calc-result-value" id="r_sueldos_devengados">Bs. --------</span>
                </div>
                <div id="det_devengados" style="font-size:0.74rem;color:#555;margin-top:4px;padding-left:10px;border-left:2px solid var(--gold);"></div>
            </div>

            <div class="calc-result-item" id="row_desahucio" style="flex-direction:column;align-items:stretch;">
                <div style="display:flex;justify-content:space-between;width:100%;">
                    <span class="calc-result-label">3. Desahucio (3 sueldos)</span>
                    <span class="calc-result-value" id="r_desahucio">Bs. --------</span>
                </div>
                <div id="det_desahucio" style="font-size:0.74rem;color:#555;margin-top:4px;padding-left:10px;border-left:2px solid var(--gold);"></div>
            </div>

            <div class="calc-result-item" style="flex-direction:column;align-items:stretch;">
                <div style="display:flex;justify-content:space-between;width:100%;">
                    <span class="calc-result-label" id="lbl_indemnizacion">4. Indemnización</span>
                    <span class="calc-result-value" id="r_indemnizacion">Bs. 0,00.-</span>
                </div>
                <div id="det_indemnizacion" style="font-size:0.74rem;color:#555;margin-top:4px;padding-left:10px;border-left:2px solid var(--gold);"></div>
            </div>

            <div class="calc-result-item" style="flex-direction:column;align-items:stretch;">
                <div style="display:flex;justify-content:space-between;width:100%;">
                    <span class="calc-result-label" id="lbl_aguinaldo">5. Aguinaldos</span>
                    <span class="calc-result-value" id="r_aguinaldo">Bs. 0,00.-</span>
                </div>
                <div id="det_aguinaldo" style="font-size:0.74rem;color:#555;margin-top:4px;padding-left:10px;border-left:2px solid var(--gold);"></div>
            </div>

            <div class="calc-result-item" id="row_multa_aguinaldo" style="display:none;justify-content:space-between;background:rgba(40,167,69,0.06);">
                <span class="calc-result-label">Multa por incumplimiento al pago de aguinaldo</span>
                <span class="calc-result-value" id="r_multa_aguinaldo_val">Bs. 0,00.-</span>
            </div>

            <div class="calc-result-item" style="flex-direction:column;align-items:stretch;">
                <div style="display:flex;justify-content:space-between;width:100%;">
                    <span class="calc-result-label" id="lbl_vacaciones">6. Vacaciones</span>
                    <span class="calc-result-value" id="r_vacaciones">Bs. --------</span>
                </div>
                <div id="det_vacaciones" style="font-size:0.74rem;color:#555;margin-top:4px;padding-left:10px;border-left:2px solid var(--gold);"></div>
            </div>
            
            <div class="calc-result-item" style="flex-direction:column;align-items:stretch;">
                <div style="display:flex;justify-content:space-between;width:100%;">
                    <span class="calc-result-label" id="lbl_antiguedad">7. Bono de antigüedad</span>
                    <span class="calc-result-value" id="r_antiguedad">Bs. --------</span>
                </div>
                <div id="det_antiguedad" style="font-size:0.74rem;color:#555;margin-top:4px;padding-left:10px;border-left:2px solid var(--gold);"></div>
            </div>
        </div>

        <div class="calc-total" style="background:var(--gradient-gold); padding:12px 15px; border-radius:8px;">
            <span class="calc-total-label" style="color:var(--navy-dark);font-weight:800;">TOTAL BENEFICIOS SOCIALES</span>
            <span class="calc-total-amount" id="r_total_beneficios" style="color:var(--navy-dark);font-weight:900;text-decoration:underline;">Bs. 0,00.-</span>
        </div>

        <!-- Monto en Literal como en los ejemplos -->
        <div id="box_literal" style="background:#f8f9fa;border:1px solid #dee2e6;border-radius:8px;padding:12px 15px;margin-top:10px;">
            <div style="font-weight:800;font-size:0.85rem;color:var(--navy);" id="r_son_literal">SON: CERO 00/100 BOLIVIANOS.</div>
            <div style="font-size:0.76rem;color:#444;margin-top:6px;line-height:1.4;">
                Conforme D.S. 28699 el monto será sujeto a actualización en UFVs más aplicación de la multa establecida del 30% realizada en ejecución de sentencia.
            </div>
        </div>

        <div class="calc-result-item" id="row_multa_30" style="margin-top:12px;display:none;">
            <span class="calc-result-label" style="color:#d9534f; font-weight:bold;">Multa 30% (D.S. 28699)</span>
            <span class="calc-result-value" id="r_multa_30" style="color:#d9534f; font-weight:bold;">Bs. 0,00.-</span>
        </div>

        <div class="calc-total" id="row_total_general" style="margin-top:12px;display:none;">
            <span class="calc-total-label">TOTAL GENERAL CON MULTA 30%</span>
            <span class="calc-total-amount" id="r_total_general">Bs. 0,00.-</span>
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
    const val = parseFloat(n);
    if (!val || val === 0) return 'Bs. ---------';
    return 'Bs. ' + val.toLocaleString('es-BO', {minimumFractionDigits:2, maximumFractionDigits:2}) + '.-';
}

function fmtAlways(n) {
    const val = parseFloat(n) || 0;
    return 'Bs. ' + val.toLocaleString('es-BO', {minimumFractionDigits:2, maximumFractionDigits:2}) + '.-';
}

function renderDetalles(id, lineas) {
    const el = document.getElementById(id);
    if (!el) return;
    if (!lineas || lineas.length === 0) {
        el.innerHTML = '';
        return;
    }
    el.innerHTML = lineas.map(l => `<div>• ${l}</div>`).join('');
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

        const tStr = `${data.anos_trabajados} años, ${data.meses_trabajados} meses, ${data.dias_trabajados} días`;
        const fiParts = fi.split('-');
        const frParts = fr.split('-');
        const fiFmt = `${fiParts[2]} – ${fiParts[1]} – ${fiParts[0]}`;
        const frFmt = `${frParts[2]} – ${frParts[1]} – ${frParts[0]}`;

        document.getElementById('txtTiempo').innerHTML = `
            <div style="display:grid;grid-template-columns:210px 1fr;row-gap:4px;font-weight:600;color:#111;">
                <div>Tiempo de servicio</div><div>: ${tStr}</div>
                <div>Fecha de ingreso</div><div>: ${fiFmt}</div>
                <div>Fecha de retiro</div><div>: ${frFmt}</div>
                <div>Sueldo percibido</div><div>: Bs. ${parseFloat(data.salario_percibido).toLocaleString('es-BO', {minimumFractionDigits:2})}.-</div>
                <div>Sueldo promedio indemnizable (SPI)</div><div>: Bs. ${parseFloat(data.spi_aplicado).toLocaleString('es-BO', {minimumFractionDigits:2})}.- (SMN Bs. 3.300,00)</div>
            </div>
        `;

        // Mostrar montos y detalles paso a paso
        document.getElementById('r_reintegro').textContent = fmt(data.reintegro);
        document.getElementById('r_sueldos_devengados').textContent = fmt(data.sueldos_devengados_monto);
        document.getElementById('r_desahucio').textContent = fmt(data.desahucio);
        document.getElementById('r_indemnizacion').textContent = fmtAlways(data.indemnizacion);
        document.getElementById('r_aguinaldo').textContent = fmtAlways(data.aguinaldo_base);

        const rowMultaAg = document.getElementById('row_multa_aguinaldo');
        if (m_aguinaldo) {
            rowMultaAg.style.display = 'flex';
            document.getElementById('r_multa_aguinaldo_val').textContent = fmtAlways(data.multa_aguinaldo_monto);
        } else {
            rowMultaAg.style.display = 'none';
        }

        document.getElementById('r_vacaciones').textContent = fmt(data.vacaciones);
        document.getElementById('r_antiguedad').textContent = fmt(data.bono_antiguedad);

        if (data.detalles) {
            renderDetalles('det_reintegro', data.detalles.reintegro);
            renderDetalles('det_devengados', data.detalles.sueldos_devengados);
            renderDetalles('det_desahucio', data.detalles.desahucio);
            renderDetalles('det_indemnizacion', data.detalles.indemnizacion);
            renderDetalles('det_aguinaldo', data.detalles.aguinaldo);
            renderDetalles('det_vacaciones', data.detalles.vacaciones);
            renderDetalles('det_antiguedad', data.detalles.bono_antiguedad);
        }

        document.getElementById('r_total_beneficios').textContent = fmtAlways(data.total_finiquito);
        document.getElementById('r_son_literal').textContent = 'SON: ' + data.literal_finiquito;

        document.getElementById('r_multa_30').textContent = fmtAlways(data.monto_multa_30);
        document.getElementById('r_total_general').textContent = fmtAlways(data.total_general);

        // Resaltar
        document.getElementById('row_desahucio').style.background = data.desahucio > 0 ? 'rgba(220,53,69,0.05)' : '';
        document.getElementById('row_multa_30').style.display = m_30 ? 'flex' : 'none';
        document.getElementById('row_total_general').style.display = m_30 ? 'flex' : 'none';

        document.getElementById('resultCard').style.opacity = '1';
        document.getElementById('resultCard').style.transition = 'opacity 0.5s';

        // Habilitar guardado
        document.getElementById('hf_ingreso').value = fi;
        document.getElementById('hf_retiro').value = fr;
        document.getElementById('hf_salario').value = sal;
        document.getElementById('hf_tipo').value = tipo;
        document.getElementById('hf_reintegro').value = data.reintegro;
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
