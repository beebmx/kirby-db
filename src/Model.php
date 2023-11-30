<?php

namespace Beebmx\KirbyDb;

use Beebmx\KirbyDb\Contracts\BootDatabase;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Model extends Eloquent
{
    use BootDatabase;

    public function __construct(array $attributes = [])
    {
        if (! static::resolveDatabaseManagerInstance()) {
            static::autoloadDatabaseManager();
        } else {
            static::setCapsuleInstance();
        }

        parent::__construct($attributes);
    }
}
