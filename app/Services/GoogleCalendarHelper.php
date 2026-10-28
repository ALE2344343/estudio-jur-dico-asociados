<?php

namespace App\Services;

use Carbon\Carbon;

class GoogleCalendarHelper
{
    /**
     * Genera un enlace directo tipo "Agregar a Google Calendar" (web intent)
     * No requiere APIs complejas ni credenciales OAuth.
     */
    public static function generarEnlace(
        string $titulo,
        string $fecha,
        ?string $hora = null,
        ?string $descripcion = null,
        ?string $ubicacion = null,
        int $duracionMinutos = 60
    ): string {
        $tz = config('app.timezone', 'America/La_Paz');

        if ($hora) {
            $horaLimpia = substr($hora, 0, 5);
            try {
                $inicio = Carbon::createFromFormat('!Y-m-d H:i', $fecha . ' ' . $horaLimpia, $tz);
            } catch (\Exception $e) {
                $inicio = Carbon::parse($fecha, $tz)->startOfDay();
            }
            $fin = $inicio->copy()->addMinutes($duracionMinutos);
            // Formato para Google Calendar UTC o local: Ymd\THis
            $fechasParam = $inicio->format('Ymd\THis') . '/' . $fin->format('Ymd\THis');
        } else {
            // Evento de todo el día
            $inicio = Carbon::parse($fecha, $tz);
            $fin = $inicio->copy()->addDay();
            $fechasParam = $inicio->format('Ymd') . '/' . $fin->format('Ymd');
        }

        $params = [
            'action'   => 'TEMPLATE',
            'text'     => $titulo,
            'dates'    => $fechasParam,
            'details'  => $descripcion ?: 'Recordatorio programado en el Sistema del Estudio Jurídico & Asociados.',
            'location' => $ubicacion ?: 'Estudio Jurídico & Asociados',
            'ctz'      => $tz,
        ];

        return 'https://calendar.google.com/calendar/render?' . http_build_query($params);
    }
}
