<?php
/**
 * Script de Respaldo y Sincronización: MySQL (XAMPP) <-> SQLite (Motor Interno Blindado)
 * Garantiza que el sistema NUNCA se caiga aunque MySQL de XAMPP se apague o falle.
 */

$sqlitePath = __DIR__ . '/database.sqlite';
if (!file_exists($sqlitePath)) {
    touch($sqlitePath);
}

$pdoSqlite = new PDO('sqlite:' . $sqlitePath);
$pdoSqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdoSqlite->exec('PRAGMA foreign_keys = OFF;');
$pdoSqlite->exec('PRAGMA journal_mode = WAL;');

// Crear estructura completa en SQLite si no existe
$schemas = [
    "CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre VARCHAR(100) NOT NULL,
        apellido VARCHAR(100) NOT NULL,
        username VARCHAR(60) UNIQUE NOT NULL,
        email VARCHAR(120) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        rol VARCHAR(30) DEFAULT 'abogado',
        telefono VARCHAR(30) NULL,
        telegram_chat_id VARCHAR(60) NULL,
        especialidad VARCHAR(100) NULL,
        matricula VARCHAR(50) NULL,
        activo INTEGER DEFAULT 1,
        ultimo_acceso DATETIME NULL,
        avatar VARCHAR(255) NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",
    "CREATE TABLE IF NOT EXISTS clientes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        codigo VARCHAR(30) UNIQUE NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        apellido VARCHAR(100) NOT NULL,
        ci VARCHAR(30) UNIQUE NOT NULL,
        ci_expedido VARCHAR(15) NULL,
        fecha_nacimiento DATE NULL,
        estado_civil VARCHAR(40) NULL,
        ocupacion VARCHAR(100) NULL,
        empresa VARCHAR(150) NULL,
        telefono VARCHAR(30) NULL,
        celular VARCHAR(30) NULL,
        email VARCHAR(120) NULL,
        direccion TEXT NULL,
        ciudad VARCHAR(80) NULL,
        departamento VARCHAR(80) NULL,
        estado VARCHAR(30) DEFAULT 'activo',
        observaciones TEXT NULL,
        usuario_id INTEGER NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",
    "CREATE TABLE IF NOT EXISTS casos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        numero_caso VARCHAR(40) UNIQUE NOT NULL,
        titulo VARCHAR(250) NOT NULL,
        descripcion TEXT NULL,
        tipo VARCHAR(80) NOT NULL,
        estado VARCHAR(40) DEFAULT 'activo',
        prioridad VARCHAR(30) DEFAULT 'media',
        cliente_id INTEGER NOT NULL,
        abogado_id INTEGER NOT NULL,
        contraparte VARCHAR(200) NULL,
        juzgado VARCHAR(200) NULL,
        numero_expediente VARCHAR(100) NULL,
        juez VARCHAR(150) NULL,
        fecha_inicio DATE NULL,
        fecha_vencimiento DATE NULL,
        fecha_cierre DATE NULL,
        honorarios DECIMAL(12,2) NULL,
        honorarios_pagados DECIMAL(12,2) DEFAULT 0,
        notas_privadas TEXT NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",
    "CREATE TABLE IF NOT EXISTS audiencias (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        caso_id INTEGER NOT NULL,
        titulo VARCHAR(255) NOT NULL,
        fecha_hora DATETIME NOT NULL,
        lugar VARCHAR(255) NULL,
        estado VARCHAR(50) DEFAULT 'programada',
        recordatorio_minutos INTEGER DEFAULT 15,
        notas TEXT NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",
    "CREATE TABLE IF NOT EXISTS documentos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        codigo VARCHAR(40) UNIQUE NOT NULL,
        nombre VARCHAR(250) NOT NULL,
        descripcion TEXT NULL,
        tipo VARCHAR(80) NOT NULL,
        archivo VARCHAR(255) NULL,
        extension VARCHAR(20) NULL,
        tamano INTEGER NULL,
        es_drive INTEGER DEFAULT 0,
        drive_link TEXT NULL,
        caso_id INTEGER NULL,
        cliente_id INTEGER NULL,
        subido_por INTEGER NULL,
        estado VARCHAR(30) DEFAULT 'activo',
        notas TEXT NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",
    "CREATE TABLE IF NOT EXISTS leyes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        numero VARCHAR(120) NOT NULL,
        titulo VARCHAR(350) NOT NULL,
        descripcion TEXT NOT NULL,
        articulos LONGTEXT NULL,
        categoria VARCHAR(100) NOT NULL,
        fecha_promulgacion DATE NULL,
        gaceta_oficial VARCHAR(100) NULL,
        link_oficial TEXT NULL,
        archivo_pdf VARCHAR(255) NULL,
        vigente INTEGER DEFAULT 1,
        notas TEXT NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",
    "CREATE TABLE IF NOT EXISTS calculos_finiquito (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER NULL,
        cliente_id INTEGER NULL,
        caso_id INTEGER NULL,
        trabajador_nombre VARCHAR(200) NOT NULL,
        empresa VARCHAR(200) NOT NULL,
        fecha_ingreso DATE NOT NULL,
        fecha_retiro DATE NOT NULL,
        salario_mensual DECIMAL(12,2) NOT NULL,
        tipo_retiro VARCHAR(80) NOT NULL,
        anos_trabajados INTEGER DEFAULT 0,
        meses_trabajados INTEGER DEFAULT 0,
        indemnizacion DECIMAL(12,2) DEFAULT 0,
        desahucio DECIMAL(12,2) DEFAULT 0,
        vacaciones DECIMAL(12,2) DEFAULT 0,
        aguinaldo DECIMAL(12,2) DEFAULT 0,
        prima DECIMAL(12,2) DEFAULT 0,
        bono_antiguedad DECIMAL(12,2) DEFAULT 0,
        dias_pendientes DECIMAL(12,2) DEFAULT 0,
        reintegro DECIMAL(12,2) DEFAULT 0,
        sueldos_devengados_meses INTEGER DEFAULT 0,
        sueldos_devengados_monto DECIMAL(12,2) DEFAULT 0,
        tomo_vacaciones INTEGER DEFAULT 0,
        multa_aguinaldo INTEGER DEFAULT 0,
        multa_30 INTEGER DEFAULT 0,
        monto_multa_30 DECIMAL(12,2) DEFAULT 0,
        total_finiquito DECIMAL(12,2) DEFAULT 0,
        total_general DECIMAL(12,2) DEFAULT 0,
        observaciones TEXT NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",
    "CREATE TABLE IF NOT EXISTS notificaciones (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        caso_id INTEGER NULL,
        usuario_id INTEGER NULL,
        titulo VARCHAR(255) NOT NULL,
        tipo VARCHAR(100) NOT NULL,
        juzgado VARCHAR(200) NULL,
        fecha_recepcion DATE NOT NULL,
        fecha_vencimiento DATE NULL,
        descripcion TEXT NULL,
        estado VARCHAR(50) DEFAULT 'pendiente',
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",
    "CREATE TABLE IF NOT EXISTS calendario_anotaciones (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER NOT NULL,
        titulo VARCHAR(200) NOT NULL,
        descripcion TEXT NULL,
        fecha DATE NOT NULL,
        hora VARCHAR(20) NULL,
        color VARCHAR(30) DEFAULT '#2563EB',
        recordatorio_minutos INTEGER DEFAULT 5,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",
    "CREATE TABLE IF NOT EXISTS historial_actividades (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER NULL,
        usuario_nombre VARCHAR(150) NOT NULL,
        usuario_rol VARCHAR(50) NOT NULL,
        modulo VARCHAR(100) NOT NULL,
        accion VARCHAR(100) NOT NULL,
        descripcion TEXT NOT NULL,
        registro_id INTEGER NULL,
        ip_address VARCHAR(60) NULL,
        created_at DATETIME NULL
    )"
];

