<?php

namespace Beebmx\KirbyDb\Tests\Fixtures\Models;

use Beebmx\KirbyDb\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}