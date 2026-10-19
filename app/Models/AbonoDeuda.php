<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbonoDeuda extends Model
{
    protected $table = 'abono_deudas';

    protected $fillable = [
        'deuda_id',
        'cuenta_id',
        'movimiento_id',
        'valor',
        'fecha',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'integer',
            'fecha' => 'date',
        ];
    }

    public function deuda(): BelongsTo
    {
        return $this->belongsTo(Deuda::class);
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    public function movimiento(): BelongsTo
    {
        return $this->belongsTo(Movimiento::class);
    }
}
