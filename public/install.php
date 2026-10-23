<?php
/**
 * =============================================================
 *  INSTALADOR WEB - Estudio Jurídico & Asociados
 * =============================================================
 *  Acceder via: http://localhost/SISTEMA/install.php
 * =============================================================
 */

set_time_limit(300);
error_reporting(E_ALL);

// ── Detectar raíz del proyecto Laravel ──────────────────────
$basePath = realpath(__DIR__ . '/..');
if (!$basePath || !file_exists($basePath . '/artisan')) {
    $candidates = [
        'C:\\nuevo xap\\php\\proyectox',
        'C:\\xampp\\htdocs\\proyectox',
    ];
    foreach ($candidates as $c) {
        if (is_file($c . '/artisan')) {
            $basePath = $c;
            break;
        }
    }
}

// ── Detectar PHP ejecutable ─────────────────────────────────
$phpCmd = 'php';
$phpCandidates = [
    'C:\\nuevo xap\\php\\php.exe',
    'C:\\xampp\\php\\php.exe',
];
foreach ($phpCandidates as $p) {
    if (is_file($p)) {
        $phpCmd = '"' . $p . '"';
        break;
    }
}

// ── Auto-detectar la URL base (ej: /SISTEMA) ────────────────
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseUrl = rtrim(str_replace('/install.php', '', $scriptName), '/');
if (empty($baseUrl)) $baseUrl = '';

// ── Funciones auxiliares ────────────────────────────────────
$logs = [];

function logStep($icon, $title, $detail, $ok = true) {
    global $logs;
    $logs[] = [
        'icon'   => $icon,
        'title'  => $title,
        'detail' => $detail,
        'ok'     => $ok,
    ];
}

function runArtisan($phpCmd, $basePath, $command) {
    $full = "$phpCmd -d memory_limit=512M \"$basePath/artisan\" $command 2>&1";
    $output = [];
    $code   = 0;
    exec($full, $output, $code);
    return ['output' => implode("\n", $output), 'code' => $code];
}

// ── ¿Ya se ejecutó la instalación? ──────────────────────────
$installing = isset($_GET['run']);
$finished   = false;

