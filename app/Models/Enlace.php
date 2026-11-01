<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enlace extends Model
{
    protected $fillable = [
        'user_id', 'folder_id', 'title', 'url', 'description', 'tags', 'is_folder',
        'is_favorite', 'visits', 'last_visited_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_folder' => 'boolean',
            'is_favorite' => 'boolean',
            'visits' => 'integer',
            'last_visited_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(self::class, 'folder_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'folder_id');
    }
}
