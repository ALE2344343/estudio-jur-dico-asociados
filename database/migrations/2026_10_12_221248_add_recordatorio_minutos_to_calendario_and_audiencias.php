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
        if (Schema::hasTable('calendario_anotaciones') && !Schema::hasColumn('calendario_anotaciones', 'recordatorio_minutos')) {
            Schema::table('calendario_anotaciones', function (Blueprint $table) {
                $table->integer('recordatorio_minutos')->default(5)->after('color');
            });
        }

        if (Schema::hasTable('audiencias') && !Schema::hasColumn('audiencias', 'recordatorio_minutos')) {
            Schema::table('audiencias', function (Blueprint $table) {
                $table->integer('recordatorio_minutos')->default(15)->after('estado');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('calendario_anotaciones') && Schema::hasColumn('calendario_anotaciones', 'recordatorio_minutos')) {
            Schema::table('calendario_anotaciones', function (Blueprint $table) {
                $table->dropColumn('recordatorio_minutos');
            });
        }

        if (Schema::hasTable('audiencias') && Schema::hasColumn('audiencias', 'recordatorio_minutos')) {
            Schema::table('audiencias', function (Blueprint $table) {
                $table->dropColumn('recordatorio_minutos');
            });
        }
    }
};
