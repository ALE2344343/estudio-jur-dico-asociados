<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->time('hora_vencimiento')->nullable()->after('fecha_vencimiento');
            $table->integer('recordatorio_minutos')->default(60)->after('hora_vencimiento'); // Minutos de anticipación
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropColumn(['hora_vencimiento', 'recordatorio_minutos']);
        });
    }
};
