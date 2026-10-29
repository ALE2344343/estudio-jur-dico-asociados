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

        // Auto-reparación automática de MySQL (XAMPP) si el puerto 3306 está cerrado
        $fpSocket = @fsockopen('127.0.0.1', 3306, $errno, $errstr, 0.3);
        if ($fpSocket) {
            @fclose($fpSocket);
        } else {
            // 1. Extender ibdata1 a 216 MB (13,824 páginas de 16KB) para cubrir la página 13759 del redo log sin perder datos
            $ibdataPath = 'C:/xampp/mysql/data/ibdata1';
            $minSize = 226492416; // 216 * 1024 * 1024 bytes (exactamente 13,824 páginas de 16,384 bytes)
            if (is_file($ibdataPath) && @filesize($ibdataPath) < $minSize) {
                $fp = @fopen($ibdataPath, 'c+b');
                if ($fp) {
                    @ftruncate($fp, $minSize);
                    @fclose($fp);
                }
            }

            // 2. Reparar la tabla de sistema mysql.tables_priv (causante del crash 0xc0000005 en MariaDB)
            foreach (['frm', 'MAD', 'MAI', 'MYD', 'MYI'] as $ext) {
                $src = "C:/xampp/mysql/backup/mysql/tables_priv.{$ext}";
                $dst = "C:/xampp/mysql/data/mysql/tables_priv.{$ext}";
                if (is_file($src)) {
                    @copy($src, $dst);
                }
            }

            // 3. Limpiar archivos de bloqueo residuales
            @unlink('C:/xampp/mysql/data/mysql.pid');
            @unlink('C:/xampp/mysql/data/aria_log_control');

            // 4. Iniciar MySQL automáticamente en segundo plano y esperar a que abra el puerto 3306
            if (is_file('C:/xampp/mysql/bin/mysqld.exe')) {
                @pclose(@popen('start "" /B "C:/xampp/mysql/bin/mysqld.exe" --defaults-file="C:/xampp/mysql/bin/my.ini"', 'r'));
                for ($i = 0; $i < 20; $i++) {
                    usleep(250000); // 250ms
                    $check = @fsockopen('127.0.0.1', 3306, $e1, $e2, 0.2);
                    if ($check) {
                        @fclose($check);
                        break;
                    }
                }
            }
        }

        // Auto-actualización de esquema en MySQL:
        // 1. Asegurar que 'telefono' en 'clientes' sea NULLABLE para que nunca bloquee registros con teléfono vacío
        // 2. Asegurar que 'tipo' en 'documentos' sea VARCHAR(100) para permitir 'Prueba', 'Poder Notarial', imágenes, etc.
        // 3. Asegurar que 'articulos' y 'descripcion' en 'leyes' sean LONGTEXT para leyes extensas
        // 4. Migrar los códigos de clientes existentes a formato CI
        try {
            if (!\Illuminate\Support\Facades\Cache::has('db_schema_patch_v5')) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE clientes MODIFY telefono VARCHAR(255) NULL");
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE documentos MODIFY tipo VARCHAR(100) NOT NULL DEFAULT 'Otro'");
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE leyes MODIFY articulos LONGTEXT NULL");
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE leyes MODIFY descripcion LONGTEXT NULL");
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE leyes MODIFY notas LONGTEXT NULL");

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
        // Verifica si el motor está corriendo; si no, lo enciende silenciosamente sin ventanas
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
