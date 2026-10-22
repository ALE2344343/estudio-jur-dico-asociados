<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Caso;
use App\Models\Usuario;
use App\Models\Documento;
use App\Models\CalculoFiniquito;
use App\Models\Audiencia;
use App\Models\CalendarioAnotacion;
use App\Models\HistorialActividad;
use App\Services\NotificadorExternoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function index()
    {
        if (!Session::has('usuario_id')) {
            return redirect()->route('inicio');
        }

        $usuarioId  = Session::get('usuario_id');
        $usuarioRol = Session::get('usuario_rol');

        if ($usuarioRol === 'administrador') {
            $totalClientes    = Cliente::count();
            $totalCasos       = Caso::count();
            $casosActivos     = Caso::where('estado', 'activo')->orWhere('estado', 'en_proceso')->count();
            $totalDocumentos  = Documento::count();
            $casosUrgentes    = Caso::where('prioridad', 'urgente')->count();
            $casosRecientes   = Caso::with(['cliente', 'abogado'])
                                    ->orderBy('created_at', 'desc')
                                    ->take(5)->get();
            $clientesRecientes = Cliente::orderBy('created_at', 'desc')->take(5)->get();
            $audiencias       = Audiencia::with(['caso.abogado'])->where('fecha_hora', '>=', now()->startOfDay())->orderBy('fecha_hora', 'asc')->take(5)->get();
            // Anotaciones de todos los abogados para supervisión del Administrador
            $anotacionesAbogados = CalendarioAnotacion::with('usuario')->orderBy('fecha', 'desc')->take(10)->get();
        } else {
            $totalClientes    = Cliente::where('usuario_id', $usuarioId)->count();
            $totalCasos       = Caso::where('abogado_id', $usuarioId)->count();
            $casosActivos     = Caso::where('abogado_id', $usuarioId)
                                    ->whereIn('estado', ['activo', 'en_proceso'])->count();
            $totalDocumentos  = Documento::where('subido_por', $usuarioId)->count();
            $casosUrgentes    = Caso::where('abogado_id', $usuarioId)->where('prioridad', 'urgente')->count();
            $casosRecientes   = Caso::with(['cliente', 'abogado'])
                                    ->where('abogado_id', $usuarioId)
                                    ->orderBy('created_at', 'desc')
                                    ->take(5)->get();
            $clientesRecientes = Cliente::where('usuario_id', $usuarioId)
                                        ->orderBy('created_at', 'desc')->take(5)->get();
            $audiencias       = Audiencia::with(['caso.abogado'])->whereHas('caso', function($q) use ($usuarioId) { $q->where('abogado_id', $usuarioId); })->where('fecha_hora', '>=', now()->startOfDay())->orderBy('fecha_hora', 'asc')->take(5)->get();

            // Eventos completos para el calendario del abogado
            $todasAudiencias = Audiencia::with('caso')
                ->whereHas('caso', function($q) use ($usuarioId) { $q->where('abogado_id', $usuarioId); })
                ->get();
            $plazosCasos = Caso::where('abogado_id', $usuarioId)
                ->whereNotNull('fecha_vencimiento')
                ->get();

            $eventosCalendario = [];
            foreach ($todasAudiencias as $aud) {
                $eventosCalendario[] = [
                    'id'       => 'aud_' . $aud->id,
                    'tipo'     => 'audiencia',
                    'titulo'   => ($aud->caso ? $aud->caso->numero_caso : 'Audiencia') . ' - ' . $aud->titulo,
                    'hora'     => \Carbon\Carbon::parse($aud->fecha_hora)->format('H:i'),
                    'fecha'    => \Carbon\Carbon::parse($aud->fecha_hora)->format('Y-m-d'),
                    'color'    => '#0F766E', // Verde esmeralda
                    'lugar'    => $aud->lugar ?? 'Sin juzgado especificado',
                    'caso'     => $aud->caso ? $aud->caso->numero_caso . ' (' . $aud->caso->titulo . ')' : '',
                    'link'     => $aud->caso_id ? route('casos.show', $aud->caso_id) : '#'
                ];
            }
            foreach ($plazosCasos as $c) {
                $eventosCalendario[] = [
                    'id'       => 'plazo_' . $c->id,
                    'tipo'     => 'plazo',
                    'titulo'   => 'Plazo: ' . $c->numero_caso,
                    'hora'     => '23:59',
                    'fecha'    => \Carbon\Carbon::parse($c->fecha_vencimiento)->format('Y-m-d'),
                    'color'    => '#B45309', // Ámbar
                    'lugar'    => 'Vencimiento Procesal',
                    'caso'     => $c->numero_caso . ' (' . $c->titulo . ')',
                    'link'     => route('casos.show', $c->id)
                ];
            }

            // Anotaciones PERSONALES del abogado (SOLO las suyas, nadie más las ve excepto el admin)
            $misAnotaciones = CalendarioAnotacion::where('usuario_id', $usuarioId)->get();
            foreach ($misAnotaciones as $nota) {
                $eventosCalendario[] = [
                    'id'                   => 'nota_' . $nota->id,
                    'nota_id'              => $nota->id,
                    'tipo'                 => 'anotacion',
                    'titulo'               => ($nota->hora ? $nota->hora . ' - ' : '') . $nota->titulo,
                    'titulo_puro'          => $nota->titulo,
                    'hora'                 => $nota->hora ?? '',
                    'fecha'                => $nota->fecha->format('Y-m-d'),
                    'color'                => $nota->color ?? '#2563EB',
                    'lugar'                => 'Anotación Personal',
                    'caso'                 => 'Recordatorio Personal',
                    'detalle'              => $nota->descripcion ?? '',
                    'recordatorio_minutos' => $nota->recordatorio_minutos ?? 5,
                    'link'                 => '#',
                    'es_propia'            => true,
                ];
            }
        }

        if ($usuarioRol === 'administrador') {
            return view('auth.dashboard.admin', compact(
                'totalClientes', 'totalCasos', 'casosActivos',
                'totalDocumentos', 'casosUrgentes', 'casosRecientes', 'clientesRecientes', 'audiencias',
                'anotacionesAbogados'
            ));
        } else {
            return view('auth.dashboard.abogado', compact(
                'totalClientes', 'totalCasos', 'casosActivos',
                'totalDocumentos', 'casosUrgentes', 'casosRecientes', 'clientesRecientes', 'audiencias',
                'eventosCalendario'
            ));
        }
    }

    // Guardar anotación personal en el calendario
    public function storeAnotacion(Request $request)
    {
        if (!Session::has('usuario_id')) return redirect()->route('inicio');

        $request->validate([
            'titulo'               => 'required|max:150',
            'fecha'                => 'required|date',
            'hora'                 => 'nullable',
            'descripcion'          => 'nullable',
            'color'                => 'nullable',
            'recordatorio_minutos' => 'nullable|integer',
        ]);

        $usuarioId = Session::get('usuario_id');
        $nota = CalendarioAnotacion::create([
            'usuario_id'           => $usuarioId,
            'titulo'               => $request->titulo,
            'fecha'                => $request->fecha,
            'hora'                 => $request->hora,
            'descripcion'          => $request->descripcion,
            'color'                => $request->color ?? '#2563EB',
            'recordatorio_minutos' => $request->recordatorio_minutos ?? 5,
        ]);

        HistorialActividad::registrar('Calendario', 'Creación', "Agregó anotación personal: '{$nota->titulo}' para el {$nota->fecha->format('d/m/Y')}", $nota->id);

        // Enviar notificación inmediata al Telegram del usuario
        $usuario = Usuario::find($usuarioId);
        $horaTexto = $nota->hora ? substr($nota->hora, 0, 5) : 'Todo el día';
        $minAnt = $nota->recordatorio_minutos ?? 5;
        $cuerpoAlerta = "Fecha: {$nota->fecha->format('d/m/Y')}\n"
                      . "Hora: {$horaTexto}\n"
                      . "Recordatorio: {$nota->titulo}\n"
                      . ($nota->descripcion ? "Detalle: {$nota->descripcion}\n" : "")
                      . "Anticipación programada: " . ($minAnt > 0 ? "{$minAnt} minutos antes" : "A la hora exacta");

        $resEnvio = NotificadorExternoService::notificarUsuario(
            $usuario,
            "Nueva Anotación en Calendario: {$nota->titulo}",
            $cuerpoAlerta,
            "Tu agenda personal ha sido actualizada."
        );

        if (!empty($resEnvio['telegram']['ok'])) {
            $msgExito = 'Anotación personal guardada y enviada inmediatamente a tu Telegram.';
        } else {
            $motivo = $resEnvio['telegram']['mensaje'] ?? 'Abre el botón Configurar Telegram para vincular tu Bot @Bethg_bot.';
            $msgExito = 'Anotación personal guardada en el calendario. (Telegram: ' . $motivo . ')';
        }

        return back()->with('success', $msgExito);
    }

    // Actualizar anotación personal existente
    public function updateAnotacion(Request $request, int $id)
    {
        if (!Session::has('usuario_id')) return redirect()->route('inicio');

        $nota = CalendarioAnotacion::findOrFail($id);
        $usuarioId = Session::get('usuario_id');
        $usuarioRol = Session::get('usuario_rol');

        if ($nota->usuario_id != $usuarioId && $usuarioRol !== 'administrador') {
            abort(403, 'No tienes permiso para editar esta anotación.');
        }

        $request->validate([
            'titulo'               => 'required|max:150',
            'fecha'                => 'required|date',
            'hora'                 => 'nullable',
            'descripcion'          => 'nullable',
            'color'                => 'nullable',
            'recordatorio_minutos' => 'nullable|integer',
        ]);

        $datosNuevos = [
            'titulo'               => $request->titulo,
            'fecha'                => $request->fecha,
            'hora'                 => $request->hora,
            'descripcion'          => $request->descripcion,
            'color'                => $request->color ?? '#2563EB',
            'recordatorio_minutos' => $request->recordatorio_minutos ?? 5,
        ];

        HistorialActividad::registrarCambios('Calendario', "anotación de agenda '{$nota->titulo}'", $nota, $datosNuevos, [
            'titulo'               => 'Título',
            'fecha'                => 'Fecha',
            'hora'                 => 'Hora',
            'descripcion'          => 'Descripción',
            'recordatorio_minutos' => 'Minutos de Anticipación',
        ]);

        $nota->update($datosNuevos);

        // Enviar aviso de actualización por Telegram
        $usuario = Usuario::find($nota->usuario_id);
        $horaTexto = $nota->hora ? substr($nota->hora, 0, 5) : 'Todo el día';
        $cuerpoAlerta = "Fecha: {$nota->fecha->format('d/m/Y')}\n"
                      . "Hora: {$horaTexto}\n"
                      . "Recordatorio: {$nota->titulo}\n"
                      . ($nota->descripcion ? "Detalle: {$nota->descripcion}" : "");

        NotificadorExternoService::notificarUsuario(
            $usuario,
            "Recordatorio Actualizado: {$nota->titulo}",
            $cuerpoAlerta,
            "Anotación modificada en el calendario."
        );

        return back()->with('success', 'Recordatorio actualizado correctamente y notificado a Telegram.');
    }

    // Eliminar anotación personal
    public function destroyAnotacion(int $id)
    {
        if (!Session::has('usuario_id')) return redirect()->route('inicio');

        $nota = CalendarioAnotacion::findOrFail($id);
        $usuarioId = Session::get('usuario_id');
        $usuarioRol = Session::get('usuario_rol');

        // Solo el dueño de la nota o un administrador puede eliminarla
        if ($nota->usuario_id != $usuarioId && $usuarioRol !== 'administrador') {
            abort(403, 'No tienes permiso para eliminar esta anotación.');
        }

        $tit = $nota->titulo;
        $nota->delete();

        HistorialActividad::registrar('Calendario', 'Eliminación', "Eliminó anotación personal: '{$tit}'", $id);

        return back()->with('success', 'Anotación eliminada del calendario.');
    }

    // Endpoint JSON para consultar alertas y audiencias de hoy para notificaciones de escritorio
    public function alertasPendientes(Request $request)
    {
        if (!Session::has('usuario_id')) {
            return response()->json(['success' => false, 'alertas' => []]);
        }

        $usuarioId  = Session::get('usuario_id');
        $usuarioRol = Session::get('usuario_rol');
        $ahora = now();
        $ahoraMinuto = $ahora->copy()->startOfMinute();
        $hoy = $ahora->toDateString();

        $alertas = [];

        // 1. AUDIENCIAS JUDICIALES DE HOY
        if ($usuarioRol === 'administrador') {
            $audiencias = Audiencia::with(['caso.cliente', 'caso.abogado'])
                ->whereDate('fecha_hora', $hoy)
                ->orderBy('fecha_hora', 'asc')
                ->get();
        } else {
            $audiencias = Audiencia::with(['caso.cliente'])
                ->whereHas('caso', function($q) use ($usuarioId) {
                    $q->where('abogado_id', $usuarioId);
                })
                ->whereDate('fecha_hora', $hoy)
                ->orderBy('fecha_hora', 'asc')
                ->get();
        }

        foreach ($audiencias as $aud) {
            $audCarbon = \Carbon\Carbon::parse($aud->fecha_hora)->startOfMinute();
            $horaFormat = $audCarbon->format('H:i');
            $diffMinutos = (int) round($ahoraMinuto->diffInMinutes($audCarbon, false));

            $alertas[] = [
                'id'                   => 'aud_' . $aud->id,
                'tipo'                 => 'Audiencia Judicial',
                'badge'                => 'Audiencia',
                'titulo'               => $aud->titulo,
                'hora'                 => $horaFormat,
                'lugar'                => $aud->lugar ?? 'Juzgado asignado',
                'caso'                 => $aud->caso ? $aud->caso->numero_caso . ' - ' . $aud->caso->titulo : 'Sin caso',
                'detalle'              => ($aud->lugar ? 'Juzgado: ' . $aud->lugar : '') . ($aud->caso ? ' | Caso: ' . $aud->caso->numero_caso : ''),
                'link'                 => $aud->caso_id ? route('casos.show', $aud->caso_id) : route('dashboard'),
                'minutos'              => $diffMinutos,
                'recordatorio_minutos' => $aud->recordatorio_minutos ?? 15,
                'color'                => '#0F766E',
            ];
        }

        // 2. CITAS Y ANOTACIONES DEL CALENDARIO DE HOY
        $anotaciones = CalendarioAnotacion::where('usuario_id', $usuarioId)
            ->whereDate('fecha', $hoy)
            ->get();

        foreach ($anotaciones as $nota) {
            $horaStr = $nota->hora ? substr($nota->hora, 0, 5) : '09:00';
            $diffMinutos = null;
            if ($nota->hora) {
                try {
                    $horaCarbon = \Carbon\Carbon::createFromFormat('!Y-m-d H:i', $hoy . ' ' . $horaStr)->startOfMinute();
                    $diffMinutos = (int) round($ahoraMinuto->diffInMinutes($horaCarbon, false));
                } catch (\Exception $e) {
                    $diffMinutos = 0;
                }
            }

            $alertas[] = [
                'id'                   => 'nota_' . $nota->id,
                'tipo'                 => 'Cita de Agenda',
                'badge'                => 'Cita / Recordatorio',
                'titulo'               => $nota->titulo,
                'hora'                 => $nota->hora ? $horaStr : 'Todo el día',
                'lugar'                => 'Agenda Personal',
                'caso'                 => 'Recordatorio Personal',
                'detalle'              => $nota->descripcion ?: 'Programado en el calendario personal',
                'link'                 => route('dashboard'),
                'minutos'              => $diffMinutos,
                'recordatorio_minutos' => $nota->recordatorio_minutos ?? 5,
                'color'                => $nota->color ?? '#2563EB',
            ];
        }

        // 3. PLAZOS PROCESALES URGENTES (Hoy o Mañana)
        $plazos = Caso::where(function($q) use ($usuarioId, $usuarioRol) {
                if ($usuarioRol !== 'administrador') {
                    $q->where('abogado_id', $usuarioId);
                }
            })
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<=', now()->addDay()->toDateString())
            ->whereDate('fecha_vencimiento', '>=', $hoy)
            ->whereIn('estado', ['activo', 'en_proceso'])
            ->get();

        foreach ($plazos as $c) {
            $esHoy = \Carbon\Carbon::parse($c->fecha_vencimiento)->isToday();
            $alertas[] = [
                'id'             => 'plazo_' . $c->id,
                'tipo'           => 'Plazo Procesal',
                'badge'          => 'Vencimiento',
                'titulo'         => 'Plazo: ' . $c->numero_caso,
                'hora'           => $esHoy ? 'Hoy (Vencimiento)' : 'Mañana',
                'lugar'          => 'Juzgado',
                'caso'           => $c->numero_caso . ' (' . $c->titulo . ')',
                'detalle'        => 'Plazo procesal vence ' . ($esHoy ? 'HOY' : 'MAÑANA'),
                'link'           => route('casos.show', $c->id),
                'minutos'        => $esHoy ? 0 : 1440,
                'color'          => '#B45309',
            ];
        }

        return response()->json([
            'success'       => true,
            'hora_servidor' => $ahora->format('H:i'),
            'fecha_servidor'=> $ahora->format('d/m/Y'),
            'total'         => count($alertas),
            'alertas'       => $alertas,
        ]);
    }

    // Obtener configuración de Telegram para el modal del usuario actual
    public function obtenerConfigExterna()
    {
        if (!Session::has('usuario_id')) {
            return response()->json(['ok' => false]);
        }
        $usuario = Usuario::find(Session::get('usuario_id'));
        $config  = NotificadorExternoService::obtenerConfiguracion();

        return response()->json([
            'ok'                    => true,
            'es_admin'              => Session::get('usuario_rol') === 'administrador',
            'mi_telegram_id'        => $usuario?->telegram_chat_id ?? '',
            'telegram_bot_token'    => $config['telegram_bot_token'] ?? '',
            'telegram_bot_username' => $config['telegram_bot_username'] ?? 'Bethg_bot',
        ]);
    }

    // Guardar configuración de Telegram (Chat ID del usuario actual + Token del Bot)
    public function guardarConfigExterna(Request $request)
    {
        if (!Session::has('usuario_id')) {
            return response()->json(['ok' => false, 'mensaje' => 'Sesión no válida.']);
        }

        $usuario = Usuario::findOrFail(Session::get('usuario_id'));

        if ($request->has('telegram_bot_token')) {
            $tokenLimpio = trim((string) $request->telegram_bot_token);
            if (!empty($tokenLimpio)) {
                NotificadorExternoService::guardarConfiguracion(['telegram_bot_token' => $tokenLimpio]);
            }
        }

        if ($request->has('mi_telegram_id')) {
            NotificadorExternoService::guardarTelegramIdUsuario($usuario->id, $request->mi_telegram_id);
        }

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Configuración de Telegram guardada correctamente.',
        ]);
    }

    // Detectar automáticamente el Chat ID de quien le escribió al Bot de Telegram
    public function detectarTelegramChats()
    {
        if (!Session::has('usuario_id')) {
            return response()->json(['ok' => false, 'mensaje' => 'Sesión expirada.']);
        }
        $res = NotificadorExternoService::detectarChatsRecientesBot();
        // Si detectó al menos un chat, guardarlo automáticamente para el usuario actual
        if (!empty($res['ok']) && !empty($res['chats'])) {
            $ultimo = end($res['chats']);
            NotificadorExternoService::guardarTelegramIdUsuario((int) Session::get('usuario_id'), (string) $ultimo['chat_id']);
        }
        return response()->json($res);
    }

    // enviar mensaje de prueba inmediato a Telegram
    public function probarNotificacionExterna(Request $request)
    {
        if (!Session::has('usuario_id')) {
            return response()->json(['ok' => false, 'mensaje' => 'Sesión expirada.']);
        }

        $usuario = Usuario::findOrFail(Session::get('usuario_id'));

        $titulo  = "Prueba de Alerta en Tiempo Real";
        $mensaje = "¡Hola {$usuario->nombre_completo}! Este es un aviso oficial de prueba enviado desde el Sistema del Bufete de Abogados a través de tu Bot de Telegram.\n"
                 . "• Recibirás aquí tus recordatorios de audiencias, anotaciones de calendario, casos asignados y notificaciones judiciales.";
        $extra   = "Canal de Telegram verificado correctamente.";

        $resTelegram = NotificadorExternoService::enviarTelegram(
            $usuario->telegram_chat_id,
            $titulo,
            $mensaje,
            $extra,
            $usuario->id
        );

        return response()->json([
            'ok'             => true,
            'mi_telegram_id' => NotificadorExternoService::obtenerTelegramIdUsuario($usuario->id),
            'resultados'     => [
                'telegram' => $resTelegram,
            ],
        ]);
    }

    // alerta a Telegram 
    public function despacharAlertaExterna(Request $request)
    {
        if (!Session::has('usuario_id')) {
            return response()->json(['ok' => false]);
        }

        $usuario = Usuario::find(Session::get('usuario_id'));
        if (!$usuario) {
            return response()->json(['ok' => false]);
        }

        $titulo  = (string) $request->input('titulo', 'Recordatorio de Agenda');
        $hora    = (string) $request->input('hora', '');
        $detalle = (string) $request->input('detalle', '');
        $caso    = (string) $request->input('caso', '');

        $cuerpo = "Hora programada: {$hora}\n"
                . ($caso ? "Expediente / Referencia: {$caso}\n" : "")
                . ($detalle ? "Detalle: {$detalle}" : "");

        $res = NotificadorExternoService::notificarUsuario(
            $usuario,
            $titulo,
            $cuerpo,
            "Aviso automático del sistema de alertas."
        );

        return response()->json(['ok' => true, 'resultado' => $res]);
    }
}

