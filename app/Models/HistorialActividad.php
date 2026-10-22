<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class HistorialActividad extends Model
{
    protected $table = 'historial_actividades';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'usuario_nombre',
        'usuario_rol',
        'modulo',
        'accion',
        'descripcion',
        'registro_id',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Registrar una actividad o modificación en el historial de forma centralizada
     */
    public static function registrar(string $modulo, string $accion, string $descripcion, ?int $registroId = null): void
    {
        try {
            $nombre = Session::get('usuario_nombre', 'Sistema');
            $rol    = Session::get('usuario_rol', 'sistema');
            $id     = Session::get('usuario_id', null);

            self::create([
                'usuario_id'     => $id,
                'usuario_nombre' => $nombre,
                'usuario_rol'    => ucfirst($rol),
                'modulo'         => $modulo,
                'accion'         => $accion,
                'descripcion'    => $descripcion,
                'registro_id'    => $registroId,
                'ip_address'     => request()->ip(),
                'created_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            // Evitar que un fallo al registrar log interrumpa la operación principal
            \Log::error('Error registrando auditoría: ' . $e->getMessage());
        }
    }

    /**
     * Registrar una modificación calculando la diferencia exacta entre los valores previos y nuevos
     */
    public static function registrarCambios(string $modulo, string $entidadTitulo, Model $model, array $nuevosDatos, array $etiquetas = []): void
    {
        try {
            $cambios = [];
            foreach ($nuevosDatos as $campo => $nuevoValor) {
                if (in_array($campo, ['_token', '_method', 'updated_at', 'created_at', 'password_confirmation'])) {
                    continue;
                }

                $valorAnterior = $model->getOriginal($campo);

                // Tratamiento especial de contraseñas
                if ($campo === 'password') {
                    if (!empty($nuevoValor)) {
                        $cambios[] = "Contraseña: Se actualizó la clave de acceso";
                    }
                    continue;
                }

                // Tratamiento de booleanos
                if (is_bool($valorAnterior) || in_array($campo, ['activo', 'es_drive'])) {
                    $antBool = $valorAnterior ? 'Sí / Activo' : 'No / Inactivo';
                    $nueBool = $nuevoValor ? 'Sí / Activo' : 'No / Inactivo';
                    if ($antBool !== $nueBool) {
                        $label = $etiquetas[$campo] ?? ucfirst($campo);
                        $cambios[] = "{$label}: '{$antBool}' ➔ '{$nueBool}'";
                    }
                    continue;
                }

                // Normalizar valores para comparación
                $vAnt = is_null($valorAnterior) ? '' : trim((string)$valorAnterior);
                $vNue = is_null($nuevoValor) ? '' : trim((string)$nuevoValor);

                // Normalización inteligente de fechas (elimina horas 00:00:00 innecesarias)
                if (preg_match('/^\d{4}-\d{2}-\d{2}/', $vAnt) || preg_match('/^\d{4}-\d{2}-\d{2}/', $vNue)) {
                    $fechaAnt = preg_match('/^\d{4}-\d{2}-\d{2}/', $vAnt) ? substr($vAnt, 0, 10) : '';
                    $fechaNue = preg_match('/^\d{4}-\d{2}-\d{2}/', $vNue) ? substr($vNue, 0, 10) : '';

                    if ($fechaAnt === $fechaNue) {
                        continue; // Misma fecha, no hubo cambio
                    }

                    if ($fechaAnt !== '') {
                        try { $vAnt = \Carbon\Carbon::parse($fechaAnt)->format('d/m/Y'); } catch (\Throwable $e) {}
                    }
                    if ($fechaNue !== '') {
                        try { $vNue = \Carbon\Carbon::parse($fechaNue)->format('d/m/Y'); } catch (\Throwable $e) {}
                    }
                }

                if ($vAnt !== $vNue) {
                    $label = $etiquetas[$campo] ?? ucfirst(str_replace('_', ' ', $campo));
                    $antTexto = $vAnt === '' ? '(vacío)' : $vAnt;
                    $nueTexto = $vNue === '' ? '(vacío)' : $vNue;
                    $cambios[] = "{$label}: '{$antTexto}' ➔ '{$nueTexto}'";
                }
            }

            $accion = 'Modificación';
            if (count($cambios) === 1) {
                $primerCampo = trim(explode(':', $cambios[0])[0]);
                $accion = "Modificación de {$primerCampo}";
            } elseif (count($cambios) > 1) {
                $accion = 'Modificación de Datos';
            }

            if (empty($cambios)) {
                $descripcion = "Guardó datos de {$entidadTitulo} sin alteraciones en los valores";
            } else {
                $descripcion = "Modificó en {$entidadTitulo} :: " . implode(' | ', $cambios);
            }

            self::registrar($modulo, $accion, $descripcion, $model->id);
        } catch (\Throwable $e) {
            \Log::error('Error registrando cambios detallados: ' . $e->getMessage());
        }
    }
}
