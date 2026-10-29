<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Caso;
use App\Models\Audiencia;
use App\Models\Notificacion;
use App\Models\HistorialActividad;
use App\Services\NotificadorExternoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class GestionLegalController extends Controller
{
    private function checkAdmin()
    {
        if (!Session::has('usuario_id')) return redirect()->route('inicio');
        if (Session::get('usuario_rol') !== 'administrador') {
            abort(403, 'Acceso restringido a administradores.');
        }
        return null;
    }

    private function checkAdminOrAbogado()
    {
        if (!Session::has('usuario_id')) return redirect()->route('inicio');
        return null;
    }

    public function index()
    {
        if (!Session::has('usuario_id')) return redirect()->route('inicio');
        if (Session::get('usuario_rol') !== 'administrador') {
            return redirect()->route('dashboard')->with('error', 'El Centro de Control y Asignaciones es exclusivo para el Administrador.');
        }

        $rol = Session::get('usuario_rol');
        $userId = Session::get('usuario_id');
        $esAdmin = ($rol === 'administrador');

        // 1. Usuarios (Abogados y Administradores)
        $usuarios = Usuario::orderBy('rol', 'desc')->orderBy('nombre', 'asc')->get();
        $abogados = Usuario::where('rol', 'abogado')->where('activo', true)->orderBy('nombre')->get();

        // 2. Casos y Asignaciones
        $casosQuery = Caso::with(['cliente', 'abogado'])->orderBy('created_at', 'desc');
        if (!$esAdmin) {
            $casosQuery->where('abogado_id', $userId);
        }
        $casos = $casosQuery->get();

        // 3. Recordatorios: Audiencias y Plazos Procesales
        $audienciasQuery = Audiencia::with(['caso.abogado', 'caso.cliente'])
            ->where('fecha_hora', '>=', now()->startOfDay())
            ->orderBy('fecha_hora', 'asc');
        if (!$esAdmin) {
            $audienciasQuery->whereHas('caso', function($q) use ($userId) {
                $q->where('abogado_id', $userId);
            });
        }
        $audiencias = $audienciasQuery->get();

        $plazosQuery = Caso::with(['abogado', 'cliente'])
            ->whereNotNull('fecha_vencimiento')
            ->whereIn('estado', ['activo', 'en_proceso'])
            ->orderBy('fecha_vencimiento', 'asc');
        if (!$esAdmin) {
            $plazosQuery->where('abogado_id', $userId);
        }
        $plazos = $plazosQuery->get();

        // 4. Llegada de Notificaciones Judiciales
        $notificacionesQuery = Notificacion::with(['caso.abogado', 'usuario'])
            ->orderBy('estado', 'asc')
            ->orderBy('fecha_recepcion', 'desc');
        if (!$esAdmin) {
            $notificacionesQuery->where(function($q) use ($userId) {
                $q->where('usuario_id', $userId)
                  ->orWhereHas('caso', function($qc) use ($userId) {
                      $qc->where('abogado_id', $userId);
                  });
            });
        }
        $notificaciones = $notificacionesQuery->get();

        return view('gestion.index', compact('usuarios', 'abogados', 'casos', 'audiencias', 'plazos', 'notificaciones', 'esAdmin'));
    }

    // Cambiar rol de usuario (administrador / abogado)
    public function cambiarRol(Request $request, int $id)
    {
        if ($r = $this->checkAdmin()) return $r;

        $request->validate(['rol' => 'required|in:administrador,abogado']);
        $usuario = Usuario::findOrFail($id);

        if ($usuario->id == Session::get('usuario_id') && $request->rol !== 'administrador') {
            return back()->with('error', 'No puedes quitarte el rol de administrador a ti mismo.');
        }

        $usuario->update(['rol' => $request->rol]);
        HistorialActividad::registrar('Control y Asignaciones', 'Modificación', "Cambió el rol de {$usuario->nombre_completo} a " . ucfirst($request->rol), $usuario->id);
        return back()->with('success', "Rol actualizado a {$request->rol} para {$usuario->nombre_completo}.");
    }

    // Alternar estado activo/inactivo
    public function toggleActivo(int $id)
    {
        if ($r = $this->checkAdmin()) return $r;

        $usuario = Usuario::findOrFail($id);
        if ($usuario->id == Session::get('usuario_id')) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $usuario->update(['activo' => !$usuario->activo]);
        HistorialActividad::registrar('Control y Asignaciones', 'Cambio de Estado', "Cambió estado de {$usuario->nombre_completo} a " . ($usuario->activo ? 'Activo' : 'Inactivo'), $usuario->id);
        return back()->with('success', "Estado de {$usuario->nombre_completo} actualizado.");
    }

    // Asignar o reasignar caso a un abogado
    public function asignarCaso(Request $request, int $id)
    {
        if ($r = $this->checkAdmin()) return $r;

        $request->validate([
            'abogado_id' => 'nullable|exists:usuarios,id'
        ]);

        $caso = Caso::with('cliente')->findOrFail($id);
        $caso->update(['abogado_id' => $request->abogado_id]);
        $caso->load('abogado');

        $abogadoNombre = $caso->abogado ? $caso->abogado->nombre_completo : 'Sin asignar';
        HistorialActividad::registrar('Casos', 'Reasignación', "Asignó el caso {$caso->numero_caso} al abogado: {$abogadoNombre}", $caso->id);

        if ($caso->abogado) {
            $clienteNom = $caso->cliente ? $caso->cliente->nombre_completo : 'N/A';
            NotificadorExternoService::notificarUsuario(
                $caso->abogado,
                "Asignación de Expediente ({$caso->numero_caso})",
                "Se le ha asignado la responsabilidad del siguiente caso jurídico:\n"
                . "• Código: {$caso->numero_caso}\n"
                . "• Título: {$caso->titulo}\n"
                . "• Materia: {$caso->tipo}\n"
                . "• Cliente: {$clienteNom}",
                "Juzgado: " . ($caso->juzgado ?: 'Por definir')
            );
        }

        return back()->with('success', "Caso {$caso->numero_caso} asignado a: {$abogadoNombre}.");
    }

    // Helper para calcular fecha de vencimiento según la materia jurídica procesal
    private function calcularVencimientoSegunMateria(Carbon $fechaRecepcion, int $plazo, string $materia): Carbon
    {
        $fecha = $fechaRecepcion->copy();

        switch ($materia) {
            case 'civil':
                // Ley 439 Art. 90:
                // Si plazo <= 15: días hábiles (lunes a viernes, excluye fines de semana). Empieza al día siguiente.
                // Si plazo > 15: días corridos / calendario.
                if ($plazo <= 15) {
                    while ($plazo > 0) {
                        $fecha->addDay();
                        if ($fecha->isWeekday()) {
                            $plazo--;
                        }
                    }
                } else {
                    $fecha->addDays($plazo);
                }
                break;

            case 'penal':
                // Ley 1970 Art. 130:
                // Plazos corridos e ininterrumpidos. Empieza desde las 00:00 del día siguiente.
                // Si el último día cae en sábado o domingo, se traslada al primer día hábil siguiente.
                $fecha->addDays($plazo);
                while (!$fecha->isWeekday()) {
                    $fecha->addDay();
                }
                break;

            case 'laboral':
                // CPT Art. 43:
                // Días hábiles judiciales/administrativos (de lunes a viernes). Empieza al día hábil siguiente.
                while ($plazo > 0) {
                    $fecha->addDay();
                    if ($fecha->isWeekday()) {
                        $plazo--;
                    }
                }
                break;

            case 'accion_libertad':
                // Materia Constitucional (Acción de Libertad):
                // Plazo fatal, corrido e ininterrumpido (24/7 sin excepción de fines de semana)
                $fecha->addDays($plazo);
                break;

            default:
                // Por defecto: días hábiles (lunes a viernes)
                while ($plazo > 0) {
                    $fecha->addDay();
                    if ($fecha->isWeekday()) {
                        $plazo--;
                    }
                }
                break;
        }

        return $fecha;
    }

    // Registrar llegada de notificación judicial
    public function storeNotificacion(Request $request)
    {
        if ($r = $this->checkAdmin()) return $r;

        $request->validate([
            'titulo'               => 'required|max:200',
            'materia'              => 'nullable|string|in:civil,penal,laboral,accion_libertad',
            'fecha_recepcion'      => 'required|date',
            'caso_id'              => 'nullable|exists:casos,id',
            'usuario_id'           => 'nullable|exists:usuarios,id',
            'dias_habiles'         => 'nullable|integer|min:1|max:120',
            'fecha_vencimiento'    => 'nullable|date',
            'hora_vencimiento'     => 'nullable',
            'recordatorio_minutos' => 'nullable|integer',
            'juzgado'              => 'nullable|max:150',
            'descripcion'          => 'nullable',
        ]);

        $data = $request->all();
        $materia = $request->input('materia', 'civil') ?: 'civil';
        $data['tipo'] = $materia;

        // Calcular vencimiento según las reglas procesales de la materia
        if ($request->filled('dias_habiles') && (int)$request->dias_habiles > 0) {
            $fechaRec = Carbon::parse($request->fecha_recepcion);
            $data['fecha_vencimiento'] = $this->calcularVencimientoSegunMateria($fechaRec, (int)$request->dias_habiles, $materia)->format('Y-m-d');
        }

        $notif = Notificacion::create($data);
        HistorialActividad::registrar('Notificaciones Judiciales', 'Creación', "Registró notificación judicial: '{$notif->titulo}' (Juzgado: " . ($notif->juzgado ?? 'N/A') . ")", $notif->id);

        // Determinar abogado destinatario (el usuario seleccionado o el abogado del caso)
        $destinatario = null;
        if ($notif->usuario_id) {
            $destinatario = Usuario::find($notif->usuario_id);
        } elseif ($notif->caso_id) {
            $casoVinc = Caso::with('abogado')->find($notif->caso_id);
            $destinatario = $casoVinc?->abogado;
        }

        $detalleTelegram = "";
        if ($destinatario) {
            $casoTxt = $notif->caso ? $notif->caso->numero_caso : 'General';
            $vencTxt = null;
            if ($notif->fecha_vencimiento) {
                $horaTxt = $notif->hora_vencimiento ? " a las " . substr($notif->hora_vencimiento, 0, 5) : "";
                $vencTxt = "Plazo fatal / Vencimiento: " . Carbon::parse($notif->fecha_vencimiento)->format('d/m/Y') . $horaTxt;
            }
            $res = NotificadorExternoService::notificarUsuario(
                $destinatario,
                "Notificación Judicial Recibida ({$notif->tipo})",
                "Se ha recepcionado una nueva actuación judicial en el bufete:\n"
                . "• Actuación: {$notif->titulo}\n"
                . "• Tipo: {$notif->tipo}\n"
                . "• Caso: {$casoTxt}\n"
                . "• Juzgado: " . ($notif->juzgado ?: 'N/A') . "\n"
                . ($notif->descripcion ? "• Detalle: {$notif->descripcion}" : ""),
                $vencTxt
            );
            if (!empty($res['telegram']['ok'])) {
                $detalleTelegram = " y enviada por Telegram a {$destinatario->nombre_completo}";
            }
        }

        return back()->with('success', 'Notificación judicial registrada con éxito' . $detalleTelegram . '.');
    }

    // Actualizar notificación judicial existente
    public function updateNotificacion(Request $request, int $id)
    {
        if ($r = $this->checkAdmin()) return $r;

        $notif = Notificacion::findOrFail($id);

        $request->validate([
            'titulo'               => 'required|max:200',
            'materia'              => 'nullable|string|in:civil,penal,laboral,accion_libertad',
            'fecha_recepcion'      => 'required|date',
            'caso_id'              => 'nullable|exists:casos,id',
            'usuario_id'           => 'nullable|exists:usuarios,id',
            'dias_habiles'         => 'nullable|integer|min:1|max:120',
            'fecha_vencimiento'    => 'nullable|date',
            'hora_vencimiento'     => 'nullable',
            'recordatorio_minutos' => 'nullable|integer',
            'juzgado'              => 'nullable|max:150',
            'descripcion'          => 'nullable',
        ]);

        $data = $request->all();
        $materia = $request->input('materia', $notif->tipo ?: 'civil') ?: 'civil';
        $data['tipo'] = $materia;

        if ($request->filled('dias_habiles') && (int)$request->dias_habiles > 0) {
            $fechaRec = Carbon::parse($request->fecha_recepcion);
            $data['fecha_vencimiento'] = $this->calcularVencimientoSegunMateria($fechaRec, (int)$request->dias_habiles, $materia)->format('Y-m-d');
        }

        $notif->update($data);
        HistorialActividad::registrar('Notificaciones Judiciales', 'Modificación', "Actualizó notificación judicial: '{$notif->titulo}'", $notif->id);

        return back()->with('success', 'Notificación judicial actualizada correctamente.');
    }

    // Actualizar audiencia judicial desde el panel de gestión
    public function updateAudiencia(Request $request, int $id)
    {
        if ($r = $this->checkAdmin()) return $r;

        $aud = Audiencia::findOrFail($id);

        $request->validate([
            'titulo'               => 'required|max:200',
            'fecha_hora'           => 'required|date',
            'lugar'                => 'nullable|max:200',
            'recordatorio_minutos' => 'nullable|integer',
        ]);

        $aud->update([
            'titulo'               => $request->titulo,
            'fecha_hora'           => $request->fecha_hora,
            'lugar'                => $request->lugar,
            'recordatorio_minutos' => $request->recordatorio_minutos ?? 15,
        ]);

        HistorialActividad::registrar('Audiencias', 'Modificación', "Actualizó audiencia '{$aud->titulo}'", $aud->id);

        return back()->with('success', 'Audiencia actualizada correctamente.');
    }

    // Eliminar audiencia judicial desde el panel de gestión
    public function destroyAudiencia(int $id)
    {
        if ($r = $this->checkAdmin()) return $r;

        $aud = Audiencia::findOrFail($id);
        $tit = $aud->titulo;
        $aud->delete();

        HistorialActividad::registrar('Audiencias', 'Eliminación', "Eliminó audiencia: '{$tit}'", $id);

        return back()->with('success', 'Audiencia eliminada del registro.');
    }

    // Reenviar o notificar audiencia judicial a Telegram del abogado
    public function notificarAudienciaTelegram(int $id)
    {
        if ($r = $this->checkAdmin()) return $r;

        $aud = Audiencia::with(['caso.abogado', 'caso.cliente'])->findOrFail($id);
        $abogado = $aud->caso?->abogado;

        if (!$abogado) {
            return back()->with('error', 'El caso no tiene un abogado asignado para notificar.');
        }

        $fechaFmt = Carbon::parse($aud->fecha_hora)->format('d/m/Y H:i');
        $num = $aud->caso->numero_caso ?? 'Sin código';
        $titCaso = $aud->caso->titulo ?? '';
        $clienteNom = $aud->caso->cliente ? $aud->caso->cliente->nombre_completo : 'N/A';
        $lugarTxt = $aud->lugar ?: 'Juzgado correspondiente';

        $msg = "Recordatorio de Audiencia Judicial (Reenviado por Administración):\n"
             . "• Audiencia: {$aud->titulo}\n"
             . "• Expediente: {$num} ({$titCaso})\n"
             . "• Cliente: {$clienteNom}\n"
             . "• Fecha y Hora: {$fechaFmt}\n"
             . "• Lugar / Enlace: {$lugarTxt}";

        $res = NotificadorExternoService::notificarUsuario(
            $abogado,
            "Recordatorio de Audiencia ({$num})",
            $msg,
            "Aviso de Audiencia Judicial"
        );

        if (!empty($res['telegram']['ok'])) {
            return back()->with('success', "Recordatorio de audiencia enviado exitosamente por Telegram a {$abogado->nombre_completo}.");
        }

        $motivo = $res['telegram']['mensaje'] ?? 'No se pudo enviar';
        return back()->with('error', "No se pudo enviar la alerta por Telegram a {$abogado->nombre_completo} ({$motivo}). Verifique que el abogado haya iniciado el bot @Bethg_bot.");
    }

    // Cambiar estado de notificación (atendida / pendiente)
    public function toggleNotificacion(int $id)
    {
        if ($r = $this->checkAdmin()) return $r;

        $notif = Notificacion::findOrFail($id);
        $nuevoEstado = $notif->estado === 'pendiente' ? 'atendido' : 'pendiente';
        $notif->update(['estado' => $nuevoEstado]);
        HistorialActividad::registrar('Notificaciones Judiciales', 'Cambio de Estado', "Marcó notificación '{$notif->titulo}' como " . ucfirst($nuevoEstado), $notif->id);
        return back()->with('success', "Notificación marcada como {$nuevoEstado}.");
    }

    // Eliminar notificación
    public function destroyNotificacion(int $id)
    {
        if ($r = $this->checkAdmin()) return $r;

        $notif = Notificacion::findOrFail($id);
        $tit = $notif->titulo;
        $notif->delete();
        HistorialActividad::registrar('Notificaciones Judiciales', 'Eliminación', "Eliminó notificación judicial: '{$tit}'", $id);
        return back()->with('success', 'Notificación eliminada.');
    }
}
