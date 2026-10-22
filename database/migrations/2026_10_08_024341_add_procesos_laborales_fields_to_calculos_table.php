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
        Schema::table('calculos_finiquito', function (Blueprint $table) {
            $table->decimal('reintegro', 10, 2)->default(0)->after('desahucio');
            $table->integer('sueldos_devengados_meses')->default(0)->after('reintegro');
            $table->decimal('sueldos_devengados_monto', 10, 2)->default(0)->after('sueldos_devengados_meses');
            $table->boolean('tomo_vacaciones')->default(false)->after('vacaciones');
            $table->boolean('multa_aguinaldo')->default(false)->after('aguinaldo');
            $table->boolean('multa_30')->default(false)->after('total_finiquito');
            $table->decimal('monto_multa_30', 10, 2)->default(0)->after('multa_30');
            $table->decimal('total_general', 10, 2)->default(0)->after('monto_multa_30');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calculos_finiquito', function (Blueprint $table) {
            $table->dropColumn([
                'reintegro',
                'sueldos_devengados_meses',
                'sueldos_devengados_monto',
                'tomo_vacaciones',
                'multa_aguinaldo',
                'multa_30',
                'monto_multa_30',
                'total_general'
            ]);
        });
    }
};
