<?php

namespace Tests\Fixtures\Models;

use Beebmx\KirbyDb\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeNews(Builder $query): Builder
    {
        return $query->where('type', 'news');
    }

    public function scopeInfo(Builder $query): Builder
    {
        return $query->where('type', 'info');
    }
}
