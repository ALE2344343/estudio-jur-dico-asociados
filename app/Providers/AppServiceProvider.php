<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Usar Bootstrap 5 para la paginación (no requiere Tailwind)
        Paginator::useBootstrapFive();

        // Forzar HTTPS en producción (Render / Cloud) para que carguen estilos CSS e imágenes
        if (config('app.env') === 'production' || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Auto-reparación automática de MySQL (XAMPP) solo en entorno local Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && config('database.default') === 'mysql') {
            $fpSocket = @fsockopen('127.0.0.1', 3306, $errno, $errstr, 0.3);
            if ($fpSocket) {
                @fclose($fpSocket);
            } else {
                $ibdataPath = 'C:/xampp/mysql/data/ibdata1';
                $minSize = 226492416;
                if (is_file($ibdataPath) && @filesize($ibdataPath) < $minSize) {
                    $fp = @fopen($ibdataPath, 'c+b');
                    if ($fp) {
                        @ftruncate($fp, $minSize);
                        @fclose($fp);
                    }
                }

                foreach (['frm', 'MAD', 'MAI', 'MYD', 'MYI'] as $ext) {
                    $src = "C:/xampp/mysql/backup/mysql/tables_priv.{$ext}";
                    $dst = "C:/xampp/mysql/data/mysql/tables_priv.{$ext}";
                    if (is_file($src)) {
                        @copy($src, $dst);
                    }
                }

                @unlink('C:/xampp/mysql/data/mysql.pid');
                @unlink('C:/xampp/mysql/data/aria_log_control');

                if (is_file('C:/xampp/mysql/bin/mysqld.exe')) {
                    @pclose(@popen('start "" /B "C:/xampp/mysql/bin/mysqld.exe" --defaults-file="C:/xampp/mysql/bin/my.ini"', 'r'));
                    for ($i = 0; $i < 20; $i++) {
                        usleep(250000);
                        $check = @fsockopen('127.0.0.1', 3306, $e1, $e2, 0.2);
                        if ($check) {
                            @fclose($check);
                            break;
                        }
                    }
                }
            }
        }

        // Auto-actualización de esquema
        try {
            if (!\Illuminate\Support\Facades\Cache::has('db_schema_patch_v5')) {
                if (config('database.default') === 'mysql') {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE clientes MODIFY telefono VARCHAR(255) NULL");
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE documentos MODIFY tipo VARCHAR(100) NOT NULL DEFAULT 'Otro'");
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE leyes MODIFY articulos LONGTEXT NULL");
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE leyes MODIFY descripcion LONGTEXT NULL");
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE leyes MODIFY notas LONGTEXT NULL");
                }

                // Actualizar clientes que aún tengan formato antiguo 'CLI-...'
                $clientesAntiguos = \App\Models\Cliente::whereNotNull('ci')->get();
                foreach ($clientesAntiguos as $cl) {
                    $nuevoCod = \App\Models\Cliente::generarCodigo($cl->ci, $cl->ci_expedido);
                    if ($cl->codigo !== $nuevoCod) {
                        \Illuminate\Support\Facades\DB::table('clientes')->where('id', $cl->id)->update(['codigo' => $nuevoCod]);
                    }
                }

                // Actualizar casos existentes de 'CASO-...' a 'NUREJ-...'
                \Illuminate\Support\Facades\DB::statement("UPDATE casos SET numero_caso = REPLACE(numero_caso, 'CASO-', 'NUREJ-') WHERE numero_caso LIKE 'CASO-%'");

                \Illuminate\Support\Facades\Cache::forever('db_schema_patch_v5', true);
            }
        } catch (\Throwable $e) {
            // Ignorar excepciones transitorias de conexión
        }

        // Auto-ejecución invisible del motor de alertas de Telegram en segundo plano
        try {
            if (!\Illuminate\Support\Facades\Cache::has('motor_alertas_activo_check')) {
                \Illuminate\Support\Facades\Cache::put('motor_alertas_activo_check', true, now()->addMinutes(10));
                $phpCmd = is_file('C:/nuevo xap/php/php.exe') ? 'C:/nuevo xap/php/php.exe' : 'php';
                $artisanPath = base_path('artisan');
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    @pclose(@popen("start /min \"\" \"{$phpCmd}\" \"{$artisanPath}\" alertas:despachar", "r"));
                }
            }
        } catch (\Throwable $e) {
            // Silencioso
        }
    }
}
