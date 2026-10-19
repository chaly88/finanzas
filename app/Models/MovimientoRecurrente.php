<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MovimientoRecurrente extends Model
{
    protected $table = 'movimientos_recurrentes';

    protected $fillable = [
        'cuenta_id',
        'categoria_id',
        'tipo',
        'concepto',
        'valor',
        'frecuencia',
        'fecha_inicio',
        'proxima_fecha',
        'fecha_fin',
        'medio_pago',
        'observacion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'integer',
            'fecha_inicio' => 'date',
            'proxima_fecha' => 'date',
            'fecha_fin' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(
            Movimiento::class,
            'movimiento_recurrente_id'
        );
    }
}

