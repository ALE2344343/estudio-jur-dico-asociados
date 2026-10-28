<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar verificación continua de recordatorios y alertas para Telegram
\Illuminate\Support\Facades\Schedule::command('alertas:despachar')->everyMinute();
