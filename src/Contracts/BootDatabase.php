<?php

namespace Beebmx\KirbyDb\Contracts;

use Beebmx\KirbyDb\DatabaseManager;
use Beebmx\KirbyDb\DB;
use Beebmx\KirbyDb\Model;
use Beebmx\KirbyDb\Schema;
use Illuminate\Database\Capsule\Manager as Capsule;
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
        static::$instance = (new DatabaseManager(
            Kirby::instance()->option('beebmx.kirby-db.drivers'),
            Kirby::instance()->option('beebmx.kirby-db.default'),
            Kirby::instance()->option('beebmx.kirby-db.eloquent')
        ))->getCapsule();
    }

    protected static function setCapsuleInstance()
    {
        static::$instance = static::getFirstCapsuleInstance();
    }

    /**
     * Check if an instance was created
     */
    protected static function resolveDatabaseManagerInstance(): bool
    {
        return static::getFirstCapsuleInstance() instanceof Capsule;
    }

    protected static function getFirstCapsuleInstance(): ?Capsule
    {
        if (Schema::getCapsuleInstance()) {
            return Schema::getCapsuleInstance();
        }

        if (DB::getCapsuleInstance()) {
            return DB::getCapsuleInstance();
        }

        if (Model::getCapsuleInstance()) {
            return Model::getCapsuleInstance();
        }

        return null;
    }

    public static function getCapsuleInstance(): ?Capsule
    {
        return static::$instance;
    }
}
