# Kirby DB

Kirby DB use Laravel `illuminate/database` package and enable their features for Kirby.

This package enable Laravel Query Builder, Laravel Eloquent ORM and Laravel Schema for your own Kirby applications.

## Installation

### Installation with composer

```ssh
composer require beebmx/kirby-db
```

## Usage

First you need to set your database in your `config/config.php` file:

```php
<?php
return [
    'beebmx.kirby-db.drivers' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => 'storage/database/database.sqlite',
            'prefix' => ''
        ]
    ],
];
```

### Usage Query Builder
To use Query Builder:

```php
use Beebmx\KirbyDB\DB;

DB::table('users')->get();
```

All the documentation about Query Builder is in the [official documentation](https://laravel.com/docs/master/queries).

### Usage Eloquent ORM

To use Eloquent ORM, first you need to create a `Model`:

```php
use Beebmx\KirbyDB\Model;

class User extends Model {

}

```

Then you can use your new model with:

```php
\User::all();
```

All the documentation about Eloquent ORM is in the [official documentation](https://laravel.com/docs/master/eloquent).

## Options

The default values of the package are:

| Option | Default | Values | Description |
|:--|:--|:--|:--|
| beebmx.kirby-db.default | sqlite | `mysql` / `sqlite` / `pgsql` / `sqlsrv` | Default driver |
| beebmx.kirby-db.drivers | [] | (array) | Array with all the drivers available |
| beebmx.kirby-db.eloquent | false | `true` / `false` | Enable Eloquent ORM |

## Driver Examples

This is an example of the drivers available:

```php
return [
    'beebmx.kirby-db.drivers' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => 'storage/database/database.sqlite',
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
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => '127.0.0.1',
            'port' => '5432',
            'database' => 'database',
            'username' => 'user',
            'password' => '',
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => 'localhost',
            'port' => '1433',
            'database' => 'database',
            'username' => 'user',
            'password' => '',
            'charset' => 'utf8',
            'prefix' => '',
        ],
    ],
];
```

## Usage note

You can use [Kirby Env](https://github.com/beebmx/kirby-env) to hide your credentials from your code.
