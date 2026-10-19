<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_recurrentes', function (Blueprint $table) {
            $table->id();

            // Cuenta donde se registrará el ingreso o gasto.
            $table->foreignId('cuenta_id')
                ->constrained('cuentas')
                ->restrictOnDelete();

            // Categoría opcional del movimiento.
            $table->foreignId('categoria_id')
                ->nullable()
                ->constrained('categorias')
                ->restrictOnDelete();

            // ingreso o gasto.
            $table->string('tipo', 20);

            $table->string('concepto', 150);

            // Valores enteros en pesos colombianos.
            $table->unsignedBigInteger('valor');

            // semanal, quincenal, mensual, trimestral o anual.
            $table->string('frecuencia', 20);

            $table->date('fecha_inicio');

            $table->date('proxima_fecha');

            $table->date('fecha_fin')->nullable();

            $table->string('medio_pago', 30)->nullable();

            $table->text('observacion')->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamps();

            // Facilita la consulta de programaciones activas por vencimiento.
            $table->index(
                ['activo', 'proxima_fecha'],
                'mov_rec_activo_proxima_fecha_idx'
            );

            // Facilita las consultas por cuenta.
            $table->index('cuenta_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_recurrentes');
    }
};
