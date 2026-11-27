<?php

namespace Beebmx\KirbyDb;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\Paginator;
use InvalidArgumentException;
use Kirby\Cms\App as Kirby;

class DatabaseManager
{
    protected static ?DatabaseManager $instance = null;

    protected Capsule $db;

    protected string $default;

    protected array $drivers;

    protected ?string $basePath;

    /**
     * Constructor
     */
    public function __construct(array $drivers, ?string $default = null, ?string $basePath = null)
    {
        $this->db = new Capsule;
        $this->drivers = $drivers;
        $this->default = $this->resolveDefault($default);
        $this->basePath = $basePath;

        $this->initialize();
    }

    /**
     * Creates the shared instance from the Kirby options, only once
     */
    public static function boot(): static
    {
        if (static::$instance === null) {
            $kirby = Kirby::instance();

            static::$instance = new static(
                drivers: $kirby->option('beebmx.db.drivers', []),
                default: $kirby->option('beebmx.db.default'),
                basePath: $kirby->roots()->base() ?? $kirby->roots()->index(),
            );
        }

        return static::$instance;
    }

    /**
     * Returns the shared instance, if it was created
     */
    public static function instance(): ?static
    {
        return static::$instance;
    }

    /**
     * Replaces the shared instance (null to create it again with the next query)
     */
    public static function setInstance(?DatabaseManager $instance): void
    {
        static::$instance = $instance;

        $instance?->getCapsule()->setAsGlobal();
        $instance?->getCapsule()->bootEloquent();
    }

    public function getCapsule(): Capsule
    {
        return $this->db;
    }

    /**
     * Returns the name of the default driver
     */
    public function getDefault(): string
    {
        return $this->default;
    }

    /**
     * Initialize options for DatabaseManager
     */
    protected function initialize(): void
    {
        $this->addConnections();
        $this->db->setEventDispatcher(new Dispatcher($this->db->getContainer()));
        $this->db->setAsGlobal();
        $this->db->bootEloquent();

        $this->configurePagination();
    }

    /**
     * Resolves the default driver.
     *
     * When no default driver is set, Kirby still merges the plugin's
     * example drivers (sqlite, mysql, pgsql, sqlsrv) into the user's,
     * so the first driver that differs from those examples is used.
     * If none does, the first driver is used (sqlite by default).
     */
    protected function resolveDefault(?string $default): string
    {
        if (empty($this->drivers)) {
            throw new InvalidArgumentException('There are no database drivers configured. Set them with the [beebmx.db.drivers] option.');
        }

        if (! empty($default)) {
            if (! array_key_exists($default, $this->drivers)) {
                throw new InvalidArgumentException("The default database driver [{$default}] is not configured. Add it to the [beebmx.db.drivers] option or change the [beebmx.db.default] option.");
            }

            return $default;
        }

        $examples = (require dirname(__DIR__).'/extensions/options.php')['drivers'];

        foreach ($this->drivers as $driver => $connection) {
            if (($examples[$driver] ?? null) !== $connection) {
                return $driver;
            }
        }

        return array_key_first($this->drivers);
    }

    /**
     * Add all the connections and set default connection
     */
    protected function addConnections(): void
    {
        foreach ($this->drivers as $driver => $connection) {
            $this->db->addConnection(
                $this->resolveSqlitePath($connection),
                $this->default === $driver ? 'default' : $driver
            );
        }
    }

    /**
     * Resolves a relative SQLite database path from the base path,
     * instead of the current working directory
     */
    protected function resolveSqlitePath(array $connection): array
    {
        $database = $connection['database'] ?? null;

        if (($connection['driver'] ?? null) !== 'sqlite'
            || $this->basePath === null
            || ! is_string($database)
            || $database === ''
            || $database === ':memory:'
            || str_starts_with($database, 'file:')
            || str_contains($database, 'mode=memory')
            || $this->isAbsolutePath($database)) {
            return $connection;
        }

        $connection['database'] = rtrim($this->basePath, '/\\').DIRECTORY_SEPARATOR.$database;

        return $connection;
    }

    protected function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/')
            || str_starts_with($path, '\\')
            || preg_match('/^[a-zA-Z]:[\\\\\/]/', $path) === 1;
    }

    /**
     * Resolves the current page, cursor, path and query string of the paginators from the Kirby request
     */
    protected function configurePagination(): void
    {
        Paginator::currentPathResolver(
            fn (): string => Kirby::instance()->request()->url(['query' => null])->toString()
        );

        Paginator::currentPageResolver(function (string $pageName = 'page'): int {
            $page = Kirby::instance()->request()->get($pageName);

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }

            return 1;
        });

        Paginator::queryStringResolver(
            fn (): array => Kirby::instance()->request()->query()->toArray()
        );

        CursorPaginator::currentCursorResolver(function (string $cursorName = 'cursor'): ?Cursor {
            $cursor = Kirby::instance()->request()->get($cursorName);

            return is_string($cursor) ? Cursor::fromEncoded($cursor) : null;
        });
    }
}
