<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cuenta_id')
                ->constrained('cuentas')
                ->restrictOnDelete();
            $table->foreignId('cuenta_destino_id')
                ->nullable()
                ->constrained('cuentas')
                ->restrictOnDelete();
            $table->string('tipo', 20);
            // ingreso, gasto o transferencia

            $table->string('concepto', 150);

            $table->unsignedBigInteger('valor');

            $table->date('fecha');

            $table->string('medio_pago', 30)->nullable();

            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->index(['fecha', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
