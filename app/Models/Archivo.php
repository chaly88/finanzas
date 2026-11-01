<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Archivo extends Model
{
    protected $fillable = [
        'user_id', 'parent_id', 'name', 'path', 'mime_type', 'extension', 'size', 'is_folder',
    ];

    protected function casts(): array
    {
        return ['is_folder' => 'boolean', 'size' => 'integer'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
