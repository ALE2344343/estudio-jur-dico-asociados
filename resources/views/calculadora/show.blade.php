@extends('layouts.dashboard')
@section('title','Detalle Cálculo')
@section('page-title','Detalle de Finiquito')

@section('top-bar-actions')
<a href="{{ route('calculadora.index') }}" class="btn-secondary">← Volver</a>
<button onclick="window.print()" class="btn-primary">Imprimir</button>
@endsection

@section('content')
<div style="max-width:800px;margin:0 auto;">

    <!-- Encabezado imprimible -->
    <div style="background:var(--gradient-navy);border-radius:12px 12px 0 0;padding:25px 30px;text-align:center;" class="animate-in">
        <div style="font-family:'Cinzel',serif;font-size:0.7rem;color:var(--gold);letter-spacing:4px;margin-bottom:5px;">ESTUDIO JURÍDICO & ASOCIADOS</div>
        <div style="font-family:'Cinzel',serif;font-size:1.4rem;color:white;margin-bottom:5px;">LIQUIDACIÓN DE BENEFICIOS SOCIALES</div>
        <div style="font-size:0.8rem;color:rgba(255,255,255,0.6);">Beneficios sociales — Normativa Vigente</div>
    </div>

    <div style="background:white;border:1px solid rgba(201,168,76,0.2);border-top:none;padding:30px;border-radius:0 0 12px 12px;">

        <!-- Datos del trabajador -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:25px;padding-bottom:20px;border-bottom:2px solid rgba(201,168,76,0.2);">
            <div>
                <div style="font-family:'Cinzel',serif;font-size:0.7rem;color:var(--gold-dark);letter-spacing:2px;margin-bottom:4px;">TRABAJADOR</div>
                <div style="font-size:1.1rem;font-weight:600;color:var(--navy);">{{ $calculo->trabajador_nombre }}</div>
            </div>
            <div>
                <div style="font-family:'Cinzel',serif;font-size:0.7rem;color:var(--gold-dark);letter-spacing:2px;margin-bottom:4px;">EMPRESA</div>
                <div style="font-size:1.1rem;font-weight:600;color:var(--navy);">{{ $calculo->empresa }}</div>
            </div>
            <div>
                <div style="font-family:'Cinzel',serif;font-size:0.7rem;color:var(--text-gray);letter-spacing:1px;margin-bottom:3px;">FECHA INGRESO</div>
                <div style="font-size:0.95rem;color:var(--navy);">{{ $calculo->fecha_ingreso->format('d/m/Y') }}</div>
            </div>
            <div>
                <div style="font-family:'Cinzel',serif;font-size:0.7rem;color:var(--text-gray);letter-spacing:1px;margin-bottom:3px;">FECHA RETIRO</div>
                <div style="font-size:0.95rem;color:var(--navy);">{{ $calculo->fecha_retiro->format('d/m/Y') }}</div>
            </div>
            <div>
                <div style="font-family:'Cinzel',serif;font-size:0.7rem;color:var(--text-gray);letter-spacing:1px;margin-bottom:3px;">SALARIO MENSUAL</div>
                <div style="font-size:0.95rem;color:var(--navy);font-weight:600;">Bs. {{ number_format($calculo->salario_mensual,2) }}</div>
            </div>
            <div>
                <div style="font-family:'Cinzel',serif;font-size:0.7rem;color:var(--text-gray);letter-spacing:1px;margin-bottom:3px;">TIPO DE RETIRO</div>
                <div style="font-size:0.95rem;color:var(--navy);">{{ str_replace('_',' ',ucfirst($calculo->tipo_retiro)) }}</div>
            </div>
            <div style="grid-column:span 2;">
                <div style="font-family:'Cinzel',serif;font-size:0.7rem;color:var(--text-gray);letter-spacing:1px;margin-bottom:3px;">TIEMPO DE SERVICIO</div>
                <div style="font-size:0.95rem;color:var(--navy);">
                    {{ floor($calculo->anos_trabajados) }} año(s) y {{ round(($calculo->anos_trabajados - floor($calculo->anos_trabajados)) * 12) }} mes(es)
                    <span style="color:var(--text-gray);font-size:0.82rem;">({{ $calculo->fecha_ingreso->diffInDays($calculo->fecha_retiro) }} días)</span>
                </div>
            </div>
        </div>

        <!-- Tabla de conceptos -->
        <table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
            <thead>
                <tr style="background:var(--navy);">
                    <th style="padding:12px 15px;text-align:left;font-family:'Cinzel',serif;font-size:0.72rem;color:var(--gold);letter-spacing:2px;">CONCEPTO</th>
                    <th style="padding:12px 15px;text-align:left;font-family:'Cinzel',serif;font-size:0.72rem;color:var(--gold);letter-spacing:2px;">BASE LEGAL</th>
                    <th style="padding:12px 15px;text-align:right;font-family:'Cinzel',serif;font-size:0.72rem;color:var(--gold);letter-spacing:2px;">MONTO (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                @php
                $anosText = floor($calculo->anos_trabajados);
                $mesesText = round(($calculo->anos_trabajados - floor($calculo->anos_trabajados)) * 12);
                $tiempoStr = "($anosText a, $mesesText m)";
                $soloMesesStr = "($mesesText m)";
                
                $conceptos = [
                    ['1. Reintegro', '-', $calculo->reintegro],
                    ['2. Sueldos Devengados ' . ($calculo->sueldos_devengados_meses > 0 ? '('.$calculo->sueldos_devengados_meses.' meses)' : ''), '-', $calculo->sueldos_devengados_monto],
                    ['3. Desahucio (3 sueldos)', 'Art. 12 LGT', $calculo->desahucio],
                    ['4. Indemnización ' . $tiempoStr, 'Art. 13 LGT', $calculo->indemnizacion],
                    ['5. Aguinaldo Proporcional ' . $soloMesesStr . ($calculo->multa_aguinaldo ? ' + Multa 100%' : ''), 'D.S. 1802', $calculo->aguinaldo],
                    ['6. Vacaciones Proporcionales ' . ($calculo->tomo_vacaciones ? '(Ya Tomadas)' : $tiempoStr), 'Art. 44 LGT', $calculo->vacaciones],
                    ['Prima Anual Proporcional ' . $soloMesesStr, 'Art. 57 LGT', $calculo->prima],
                    ['Bono de Antigüedad', 'D.S. 23932', $calculo->bono_antiguedad],
                ];
                @endphp
                @foreach($conceptos as $i => [$concepto, $ley, $monto])
                <tr style="border-bottom:1px solid rgba(201,168,76,0.12);" class="{{ $monto==0?'row-zero':'' }} {{ $i%2==0?'row-even':'' }}">
                    <td style="padding:13px 15px;font-size:0.88rem;color:var(--navy);">{{ $concepto }}</td>
                    <td style="padding:13px 15px;font-size:0.78rem;color:var(--text-gray);">{{ $ley }}</td>
                    <td style="padding:13px 15px;text-align:right;font-family:'Cinzel',serif;font-size:0.95rem;" class="{{ $monto>0?'monto-pos':'monto-zero' }}">
                        {{ number_format($monto,2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:var(--navy);">
                    <td colspan="2" style="padding:12px 15px;font-family:'Cinzel',serif;font-size:0.9rem;color:var(--gold);letter-spacing:2px;">TOTAL BENEFICIOS SOCIALES</td>
                    <td style="padding:12px 15px;text-align:right;font-family:'Cinzel',serif;font-size:1.2rem;color:var(--gold);font-weight:700;">
                        {{ number_format($calculo->total_finiquito,2) }}
                    </td>
                </tr>
                @if($calculo->multa_30)
                <tr style="background:rgba(220,53,69,0.1);">
                    <td colspan="2" style="padding:12px 15px;font-family:'Cinzel',serif;font-size:0.9rem;color:#d9534f;letter-spacing:1px; font-weight:bold;">MULTA 30% (D.S. 28699)</td>
                    <td style="padding:12px 15px;text-align:right;font-family:'Cinzel',serif;font-size:1.2rem;color:#d9534f;font-weight:700;">
                        {{ number_format($calculo->monto_multa_30,2) }}
                    </td>
                </tr>
                @endif
                <tr style="background:var(--gold);">
                    <td colspan="2" style="padding:16px 15px;font-family:'Cinzel',serif;font-size:1.1rem;color:var(--navy-dark);letter-spacing:2px; font-weight:bold;">TOTAL GENERAL A PAGAR</td>
                    <td style="padding:16px 15px;text-align:right;font-family:'Cinzel',serif;font-size:1.5rem;color:var(--navy-dark);font-weight:900;">
                        Bs. {{ number_format($calculo->total_general,2) }}
                    </td>
                </tr>
            </tfoot>
        </table>

        @if($calculo->observaciones)
        <div class="ley-ref"><strong>Observaciones:</strong> {{ $calculo->observaciones }}</div>
        @endif

        <!-- Pie con firmas -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;margin-top:50px;padding-top:20px;border-top:1px dashed rgba(201,168,76,0.3);">
            <div style="text-align:center;">
                <div style="border-top:1px solid #333;padding-top:8px;font-size:0.8rem;color:var(--text-gray);">Firma del Trabajador</div>
                <div style="font-size:0.75rem;color:var(--text-gray);margin-top:4px;">{{ $calculo->trabajador_nombre }}</div>
            </div>
            <div style="text-align:center;">
                <div style="border-top:1px solid #333;padding-top:8px;font-size:0.8rem;color:var(--text-gray);">Firma del Empleador</div>
                <div style="font-size:0.75rem;color:var(--text-gray);margin-top:4px;">{{ $calculo->empresa }}</div>
            </div>
        </div>

        <div style="text-align:center;margin-top:25px;font-size:0.72rem;color:var(--text-gray);">
            Calculado por {{ $calculo->usuario?->nombre_completo }} — {{ $calculo->created_at->format('d/m/Y H:i') }} —
            Estudio Jurídico & Asociados
        </div>
    </div>
</div>

@push('styles')
<style>
.row-zero { opacity: 0.4; }
.row-even { background: rgba(248,245,240,0.4); }
.monto-pos { color: var(--navy); font-weight: 600; }
.monto-zero { color: var(--text-gray); font-weight: 400; }
@media print {
    .sidebar, .top-bar, .btn-secondary, .btn-primary { display: none !important; }
    .main-content { margin-left: 0 !important; }
    .content-area { padding: 0 !important; }
    body { background: white !important; }
}
</style>
@endpush
@endsection
