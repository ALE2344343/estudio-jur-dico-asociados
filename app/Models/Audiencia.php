<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audiencia extends Model
{
    protected $table = 'audiencias';

    protected $fillable = ['caso_id', 'titulo', 'fecha_hora', 'lugar', 'estado', 'recordatorio_minutos'];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public function caso()
    {
        return $this->belongsTo(Caso::class, 'caso_id');
    }
}

