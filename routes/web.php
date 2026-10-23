<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CasoController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\CalculadoraController;
use App\Http\Controllers\LeyController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\GestionLegalController;
use App\Http\Controllers\HistorialController;
use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────
// RUTAS DE AUTENTICACIÓN
// ──────────────────────────────────────────────
Route::get('/', [AuthController::class, 'inicio'])->name('inicio');
Route::get('/login/administrador', [AuthController::class, 'showLoginAdmin'])->name('login.admin');
Route::get('/login/abogado', [AuthController::class, 'showLoginAbogado'])->name('login.abogado');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// ──────────────────────────────────────────────
// RUTAS DEL SISTEMA (requieren sesión)
// ──────────────────────────────────────────────
Route::middleware([])->group(function () {

    // Dashboard y Anotaciones del Calendario
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/alertas/pendientes', [DashboardController::class, 'alertasPendientes'])->name('alertas.pendientes');
    Route::get('/alertas/externas/config', [DashboardController::class, 'obtenerConfigExterna'])->name('alertas.externas.config');
    Route::post('/alertas/externas/config', [DashboardController::class, 'guardarConfigExterna'])->name('alertas.externas.guardar');
    Route::get('/alertas/externas/detectar-telegram', [DashboardController::class, 'detectarTelegramChats'])->name('alertas.externas.detectar');
    Route::post('/alertas/externas/probar', [DashboardController::class, 'probarNotificacionExterna'])->name('alertas.externas.probar');
    Route::post('/alertas/externas/despachar', [DashboardController::class, 'despacharAlertaExterna'])->name('alertas.externas.despachar');
    Route::post('/calendario/anotaciones', [DashboardController::class, 'storeAnotacion'])->name('calendario.anotaciones.store');
    Route::put('/calendario/anotaciones/{id}', [DashboardController::class, 'updateAnotacion'])->name('calendario.anotaciones.update');
    Route::delete('/calendario/anotaciones/{id}', [DashboardController::class, 'destroyAnotacion'])->name('calendario.anotaciones.destroy');

    // Clientes
    Route::resource('clientes', ClienteController::class);

    // Casos
    Route::resource('casos', CasoController::class);
    Route::post('/casos/{id}/audiencias', [CasoController::class, 'storeAudiencia'])->name('audiencias.store');

    // Documentos
    Route::get('/documentos', [DocumentoController::class, 'index'])->name('documentos.index');
    Route::post('/documentos', [DocumentoController::class, 'store'])->name('documentos.store');
    Route::get('/documentos/{id}/edit', [DocumentoController::class, 'edit'])->name('documentos.edit');
    Route::put('/documentos/{id}', [DocumentoController::class, 'update'])->name('documentos.update');
    Route::get('/documentos/{id}', [DocumentoController::class, 'show'])->name('documentos.show');
    Route::delete('/documentos/{id}', [DocumentoController::class, 'destroy'])->name('documentos.destroy');

    // Calculadora de Finiquito
    Route::get('/calculadora', [CalculadoraController::class, 'index'])->name('calculadora.index');
    Route::post('/calculadora/calcular', [CalculadoraController::class, 'calcular'])->name('calculadora.calcular');
    Route::post('/calculadora/guardar', [CalculadoraController::class, 'guardar'])->name('calculadora.guardar');
    Route::get('/calculadora/{id}', [CalculadoraController::class, 'showCalculo'])->name('calculadora.show');

    // Leyes
    Route::get('/leyes/archivo/{path}', function ($path) {
        $fullPath = storage_path('app/public/' . $path);
        if (!file_exists($fullPath)) {
            abort(404, 'Archivo no encontrado');
        }
        return response()->file($fullPath);
    })->where('path', '.*')->name('leyes.descargar_archivo');

    Route::post('/leyes/{id}/upload-pdf', [LeyController::class, 'uploadPdf'])->name('leyes.upload_pdf');
    Route::resource('leyes', LeyController::class);

    // Usuarios (solo administrador)
    Route::resource('usuarios', UsuarioController::class);

    // Control y Asignaciones (solo administrador)
    Route::get('/gestion-legal', [GestionLegalController::class, 'index'])->name('gestion.index');
    Route::post('/gestion-legal/usuarios/{id}/rol', [GestionLegalController::class, 'cambiarRol'])->name('gestion.usuarios.rol');
    Route::post('/gestion-legal/usuarios/{id}/toggle-activo', [GestionLegalController::class, 'toggleActivo'])->name('gestion.usuarios.activo');
    Route::post('/gestion-legal/casos/{id}/asignar', [GestionLegalController::class, 'asignarCaso'])->name('gestion.casos.asignar');
    Route::post('/gestion-legal/notificaciones', [GestionLegalController::class, 'storeNotificacion'])->name('gestion.notificaciones.store');
    Route::put('/gestion-legal/notificaciones/{id}', [GestionLegalController::class, 'updateNotificacion'])->name('gestion.notificaciones.update');
    Route::post('/gestion-legal/notificaciones/{id}/toggle', [GestionLegalController::class, 'toggleNotificacion'])->name('gestion.notificaciones.toggle');
    Route::delete('/gestion-legal/notificaciones/{id}', [GestionLegalController::class, 'destroyNotificacion'])->name('gestion.notificaciones.destroy');
    Route::post('/gestion-legal/audiencias/{id}/notificar-telegram', [GestionLegalController::class, 'notificarAudienciaTelegram'])->name('gestion.audiencias.notificar');
    Route::put('/gestion-legal/audiencias/{id}', [GestionLegalController::class, 'updateAudiencia'])->name('gestion.audiencias.update');
    Route::delete('/gestion-legal/audiencias/{id}', [GestionLegalController::class, 'destroyAudiencia'])->name('gestion.audiencias.destroy');

    // Historial de Auditoría (solo administrador, solo lectura)
    Route::get('/historial', [HistorialController::class, 'index'])->name('historial.index');
});

