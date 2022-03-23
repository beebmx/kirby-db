<?php

namespace Beebmx\KirbyDb\Tests\Fixtures\Models;

use Beebmx\KirbyDb\Model;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model
{
    protected $guarded = [];

    public function user()
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