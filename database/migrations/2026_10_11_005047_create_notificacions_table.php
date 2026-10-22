<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caso_id')->nullable()->constrained('casos')->onDelete('cascade');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null'); // Abogado asignado
            $table->string('titulo');
            $table->string('tipo')->default('notificacion_judicial'); // notificacion_judicial, citacion, providencia, memorial
            $table->date('fecha_recepcion');
            $table->date('fecha_vencimiento')->nullable(); // Plazo procesal perentorio
            $table->text('descripcion')->nullable();
            $table->string('juzgado')->nullable();
            $table->enum('estado', ['pendiente', 'atendido'])->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
