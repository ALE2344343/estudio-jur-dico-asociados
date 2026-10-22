<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // e.g. CLI-001
            $table->string('nombre');
            $table->string('apellido');
            $table->string('ci')->unique(); // Cédula de Identidad
            $table->string('ci_expedido')->nullable(); // Expedido en (depto)
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('genero', ['M', 'F', 'Otro'])->nullable();
            $table->string('telefono');
            $table->string('celular')->nullable();
            $table->string('email')->nullable();
            $table->text('direccion')->nullable();
            $table->string('ciudad')->default('La Paz');
            $table->string('departamento')->default('La Paz');
            $table->string('ocupacion')->nullable();
            $table->string('empresa')->nullable();
            $table->enum('estado_civil', ['Soltero/a','Casado/a','Divorciado/a','Viudo/a','Conviviente'])->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->unsignedBigInteger('usuario_id')->nullable(); // Abogado asignado
            $table->foreign('usuario_id')->references('id')->on('usuarios')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
