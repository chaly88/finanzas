<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->foreignId('movimiento_recurrente_id')
                ->nullable()
                ->constrained('movimientos_recurrentes')
                ->restrictOnDelete();

            // Evita dos movimientos de la misma programación
            // registrados en una misma fecha.
            $table->unique(
                ['movimiento_recurrente_id', 'fecha'],
                'mov_rec_fecha_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropUnique('mov_rec_fecha_unique');

            $table->dropConstrainedForeignId(
                'movimiento_recurrente_id'
            );
        });
    }
};
