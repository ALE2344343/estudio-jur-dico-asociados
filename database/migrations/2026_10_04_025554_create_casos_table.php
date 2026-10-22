<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('casos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_caso')->unique(); // e.g. CASO-2024-001
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->enum('tipo', [
                'Civil', 'Penal', 'Laboral', 'Familia', 'Comercial',
                'Administrativo', 'Constitucional', 'Tributario', 'Otro'
            ])->default('Civil');
            $table->enum('estado', ['activo', 'en_proceso', 'suspendido', 'cerrado', 'ganado', 'perdido'])
                  ->default('activo');
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');
            $table->date('fecha_inicio');
            $table->date('fecha_vencimiento')->nullable();
            $table->date('fecha_cierre')->nullable();
            $table->string('juzgado')->nullable();
            $table->string('juez')->nullable();
            $table->string('numero_expediente')->nullable(); // Número en juzgado
            $table->text('contraparte')->nullable();
            $table->text('notas_privadas')->nullable();
            $table->decimal('honorarios', 10, 2)->nullable();
            $table->enum('estado_honorarios', ['pendiente', 'parcial', 'pagado'])->default('pendiente');
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('abogado_id');
            $table->foreign('cliente_id')->references('id')->on('clientes')->cascadeOnDelete();
            $table->foreign('abogado_id')->references('id')->on('usuarios')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('casos');
    }
};
