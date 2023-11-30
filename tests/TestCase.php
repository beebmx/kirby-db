<?php

namespace Beebmx\KirbyDb\Tests;

use Kirby\Cms\App;
use PHPUnit\Framework\TestCase as BaseTestCase;

require_once dirname(__DIR__).'/index.php';

abstract class TestCase extends BaseTestCase
{
    protected function setDatabase(bool $eloquent = true): App
    {
        return new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'options' => [
                'beebmx.kirby-db.eloquent' => $eloquent,
                'beebmx.kirby-db.drivers' => [
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
        ]);
    }
}
