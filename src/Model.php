<?php

namespace Beebmx\KirbyDb;

use Beebmx\KirbyDb\Contracts\BootDatabase;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    use BootDatabase;

    /**
     * Boots the database and Eloquent before the model
     */
    public function __construct(array $attributes = [])
    {
        static::bootDatabase();

        parent::__construct($attributes);
    }

    /**
     * Boots the database and Eloquent before registering a model event,
     * so `User::creating(...)` and observers work before the first query
     */
    protected static function registerModelEvent($event, $callback)
    {
        static::bootDatabase();

        parent::registerModelEvent($event, $callback);
    }
}
