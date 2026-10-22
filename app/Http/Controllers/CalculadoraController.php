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

        $smn = 3300; 
        $spi = max($salario, $smn);

        $indemnizacion = ($spi * $anos) + (($spi / 12) * $meses) + (($spi / 360) * $dias);
        $desahucio = ($tipoRetiro === 'despido_injustificado') ? ($spi * 3) : 0;

        $vacacionesMonto = 0;
        $vacacionesDias  = 0;
        if ($anos >= 1 && !$tomoVacaciones) {
            $escala = 15;
            if ($anos >= 5)  $escala = 20;
            if ($anos >= 10) $escala = 30;
            
            $vacacionesDias = ($escala / 12 * $meses) + ($escala / 360 * $dias);
            $vacacionesMonto = ($spi / 30) * $vacacionesDias;
        }

        $aguinaldo = (($spi / 12) * $meses) + (($spi / 360) * $dias);
        if ($multaAguinaldo) {
            $aguinaldo = $aguinaldo * 2; // Multa del 100%
        }

        $prima = (($spi / 12) * $meses) + (($spi / 360) * $dias);

        $bonoAntiguedad = 0;
        $porcentajeAntiguedad = 0;
        if ($anos >= 2) {
            if ($anos >= 2)  $porcentajeAntiguedad = 5;
            if ($anos >= 4)  $porcentajeAntiguedad = 11;
            if ($anos >= 6)  $porcentajeAntiguedad = 18;
            if ($anos >= 8)  $porcentajeAntiguedad = 26;
            if ($anos >= 10) $porcentajeAntiguedad = 34;
            if ($anos >= 12) $porcentajeAntiguedad = 42;
            if ($anos >= 15) $porcentajeAntiguedad = 50;

            $bonoMensual = $smn * ($porcentajeAntiguedad / 100);
            $bonoAntiguedad = ($bonoMensual * $meses) + (($bonoMensual / 30) * $dias);
        }

        $sueldosDevengadosMonto = $spi * $sueldosDevengadosMeses;

        $subtotal = $indemnizacion + $desahucio + $vacacionesMonto + $aguinaldo + $prima + $bonoAntiguedad + $reintegro + $sueldosDevengadosMonto;
        
        $montoMulta30 = 0;
        if ($multa30) {
            $montoMulta30 = $subtotal * 0.30;
        }

        $totalGeneral = $subtotal + $montoMulta30;

        return [
            'anos_trabajados'  => $anos,
            'meses_trabajados' => $meses,
            'dias_trabajados'  => $dias,
            'indemnizacion'    => round($indemnizacion, 2),
            'desahucio'        => round($desahucio, 2),
            'vacaciones'       => round($vacacionesMonto, 2),
            'aguinaldo'        => round($aguinaldo, 2),
            'prima'            => round($prima, 2),
            'bono_antiguedad'  => round($bonoAntiguedad, 2),
            'dias_pendientes'  => 0,
            
            'reintegro'                => round($reintegro, 2),
            'sueldos_devengados_meses' => $sueldosDevengadosMeses,
            'sueldos_devengados_monto' => round($sueldosDevengadosMonto, 2),
            'tomo_vacaciones'          => $tomoVacaciones,
            'multa_aguinaldo'          => $multaAguinaldo,
            'multa_30'                 => $multa30,
            'monto_multa_30'           => round($montoMulta30, 2),

            'total_finiquito'  => round($subtotal, 2),
            'total_general'    => round($totalGeneral, 2),
            
            'dias_vacacion'    => round($vacacionesDias, 2),
            'salario_diario'   => round($spi / 30, 2),
            'porcentaje_antiguedad' => $porcentajeAntiguedad,
            'smn_aplicado'     => $smn,
            'spi_aplicado'     => $spi
        ];
    }

    public function showCalculo(int $id)
    {
        if ($r = $this->checkAuth()) return $r;
        $calculo = CalculoFiniquito::with(['cliente','caso','usuario'])->findOrFail($id);
        return view('calculadora.show', compact('calculo'));
    }
}
