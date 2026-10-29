<?php

namespace App\Http\Controllers;

use App\Models\CalculoFiniquito;
use App\Models\Cliente;
use App\Models\Caso;
use App\Models\HistorialActividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CalculadoraController extends Controller
{
    private function checkAuth()
    {
        if (!Session::has('usuario_id')) return redirect()->route('inicio');
        return null;
    }

    public function index()
    {
        if ($r = $this->checkAuth()) return $r;
        if (Session::get('usuario_rol') === 'administrador') {
            return redirect()->route('dashboard')->with('error', 'El módulo de Procesos Laborales está reservado para los abogados.');
        }
        $userId   = Session::get('usuario_id');
        $rol      = Session::get('usuario_rol');
        $historial = CalculoFiniquito::with(['cliente','caso'])
            ->where('usuario_id', $userId)
            ->orderBy('created_at','desc')
            ->take(10)->get();
        $clientes = ($rol === 'administrador') ? Cliente::all() : Cliente::where('usuario_id',$userId)->get();
        $casos    = ($rol === 'administrador') ? Caso::where('tipo','Laboral')->get() : Caso::where('abogado_id',$userId)->where('tipo','Laboral')->get();

        return view('calculadora.index', compact('historial','clientes','casos'));
    }

    public function calcular(Request $request)
    {
        $request->validate([
            'fecha_ingreso'   => 'required|date',
            'fecha_retiro'    => 'required|date|after:fecha_ingreso',
            'salario_mensual' => 'required|numeric|min:1',
            'tipo_retiro'     => 'required',
        ]);

        $resultado = $this->calcularFiniquito(
            $request->fecha_ingreso,
            $request->fecha_retiro,
            $request->salario_mensual,
            $request->tipo_retiro,
            $request->reintegro ?? 0,
            $request->sueldos_devengados_meses ?? 0,
            filter_var($request->tomo_vacaciones, FILTER_VALIDATE_BOOLEAN),
            filter_var($request->multa_aguinaldo, FILTER_VALIDATE_BOOLEAN),
            filter_var($request->multa_30, FILTER_VALIDATE_BOOLEAN)
        );

        return response()->json($resultado);
    }

    public function guardar(Request $request)
    {
        if ($r = $this->checkAuth()) return $r;

        $request->validate([
            'trabajador_nombre' => 'required',
            'empresa'           => 'required',
            'fecha_ingreso'     => 'required|date',
            'fecha_retiro'      => 'required|date|after:fecha_ingreso',
            'salario_mensual'   => 'required|numeric|min:1',
            'tipo_retiro'       => 'required',
        ]);

        $calc = $this->calcularFiniquito(
            $request->fecha_ingreso,
            $request->fecha_retiro,
            $request->salario_mensual,
            $request->tipo_retiro,
            $request->reintegro ?? 0,
            $request->sueldos_devengados_meses ?? 0,
            filter_var($request->tomo_vacaciones, FILTER_VALIDATE_BOOLEAN),
            filter_var($request->multa_aguinaldo, FILTER_VALIDATE_BOOLEAN),
            filter_var($request->multa_30, FILTER_VALIDATE_BOOLEAN)
        );

        $nuevo = CalculoFiniquito::create([
            'usuario_id'        => Session::get('usuario_id'),
            'cliente_id'        => $request->cliente_id ?: null,
            'caso_id'           => $request->caso_id ?: null,
            'trabajador_nombre' => $request->trabajador_nombre,
            'empresa'           => $request->empresa,
            'fecha_ingreso'     => $request->fecha_ingreso,
            'fecha_retiro'      => $request->fecha_retiro,
            'salario_mensual'   => $request->salario_mensual,
            'tipo_retiro'       => $request->tipo_retiro,
            'anos_trabajados'   => $calc['anos_trabajados'],
            'meses_trabajados'  => $calc['meses_trabajados'],
            'indemnizacion'     => $calc['indemnizacion'],
            'desahucio'         => $calc['desahucio'],
            'vacaciones'        => $calc['vacaciones'],
            'aguinaldo'         => $calc['aguinaldo'],
            'prima'             => $calc['prima'],
            'bono_antiguedad'   => $calc['bono_antiguedad'],
            'dias_pendientes'   => $calc['dias_pendientes'],
            'reintegro'                => $calc['reintegro'],
            'sueldos_devengados_meses' => $calc['sueldos_devengados_meses'],
            'sueldos_devengados_monto' => $calc['sueldos_devengados_monto'],
            'tomo_vacaciones'          => $calc['tomo_vacaciones'],
            'multa_aguinaldo'          => $calc['multa_aguinaldo'],
            'multa_30'                 => $calc['multa_30'],
            'monto_multa_30'           => $calc['monto_multa_30'],
            'total_finiquito'   => $calc['total_finiquito'],
            'total_general'     => $calc['total_general'],
            'observaciones'     => $request->observaciones,
        ]);

        $totalFmt = number_format($nuevo->total_general, 2);
        HistorialActividad::registrar('Calculadora', 'Creación', "Generó liquidación de beneficios sociales para {$nuevo->trabajador_nombre} (Empresa: {$nuevo->empresa} | Total: Bs. {$totalFmt})", $nuevo->id);

        return redirect()->route('calculadora.show', $nuevo->id)->with('success', 'Cálculo guardado. Ya puede imprimir la liquidación.');
    }

    /**
     * Calcula el finiquito según la Ley General del Trabajo boliviana
     * aplicando el SMN actualizado a 3300 Bs. (según requerimiento)
     */
    private function calcularFiniquito(
        string $fechaIngreso, 
        string $fechaRetiro, 
        float $salario, 
        string $tipoRetiro, 
        float $reintegro = 0,
        int $sueldosDevengadosMeses = 0,
        bool $tomoVacaciones = false,
        bool $multaAguinaldo = false,
        bool $multa30 = false
    ): array
    {
        $inicio  = \Carbon\Carbon::parse($fechaIngreso);
        $fin     = \Carbon\Carbon::parse($fechaRetiro);

        $diff = $inicio->diff($fin);
        $anos  = $diff->y;
        $meses = $diff->m;
        $dias  = $diff->d;

        $smn = 3300.00; 
        $spi = max($salario, $smn);

        // 1. REINTEGRO DE SUELDOS (cuando el salario percibido era menor al SMN de Bs. 3.300)
        $reintegroAuto = 0.0;
        $detalleReintegro = [];
        if ($salario < $smn) {
            $difMensual = round($smn - $salario, 2);
            $difDiaria  = round($difMensual / 30, 2);

            $mesesReintegroTotal = ($anos * 12) + $meses - $sueldosDevengadosMeses;
            if ($mesesReintegroTotal < 0) $mesesReintegroTotal = 0;

            $montoMesesReintegro = round($difMensual * $mesesReintegroTotal, 2);
            $montoDiasReintegro  = round($difDiaria * $dias, 2);
            $reintegroAuto       = round($montoMesesReintegro + $montoDiasReintegro, 2);

            $detalleReintegro[] = "3300 (SMN) - " . number_format($salario, 2, ',', '.') . " salario = " . number_format($difMensual, 2, ',', '.') . " por mes de reintegro";
            if ($mesesReintegroTotal > 0) {
                $detalleReintegro[] = number_format($difMensual, 2, ',', '.') . " x {$mesesReintegroTotal} mes(es) = " . number_format($montoMesesReintegro, 2, ',', '.');
            }
            if ($dias > 0) {
                $detalleReintegro[] = number_format($difMensual, 2, ',', '.') . " / 30 días = " . number_format($difDiaria, 2, ',', '.') . " por día de reintegro";
                $detalleReintegro[] = number_format($difDiaria, 2, ',', '.') . " x {$dias} día(s) = " . number_format($montoDiasReintegro, 2, ',', '.');
            }
            $detalleReintegro[] = "Total reintegro: Bs. " . number_format($reintegroAuto, 2, ',', '.') . ".-";
        }

        $reintegroFinal = ($reintegro > 0) ? round($reintegro, 2) : $reintegroAuto;
        if ($reintegro > 0 && empty($detalleReintegro)) {
            $detalleReintegro[] = "Reintegro establecido manualmente: Bs. " . number_format($reintegroFinal, 2, ',', '.') . ".-";
        } elseif ($reintegroFinal == 0) {
            $detalleReintegro[] = "No corresponde (El sueldo percibido es igual o superior al SMN de Bs. 3.300,00)";
        }

        // 2. SUELDOS DEVENGADOS
        $sueldosDevengadosMonto = round($spi * $sueldosDevengadosMeses, 2);
        $detalleDevengados = [];
        if ($sueldosDevengadosMeses > 0) {
            $detalleDevengados[] = "Sueldos devengados ({$sueldosDevengadosMeses} mes(es)) x Bs. " . number_format($spi, 2, ',', '.') . " = Bs. " . number_format($sueldosDevengadosMonto, 2, ',', '.') . ".-";
        } else {
            $detalleDevengados[] = "No corresponde";
        }

        // 3. DESAHUCIO
        $desahucio = ($tipoRetiro === 'despido_injustificado') ? round($spi * 3, 2) : 0.00;
        $detalleDesahucio = [];
        if ($desahucio > 0) {
            $detalleDesahucio[] = "3 sueldos por el despido — Desahucio: Bs. " . number_format($desahucio, 2, ',', '.') . ".- (Fórmula: SPI x 3 sueldos)";
        } else {
            $detalleDesahucio[] = "No corresponde";
        }

        // 4. INDEMNIZACIÓN
        $indemAnos  = round($spi * $anos, 2);
        $indemMeses = round(($spi / 12) * $meses, 2);
        $indemDias  = round(($spi / 360) * $dias, 2);
        $indemnizacion = round($indemAnos + $indemMeses + $indemDias, 2);
        $detalleIndemnizacion = [];
        if ($anos > 0) {
            $detalleIndemnizacion[] = "Indemnización ({$anos} año(s)) = " . number_format($indemAnos, 2, ',', '.') . ".- (Fórmula: SPI x {$anos} año(s))";
        }
        if ($meses > 0) {
            $detalleIndemnizacion[] = "Indemnización ({$meses} mes(es)) = " . number_format($indemMeses, 2, ',', '.') . ".- (Fórmula: SPI / 12 x {$meses} mes(es))";
        }
        if ($dias > 0) {
            $detalleIndemnizacion[] = "Indemnización ({$dias} día(s)) = " . number_format($indemDias, 2, ',', '.') . ".- (Fórmula: SPI / 360 x {$dias} día(s))";
        }
        $detalleIndemnizacion[] = "Total indemnización ({$anos} años, {$meses} meses y {$dias} días) = Bs. " . number_format($indemnizacion, 2, ',', '.') . ".-";

        // 5. AGUINALDO
        $mesesAguinaldo = ($anos >= 1 && $meses == 0 && $dias == 0) ? 12 : $meses;
        $diasAguinaldo  = $dias;
        $aguiMeses = round(($spi / 12) * $mesesAguinaldo, 2);
        $aguiDias  = round(($spi / 360) * $diasAguinaldo, 2);
        $aguinaldoBase = round($aguiMeses + $aguiDias, 2);
        $multaAguinaldoMonto = $multaAguinaldo ? $aguinaldoBase : 0.00;
        $aguinaldo = round($aguinaldoBase + $multaAguinaldoMonto, 2);

        $detalleAguinaldo = [];
        if ($mesesAguinaldo > 0) {
            $detalleAguinaldo[] = "Aguinaldo ({$mesesAguinaldo} mes(es)) = " . number_format($aguiMeses, 2, ',', '.') . ".- (Fórmula: SPI / 12 x {$mesesAguinaldo} mes(es))";
        }
        if ($diasAguinaldo > 0) {
            $detalleAguinaldo[] = "Aguinaldo ({$diasAguinaldo} día(s)) = " . number_format($aguiDias, 2, ',', '.') . ".- (Fórmula: SPI / 360 x {$diasAguinaldo} día(s))";
        }
        $detalleAguinaldo[] = "Total aguinaldo = Bs. " . number_format($aguinaldoBase, 2, ',', '.') . ".-";
        if ($multaAguinaldo) {
            $detalleAguinaldo[] = "Multa por incumplimiento al pago de aguinaldo = Bs. " . number_format($multaAguinaldoMonto, 2, ',', '.') . ".-";
        }

        // 6. VACACIONES
        $vacacionesMonto = 0.00;
        $vacacionesDias  = 0.00;
        $detalleVacaciones = [];
        if ($anos >= 1 && !$tomoVacaciones) {
            $escala = 15;
            if ($anos >= 5)  $escala = 20;
            if ($anos >= 10) $escala = 30;
            
            if ($meses == 0 && $dias == 0) {
                $vacacionesDias  = $escala;
                $vacacionesMonto = round(($spi / 30) * $vacacionesDias, 2);
                $detalleVacaciones[] = "Vacación anual (12 meses = {$escala} días de vacación) -> Bs. " . number_format($vacacionesMonto, 2, ',', '.') . ".-";
            } else {
                $diasMeses = round(($escala / 12) * $meses, 2);
                $diasDias  = round(($escala / 360) * $dias, 2);
                $vacacionesDias = round($diasMeses + $diasDias, 2);
                $vacacionesMonto = round(($spi / 30) * $vacacionesDias, 2);
                $detalleVacaciones[] = "Por meses: (Fórmula: {$escala} días / 12 meses x {$meses} mes(es)) = {$diasMeses} días";
                $detalleVacaciones[] = "Por días: (Fórmula: {$escala} días / 360 días x {$dias} día(s)) = {$diasDias} días";
                $detalleVacaciones[] = "Total días de vacación = {$vacacionesDias} días";
                $detalleVacaciones[] = "Fórmula: (SPI Bs. " . number_format($spi, 2, ',', '.') . " / 30 días x {$vacacionesDias} días) = Bs. " . number_format($vacacionesMonto, 2, ',', '.') . ".-";
            }
        } else {
            $detalleVacaciones[] = $anos < 1 ? "No corresponde (Requisito haber trabajado 1 año)" : "No corresponde (Vacaciones ya tomadas)";
        }

        // 7. BONO DE ANTIGÜEDAD
        $bonoAntiguedad = 0.00;
        $porcentajeAntiguedad = 0;
        $detalleBono = [];
        if ($anos >= 2) {
            if ($anos >= 2)  $porcentajeAntiguedad = 5;
            if ($anos >= 5)  $porcentajeAntiguedad = 11;
            if ($anos >= 8)  $porcentajeAntiguedad = 18;
            if ($anos >= 11) $porcentajeAntiguedad = 26;
            if ($anos >= 15) $porcentajeAntiguedad = 34;
            if ($anos >= 20) $porcentajeAntiguedad = 42;
            if ($anos >= 25) $porcentajeAntiguedad = 50;

            $bonoMensual = round($smn * ($porcentajeAntiguedad / 100), 2);
            $bonoDiario  = round($bonoMensual / 30, 2);
            $mesesBono   = ($meses == 0 && $dias == 0) ? 12 : $meses;

            $montoBonoMeses = round($bonoMensual * $mesesBono, 2);
            $montoBonoDias  = round($bonoDiario * $dias, 2);
            $bonoAntiguedad = round($montoBonoMeses + $montoBonoDias, 2);

            $detalleBono[] = "({$porcentajeAntiguedad}% del SMN): 3300 x {$porcentajeAntiguedad} / 100 = " . number_format($bonoMensual, 2, ',', '.') . " por mes";
            if ($mesesBono > 0) {
                $detalleBono[] = number_format($bonoMensual, 2, ',', '.') . " x {$mesesBono} mes(es) = " . number_format($montoBonoMeses, 2, ',', '.');
            }
            if ($dias > 0) {
                $detalleBono[] = number_format($bonoDiario, 2, ',', '.') . " (sueldo día) x {$dias} día(s) = " . number_format($montoBonoDias, 2, ',', '.');
            }
            $detalleBono[] = "Total bono de antigüedad = Bs. " . number_format($bonoAntiguedad, 2, ',', '.') . ".-";
        } else {
            $detalleBono[] = "No corresponde (Requisito haber trabajado 2 años mínimamente)";
        }

        $prima = 0.00;

        $subtotal = round($reintegroFinal + $sueldosDevengadosMonto + $desahucio + $indemnizacion + $aguinaldo + $vacacionesMonto + $bonoAntiguedad + $prima, 2);
        
        $montoMulta30 = 0.00;
        if ($multa30) {
            $montoMulta30 = round($subtotal * 0.30, 2);
        }

        $totalGeneral = round($subtotal + $montoMulta30, 2);

        return [
            'anos_trabajados'  => $anos,
            'meses_trabajados' => $meses,
            'dias_trabajados'  => $dias,
            'indemnizacion'    => $indemnizacion,
            'desahucio'        => $desahucio,
            'vacaciones'       => $vacacionesMonto,
            'aguinaldo'        => $aguinaldo,
            'aguinaldo_base'   => $aguinaldoBase,
            'multa_aguinaldo_monto' => $multaAguinaldoMonto,
            'prima'            => $prima,
            'bono_antiguedad'  => $bonoAntiguedad,
            'dias_pendientes'  => $dias,
            
            'reintegro'                => $reintegroFinal,
            'reintegro_auto'           => $reintegroAuto,
            'sueldos_devengados_meses' => $sueldosDevengadosMeses,
            'sueldos_devengados_monto' => $sueldosDevengadosMonto,
            'tomo_vacaciones'          => $tomoVacaciones,
            'multa_aguinaldo'          => $multaAguinaldo,
            'multa_30'                 => $multa30,
            'monto_multa_30'           => $montoMulta30,

            'total_finiquito'  => $subtotal,
            'total_general'    => $totalGeneral,
            'literal_finiquito'=> CalculoFiniquito::numeroALetras($subtotal),
            'literal_general'  => CalculoFiniquito::numeroALetras($totalGeneral),
            
            'dias_vacacion'    => $vacacionesDias,
            'salario_diario'   => round($spi / 30, 2),
            'porcentaje_antiguedad' => $porcentajeAntiguedad,
            'smn_aplicado'     => $smn,
            'spi_aplicado'     => $spi,
            'salario_percibido'=> $salario,
            'detalles' => [
                'reintegro'          => $detalleReintegro,
                'sueldos_devengados' => $detalleDevengados,
                'desahucio'          => $detalleDesahucio,
                'indemnizacion'      => $detalleIndemnizacion,
                'aguinaldo'          => $detalleAguinaldo,
                'vacaciones'         => $detalleVacaciones,
                'bono_antiguedad'    => $detalleBono,
            ]
        ];
    }

    public function showCalculo(int $id)
    {
        if ($r = $this->checkAuth()) return $r;
        $calculo = CalculoFiniquito::with(['cliente','caso','usuario'])->findOrFail($id);
        $desglose = $this->calcularFiniquito(
            $calculo->fecha_ingreso->format('Y-m-d'),
            $calculo->fecha_retiro->format('Y-m-d'),
            (float)$calculo->salario_mensual,
            $calculo->tipo_retiro,
            (float)$calculo->reintegro,
            (int)$calculo->sueldos_devengados_meses,
            (bool)$calculo->tomo_vacaciones,
            (bool)$calculo->multa_aguinaldo,
            (bool)$calculo->multa_30
        );
        return view('calculadora.show', compact('calculo', 'desglose'));
    }
}
