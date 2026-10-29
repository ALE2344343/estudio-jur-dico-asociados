<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalculoFiniquito extends Model
{
    protected $table = 'calculos_finiquito';

    protected $fillable = [
        'cliente_id', 'caso_id', 'usuario_id', 'trabajador_nombre', 'empresa',
        'fecha_ingreso', 'fecha_retiro', 'salario_mensual', 'tipo_retiro',
        'anos_trabajados', 'meses_trabajados', 'indemnizacion', 'desahucio',
        'vacaciones', 'aguinaldo', 'prima', 'bono_antiguedad',
        'dias_pendientes', 'total_finiquito', 'observaciones',
        'reintegro', 'sueldos_devengados_meses', 'sueldos_devengados_monto',
        'tomo_vacaciones', 'multa_aguinaldo', 'multa_30', 'monto_multa_30',
        'total_general'
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'fecha_retiro'  => 'date',
        'tomo_vacaciones' => 'boolean',
        'multa_aguinaldo' => 'boolean',
        'multa_30' => 'boolean',
    ];

    public static function numeroALetras(float $monto): string
    {
        $entero = (int) floor($monto);
        $centavos = (int) round(($monto - $entero) * 100);
        if ($centavos === 100) {
            $entero++;
            $centavos = 0;
        }
        $letras = self::convertirEntero($entero);
        $centavosStr = str_pad((string)$centavos, 2, '0', STR_PAD_LEFT) . '/100 BOLIVIANOS.';
        return trim($letras) . ' ' . $centavosStr;
    }

    private static function convertirEntero(int $n): string
    {
        if ($n === 0) return 'CERO';
        if ($n === 100) return 'CIEN';

        $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $especiales = [
            10 => 'DIEZ', 11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE',
            15 => 'QUINCE', 16 => 'DIECISÉIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO', 19 => 'DIECINUEVE',
            20 => 'VEINTE', 21 => 'VEINTIÚN', 22 => 'VEINTIDÓS', 23 => 'VEINTITRÉS', 24 => 'VEINTICUATRO',
            25 => 'VEINTICINCO', 26 => 'VEINTISÉIS', 27 => 'VEINTISIETE', 28 => 'VEINTIOCHO', 29 => 'VEINTINUEVE'
        ];
        $decenas = ['', '', '', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        if ($n >= 1000000) {
            $millones = (int) floor($n / 1000000);
            $resto = $n % 1000000;
            $strMillones = ($millones === 1) ? 'UN MILLÓN' : self::convertirEntero($millones) . ' MILLONES';
            return $strMillones . ($resto > 0 ? ' ' . self::convertirEntero($resto) : '');
        }

        if ($n >= 1000) {
            $miles = (int) floor($n / 1000);
            $resto = $n % 1000;
            $strMiles = ($miles === 1) ? 'MIL' : self::convertirEntero($miles) . ' MIL';
            return $strMiles . ($resto > 0 ? ' ' . self::convertirEntero($resto) : '');
        }

        if ($n >= 100) {
            $c = (int) floor($n / 100);
            $resto = $n % 100;
            return $centenas[$c] . ($resto > 0 ? ' ' . self::convertirEntero($resto) : '');
        }

        if ($n >= 10 && $n <= 29) {
            return $especiales[$n];
        }

        if ($n >= 30) {
            $d = (int) floor($n / 10);
            $u = $n % 10;
            return $decenas[$d] . ($u > 0 ? ' Y ' . $unidades[$u] : '');
        }

        return $unidades[$n];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function caso()
    {
        return $this->belongsTo(Caso::class, 'caso_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
