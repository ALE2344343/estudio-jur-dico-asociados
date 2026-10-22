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
