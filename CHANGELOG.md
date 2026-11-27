# Changelog

All notable changes to `Database` will be documented in this file.

## 5.0.1 - 2026-11-27

- Republished 5.0.0 with its release notes in the commit message; there are no code changes from 5.0.0

## 5.0.0 - 2026-11-27

- Added support for Kirby 6, keeping Kirby 4 and 5 support (`extra.kirby.compatible` is `^4.0 || ^5.0 || ^6.0`)
- Added support for PHP 8.6
- Added support for `illuminate/*` 13, keeping 10, 11 and 12
- Added compatibility with `beebmx/kirby-blade` 7.x and `beebmx/kirby-scheduler` 2.x; both stay optional
- Added Kirby Blade (`blade` group) and Kirby Scheduler (`scheduler` group) integration tests, registered only when the plugins are installed
- Added tests for the default driver resolution
- Added a compatibility table to the README
- Added the missing documentation of the `beebmx.db.default` option (#5)
- Added the missing documentation of the `KIRBY_HELPER_E` constant, required to avoid the `Cannot redeclare function e()` error
- Added the Schema, pagination and model events usage to the README
- Fixed the namespace in the README, `Beebmx\KirbyDb` instead of `Beebmx\KirbyDB` (#5)
- Fixed the SQLite example driver being used as the default driver when only another driver is set in `beebmx.db.drivers` (#5)
- Changed the default value of `beebmx.db.default` to `null`; when it isn't set, the first driver that's different from the example drivers is used (`sqlite` if there's none)
- Changed `DatabaseManager` to throw an `InvalidArgumentException` when `beebmx.db.default` isn't in `beebmx.db.drivers`, or when there are no drivers
- Added `DatabaseManager::getDefault()`
- Added pagination support (`paginate()`, `simplePaginate()` and `cursorPaginate()`), with the current page, cursor, path and query string resolved from the Kirby request; `illuminate/pagination` is now a dependency
- Fixed the model events and observers not being called; `illuminate/events` is now a dependency
- Fixed the `Call to a member function connection() on null` error when using a model with the `beebmx.db.eloquent` option disabled
- Removed the `beebmx.db.eloquent` option; Eloquent is always booted with the database connections, and the option is ignored if it's still set
- Removed the `$eloquent` argument of the `DatabaseManager` constructor
- Fixed the model events registered before the first query being ignored
- Fixed relative SQLite paths being resolved from the current working directory; they're resolved from the Kirby `base` root (or `index` root)
- Changed the database connections to be created once in `DatabaseManager::boot()`, shared by `DB`, `Schema` and `Model`
- Added `DatabaseManager::boot()`, `instance()` and `setInstance()`, and the `$basePath` argument of the `DatabaseManager` constructor
- Removed the protected `autoloadDatabaseManager()`, `setCapsuleInstance()`, `resolveDatabaseManagerInstance()` and `getFirstCapsuleInstance()` methods of the `BootDatabase` trait, replaced by `bootDatabase()`; `getCapsuleInstance()` is kept
- Changed `DB::__callStatic()` and `Schema::__callStatic()` to have parameter and return types, and fixed their docblocks
- Changed `illuminate/hashing` from a dependency to a development dependency; require it in your project if you use it
- Changed the minimum PHP version to 8.4
- Changed the `test` script to disable Kirby's `e()` and `dump()` helpers with `auto_prepend_file`; the previous approach is available as `test-setup`
- Changed the `App()` test helper to `KirbyInstance()`, because PHP function names are case-insensitive and it replaced Laravel's `app()` helper
- Changed the tests to hash passwords with 4 bcrypt rounds, reducing the test suite time from ~10s to <1s
- Removed `spatie/ray` from the development dependencies
- Upgraded to `pestphp/pest` 5
- Updated GitHub workflow with code style, PHP 8.4–8.6, Kirby 4/5, and Kirby Blade/Scheduler jobs

## 4.1.0 - 2025-08-20

- Changed the plugin prefix from `beebmx.kirby-db` to `beebmx.db`

## 4.0.1 - 2025-06-19

- Fixed the GitHub workflow to run the tests with Pest

## 4.0.0 - 2025-06-19

- Added support for Kirby 5, keeping Kirby 4 support
- Added support for `illuminate/database` 12
- Changed the minimum PHP version to 8.2
- Upgraded to `pestphp/pest` 3

## 3.1.0 - 2024-08-02

- Added support for `illuminate/database` 11

## 3.0.0 - 2023-11-30

- Added support for Kirby 4
- Upgraded to `illuminate/database` 10
- Changed the minimum PHP version to 8.1

## 2.0.0 - 2022-03-22

- Upgraded to `illuminate/database` 9
- Changed the minimum PHP version to 8.0

## 1.0.4 - 2020-06-08

- Fixed the model attributes not being passed to the parent constructor

## 1.0.0 - 2019-01-21

- Initial release
