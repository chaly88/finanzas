<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deuda extends Model
{
    protected $fillable = [
        'tipo',
        'acreedor_deudor',
        'concepto',
        'valor_inicial',
        'fecha',
        'fecha_vencimiento',
        'estado',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'valor_inicial' => 'integer',
            'fecha' => 'date',
            'fecha_vencimiento' => 'date',
        ];
    }

    public function abonos(): HasMany
    {
        return $this->hasMany(AbonoDeuda::class);
    }

    /**
     * Calcula el total abonado a la deuda.
     */
    public function getTotalAbonadoAttribute(): int
    {
        if (array_key_exists('abonos_sum_valor', $this->attributes)) {
            return (int) ($this->attributes['abonos_sum_valor'] ?? 0);
        }

        return (int) $this->abonos()->sum('valor');
    }

    /**
     * Calcula el saldo pendiente de la deuda.
     */
    public function getSaldoPendienteAttribute(): int
    {
        return max(
            0,
            (int) $this->valor_inicial - $this->total_abonado
        );
    }

    /**
     * Calcula automáticamente el estado de la deuda.
     *
     * pagada: no tiene saldo pendiente.
     * vencida: tiene saldo pendiente y venció antes de hoy.
     * pendiente: tiene saldo pendiente y no está vencida.
     */
    public function getEstadoCalculadoAttribute(): string
    {
        if ($this->saldo_pendiente === 0) {
            return 'pagada';
        }

        if (
            $this->fecha_vencimiento !== null &&
            $this->fecha_vencimiento->lt(today())
        ) {
            return 'vencida';
        }

        return 'pendiente';
    }
}