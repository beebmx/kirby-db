<?php

use Beebmx\KirbyDb\DatabaseManager;
use Kirby\Cms\App as Kirby;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
| Don't name a helper `app()` (or `App()`, PHP function names are case-insensitive),
| the Illuminate packages call Laravel's `app()` helper when it exists.
|
*/

/**
 * Creates a Kirby instance without the optional plugins loaded,
 * even when they're installed
 */
function KirbyInstance(array $options = [], array $request = []): Kirby
{
    optionalPlugins(loaded: false);
    resetDatabase();

    Kirby::$enableWhoops = false;

    return new Kirby([
        'roots' => [
            'index' => '/dev/null',
            'base' => $base = dirname(__DIR__),
        ],
        'request' => $request,
        'options' => [
            'beebmx.db' => array_merge(
                require dirname(__DIR__).'/extensions/options.php',
                [
                    'drivers' => [
                        'sqlite' => [
                            'driver' => 'sqlite',
                            'database' => ':memory:',
                            'prefix' => '',
                        ],
                        'mysql' => [
                            'driver' => 'mysql',
                            'host' => '127.0.0.1',
                            'port' => '3306',
                            'database' => 'database',
                            'username' => 'user',
                            'password' => '',
                            'unix_socket' => '',
                            'charset' => 'utf8mb4',
                            'collation' => 'utf8mb4_unicode_ci',
                            'prefix' => '',
                            'strict' => true,
                            'engine' => null,
                        ],
                    ],
                ],
                $options
            ),
        ],
    ]);
}

/**
 * Creates a Kirby instance with kirby-db and the optional plugins
 * installed by Composer in `tests/Fixtures/site/plugins`
 * (`beebmx/kirby-blade`, `beebmx/kirby-scheduler`) loaded
 */
function KirbyPluginsInstance(array $options = []): Kirby
{
    require_once dirname(__DIR__).'/index.php';

    optionalPlugins(loaded: true);

    Kirby::$enableWhoops = false;

    // The plugins load their own `vendor/autoload.php` with `@include_once`,
    // which doesn't exist when Composer installs them, so the warnings
    // suppressed with `@` are ignored and any other goes to PHPUnit
    $previous = set_error_handler(function (int $level, string ...$error) use (&$previous): bool {
        if ((error_reporting() & $level) === 0) {
            return true;
        }

        return $previous !== null && $previous($level, ...$error) !== false;
    });

    try {
        return createKirbyWithPlugins($options);
    } finally {
        restore_error_handler();
    }
}

function createKirbyWithPlugins(array $options): Kirby
{
    return new Kirby([
        'roots' => [
            'index' => '/dev/null',
            'base' => $base = fixtures(''),
            'cache' => $base.'/cache',
            'plugins' => $base.'/site/plugins',
            'templates' => $base.'/site/templates',
        ],
        'options' => array_merge([
            'beebmx.db' => [
                'drivers' => [
                    'sqlite' => [
                        'driver' => 'sqlite',
                        'database' => ':memory:',
                        'prefix' => '',
                    ],
                ],
            ],
        ], $options),
    ]);
}

/**
 * Kirby registers plugins once per process, so the optional plugins
 * are stashed to keep them out of the tests that don't use them
 */
function optionalPlugins(bool $loaded): void
{
    static $stash = [];

    Closure::bind(static function () use ($loaded, &$stash): void {
        foreach (['beebmx/kirby-blade', 'beebmx/scheduler'] as $name) {
            if ($loaded === false && isset(static::$plugins[$name])) {
                $stash[$name] = static::$plugins[$name];
                unset(static::$plugins[$name]);
            }

            if ($loaded === true && isset($stash[$name])) {
                static::$plugins[$name] = $stash[$name];
            }
        }
    }, null, Kirby::class)();
}

/**
 * The database connections are created once per process (with the first query),
 * so they're created again with the options of the next Kirby instance
 */
function resetDatabase(): void
{
    DatabaseManager::setInstance(null);
}

function isBladeInstalled(): bool
{
    return class_exists('Beebmx\\KirbyBlade\\Application');
}

function isSchedulerInstalled(): bool
{
    return class_exists('Beebmx\\KirbyScheduler\\Schedule');
}

function fixtures(string $path): string
{
    return rtrim(__DIR__.'/Fixtures/'.$path, '/');
}
