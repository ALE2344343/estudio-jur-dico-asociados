<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leyes', function (Blueprint $table) {
            $table->id();
            $table->string('numero'); // e.g. "Ley 321" o "D.S. 1842"
            $table->string('titulo');
            $table->text('descripcion');
            $table->text('articulos')->nullable(); // JSON con artículos relevantes
            $table->date('fecha_promulgacion')->nullable();
            $table->enum('categoria', [
                'Laboral', 'Civil', 'Penal', 'Familia', 'Comercial',
                'Administrativo', 'Constitucional', 'Tributario',
                'Seguridad Social', 'Otro'
            ])->default('Laboral');
            $table->string('gaceta_oficial')->nullable();
            $table->string('link_oficial')->nullable();
            $table->boolean('vigente')->default(true);
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        // Tabla de calculos de finiquito
        Schema::create('calculos_finiquito', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('caso_id')->nullable();
            $table->unsignedBigInteger('usuario_id');
            // Datos del trabajador
            $table->string('trabajador_nombre');
            $table->string('empresa');
            $table->date('fecha_ingreso');
            $table->date('fecha_retiro');
            $table->decimal('salario_mensual', 10, 2);
            $table->enum('tipo_retiro', ['voluntario', 'despido_justificado', 'despido_injustificado', 'mutuo_acuerdo'])->default('despido_injustificado');
            // Resultados calculados
            $table->decimal('anos_trabajados', 8, 4)->default(0);
            $table->decimal('meses_trabajados', 8, 4)->default(0);
            $table->decimal('indemnizacion', 10, 2)->default(0);
            $table->decimal('desahucio', 10, 2)->default(0);
            $table->decimal('vacaciones', 10, 2)->default(0);
            $table->decimal('aguinaldo', 10, 2)->default(0);
            $table->decimal('prima', 10, 2)->default(0);
            $table->decimal('bono_antiguedad', 10, 2)->default(0);
            $table->decimal('dias_pendientes', 10, 2)->default(0);
            $table->decimal('total_finiquito', 10, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->foreign('cliente_id')->references('id')->on('clientes')->nullOnDelete();
            $table->foreign('caso_id')->references('id')->on('casos')->nullOnDelete();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculos_finiquito');
        Schema::dropIfExists('leyes');
    }
};
