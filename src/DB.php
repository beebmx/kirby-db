<?php

namespace Beebmx\KirbyDb;

use Beebmx\KirbyDb\Contracts\BootDatabase;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;

/**
 * @mixin Capsule
 * @mixin Connection
 */
class DB
{
    use BootDatabase;

    /**
     * Forwards the call to Illuminate\Database\Capsule\Manager
     * (and to the default connection), for example `DB::table('users')`
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        static::bootDatabase();

        return Capsule::$method(...$args);
    }
}
