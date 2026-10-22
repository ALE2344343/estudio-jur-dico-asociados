<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ley extends Model
{
    protected $table = 'leyes';

    protected $fillable = [
        'numero', 'titulo', 'descripcion', 'articulos', 'archivo_pdf', 'fecha_promulgacion',
        'categoria', 'gaceta_oficial', 'link_oficial', 'vigente', 'notas'
    ];

    protected $casts = [
        'fecha_promulgacion' => 'date',
        'vigente'            => 'boolean',
    ];
}
