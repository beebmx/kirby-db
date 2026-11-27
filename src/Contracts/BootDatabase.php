<?php

namespace Beebmx\KirbyDb\Contracts;

use Beebmx\KirbyDb\DatabaseManager;
use Illuminate\Database\Capsule\Manager as Capsule;

trait BootDatabase
{
    /**
     * Creates the database connections from the Kirby options
     * and boots Eloquent, only once
     */
    protected static function bootDatabase(): DatabaseManager
    {
        return DatabaseManager::boot();
    }

    /**
     * Returns the Capsule of the database connections, if they were created
     */
    public static function getCapsuleInstance(): ?Capsule
    {
        return DatabaseManager::instance()?->getCapsule();
    }
}
