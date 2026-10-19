<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deudas', function (Blueprint $table) {
            $table->id();

            $table->string('tipo', 20); // por_pagar o por_cobrar
            $table->string('acreedor_deudor'); // Persona o entidad
            $table->string('concepto');
            $table->unsignedBigInteger('valor_inicial');
            $table->date('fecha');
            $table->date('fecha_vencimiento')->nullable();

            $table->string('estado', 20)->default('pendiente');
            // pendiente, pagada o vencida

            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->index(['tipo', 'estado']);
            $table->index('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deudas');
    }
};