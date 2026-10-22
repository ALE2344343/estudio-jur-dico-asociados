<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->string('usuario_nombre'); // Para preservar el autor aunque se borre el usuario
            $table->string('usuario_rol');
            $table->string('modulo'); // Clientes, Casos, Documentos, Usuarios, Asignaciones, Notificaciones, Audiencias
            $table->string('accion'); // Creación, Modificación, Eliminación, Reasignación
            $table->string('descripcion');
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_actividades');
    }
};
