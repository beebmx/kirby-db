<?php

use Beebmx\KirbyDb\DatabaseManager;
use Beebmx\KirbyDb\DB;
use Illuminate\Database\Capsule\Manager as Capsule;
use Kirby\Cms\App as Kirby;
use Kirby\Filesystem\Dir;

beforeEach(function () {
    $this->global = (new ReflectionProperty(Capsule::class, 'instance'))->getValue();
    $this->base = sys_get_temp_dir().'/kirby-db-'.bin2hex(random_bytes(4));
    $this->cwd = getcwd();

    Dir::make($this->base.'/storage');
});

afterEach(function () {
    chdir($this->cwd);
    resetDatabase();
    Dir::remove($this->base);
    (new ReflectionProperty(Capsule::class, 'instance'))->setValue(null, $this->global);
});

function sqliteDatabaseOf(string $database, ?string $basePath): string
{
    $manager = new DatabaseManager(
        drivers: ['sqlite' => ['driver' => 'sqlite', 'database' => $database, 'prefix' => '']],
        basePath: $basePath,
    );

    return $manager->getCapsule()->getConnection()->getConfig('database');
}

it('resolves a relative sqlite path from the base path', function () {
    expect(sqliteDatabaseOf('storage/database.sqlite', '/var/www/site'))
        ->toBe('/var/www/site'.DIRECTORY_SEPARATOR.'storage/database.sqlite')
        ->and(sqliteDatabaseOf('storage/database.sqlite', '/var/www/site/'))
        ->toBe('/var/www/site'.DIRECTORY_SEPARATOR.'storage/database.sqlite');
});

it('keeps the sqlite paths that are not relative', function (string $database) {
    expect(sqliteDatabaseOf($database, '/var/www/site'))->toBe($database);
})->with([
    'absolute' => '/var/data/database.sqlite',
    'windows' => 'C:\\data\\database.sqlite',
    'windows forward slashes' => 'C:/data/database.sqlite',
    'memory' => ':memory:',
    'uri' => 'file:database.sqlite?mode=ro',
]);

it('keeps the relative sqlite path without a base path', function () {
    expect(sqliteDatabaseOf('storage/database.sqlite', null))->toBe('storage/database.sqlite');
});

it('does not change the other drivers', function () {
    $manager = new DatabaseManager(
        drivers: ['mysql' => ['driver' => 'mysql', 'host' => '127.0.0.1', 'database' => 'storage/database']],
        basePath: '/var/www/site',
    );

    expect($manager->getCapsule()->getConnection()->getConfig('database'))->toBe('storage/database');
});

it('uses the Kirby base root, whatever the working directory is', function () {
    touch($this->base.'/storage/database.sqlite');
    chdir(sys_get_temp_dir());
    resetDatabase();
    Kirby::$enableWhoops = false;

    new Kirby([
        'roots' => ['index' => $this->base.'/public', 'base' => $this->base],
        'options' => ['beebmx.db.drivers' => ['sqlite' => ['driver' => 'sqlite', 'database' => 'storage/database.sqlite', 'prefix' => '']]],
    ]);

    DB::statement('create table notes (id integer primary key, body text)');
    DB::table('notes')->insert(['body' => 'saved']);

    expect(DB::connection()->getConfig('database'))->toBe($this->base.DIRECTORY_SEPARATOR.'storage/database.sqlite')
        ->and(filesize($this->base.'/storage/database.sqlite'))->toBeGreaterThan(0);
});

it('uses the Kirby index root without a base root', function () {
    touch($this->base.'/storage/database.sqlite');
    resetDatabase();
    Kirby::$enableWhoops = false;

    new Kirby([
        'roots' => ['index' => $this->base],
        'options' => ['beebmx.db.drivers' => ['sqlite' => ['driver' => 'sqlite', 'database' => 'storage/database.sqlite', 'prefix' => '']]],
    ]);

    expect(DB::connection()->getConfig('database'))->toBe($this->base.DIRECTORY_SEPARATOR.'storage/database.sqlite')
        ->and(DB::select('select 1 as one')[0]->one)->toBe(1);
});
