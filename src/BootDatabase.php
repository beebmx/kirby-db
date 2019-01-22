<?php

namespace Beebmx\KirbyDB;

use Beebmx\KirbyDB\DatabaseManager;
use Kirby\Cms\App as Kirby;

trait BootDatabase
{
    protected static $instance;

    /**
     * Creates a new instance of DatabaseManager
     *
     * @return void
     */
    protected static function autoloadDatabaseManager()
    {
        static::$instance = new DatabaseManager(
                    Kirby::instance()->option('beebmx.kirby-db.drivers'),
                    Kirby::instance()->option('beebmx.kirby-db.default'),
                    Kirby::instance()->option('beebmx.kirby-db.eloquent')
                );
    }

    /**
     * Check if an instance was created
     *
     * @return bool
     */
    protected static function resolveDatabaseManagerInstance()
    {
        return !!static::$instance;
    }
}
