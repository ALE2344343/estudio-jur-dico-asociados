<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Audiencia;
use App\Models\CalendarioAnotacion;
use App\Models\Notificacion;
use App\Models\Caso;
use App\Models\Usuario;
use App\Services\NotificadorExternoService;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DespacharAlertasProgramadas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alertas:despachar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revisa y despacha recordatorios de audiencias, anotaciones y notificaciones judiciales a Telegram sin necesidad de tener la web abierta.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ahora = now(config('app.timezone'));
        $ahoraMinuto = $ahora->copy()->startOfMinute();
        $hoy = $ahora->toDateString();

        $this->info("[{$ahora->format('Y-m-d H:i:s')}] Verificando alertas programadas para hoy ({$hoy})...");

        // 1. AUDIENCIAS JUDICIALES DE HOY
        $audiencias = Audiencia::with(['caso.abogado', 'caso.cliente'])
            ->whereDate('fecha_hora', $hoy)
            ->get();

        foreach ($audiencias as $aud) {
            $audCarbon = Carbon::parse($aud->fecha_hora, config('app.timezone'))->startOfMinute();
            $diffMinutos = (int) round($ahoraMinuto->diffInMinutes($audCarbon, false));
            $dest = $aud->caso?->abogado;

            $this->evaluarYDespachar(
                idUnico: 'aud_' . $aud->id,
                titulo: $aud->titulo,
                hora: $audCarbon->format('H:i'),
                detalle: ($aud->lugar ? "Juzgado: {$aud->lugar}\n" : '') . ($aud->caso ? "Caso: {$aud->caso->numero_caso} - {$aud->caso->titulo}" : ''),
                diffMinutos: $diffMinutos,
                recordatorioMinutos: $aud->recordatorio_minutos ?? 15,
                destinatario: $dest,
                hoy: $hoy
            );
        }

        // 2. ANOTACIONES Y CITAS DE CALENDARIO DE HOY
        $anotaciones = CalendarioAnotacion::with('usuario')
            ->whereDate('fecha', $hoy)
            ->get();

        foreach ($anotaciones as $nota) {
            if (!$nota->hora) continue;

            $horaStr = substr($nota->hora, 0, 5);
            try {
                $horaCarbon = Carbon::createFromFormat('!Y-m-d H:i', $hoy . ' ' . $horaStr, config('app.timezone'))->startOfMinute();
                $diffMinutos = (int) round($ahoraMinuto->diffInMinutes($horaCarbon, false));
            } catch (\Exception $e) {
                continue;
            }

            $this->evaluarYDespachar(
                idUnico: 'nota_' . $nota->id,
                titulo: $nota->titulo,
                hora: $horaStr,
                detalle: $nota->descripcion ?: 'Anotación personal en agenda.',
                diffMinutos: $diffMinutos,
                recordatorioMinutos: $nota->recordatorio_minutos ?? 5,
                destinatario: $nota->usuario,
                hoy: $hoy
            );
        }

        // 3. NOTIFICACIONES JUDICIALES CON VENCIMIENTO HOY
        $notificaciones = Notificacion::with(['caso.abogado', 'usuario'])
            ->where('estado', '!=', 'atendido')
            ->whereDate('fecha_vencimiento', $hoy)
            ->get();

        foreach ($notificaciones as $notif) {
            $horaStr = $notif->hora_vencimiento ? substr($notif->hora_vencimiento, 0, 5) : '23:59';
            try {
                $horaCarbon = Carbon::createFromFormat('!Y-m-d H:i', $hoy . ' ' . $horaStr, config('app.timezone'))->startOfMinute();
                $diffMinutos = (int) round($ahoraMinuto->diffInMinutes($horaCarbon, false));
            } catch (\Exception $e) {
                continue;
            }

            $dest = $notif->usuario ?? ($notif->caso?->abogado);

            $this->evaluarYDespachar(
                idUnico: 'notif_' . $notif->id,
                titulo: "VENCIMIENTO JUDICIAL: {$notif->titulo}",
                hora: $horaStr,
                detalle: ($notif->juzgado ? "Juzgado: {$notif->juzgado}\n" : '') . ($notif->caso ? "Expediente: {$notif->caso->numero_caso}\n" : '') . ($notif->descripcion ?: ''),
                diffMinutos: $diffMinutos,
                recordatorioMinutos: $notif->recordatorio_minutos ?? 15,
                destinatario: $dest,
                hoy: $hoy
            );
        }

        $this->info("Verificación finalizada.");
        return Command::SUCCESS;
    }

    private function evaluarYDespachar(string $idUnico, string $titulo, string $hora, string $detalle, int $diffMinutos, int $recordatorioMinutos, ?Usuario $destinatario, string $hoy)
    {
        if (!$destinatario) return;

        // A. Aviso previo con anticipación (ej. 15 min antes, 5 min antes)
        if ($recordatorioMinutos > 0 && $diffMinutos <= $recordatorioMinutos && $diffMinutos > 0) {
            $cacheKeyPrevio = "tg_previo_{$idUnico}_{$hoy}_{$recordatorioMinutos}";
            if (!Cache::has($cacheKeyPrevio)) {
                Cache::put($cacheKeyPrevio, true, now()->addHours(6));
                $this->line("-> Despachando recordatorio previo para: {$titulo} (en {$diffMinutos} min) a {$destinatario->nombre_completo}");
                NotificadorExternoService::notificarUsuario(
                    $destinatario,
                    "Recordatorio (en {$diffMinutos} min): {$titulo}",
                    "Hora programada: {$hora}\n" . ($detalle ? "{$detalle}\n" : ""),
                    "Aviso programado con {$recordatorioMinutos} min de anticipación."
                );
            }
        }

        // B. Alerta a la hora exacta (entre 0 y -5 minutos)
        if ($diffMinutos <= 0 && $diffMinutos >= -5) {
            $cacheKeyExacto = "tg_exacto_{$idUnico}_{$hoy}_{$hora}";
            if (!Cache::has($cacheKeyExacto)) {
                Cache::put($cacheKeyExacto, true, now()->addHours(6));
                $this->line("-> Despachando alerta a la HORA EXACTA para: {$titulo} a {$destinatario->nombre_completo}");
                NotificadorExternoService::notificarUsuario(
                    $destinatario,
                    "ES LA HORA: {$titulo}",
                    "Hora programada: {$hora}\n" . ($detalle ? "{$detalle}\n" : ""),
                    "Iniciando ahora en el sistema."
                );
            }
        }
    }
}
