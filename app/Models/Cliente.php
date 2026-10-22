<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'codigo', 'nombre', 'apellido', 'ci', 'ci_expedido',
        'fecha_nacimiento', 'genero', 'telefono', 'celular', 'email',
        'direccion', 'ciudad', 'departamento', 'ocupacion', 'empresa',
        'estado_civil', 'observaciones', 'estado', 'usuario_id'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function getNombreCompletoAttribute(): string
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    public function abogado()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function casos()
    {
        return $this->hasMany(Caso::class, 'cliente_id');
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'cliente_id');
    }

    public function calculos()
    {
        return $this->hasMany(CalculoFiniquito::class, 'cliente_id');
    }

    public static function generarCodigo(): string
    {
        $ultimo = self::orderBy('id', 'desc')->first();
        $numero = $ultimo ? (int) substr($ultimo->codigo, 4) + 1 : 1;
        return 'CLI-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }
}
