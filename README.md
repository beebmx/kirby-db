# Database for Kirby

[![Latest Stable Version](https://poser.pugx.org/beebmx/kirby-db/v)](//packagist.org/packages/beebmx/kirby-db)
![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/beebmx/kirby-db/tests.yml?branch=main)
[![Total Downloads](http://poser.pugx.org/beebmx/kirby-db/downloads)](https://packagist.org/packages/beebmx/kirby-db)
[![License](https://poser.pugx.org/beebmx/kirby-db/license)](//packagist.org/packages/beebmx/kirby-db)

`Database` use Laravel `illuminate/database` package and enable their features for Kirby.

This package enable Laravel Query Builder, Laravel Eloquent ORM and Laravel Schema for your own Kirby applications.

## Overview

- [1. Compatibility](#compatibility)
- [2. Installation](#installation)
- [3. Usage](#usage)
- [4. Options](#options)
- [5. Driver Examples](#driver-examples)
- [6. Testing](#testing)
- [7. Usage note](#usage-note)

## Compatibility

| Version       | Kirby 3 | Kirby 4 | Kirby 5 | Kirby 6 | PHP  |
|---------------|:-------:|:-------:|:-------:|:-------:|------|
| 5.x           |    ❌    |    ✅    |    ✅    |    ✅    | 8.4+ |
| 4.0.0 – 4.1.0 |    ❌    |    ✅    |    ✅    |    ❌    | 8.2+ |
| 3.0.0 – 3.1.0 |    ❌    |    ✅    |    ❌    |    ❌    | 8.1+ |
| 2.x           |    ✅    |    ❌    |    ❌    |    ❌    | 8.0+ |
| 1.x           |    ✅    |    ❌    |    ❌    |    ❌    | 7.1+ |

> [!NOTE]
> `Database` 5.x runs on PHP 8.4, 8.5 and 8.6, and supports `illuminate/database` 10, 11, 12 and 13.
> On PHP 8.4 or newer, `illuminate/*` 10 and 11 trigger deprecation notices; use `illuminate/*` 12 or 13 instead.

## Installation

### Installation with composer

```ssh
composer require beebmx/kirby-db
```

> [!IMPORTANT]
> There's no support for downloading the plugin manually. Please use Composer to install it.

Since `Database` depends on `illuminate/database`, and it uses some helpers from `illuminate/support`, you need to deactivate the `e()` helper of Kirby globally in your `index.php` file. Otherwise, PHP stops with the error `Cannot redeclare function e()`:

```php
<?php

/*
 * In your index.php file, and before loading Composer's autoloader, add the following line.
 */
const KIRBY_HELPER_E = false;

require __DIR__.'/vendor/autoload.php';

echo (new Kirby)->render();
```

> [!NOTE]
> You can check the Kirby [documentation](https://getkirby.com/docs/reference/templates/helpers#deactivate-a-helper-globally) for more information about helpers.

## Usage

First you need to set your database in your `config/config.php` file:

```php
<?php
return [
    'beebmx.db.default' => 'sqlite',
    'beebmx.db.drivers' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => 'storage/database/database.sqlite',
            'prefix' => ''
        ]
    ],
];
```

For example, to use a MySQL database:

```php
<?php
return [
    'beebmx.db.default' => 'mysql',
    'beebmx.db.drivers' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'database',
            'username' => 'user',
            'password' => '',
        ]
    ],
];
```

> [!IMPORTANT]
> Set `beebmx.db.default` to the driver you want to use. Kirby merges your drivers with the [example drivers](#driver-examples) of the plugin (`sqlite`, `mysql`, `pgsql` and `sqlsrv`).
> Since version `5.0.0`, when `beebmx.db.default` isn't set, `Database` uses the first driver that's different from the examples, so a `config.php` with only a `mysql` driver uses MySQL.
> Before version `5.0.0`, `beebmx.db.default` was `sqlite`, so without it every query used the SQLite example driver.

> [!NOTE]
> A relative SQLite `database` path, like `storage/database/database.sqlite`, is resolved from the Kirby `base` root, or from the `index` root when there's no `base` root.
> Before version `5.0.0`, it was resolved from the current working directory, which can be different in the Kirby CLI or in a `public` folder setup.

> [!NOTE]
> The namespace of the plugin is `Beebmx\KirbyDb` (with a lowercase `b`). `Beebmx\KirbyDB` throws a `Class "Beebmx\KirbyDB\DB" not found` error.

### Usage Query Builder
To use Query Builder:

```php
use Beebmx\KirbyDb\DB;

DB::table('users')->get();
```

All the documentation about Query Builder is in the [official documentation](https://laravel.com/docs/master/queries).

You can use any other driver from `beebmx.db.drivers` by its name (the default driver is the `default` connection):

```php
use Beebmx\KirbyDb\DB;

DB::connection('pgsql')->table('users')->get();
```

### Usage Eloquent ORM

To use Eloquent ORM, first you need to create a `Model`:

```php
use Beebmx\KirbyDb\Model;

class User extends Model {

}

```

Then you can use your new model with:

```php
\User::all();
```

All the documentation about Eloquent ORM is in the [official documentation](https://laravel.com/docs/master/eloquent).

> [!NOTE]
> Extend your models from `Beebmx\KirbyDb\Model`, so they create the database connections when they're used. A model that extends `Illuminate\Database\Eloquent\Model` directly only works after `DB`, `Schema` or a `Beebmx\KirbyDb\Model` were used.

The [model events](https://laravel.com/docs/master/eloquent#events) and [observers](https://laravel.com/docs/master/eloquent#observers) are available too:

```php
\User::creating(function (\User $user) {
    $user->email = strtolower($user->email);
});

\User::observe(\UserObserver::class);
```

### Usage Pagination

The Query Builder and the models can be paginated, and the current page is resolved from the `page` query parameter of the Kirby request (for example `/users?page=2`):

```php
use Beebmx\KirbyDb\DB;

$users = DB::table('users')->paginate(15);

foreach ($users as $user) {
    echo $user->name;
}

// In /users?page=2
echo $users->previousPageUrl(); // https://example.com/users?page=1
echo $users->nextPageUrl(); // https://example.com/users?page=3
```

The `simplePaginate()` and `cursorPaginate()` methods are available too. The `links()` method isn't available, because it renders a Laravel view; build the links with `previousPageUrl()`, `nextPageUrl()` and `url($page)` instead. All the documentation about pagination is in the [official documentation](https://laravel.com/docs/master/pagination).

### Usage Schema

To use Schema:

```php
use Beebmx\KirbyDb\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});
```

All the documentation about Schema is in the [official documentation](https://laravel.com/docs/master/migrations#tables).

## Options

The default values of the package are:

| Option             | Default | Values                                              | Description                                                                                           |
|:-------------------|:--------|:----------------------------------------------------|:------------------------------------------------------------------------------------------------------|
| beebmx.db.default  | null    | `mysql` / `sqlite` / `pgsql` / `sqlsrv` / (string)  | Default driver. When it's not set, the first driver that's different from the examples (or `sqlite`) |
| beebmx.db.drivers  | []      | (array)                                             | Array with all the drivers available                                                                  |

> [!WARNING]
> Since version `5.0.0`, the default value of `beebmx.db.default` is `null` instead of `sqlite`, and `Database` throws an `InvalidArgumentException` when `beebmx.db.default` is a driver that's not in `beebmx.db.drivers`.

> [!WARNING]
> Since version `5.0.0`, the `beebmx.db.eloquent` option was removed, because Eloquent ORM is always enabled. If it's in your `config.php` file, it's ignored, and you can remove it.

> [!WARNING]
> Since version `5.0.0`, `illuminate/hashing` isn't installed with `Database`. If you use it, for example `Illuminate\Hashing\BcryptHasher`, require it in your project with `composer require illuminate/hashing`.

> [!WARNING]
> Since version `4.1.0`, `Database` changes the plugin prefix from `beebmx.kirby-db` to `beebmx.db`.

## Driver Examples

This is an example of the drivers available:

```php
return [
    'beebmx.db.drivers' => [
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

## Testing

```ssh
composer test
```

## Usage note

You can use [Env](https://github.com/beebmx/kirby-env) to hide your credentials from your code.
