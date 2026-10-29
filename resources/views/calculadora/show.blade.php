@extends('layouts.dashboard')
@section('title','Detalle Cálculo')
@section('page-title','Detalle de Finiquito')

@section('top-bar-actions')
<a href="{{ route('calculadora.index') }}" class="btn-secondary">← Volver</a>
<button onclick="window.print()" class="btn-primary">Imprimir</button>
@endsection

@section('content')
@php
    $anos  = $desglose['anos_trabajados'] ?? (int) $calculo->anos_trabajados;
    $meses = $desglose['meses_trabajados'] ?? (int) $calculo->meses_trabajados;
    $dias  = $desglose['dias_trabajados'] ?? (int) $calculo->dias_pendientes;
    $spi   = $desglose['spi_aplicado'] ?? max((float)$calculo->salario_mensual, 3300);
    $smn   = $desglose['smn_aplicado'] ?? 3300;
    $tiempoStr = "{$anos} años, {$meses} meses, {$dias} días";
    $tiempoCorto = ($anos > 0 ? "{$anos} años, " : "") . "{$meses} meses, {$dias} días";

    $aguiBase  = $desglose['aguinaldo_base'] ?? ($calculo->multa_aguinaldo ? ($calculo->aguinaldo / 2) : $calculo->aguinaldo);
    $aguiMulta = $desglose['multa_aguinaldo_monto'] ?? ($calculo->multa_aguinaldo ? ($calculo->aguinaldo / 2) : 0);

    $literalSubtotal = $desglose['literal_finiquito'] ?? \App\Models\CalculoFiniquito::numeroALetras((float)$calculo->total_finiquito);
    $literalGeneral  = $desglose['literal_general'] ?? \App\Models\CalculoFiniquito::numeroALetras((float)$calculo->total_general);
