<?php

namespace Beebmx\KirbyDB;

use Beebmx\KirbyDB\BootDatabase;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Model extends Eloquent
{
    use BootDatabase;

    public function __construct(array $attributes = [])
    {
        if (!static::resolveDatabaseManagerInstance()) {
            static::autoloadDatabaseManager();
        }
        parent::__construct($attributes = []);
    }
}
