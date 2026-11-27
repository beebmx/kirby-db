<?php

use Beebmx\KirbyDb\DatabaseManager;
use Illuminate\Database\Capsule\Manager as Capsule;
use Kirby\Cms\App as Kirby;

require_once dirname(__DIR__, 2).'/index.php';

beforeEach(function () {
    // DatabaseManager sets its Capsule as global, so the one used by the other tests is restored afterwards
    $this->global = (new ReflectionProperty(Capsule::class, 'instance'))->getValue();
    $this->examples = (require dirname(__DIR__, 2).'/extensions/options.php')['drivers'];
    $this->mysql = [
        'driver' => 'mysql',
        'host' => 'db.example.com',
        'database' => 'finances',
        'username' => 'root',
        'password' => '',
    ];
});

afterEach(function () {
    // The managers of these tests also replace the Eloquent connections, so they're created again
    resetDatabase();
    (new ReflectionProperty(Capsule::class, 'instance'))->setValue(null, $this->global);
});

function defaultDriverOf(DatabaseManager $manager): string
{
    return $manager->getCapsule()->getConnection()->getDriverName();
}

it('uses sqlite when no drivers were customized', function () {
    $manager = new DatabaseManager($this->examples);

    expect($manager->getDefault())->toBe('sqlite')
        ->and(defaultDriverOf($manager))->toBe('sqlite');
});

it('uses the customized driver when no default is set', function () {
    $manager = new DatabaseManager(array_replace($this->examples, ['mysql' => $this->mysql]));

    expect($manager->getDefault())->toBe('mysql')
        ->and(defaultDriverOf($manager))->toBe('mysql');
});

it('uses the first customized driver when no default is set', function () {
    $manager = new DatabaseManager(array_replace($this->examples, [
        'pgsql' => [...$this->examples['pgsql'], 'database' => 'finances'],
        'mysql' => $this->mysql,
    ]));

    expect($manager->getDefault())->toBe('mysql');
});

it('uses a driver that is not in the examples when no default is set', function () {
    $manager = new DatabaseManager(['sqlite' => $this->examples['sqlite'], 'reports' => $this->mysql]);

    expect($manager->getDefault())->toBe('reports')
        ->and(defaultDriverOf($manager))->toBe('mysql');
});

it('uses the default driver when it is set', function () {
    $manager = new DatabaseManager(array_replace($this->examples, ['mysql' => $this->mysql]), 'pgsql');

    expect($manager->getDefault())->toBe('pgsql')
        ->and(defaultDriverOf($manager))->toBe('pgsql');
});

it('keeps the other drivers available by name', function () {
    $manager = new DatabaseManager(array_replace($this->examples, ['mysql' => $this->mysql]));

    expect($manager->getCapsule()->getConnection('sqlite')->getDriverName())->toBe('sqlite');
});

it('throws an exception when the default driver is not configured', function () {
    new DatabaseManager(['mysql' => $this->mysql], 'sqlite');
})->throws(InvalidArgumentException::class, 'The default database driver [sqlite] is not configured.');

it('throws an exception when there are no drivers', function () {
    new DatabaseManager([]);
})->throws(InvalidArgumentException::class, 'There are no database drivers configured.');

it('uses the only driver set in the Kirby config without a default', function () {
    Kirby::$enableWhoops = false;

    // Kirby merges the plugin's example drivers into the ones from the config
    $kirby = new Kirby([
        'roots' => ['index' => '/dev/null'],
        'options' => ['beebmx.db.drivers' => ['mysql' => $this->mysql]],
    ]);

    $manager = new DatabaseManager(
        $kirby->option('beebmx.db.drivers'),
        $kirby->option('beebmx.db.default'),
    );

    expect($kirby->option('beebmx.db.drivers'))->toHaveKeys(['sqlite', 'mysql'])
        ->and($manager->getDefault())->toBe('mysql')
        ->and(defaultDriverOf($manager))->toBe('mysql');
});

it('uses the default driver set in the Kirby config', function () {
    Kirby::$enableWhoops = false;

    $kirby = new Kirby([
        'roots' => ['index' => '/dev/null'],
        'options' => [
            'beebmx.db.default' => 'sqlite',
            'beebmx.db.drivers' => ['mysql' => $this->mysql],
        ],
    ]);

    $manager = new DatabaseManager(
        $kirby->option('beebmx.db.drivers'),
        $kirby->option('beebmx.db.default'),
    );

    expect($manager->getDefault())->toBe('sqlite');
});