@endphp
<div style="max-width:850px;margin:0 auto;">

    <!-- Encabezado imprimible -->
    <div style="background:var(--gradient-navy);border-radius:12px 12px 0 0;padding:25px 30px;text-align:center;" class="animate-in">
        <div style="font-family:'Cinzel',serif;font-size:0.7rem;color:var(--gold);letter-spacing:4px;margin-bottom:5px;">ESTUDIO JURÍDICO & ASOCIADOS</div>
        <div style="font-family:'Cinzel',serif;font-size:1.4rem;color:white;margin-bottom:5px;">LIQUIDACIÓN FINAL DE BENEFICIOS SOCIALES</div>
        <div style="font-size:0.8rem;color:rgba(255,255,255,0.6);">Derechos Laborales y Beneficios Sociales — Normativa Vigente (SMN Bs. 3.300,00)</div>
    </div>

    <div style="background:white;border:1px solid rgba(201,168,76,0.2);border-top:none;padding:35px;border-radius:0 0 12px 12px;box-shadow:0 4px 20px rgba(0,0,0,0.05);">

        <!-- Cabecera estilo Liquidación Final de los ejemplos -->
        <div style="margin-bottom:20px;">
            <div style="font-size:1.25rem;font-weight:800;color:#111;text-transform:uppercase;">{{ $calculo->trabajador_nombre }}</div>
            @if($calculo->cliente && $calculo->cliente->ci)
                <div style="font-size:1rem;font-weight:700;color:#222;">C.I. {{ $calculo->cliente->ci }} {{ $calculo->cliente->ci_expedido }}</div>
            @endif
            <div style="font-size:0.9rem;color:#555;margin-top:2px;"><strong>Empleador / Empresa:</strong> {{ $calculo->empresa }} | <strong>Motivo:</strong> {{ str_replace('_',' ',ucfirst($calculo->tipo_retiro)) }}</div>
        </div>

        <!-- Datos Imprescindibles -->
        <div style="background:#f4f9f8;border:1px solid #cce3de;border-left:5px solid var(--navy);border-radius:6px;padding:16px 20px;margin-bottom:25px;font-size:0.92rem;color:#111;">
            <div style="display:grid;grid-template-columns:250px 1fr;row-gap:6px;font-weight:700;">
                <div>Tiempo de servicio</div>
                <div>: {{ $tiempoStr }}</div>
                <div>Fecha de ingreso</div>
                <div>: {{ $calculo->fecha_ingreso->format('d – m – Y') }}</div>
                <div>Fecha de retiro</div>
                <div>: {{ $calculo->fecha_retiro->format('d – m – Y') }}</div>
                <div>Sueldo percibido declarado</div>
                <div>: Bs. {{ number_format($calculo->salario_mensual, 2, ',', '.') }}.-</div>
                <div>Sueldo promedio indemnizable (SPI)</div>
                <div>: Bs. {{ number_format($spi, 2, ',', '.') }}.- (Base SMN: Bs. {{ number_format($smn, 2, ',', '.') }}.-)</div>
            </div>
        </div>

        <!-- Tabla Resumen Liquidación Final (idéntica a las diapositivas 20 y 21) -->
        <div style="border:1px solid #e0e0e0;border-radius:8px;padding:22px 25px;background:#fafafa;margin-bottom:28px;">
            <div style="font-family:'Cinzel',serif;font-size:0.85rem;font-weight:700;color:var(--navy);letter-spacing:1px;margin-bottom:15px;border-bottom:2px solid var(--gold);padding-bottom:6px;">
                RESUMEN DE LIQUIDACIÓN FINAL
            </div>

            @php
                $filasResumen = [
                    [
                        'Reintegro de sueldos ' . ($calculo->reintegro > 0 ? "({$tiempoCorto})" : '-------------------------------------'),
                        $calculo->reintegro
                    ],
                    [
                        'Sueldos devengados ' . ($calculo->sueldos_devengados_meses > 0 ? "({$calculo->sueldos_devengados_meses} mes(es))" : '-------------------------------------'),
                        $calculo->sueldos_devengados_monto
                    ],
                    [
                        'Desahucio ' . ($calculo->desahucio > 0 ? '(3 sueldos por despido)' : '-------------------------------------'),
                        $calculo->desahucio
                    ],
                    [
                        "Indemnización ({$tiempoCorto})",
                        $calculo->indemnizacion
                    ],
                    [
                        "Aguinaldos: ({$meses} meses, {$dias} días)",
                        $aguiBase
                    ],
                    [
                        'Multa por incumplimiento al pago de aguinaldo',
                        $aguiMulta
                    ],
                    [
                        'Vacaciones ' . ($calculo->vacaciones > 0 ? "({$meses} meses, {$dias} días)" : '------------------------------------'),
                        $calculo->vacaciones
                    ],
                    [
                        'Bono de antigüedad ' . ($calculo->bono_antiguedad > 0 ? "({$meses} meses, {$dias} días)" : '------------------------'),
                        $calculo->bono_antiguedad
                    ],
                ];
            @endphp

            <table style="width:100%;border-collapse:collapse;font-size:0.95rem;">
                <tbody>
                    @foreach($filasResumen as [$etiqueta, $valor])
                    <tr>
                        <td style="padding:7px 0;color:#111;font-weight:500;">{{ $etiqueta }}</td>
                        <td style="padding:7px 0;text-align:right;font-weight:700;color:#111;white-space:nowrap;">
                            @if($valor > 0)
                                Bs. {{ number_format($valor, 2, ',', '.') }}.-
                            @else
                                Bs. ---------
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="padding-top:12px;"></td>
                    </tr>
                    <tr style="border-top:2px solid #222;">
                        <td style="padding:12px 0;font-weight:900;font-size:1.05rem;color:#000;letter-spacing:0.5px;">
                            TOTAL BENEFICIOS SOCIALES....................................................
                        </td>
                        <td style="padding:12px 0;text-align:right;font-weight:900;font-size:1.15rem;color:#000;text-decoration:underline;white-space:nowrap;">
                            Bs. {{ number_format($calculo->total_finiquito, 2, ',', '.') }}.-
                        </td>
                    </tr>
                </tfoot>
            </table>

            <!-- Literal exacto -->
            <div style="margin-top:12px;padding-top:10px;border-top:1px dashed #ccc;">
                <div style="font-weight:800;font-size:0.95rem;color:#000;text-transform:uppercase;">
                    SON: {{ $literalSubtotal }}
                </div>
                <p style="margin:10px 0 0 0;font-size:0.88rem;color:#222;line-height:1.45;">
                    Conforme D.S. 28699 el monto será sujeto a actualización en UFVs más aplicación de la multa establecida del 30% realizada en ejecución de sentencia.
                </p>
            </div>

            @if($calculo->multa_30)
            <div style="margin-top:15px;padding:12px 15px;background:rgba(220,53,69,0.08);border:1px solid rgba(220,53,69,0.3);border-radius:6px;">
                <div style="display:flex;justify-content:space-between;font-weight:800;color:#b02a37;font-size:0.95rem;">
                    <span>+ MULTA 30% POR INCUMPLIMIENTO DE PAGO (D.S. 28699):</span>
                    <span>Bs. {{ number_format($calculo->monto_multa_30, 2, ',', '.') }}.-</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-weight:900;color:var(--navy);font-size:1.1rem;margin-top:6px;padding-top:6px;border-top:1px solid rgba(220,53,69,0.2);">
                    <span>TOTAL GENERAL INCLUYENDO MULTA 30%:</span>
                    <span style="text-decoration:underline;">Bs. {{ number_format($calculo->total_general, 2, ',', '.') }}.-</span>
                </div>
                <div style="font-weight:800;font-size:0.85rem;color:var(--navy);margin-top:4px;">
                    SON: {{ $literalGeneral }}
                </div>
            </div>
            @endif
        </div>

        <!-- Desglose Técnico Paso a Paso de las Fórmulas Aplicadas -->
        @if(!empty($desglose['detalles']))
        <div style="margin-bottom:30px;">
            <div style="font-family:'Cinzel',serif;font-size:0.85rem;font-weight:700;color:var(--navy);letter-spacing:1px;margin-bottom:12px;border-bottom:2px solid rgba(26,39,68,0.15);padding-bottom:6px;">
                DESGLOSE DETALLADO DE OPERACIONES Y FÓRMULAS LEGALES
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                @php
                    $bloquesDetalle = [
                        ['2.1. Cálculo de Reintegro de Sueldos', $desglose['detalles']['reintegro'] ?? []],
                        ['2.2. Cálculo de Sueldos Devengados', $desglose['detalles']['sueldos_devengados'] ?? []],
                        ['2.3. Cálculo del Desahucio (Art. 12 LGT)', $desglose['detalles']['desahucio'] ?? []],
                        ['2.4. Cálculo de la Indemnización (Art. 13 LGT)', $desglose['detalles']['indemnizacion'] ?? []],
                        ['2.5. Cálculo de Aguinaldos (Ley 18/12/1944)', $desglose['detalles']['aguinaldo'] ?? []],
                        ['2.6. Cálculo de Vacaciones (Art. 44 LGT)', $desglose['detalles']['vacaciones'] ?? []],
                        ['2.7. Cálculo de Bono de Antigüedad (D.S. 21060)', $desglose['detalles']['bono_antiguedad'] ?? []],
                    ];
                @endphp
                @foreach($bloquesDetalle as [$tituloBloque, $lineasBloque])
                <div style="background:#fff;border:1px solid #e2e8f0;border-left:4px solid var(--gold-dark);border-radius:6px;padding:12px 15px;">
                    <div style="font-weight:700;font-size:0.82rem;color:var(--navy);margin-bottom:6px;">{{ $tituloBloque }}</div>
                    <div style="font-size:0.78rem;color:#333;line-height:1.5;">
                        @foreach($lineasBloque as $lin)
                            <div>• {{ $lin }}</div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

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
