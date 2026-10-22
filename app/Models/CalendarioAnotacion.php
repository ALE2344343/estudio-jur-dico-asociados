<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $usuario_id
 * @property string $titulo
 * @property \Illuminate\Support\Carbon $fecha
 * @property string|null $hora
 * @property string|null $descripcion
 * @property string|null $color
 * @property int|null $recordatorio_minutos
 */
class CalendarioAnotacion extends Model
{
    protected $table = 'calendario_anotaciones';

    protected $fillable = [
        'usuario_id',
        'titulo',
        'fecha',
        'hora',
        'descripcion',
        'color',
        'recordatorio_minutos',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
