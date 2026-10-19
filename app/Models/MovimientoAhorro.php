<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoAhorro extends Model
{
    protected $table = 'movimientos_ahorro';

    protected $fillable = [
        'meta_ahorro_id',
        'movimiento_id',
        'tipo',
        'monto',
        'fecha',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha' => 'date',
        ];
    }

    public function metaAhorro(): BelongsTo
    {
        return $this->belongsTo(MetaAhorro::class, 'meta_ahorro_id');
    }

    public function movimiento(): BelongsTo
    {
        return $this->belongsTo(Movimiento::class, 'movimiento_id');
    }
}
