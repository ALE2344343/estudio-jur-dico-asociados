<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificadorExternoService
{
    public const DEFAULT_BOT_TOKEN = '8899958203:AAEVzM46rWrE1PgkAFCEbYd6BInHYSUekdw';
    public const DEFAULT_BOT_USERNAME = 'Bethg_bot';

    /**
     * Función mantenida por compatibilidad (no ejecuta ALTER TABLE en MySQL).
     */
    public static function asegurarColumnaTelegram(): void
    {
        // Los IDs de Telegram se guardan en storage/app/notificaciones_config.json
    }

    /**
     * Obtiene el Telegram Chat ID de un usuario (o el Chat ID general detectado en el Bot)
     */
    public static function obtenerTelegramIdUsuario(?int $usuarioId): string
    {
        $config = self::obtenerConfiguracion();
        if ($usuarioId && !empty($config['usuarios_telegram'][$usuarioId])) {
            return trim((string) $config['usuarios_telegram'][$usuarioId]);
        }
        return trim((string) ($config['default_chat_id'] ?? ''));
    }

    /**
     * Guarda el Telegram Chat ID de un usuario en el archivo de configuración
     */
    public static function guardarTelegramIdUsuario(int $usuarioId, ?string $chatId): void
    {
        $chatId = trim((string) $chatId);
        $config = self::obtenerConfiguracion();
        $mapa = $config['usuarios_telegram'] ?? [];
        $mapa[$usuarioId] = $chatId;
        $config['usuarios_telegram'] = $mapa;
        if (!empty($chatId)) {
            $config['default_chat_id'] = $chatId;
        }
        self::guardarConfiguracion($config);
    }

    /**
     * Obtiene la configuración activa de Telegram (desde storage/app/notificaciones_config.json)
     */
    public static function obtenerConfiguracion(): array
    {
        $path = storage_path('app/notificaciones_config.json');
        $saved = [];
        if (file_exists($path)) {
            $saved = json_decode(file_get_contents($path), true) ?: [];
        }

        $token = trim((string) ($saved['telegram_bot_token'] ?? ''));
        if (empty($token)) {
            $token = env('TELEGRAM_BOT_TOKEN', self::DEFAULT_BOT_TOKEN);
        }

        return [
            'telegram_bot_token'    => $token,
            'telegram_bot_username' => $saved['telegram_bot_username'] ?? self::DEFAULT_BOT_USERNAME,
            'telegram_activo'       => $saved['telegram_activo'] ?? true,
            'usuarios_telegram'     => $saved['usuarios_telegram'] ?? [],
            'default_chat_id'       => $saved['default_chat_id'] ?? '',
        ];
    }

    /**
     * Guarda la configuración de Telegram
     */
    public static function guardarConfiguracion(array $datos): void
    {
        $actual = self::obtenerConfiguracion();
        $nuevo = array_merge($actual, $datos);
        file_put_contents(
            storage_path('app/notificaciones_config.json'),
            json_encode($nuevo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Petición HTTP rápida a la API de Telegram forzando IPv4 (evita bloqueos DNS IPv6 en Windows/XAMPP)
     */
    private static function peticionTelegramApi(string $token, string $metodo, array $params = []): array
    {
        $token = trim($token);
        $url = "https://api.telegram.org/bot{$token}/{$metodo}";

        try {
            $options = [
                'verify'  => false,
                'timeout' => 6,
            ];
            if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
                $options['curl'] = [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4];
            }

            $response = empty($params)
                ? Http::withOptions($options)->get($url)
                : Http::withOptions($options)->post($url, $params);

            $json = $response->json();
            if (is_array($json)) {
                return $json;
            }
            return [
                'ok'          => false,
                'error_code'  => $response->status(),
                'description' => 'Respuesta inválida de Telegram (HTTP ' . $response->status() . ')',
            ];
        } catch (\Throwable $e) {
            \Log::error("Error API Telegram ({$metodo}): " . $e->getMessage());
            return [
                'ok'          => false,
                'error_code'  => 500,
                'description' => 'No se pudo conectar con api.telegram.org: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Envía una notificación por Telegram a un Chat ID específico (o lo auto-detecta si está vacío)
     */
    public static function enviarTelegram(?string $chatId, string $titulo, string $mensaje, ?string $detalleExtra = null, ?int $usuarioId = null): array
    {
        $config = self::obtenerConfiguracion();
        $token = trim($config['telegram_bot_token'] ?? '');

        if (empty($token)) {
            return ['ok' => false, 'mensaje' => 'Falta configurar el Token del Bot de Telegram (@BotFather).'];
        }

        $chatId = trim((string) $chatId);

        // Si el usuario aún no tiene Chat ID guardado, intentar detectarlo automáticamente del Bot (@Bethg_bot)
        if (empty($chatId)) {
            $deteccion = self::detectarChatsRecientesBot();
            if ($deteccion['ok'] && !empty($deteccion['chats'])) {
                $ultimoChat = end($deteccion['chats']);
                $chatId = (string) $ultimoChat['chat_id'];
                if ($usuarioId) {
                    self::guardarTelegramIdUsuario($usuarioId, $chatId);
                } else {
                    self::guardarConfiguracion(['default_chat_id' => $chatId]);
                }
            } else {
                $msgError = $deteccion['mensaje'] ?? 'Aún no has iniciado conversación con tu Bot en Telegram.';
                return [
                    'ok'      => false,
                    'mensaje' => $msgError,
                ];
            }
        }

        $tituloSafe  = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
        $mensajeSafe = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
        $extraSafe   = $detalleExtra ? htmlspecialchars($detalleExtra, ENT_QUOTES, 'UTF-8') : '';

        $texto = "<b>ESTUDIO JURÍDICO &amp; ASOCIADOS</b>\n"
               . "━━━━━━━━━━━━━━━━━━━━\n"
               . "<b>{$tituloSafe}</b>\n\n"
               . "{$mensajeSafe}\n";

        if ($extraSafe) {
            $texto .= "\n📌 <i>{$extraSafe}</i>\n";
        }

        $texto .= "━━━━━━━━━━━━━━━━━━━━\n"
                 . now()->format('d/m/Y H:i');

        $res = self::peticionTelegramApi($token, 'sendMessage', [
            'chat_id'    => $chatId,
            'text'       => $texto,
            'parse_mode' => 'HTML',
        ]);

        if (!empty($res['ok'])) {
            return ['ok' => true, 'mensaje' => "¡Mensaje enviado exitosamente a tu Telegram (Chat ID: {$chatId})!"];
        }

        $code = $res['error_code'] ?? 0;
        $desc = $res['description'] ?? 'Error desconocido de Telegram.';

        if ($code === 401) {
            return [
                'ok'      => false,
                'mensaje' => 'Telegram rechazó el Token (Error 401 Unauthorized). Abre @BotFather en Telegram, envía /token, elige @Bethg_bot y pega el Token actualizado.',
            ];
        }

        if ($code === 400 && stripos($desc, 'chat not found') !== false) {
            return [
                'ok'      => false,
                'mensaje' => 'Chat no encontrado en Telegram. Abre t.me/Bethg_bot en tu celular/PC, presiona "Iniciar" (Start) y vuelve a probar.',
            ];
        }

        return ['ok' => false, 'mensaje' => "Telegram respondió: {$desc}"];
    }

    /**
     * Envía la alerta por Telegram a un Usuario del sistema (con auto-detección de Chat ID)
     */
    public static function notificarUsuario(?Usuario $usuario, string $titulo, string $mensaje, ?string $detalleExtra = null): array
    {
        if (!$usuario) {
            return ['telegram' => ['ok' => false, 'mensaje' => 'Sin usuario']];
        }

        $chatId = self::obtenerTelegramIdUsuario($usuario->id);
        $resTelegram = self::enviarTelegram($chatId, $titulo, $mensaje, $detalleExtra, $usuario->id);

        return [
            'telegram' => $resTelegram,
        ];
    }

    /**
     * Detecta los últimos usuarios que escribieron al Bot de Telegram (@Bethg_bot)
     */
    public static function detectarChatsRecientesBot(): array
    {
        $config = self::obtenerConfiguracion();
        $token = trim($config['telegram_bot_token'] ?? '');
        if (empty($token)) {
            return ['ok' => false, 'mensaje' => 'Primero pega el Token de tu Bot de Telegram.', 'chats' => []];
        }

        // 1. Verificar que el Token del Bot sea válido con getMe
        $me = self::peticionTelegramApi($token, 'getMe');
        if (empty($me['ok'])) {
            $code = $me['error_code'] ?? 0;
            if ($code === 401) {
                return [
                    'ok'      => false,
                    'mensaje' => 'El Token actual (terminado en ...' . substr($token, -6) . ') da error 401 (No autorizado/Revocado en Telegram). En Telegram entra a @BotFather, escribe /token, selecciona @Bethg_bot y copia el Token vigente.',
                    'chats'   => [],
                ];
            }
            return [
                'ok'      => false,
                'mensaje' => 'No se pudo conectar con Telegram: ' . ($me['description'] ?? 'Verifica tu conexión a internet.'),
                'chats'   => [],
            ];
        }

        $botUsername = $me['result']['username'] ?? self::DEFAULT_BOT_USERNAME;
        if ($botUsername !== ($config['telegram_bot_username'] ?? '')) {
            self::guardarConfiguracion(['telegram_bot_username' => $botUsername]);
        }

        // 2. Consultar los mensajes recientes enviados al Bot con getUpdates
        $res = self::peticionTelegramApi($token, 'getUpdates');
        if (empty($res['ok'])) {
            return [
                'ok'      => false,
                'mensaje' => 'Error consultando mensajes del Bot: ' . ($res['description'] ?? ''),
                'chats'   => [],
            ];
        }

        $updates = $res['result'] ?? [];
        $chats = [];
        foreach ($updates as $up) {
            $msg = $up['message'] ?? $up['edited_message'] ?? $up['my_chat_member'] ?? null;
            if ($msg && isset($msg['chat']['id'])) {
                $cid = (string) $msg['chat']['id'];
                $nombre = trim(($msg['chat']['first_name'] ?? '') . ' ' . ($msg['chat']['last_name'] ?? ''));
                $username = isset($msg['chat']['username']) ? '@' . $msg['chat']['username'] : '';
                $texto = $msg['text'] ?? 'Inició el bot';
                $chats[$cid] = [
                    'chat_id'  => $cid,
                    'nombre'   => $nombre ?: 'Usuario Telegram',
                    'username' => $username,
                    'mensaje'  => $texto,
                ];
            }
        }

        if (empty($chats)) {
            return [
                'ok'      => false,
                'mensaje' => "El Bot @{$botUsername} está activo, pero aún no le has enviado ningún mensaje. Abre t.me/{$botUsername} en Telegram, presiona 'Iniciar' (Start) o envíale 'Hola', y vuelve a pulsar el botón.",
                'chats'   => [],
            ];
        }

        return [
            'ok'           => true,
            'bot_username' => $botUsername,
            'chats'        => array_values($chats),
        ];
    }
}
