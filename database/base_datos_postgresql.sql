-- ============================================================================
-- SCRIPT DE BASE DE DATOS POSTGRESQL (pgAdmin 4 / Render) - SISTEMA JURÍDICO
-- Base de datos: dbproyectobufete
-- Instrucciones: Abre Query Tool en pgAdmin 4, pega este código y presiona F5
-- ============================================================================

-- 1. TABLA: usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    apellido VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(50) NOT NULL DEFAULT 'abogado' CHECK (rol IN ('administrador', 'abogado')),
    telefono VARCHAR(255) NULL,
    especialidad VARCHAR(255) NULL,
    matricula VARCHAR(255) NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    ultimo_acceso TIMESTAMP NULL,
    avatar VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. TABLA: clientes
CREATE TABLE IF NOT EXISTS clientes (
    id BIGSERIAL PRIMARY KEY,
    codigo VARCHAR(255) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    apellido VARCHAR(255) NOT NULL,
    ci VARCHAR(255) NOT NULL UNIQUE,
    ci_expedido VARCHAR(255) NULL,
    fecha_nacimiento DATE NULL,
    genero VARCHAR(20) NULL CHECK (genero IN ('M', 'F', 'Otro')),
    telefono VARCHAR(255) NULL,
    celular VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    direccion TEXT NULL,
    ciudad VARCHAR(255) NOT NULL DEFAULT 'La Paz',
    departamento VARCHAR(255) NOT NULL DEFAULT 'La Paz',
    ocupacion VARCHAR(255) NULL,
    empresa VARCHAR(255) NULL,
    estado_civil VARCHAR(50) NULL CHECK (estado_civil IN ('Soltero/a','Casado/a','Divorciado/a','Viudo/a','Conviviente')),
    observaciones TEXT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'activo' CHECK (estado IN ('activo', 'inactivo')),
    usuario_id BIGINT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 3. TABLA: casos (NUREJ)
CREATE TABLE IF NOT EXISTS casos (
    id BIGSERIAL PRIMARY KEY,
    numero_caso VARCHAR(255) NOT NULL UNIQUE,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    tipo VARCHAR(50) NOT NULL DEFAULT 'Civil' CHECK (tipo IN ('Civil', 'Penal', 'Laboral', 'Familia', 'Comercial', 'Administrativo', 'Constitucional', 'Tributario', 'Otro')),
    estado VARCHAR(50) NOT NULL DEFAULT 'activo' CHECK (estado IN ('activo', 'en_proceso', 'suspendido', 'cerrado', 'ganado', 'perdido')),
    prioridad VARCHAR(30) NOT NULL DEFAULT 'media' CHECK (prioridad IN ('baja', 'media', 'alta', 'urgente')),
    fecha_inicio DATE NOT NULL,
    fecha_vencimiento DATE NULL,
    fecha_cierre DATE NULL,
    juzgado VARCHAR(255) NULL,
    juez VARCHAR(255) NULL,
    numero_expediente VARCHAR(255) NULL,
    contraparte TEXT NULL,
    notas_privadas TEXT NULL,
    honorarios DECIMAL(10, 2) NULL,
    estado_honorarios VARCHAR(30) NOT NULL DEFAULT 'pendiente' CHECK (estado_honorarios IN ('pendiente', 'parcial', 'pagado')),
    cliente_id BIGINT NOT NULL REFERENCES clientes(id) ON DELETE CASCADE,
    abogado_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 4. TABLA: documentos
CREATE TABLE IF NOT EXISTS documentos (
    id BIGSERIAL PRIMARY KEY,
    codigo VARCHAR(255) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    descripcion VARCHAR(255) NULL,
    tipo VARCHAR(100) NOT NULL DEFAULT 'Otro',
    archivo VARCHAR(255) NULL,
    extension VARCHAR(50) NULL,
    tamano BIGINT NULL,
    drive_link VARCHAR(255) NULL,
    es_drive BOOLEAN NOT NULL DEFAULT FALSE,
    caso_id BIGINT NULL REFERENCES casos(id) ON DELETE SET NULL,
    cliente_id BIGINT NULL REFERENCES clientes(id) ON DELETE SET NULL,
    subido_por BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    estado VARCHAR(30) NOT NULL DEFAULT 'activo' CHECK (estado IN ('activo', 'archivado', 'eliminado')),
    version INTEGER NOT NULL DEFAULT 1,
    notas TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 5. TABLA: leyes (Biblioteca Jurídica + PDF)
CREATE TABLE IF NOT EXISTS leyes (
    id BIGSERIAL PRIMARY KEY,
    numero VARCHAR(255) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    articulos TEXT NULL,
    archivo_pdf VARCHAR(255) NULL,
    fecha_promulgacion DATE NULL,
    categoria VARCHAR(50) NOT NULL DEFAULT 'Laboral' CHECK (categoria IN ('Laboral', 'Civil', 'Penal', 'Familia', 'Comercial', 'Administrativo', 'Constitucional', 'Tributario', 'Seguridad Social', 'Otro')),
    gaceta_oficial VARCHAR(255) NULL,
    link_oficial VARCHAR(255) NULL,
    vigente BOOLEAN NOT NULL DEFAULT TRUE,
    notas TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 6. TABLA: calculos_finiquito (Liquidación de Beneficios Sociales SMN Bs. 3,300)
CREATE TABLE IF NOT EXISTS calculos_finiquito (
    id BIGSERIAL PRIMARY KEY,
    cliente_id BIGINT NULL REFERENCES clientes(id) ON DELETE SET NULL,
    caso_id BIGINT NULL REFERENCES casos(id) ON DELETE SET NULL,
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    trabajador_nombre VARCHAR(255) NOT NULL,
    empresa VARCHAR(255) NOT NULL,
    fecha_ingreso DATE NOT NULL,
    fecha_retiro DATE NOT NULL,
    salario_mensual DECIMAL(10, 2) NOT NULL,
    tipo_retiro VARCHAR(50) NOT NULL DEFAULT 'despido_injustificado' CHECK (tipo_retiro IN ('voluntario', 'despido_justificado', 'despido_injustificado', 'mutuo_acuerdo')),
    anos_trabajados DECIMAL(8, 4) NOT NULL DEFAULT 0,
    meses_trabajados DECIMAL(8, 4) NOT NULL DEFAULT 0,
    indemnizacion DECIMAL(10, 2) NOT NULL DEFAULT 0,
    desahucio DECIMAL(10, 2) NOT NULL DEFAULT 0,
    reintegro DECIMAL(10, 2) NOT NULL DEFAULT 0,
    sueldos_devengados_meses INTEGER NOT NULL DEFAULT 0,
    sueldos_devengados_monto DECIMAL(10, 2) NOT NULL DEFAULT 0,
    vacaciones DECIMAL(10, 2) NOT NULL DEFAULT 0,
    tomo_vacaciones BOOLEAN NOT NULL DEFAULT FALSE,
    aguinaldo DECIMAL(10, 2) NOT NULL DEFAULT 0,
    multa_aguinaldo BOOLEAN NOT NULL DEFAULT FALSE,
    prima DECIMAL(10, 2) NOT NULL DEFAULT 0,
    bono_antiguedad DECIMAL(10, 2) NOT NULL DEFAULT 0,
    dias_pendientes DECIMAL(10, 2) NOT NULL DEFAULT 0,
    total_finiquito DECIMAL(10, 2) NOT NULL DEFAULT 0,
    multa_30 BOOLEAN NOT NULL DEFAULT FALSE,
    monto_multa_30 DECIMAL(10, 2) NOT NULL DEFAULT 0,
    total_general DECIMAL(10, 2) NOT NULL DEFAULT 0,
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 7. TABLA: audiencias
CREATE TABLE IF NOT EXISTS audiencias (
    id BIGSERIAL PRIMARY KEY,
    caso_id BIGINT NOT NULL REFERENCES casos(id) ON DELETE CASCADE,
    titulo VARCHAR(255) NOT NULL,
    fecha_hora TIMESTAMP NOT NULL,
    lugar VARCHAR(255) NULL,
    estado VARCHAR(50) NOT NULL DEFAULT 'programada',
    recordatorio_minutos INTEGER NOT NULL DEFAULT 15,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 8. TABLA: notificaciones (Plazos Procesales y Alertas)
CREATE TABLE IF NOT EXISTS notificaciones (
    id BIGSERIAL PRIMARY KEY,
    caso_id BIGINT NULL REFERENCES casos(id) ON DELETE CASCADE,
    usuario_id BIGINT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    titulo VARCHAR(255) NOT NULL,
    tipo VARCHAR(100) NOT NULL DEFAULT 'notificacion_judicial',
    fecha_recepcion DATE NOT NULL,
    fecha_vencimiento DATE NULL,
    hora_vencimiento TIME NULL,
    recordatorio_minutos INTEGER NOT NULL DEFAULT 60,
    descripcion TEXT NULL,
    juzgado VARCHAR(255) NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'pendiente' CHECK (estado IN ('pendiente', 'atendido')),
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 9. TABLA: calendario_anotaciones (Agenda Personal / Eventos de Abogado)
CREATE TABLE IF NOT EXISTS calendario_anotaciones (
    id BIGSERIAL PRIMARY KEY,
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    titulo VARCHAR(255) NOT NULL,
    fecha DATE NOT NULL,
    hora VARCHAR(50) NULL,
    descripcion TEXT NULL,
    color VARCHAR(30) NOT NULL DEFAULT '#2563EB',
    recordatorio_minutos INTEGER NOT NULL DEFAULT 5,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 10. TABLA: historial_actividades (Auditoría del Sistema)
CREATE TABLE IF NOT EXISTS historial_actividades (
    id BIGSERIAL PRIMARY KEY,
    usuario_id BIGINT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    usuario_nombre VARCHAR(255) NOT NULL,
    usuario_rol VARCHAR(100) NOT NULL,
    modulo VARCHAR(100) NOT NULL,
    accion VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    registro_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 11. TABLAS INTERNAS DE LARAVEL (sessions, cache)
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);
