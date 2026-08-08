<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'source_id',
    'title',
    'translated_title',
    'summary',
    'translated_summary',
    'url',
    'published_at',
])]

class Article extends Model
{
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function userArticles(): HasMany
    {
        return $this->hasMany(UserArticle::class);
    }
}
