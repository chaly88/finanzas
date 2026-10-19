<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metas_ahorro', function (Blueprint $table) {
            $table->id();

            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();

            $table->decimal('monto_objetivo', 15, 2);

            $table->date('fecha_inicio');
            $table->date('fecha_objetivo')->nullable();

            $table->enum('prioridad', ['baja', 'media', 'alta'])
                ->default('media');

            $table->enum('estado', ['activa', 'cumplida', 'cancelada'])
                ->default('activa');

            $table->timestamps();

            $table->index('estado');
            $table->index('fecha_objetivo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas_ahorro');
    }
};
