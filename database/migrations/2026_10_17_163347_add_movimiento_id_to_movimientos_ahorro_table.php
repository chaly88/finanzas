<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('movimientos_ahorro', function (Blueprint $table) {
            $table->foreignId('movimiento_id')
                ->nullable()
                ->unique()
                ->constrained('movimientos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_ahorro', function (Blueprint $table) {
            $table->dropUnique(['movimiento_id']);
            $table->dropConstrainedForeignId('movimiento_id');
        });
    }
};