foreach ($schemas as $sql) {
    $pdoSqlite->exec($sql);
}

// Intentar copiar datos actuales de MySQL si está encendido
try {
    $pdoMysql = new PDO('mysql:host=127.0.0.1;port=3306;dbname=bufete_abogados;charset=utf8mb4', 'root', '', [
        PDO::ATTR_TIMEOUT => 2,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $tablas = [
        'usuarios', 'clientes', 'casos', 'audiencias', 'documentos',
        'leyes', 'calculos_finiquito', 'notificaciones',
        'calendario_anotaciones', 'historial_actividades'
    ];

    foreach ($tablas as $tabla) {
        try {
            $rows = $pdoMysql->query("SELECT * FROM `{$tabla}`")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                // Obtener columnas válidas en SQLite
                $colsInfo = $pdoSqlite->query("PRAGMA table_info(`{$tabla}`)")->fetchAll(PDO::FETCH_ASSOC);
                $validCols = array_column($colsInfo, 'name');

                $pdoSqlite->beginTransaction();
                foreach ($rows as $row) {
                    $filtered = array_intersect_key($row, array_flip($validCols));
                    $cols = array_keys($filtered);
                    $placeholders = array_map(fn($c) => ':' . $c, $cols);
                    $sqlInsert = "INSERT OR REPLACE INTO `{$tabla}` (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
                    $stmt = $pdoSqlite->prepare($sqlInsert);
                    foreach ($filtered as $k => $v) {
                        $stmt->bindValue(':' . $k, $v);
                    }
                    $stmt->execute();
                }
                $pdoSqlite->commit();
            }
        } catch (Throwable $e) {
            if ($pdoSqlite->inTransaction()) $pdoSqlite->rollBack();
        }
    }
    echo "Sincronizacion desde MySQL a SQLite completada con exito.\n";
} catch (Throwable $e) {
    echo "MySQL apagado; verificando datos base en SQLite...\n";
}

// Asegurar que al menos exista el usuario administrador si SQLite estaba vacío
$countUsers = (int) $pdoSqlite->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
if ($countUsers === 0) {
    $passAdmin = password_hash('admin123', PASSWORD_BCRYPT);
    $passAbog  = password_hash('abogado123', PASSWORD_BCRYPT);
    $now = date('Y-m-d H:i:s');

    $pdoSqlite->exec("INSERT INTO usuarios (nombre, apellido, username, email, password, rol, telefono, especialidad, matricula, activo, created_at, updated_at) VALUES
        ('Administrador', 'Principal', 'admin', 'admin@estudiojuridico.bo', '{$passAdmin}', 'administrador', '72000001', 'Dirección General', 'MAT-ADM-001', 1, '{$now}', '{$now}'),
        ('Juan Carlos', 'Flores', 'jflores', 'jflores@estudiojuridico.bo', '{$passAbog}', 'abogado', '72000002', 'Derecho Laboral', 'MAT-LAB-102', 1, '{$now}', '{$now}'),
        ('María Elena', 'Vargas', 'mvargas', 'mvargas@estudiojuridico.bo', '{$passAbog}', 'abogado', '72000003', 'Derecho Civil y Familiar', 'MAT-CIV-205', 1, '{$now}', '{$now}')
    ");
}
