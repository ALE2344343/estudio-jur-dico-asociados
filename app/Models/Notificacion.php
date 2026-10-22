<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'caso_id',
        'usuario_id',
        'titulo',
        'tipo',
        'fecha_recepcion',
        'fecha_vencimiento',
        'descripcion',
        'juzgado',
        'estado',
    ];

    protected $casts = [
        'fecha_recepcion'   => 'date',
        'fecha_vencimiento' => 'date',
    ];

    public function caso()
    {
        return $this->belongsTo(Caso::class, 'caso_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
