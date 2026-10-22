<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Documento extends Model
{
    protected $table = 'documentos';

    protected $fillable = [
        'codigo', 'nombre', 'descripcion', 'tipo', 'archivo',
        'extension', 'tamano', 'drive_link', 'es_drive',
        'caso_id', 'cliente_id', 'subido_por', 'estado',
        'version', 'notas'
    ];

    protected $casts = [
        'es_drive' => 'boolean',
    ];

    // ── Relaciones ──
    public function caso()
    {
        return $this->belongsTo(Caso::class, 'caso_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * El usuario que subió el documento.
     * Se accede como $documento->autor
     */
    public function autor()
    {
        return $this->belongsTo(Usuario::class, 'subido_por');
    }

    // Alias para compatibilidad
    public function subidoPor()
    {
        return $this->belongsTo(Usuario::class, 'subido_por');
    }

    // ── Accessors ──
    public function getIconoAttribute(): string
    {
        $iconos = [
            'pdf'  => '📕',
            'doc'  => '📝',
            'docx' => '📝',
            'xls'  => '📊',
            'xlsx' => '📊',
            'ppt'  => '📋',
            'pptx' => '📋',
            'jpg'  => '🖼️',
            'jpeg' => '🖼️',
            'png'  => '🖼️',
            'gif'  => '🖼️',
            'zip'  => '📦',
            'rar'  => '📦',
            'mp4'  => '🎥',
            'txt'  => '📄',
        ];

        if ($this->es_drive) return '📁';

        $ext = strtolower($this->extension ?? '');
        return $iconos[$ext] ?? '📄';
    }

    public function getTamanoFormateadoAttribute(): string
    {
        if ($this->es_drive) return 'Google Drive';
        if (!$this->tamano) return '—';

        $bytes = (int) $this->tamano;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    public static function generarCodigo(): string
    {
        $ultimo = self::orderBy('id', 'desc')->first();
        $numero = $ultimo ? (int) substr($ultimo->codigo, 4) + 1 : 1;
        return 'DOC-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
}
