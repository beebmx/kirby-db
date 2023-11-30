<?php

namespace Beebmx\KirbyDb;

use Illuminate\Database\Capsule\Manager as Capsule;

class DatabaseManager
{
    protected Capsule $db;

    protected string $default;

    protected array $drivers;

    protected bool $eloquent;

    /**
     * Constructor
     */
    public function __construct(array $drivers, string $default, bool $eloquent = false)
    {
        $this->db = new Capsule;
        $this->default = $default;
        $this->drivers = $drivers;
        $this->eloquent = $eloquent;

        $this->initialize();
    }

    public function getCapsule(): Capsule
    {
        return $this->db;
    }

    /**
     * Initialize options for DatabaseManager
     */
    protected function initialize(): void
    {
        $this->addConnections();
        $this->db->setAsGlobal();

        if ($this->eloquent) {
            $this->bootEloquent();
        }
    }

    /**
     * Add all the connections and set default connection
     */
    protected function addConnections(): void
    {
        foreach ($this->drivers as $driver => $connection) {
            $this->db->addConnection($connection, $this->default === $driver ? 'default' : $driver);
        }
    }

    /**
     * Boot Eloquent if it was required
     */
    protected function bootEloquent(): void
    {
        $this->db->bootEloquent();
    }
}
