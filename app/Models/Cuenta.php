<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AbonoDeuda;
class Cuenta extends Model
{
    protected $fillable = [
        'nombre',
        'tipo',
        'saldo_inicial',
        'fecha_apertura',
        'activa',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'saldo_inicial' => 'integer',
            'fecha_apertura' => 'date',
            'activa' => 'boolean',
        ];
    }

    /**
     * Movimientos asociados a esta cuenta,
     * tanto ingresos como gastos y transferencias de salida.
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    /**
     * Transferencias recibidas en esta cuenta.
     */
    public function transferenciasRecibidas(): HasMany
    {
        return $this->hasMany(
            Movimiento::class,
            'cuenta_destino_id'
        );
    }



    public function getSaldoActualAttribute(): int
    {
        $ingresos = $this->movimientos()
            ->where('tipo', 'ingreso')
            ->sum('valor');

        $gastos = $this->movimientos()
            ->where('tipo', 'gasto')
            ->sum('valor');

        $transferenciasEnviadas = $this->movimientos()
            ->where('tipo', 'transferencia')
            ->sum('valor');

        $transferenciasRecibidas = $this->transferenciasRecibidas()
            ->where('tipo', 'transferencia')
            ->sum('valor');

        return $this->saldo_inicial
            + $ingresos
            - $gastos
            - $transferenciasEnviadas
            + $transferenciasRecibidas;
    }

    public function abonosDeudas(): HasMany
    {
        return $this->hasMany(AbonoDeuda::class);
    }

    public function movimientosRecurrentes(): HasMany
    {
        return $this->hasMany(MovimientoRecurrente::class);
    }

}
