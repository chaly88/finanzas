<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Movimiento extends Model
{

    protected $fillable = [
        'cuenta_id',
        'cuenta_destino_id',
        'tipo',
        'concepto',
        'valor',
        'fecha',
        'medio_pago',
        'observacion',
        'categoria_id',
        'movimiento_recurrente_id',
    ];


    protected function casts(): array
    {
        return [
            'valor' => 'integer',
            'fecha' => 'date',
        ];
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }
    public function cuentaDestino(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_destino_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function abonoDeuda(): HasOne
    {
        return $this->hasOne(AbonoDeuda::class, 'movimiento_id');
    }

    public function movimientoAhorro(): HasOne
    {
        return $this->hasOne(MovimientoAhorro::class, 'movimiento_id');
    }


    public function movimientoRecurrente(): BelongsTo
    {
        return $this->belongsTo(
            MovimientoRecurrente::class,
            'movimiento_recurrente_id'
        );
    }

}
