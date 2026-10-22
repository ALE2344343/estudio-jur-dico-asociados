<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->enum('tipo', [
                'Demanda', 'Contrato', 'Acuerdo', 'Sentencia', 'Auto',
                'Memorial', 'Poder Notarial', 'Certificado', 'Resolución',
                'Recurso', 'Apelación', 'Denuncia', 'Otro'
            ])->default('Otro');
            $table->string('archivo')->nullable(); // Ruta del archivo subido
            $table->string('extension')->nullable(); // pdf, docx, etc
            $table->bigInteger('tamano')->nullable(); // bytes
            $table->string('drive_link')->nullable(); // Link de Google Drive
            $table->boolean('es_drive')->default(false);
            $table->unsignedBigInteger('caso_id')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('subido_por');
            $table->enum('estado', ['activo', 'archivado', 'eliminado'])->default('activo');
            $table->integer('version')->default(1);
            $table->text('notas')->nullable();
            $table->foreign('caso_id')->references('id')->on('casos')->nullOnDelete();
            $table->foreign('cliente_id')->references('id')->on('clientes')->nullOnDelete();
            $table->foreign('subido_por')->references('id')->on('usuarios')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
