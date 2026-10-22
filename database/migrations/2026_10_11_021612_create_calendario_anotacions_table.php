<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendario_anotaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('titulo');
            $table->date('fecha');
            $table->string('hora')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('color')->default('#2563EB'); // Azul profesional
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendario_anotaciones');
    }
};