if ($installing) {
    // PASO 1 — Verificar PHP
    logStep('🔍', 'Detectar PHP', "Ejecutable: <code>$phpCmd</code>", true);

    // PASO 2 — Verificar MySQL
    $mysqlOk = false;
    try {
        $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $pdo->query('SELECT 1');
        $mysqlOk = true;
        logStep('🗄️', 'Conexión a MySQL', 'MySQL está activo y responde correctamente.', true);
    } catch (Exception $e) {
        logStep('🗄️', 'Conexión a MySQL', 'MySQL no responde. Inicie MySQL en XAMPP.<br><small>' . htmlspecialchars($e->getMessage()) . '</small>', false);
    }

    if ($mysqlOk) {
        // PASO 3 — Crear .env
        $envFile    = $basePath . '/.env';
        $envExample = $basePath . '/.env.example';
        if (!file_exists($envFile)) {
            if (file_exists($envExample)) {
                copy($envExample, $envFile);
                logStep('📄', 'Crear archivo .env', 'Archivo .env creado a partir de .env.example.', true);
            } else {
                logStep('📄', 'Crear archivo .env', 'No se encontró .env.example — abortando.', false);
            }
        } else {
            logStep('📄', 'Archivo .env', 'Ya existe, se conserva la configuración actual.', true);
        }

        // PASO 3.5 — Configurar APP_URL y ASSET_URL automáticamente
        if (file_exists($envFile) && !empty($baseUrl)) {
            $envContent = file_get_contents($envFile);
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $fullUrl = $scheme . '://' . $host . $baseUrl;

            // Actualizar APP_URL
            $envContent = preg_replace('/^APP_URL=.*$/m', 'APP_URL=' . $fullUrl, $envContent);

            // Agregar o actualizar ASSET_URL
            if (preg_match('/^ASSET_URL=/m', $envContent)) {
                $envContent = preg_replace('/^ASSET_URL=.*$/m', 'ASSET_URL=' . $baseUrl, $envContent);
            } else {
                $envContent = preg_replace('/^(APP_URL=.*)$/m', "$1\nASSET_URL=" . $baseUrl, $envContent);
            }

            file_put_contents($envFile, $envContent);
            logStep('🌐', 'Configurar URL', "APP_URL = <code>$fullUrl</code><br>ASSET_URL = <code>$baseUrl</code>", true);
        }

        // Generar clave de app (si no existe)
        $r = runArtisan($phpCmd, $basePath, 'key:generate --force');
        logStep('🔑', 'Clave de aplicación', 'Clave APP_KEY generada correctamente.', $r['code'] === 0);

        // PASO 4 — Crear base de datos
        try {
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `bufete_abogados` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            logStep('🛢️', 'Base de datos', 'Base de datos <b>bufete_abogados</b> lista.', true);
        } catch (Exception $e) {
            logStep('🛢️', 'Base de datos', 'Error al crear la base de datos.<br><small>' . htmlspecialchars($e->getMessage()) . '</small>', false);
        }

        // PASO 5 — Migraciones
        $r = runArtisan($phpCmd, $basePath, 'migrate --force');
        $migOk = $r['code'] === 0;
        $migDetail = $migOk ? 'Tablas creadas/actualizadas correctamente.' : nl2br(htmlspecialchars($r['output']));
        logStep('📋', 'Migraciones', $migDetail, $migOk);

        // PASO 6 — Seeders
        $r = runArtisan($phpCmd, $basePath, 'db:seed --force');
        $seedOk = $r['code'] === 0;
        $seedDetail = $seedOk ? 'Datos iniciales cargados (admin, abogados, clientes, casos, leyes).' : nl2br(htmlspecialchars($r['output']));
        logStep('🌱', 'Datos iniciales', $seedDetail, $seedOk);

        // PASO 7 — Storage link
        $r = runArtisan($phpCmd, $basePath, 'storage:link');
        logStep('🔗', 'Enlace de storage', 'Enlace público creado para archivos/documentos.', true);

        // PASO 8 — Limpiar caché
        runArtisan($phpCmd, $basePath, 'config:clear');
        runArtisan($phpCmd, $basePath, 'cache:clear');
        logStep('🧹', 'Limpiar caché', 'Caché de configuración limpiada.', true);

        $finished = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador – Estudio Jurídico & Asociados</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            max-width: 700px;
            width: 100%;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #0f3460, #e94560);
            color: #fff;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 { font-size: 26px; margin-bottom: 8px; }
        .header p  { opacity: .85; font-size: 14px; }
        .header .icon { font-size: 60px; margin-bottom: 12px; }
        .body { padding: 30px; }
        .step {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid #eee;
        }
        .step:last-child { border-bottom: none; }
        .step-icon { font-size: 28px; flex-shrink: 0; }
        .step-content { flex: 1; }
        .step-content h3 { font-size: 15px; color: #333; margin-bottom: 4px; }
        .step-content p  { font-size: 13px; color: #666; line-height: 1.5; word-break: break-word; }
        .step-content code { background: #f0f0f0; padding: 1px 6px; border-radius: 4px; font-size: 12px; }
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            margin-left: 8px;
            vertical-align: middle;
        }
        .badge-ok   { background: #d4edda; color: #155724; }
        .badge-fail { background: #f8d7da; color: #721c24; }
        .btn {
            display: block;
            width: 100%;
            padding: 16px;
            font-size: 18px;
            font-weight: bold;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 20px;
            transition: transform .15s, box-shadow .15s;
            text-decoration: none;
            text-align: center;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,.2); }
        .btn-install { background: linear-gradient(135deg, #0f3460, #e94560); color: #fff; }
        .btn-enter   { background: linear-gradient(135deg, #27ae60, #2ecc71); color: #fff; }
        .btn-retry   { background: linear-gradient(135deg, #e67e22, #f39c12); color: #fff; }
        .requirements {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 10px;
        }
        .requirements h3 { margin-bottom: 10px; color: #333; }
        .requirements li { font-size: 14px; color: #555; margin-bottom: 6px; list-style: none; }
        .requirements li::before { content: '✅ '; }
        .spinner {
            display: none;
            text-align: center;
            padding: 30px;
            font-size: 16px;
            color: #666;
        }
        .spinner.active { display: block; }
        .credentials {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid #0f3460;
        }
        .credentials h3 { font-size: 15px; color: #0f3460; margin-bottom: 12px; }
        .credentials table { width: 100%; font-size: 13px; }
        .credentials td { padding: 4px 8px; }
        .credentials .label { font-weight: bold; color: #333; }
        .credentials .value { color: #666; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">⚖️</div>
            <h1>Estudio Jurídico & Asociados</h1>
            <p>Asistente de Instalación del Sistema</p>
        </div>
        <div class="body">
<?php if (!$installing): ?>
            <div class="requirements">
                <h3>Requisitos previos</h3>
                <ul>
                    <li>XAMPP instalado en este equipo</li>
                    <li>Apache iniciado (si ve esta página, ya está activo)</li>
                    <li>MySQL iniciado desde el panel de XAMPP</li>
                </ul>
            </div>
            <p style="font-size:14px; color:#666; margin-top:15px; text-align:center;">
                Al hacer clic en <b>Instalar</b>, el sistema creará la base de datos,
                ejecutará las migraciones, cargará los datos iniciales y configurará
                todo automáticamente.
            </p>
            <a href="?run=1" class="btn btn-install" id="btnInstall"
               onclick="document.getElementById('btnInstall').style.display='none';
                        document.getElementById('spinner').classList.add('active');">
                🚀 Instalar Sistema
            </a>
            <div class="spinner" id="spinner">
                ⏳ Instalando... esto puede tardar unos segundos, no cierre esta ventana.
            </div>

<?php elseif ($finished): ?>
            <?php foreach ($logs as $log): ?>
            <div class="step">
                <div class="step-icon"><?= $log['icon'] ?></div>
                <div class="step-content">
                    <h3>
                        <?= $log['title'] ?>
                        <span class="badge <?= $log['ok'] ? 'badge-ok' : 'badge-fail' ?>">
                            <?= $log['ok'] ? 'OK' : 'ERROR' ?>
                        </span>
                    </h3>
                    <p><?= $log['detail'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="credentials">
                <h3>🔐 Credenciales de acceso</h3>
                <table>
                    <tr><td class="label">Administrador:</td><td class="value">admin / admin123</td></tr>
                    <tr><td class="label">Abogado 1:</td><td class="value">cmamani / abogado123</td></tr>
                    <tr><td class="label">Abogado 2:</td><td class="value">mvargas / abogado123</td></tr>
                </table>
            </div>

            <a href="./" class="btn btn-enter">✅ Entrar al Sistema</a>

<?php else: ?>
            <?php foreach ($logs as $log): ?>
            <div class="step">
                <div class="step-icon"><?= $log['icon'] ?></div>
                <div class="step-content">
                    <h3>
                        <?= $log['title'] ?>
                        <span class="badge <?= $log['ok'] ? 'badge-ok' : 'badge-fail' ?>">
                            <?= $log['ok'] ? 'OK' : 'ERROR' ?>
                        </span>
                    </h3>
                    <p><?= $log['detail'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <p style="color:#c0392b; text-align:center; margin-top:20px; font-weight:bold;">
                ⚠️ Inicie MySQL en XAMPP y vuelva a intentarlo.
            </p>
            <a href="?run=1" class="btn btn-retry">🔄 Reintentar Instalación</a>
<?php endif; ?>
        </div>
    </div>
</body>
</html>