<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
