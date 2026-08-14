<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(
    'user_id',
    'article_id',
    'is_favorite',
    'is_read',
    'is_read_later',
    'memo',
    'read_at',
)]

class UserArticle extends Model
{
    protected function casts(): array
    {
        return [
            'is_favorite' => 'boolean',
            'is_read' => 'boolean',
            'is_read_later' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
