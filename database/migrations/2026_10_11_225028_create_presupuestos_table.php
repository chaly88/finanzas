<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presupuestos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('categoria_id')
                ->constrained('categorias')
                ->restrictOnDelete();

            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('anio');

            $table->unsignedBigInteger('valor');

            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->unique(
                ['categoria_id', 'mes', 'anio'],
                'presupuestos_categoria_mes_anio_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuestos');
    }
};
