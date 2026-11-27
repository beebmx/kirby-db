<?php

namespace Beebmx\KirbyDb;

use Beebmx\KirbyDb\Contracts\BootDatabase;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Builder;

/**
 * @mixin Builder
 */
class Schema
{
    use BootDatabase;

    /**
     * Forwards the call to the Illuminate\Database\Schema\Builder
     * of the default connection, for example `Schema::create('users', ...)`
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        static::bootDatabase();

        return Capsule::schema()->$method(...$args);
    }
}
