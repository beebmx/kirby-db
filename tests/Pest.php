<?php

use Kirby\Cms\App as Kirby;

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

pest()->extend(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function App(bool $eloquent = true, array $options = []): Kirby
{
    Kirby::$enableWhoops = false;

    return new Kirby([
        'roots' => [
            'index' => '/dev/null',
            'base' => $base = dirname(__DIR__),
        ],
        'options' => [
            'beebmx.kirby-db' => array_merge(
                require dirname(__DIR__).'/extensions/options.php',
                [
                    'eloquent' => $eloquent,
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
