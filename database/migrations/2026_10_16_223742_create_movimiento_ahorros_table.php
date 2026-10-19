<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_ahorro', function (Blueprint $table) {
            $table->id();

            $table->foreignId('meta_ahorro_id')
                ->constrained('metas_ahorro')
                ->cascadeOnDelete();

            $table->enum('tipo', ['aporte', 'retiro']);

            $table->decimal('monto', 15, 2);

            $table->date('fecha');

            $table->string('descripcion', 255)->nullable();

            $table->timestamps();

            $table->index(['meta_ahorro_id', 'fecha']);
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_ahorro');
    }
};
