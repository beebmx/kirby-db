<?php

namespace Beebmx\KirbyDB;

use Beebmx\KirbyDB\BootDatabase;
use Illuminate\Database\Capsule\Manager as Capsule;

class DB
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
        }
        return Capsule::$method(...$args);
    }
}
