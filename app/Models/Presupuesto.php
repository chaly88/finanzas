<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presupuesto extends Model
{
    protected $fillable = [
        'categoria_id',
        'mes',
        'anio',
        'valor',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'mes' => 'integer',
            'anio' => 'integer',
            'valor' => 'integer',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
}
