<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaAhorro extends Model
{
    protected $table = 'metas_ahorro';

    protected $fillable = [
        'nombre',
        'descripcion',
        'monto_objetivo',
        'fecha_inicio',
        'fecha_objetivo',
        'prioridad',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'monto_objetivo' => 'decimal:2',
            'fecha_inicio' => 'date',
            'fecha_objetivo' => 'date',
        ];
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoAhorro::class, 'meta_ahorro_id');
    }

    public function getTotalAportadoAttribute(): float
    {
        return (float) $this->movimientos()
            ->where('tipo', 'aporte')
            ->sum('monto');
    }

    public function getTotalRetiradoAttribute(): float
    {
        return (float) $this->movimientos()
            ->where('tipo', 'retiro')
            ->sum('monto');
    }

    public function getSaldoAhorradoAttribute(): float
    {
        return $this->total_aportado - $this->total_retirado;
    }

public function getPorcentajeCumplidoAttribute(): float
{
    if ((float) $this->monto_objetivo <= 0) {
        return 0;
    }

    $porcentaje = (
        $this->saldo_ahorrado / (float) $this->monto_objetivo
    ) * 100;

    return round(min(100, max(0, $porcentaje)), 2);
}
    public function getMontoFaltanteAttribute(): float
    {
        return max(
            0,
            (float) $this->monto_objetivo - $this->saldo_ahorrado
        );
    }
}

