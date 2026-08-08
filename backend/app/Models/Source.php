<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'feed_url',
    'site_url',
    'is_active',
    'last_success_at',
    'last_error_at',
    'last_error_message',
])]

class Source extends Model
{
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
