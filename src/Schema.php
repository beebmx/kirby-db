<?php

namespace Beebmx\KirbyDb;

use Beebmx\KirbyDb\Contracts\BootDatabase;
use Illuminate\Database\Capsule\Manager as Capsule;

class Schema
{
    use BootDatabase;

    /**
     * Returns a new Illuminate\Database\Capsule\Manager Object
     *
     * @param [type] $method
     * @param [type] $args
     * @return Illuminate\Database\Capsule\Manager::class
     */
    public static function __callStatic($method, $args)
    {
        if (!static::resolveDatabaseManagerInstance()) {
            static::autoloadDatabaseManager();
        } else {
            static::setCapsuleInstance();
        }

        return Capsule::schema()->$method(...$args);
    }
}
