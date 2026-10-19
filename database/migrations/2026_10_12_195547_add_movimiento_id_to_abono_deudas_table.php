<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abono_deudas', function (Blueprint $table) {
            $table->foreignId('movimiento_id')
                ->nullable()
                ->unique()
                ->constrained('movimientos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('abono_deudas', function (Blueprint $table) {
            $table->dropForeign(['movimiento_id']);
            $table->dropUnique(['movimiento_id']);
            $table->dropColumn('movimiento_id');
        });
    }
};