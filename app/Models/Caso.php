<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caso extends Model
{
    protected $table = 'casos';

    protected $fillable = [
        'numero_caso', 'titulo', 'descripcion', 'tipo', 'estado', 'prioridad',
        'fecha_inicio', 'fecha_vencimiento', 'fecha_cierre', 'juzgado', 'juez',
        'numero_expediente', 'contraparte', 'notas_privadas', 'honorarios',
        'estado_honorarios', 'cliente_id', 'abogado_id'
    ];

    protected $casts = [
        'fecha_inicio'      => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_cierre'      => 'date',
        'honorarios'        => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function abogado()
    {
        return $this->belongsTo(Usuario::class, 'abogado_id');
    }

    public function audiencias()
    {
        return $this->hasMany(Audiencia::class, 'caso_id')->orderBy('fecha_hora', 'asc');
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'caso_id');
    }

    public function calculos()
    {
        return $this->hasMany(CalculoFiniquito::class, 'caso_id');
    }

    public function getBadgeEstadoAttribute(): array
    {
        return match($this->estado) {
            'activo'     => ['badge-active', 'Activo'],
            'en_proceso' => ['badge-pending', 'En Proceso'],
            'suspendido' => ['badge-pending', 'Suspendido'],
            'cerrado'    => ['badge-closed', 'Cerrado'],
            'ganado'     => ['badge-active', 'Ganado'],
            'perdido'    => ['badge-urgent', 'Perdido'],
            default      => ['badge-closed', ucfirst($this->estado)],
        };
    }

    public function getBadgePrioridadAttribute(): array
    {
        return match($this->prioridad) {
            'baja'    => ['badge-closed', 'Baja'],
            'media'   => ['badge-pending', 'Media'],
            'alta'    => ['badge-active', 'Alta'],
            'urgente' => ['badge-urgent', '⚡ Urgente'],
            default   => ['badge-closed', ucfirst($this->prioridad)],
        };
    }

    public static function generarNumero(): string
    {
        $anio = date('Y');
        $ultimo = self::whereYear('created_at', $anio)->orderBy('id', 'desc')->first();
        $numero = $ultimo ? (int) substr($ultimo->numero_caso, -4) + 1 : 1;
        return 'NUREJ-' . $anio . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }
}

