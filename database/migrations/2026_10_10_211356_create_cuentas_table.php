
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas', function (Blueprint $table) {
            $table->id();

            $table->string('nombre', 100);
            $table->string('tipo', 30);

            // Valores en pesos colombianos (COP)
            $table->unsignedBigInteger('saldo_inicial')->default(0);

            $table->date('fecha_apertura')->nullable();

            $table->boolean('activa')->default(true);

            $table->text('observacion')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas');
    }
};
