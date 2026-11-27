<?php

use Beebmx\KirbyDb\DB;
use Beebmx\KirbyDb\Schema;
use Beebmx\KirbyScheduler\Schedule;
use Illuminate\Container\Container;
use Illuminate\Database\Schema\Blueprint;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\Fixtures\Models\User;

/*
|--------------------------------------------------------------------------
| kirby-scheduler integration
|--------------------------------------------------------------------------
|
| `beebmx/kirby-scheduler` is optional, so these tests are only registered
| when it's installed:
|
| composer require --dev beebmx/kirby-scheduler --no-update
| composer update
| composer test -- --group=scheduler
|
*/

if (! isSchedulerInstalled()) {
    return;
}

pest()->group('scheduler');

beforeEach(function () {
    // Every test starts with a fresh scheduler and container (as kirby-scheduler does)
    Schedule::destroy();
    Container::setInstance(null);

    // kirby-db boots with the first query after it, the tests create their own Kirby instance after it
    resetDatabase();
    KirbyPluginsInstance();

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('password');
        $table->timestamps();
        $table->softDeletes();
    });
});

it('loads kirby-scheduler next to kirby-db', function () {
    $kirby = KirbyPluginsInstance();

    expect($kirby->plugin('beebmx/scheduler'))->not->toBeNull()
        ->and($kirby->plugin('beebmx/db'))->not->toBeNull();
});

it('runs scheduled tasks that use the Query Builder and Eloquent', function () {
    KirbyPluginsInstance([
        'beebmx.scheduler.schedule' => function (Schedule $schedule) {
            $schedule->call(fn () => DB::table('users')->insert(['name' => 'John Doe', 'email' => 'john@doe.co', 'password' => 'secret']))
                ->everyMinute()
                ->name('query-builder');

            $schedule->call(fn () => User::create(['name' => 'Jane Doe', 'email' => 'jane@doe.co', 'password' => 'secret']))
                ->everyMinute()
                ->name('eloquent');
        },
    ]);

    Schedule::instance()->run(new BufferedOutput);

    expect(Schedule::instance()->failures())->toBeEmpty()
        ->and(DB::table('users')->count())->toBe(2)
        ->and(User::pluck('name')->sort()->values()->all())->toBe(['Jane Doe', 'John Doe']);
});

it('keeps kirby-db working after the scheduler runs', function () {
    KirbyPluginsInstance([
        'beebmx.scheduler.schedule' => fn (Schedule $schedule) => $schedule->call(fn () => true)->everyMinute(),
    ]);

    Schedule::instance()->run(new BufferedOutput);

    User::create(['name' => 'Jane Doe', 'email' => 'jane@doe.co', 'password' => 'secret']);

    expect(User::count())->toBe(1);
});

afterEach(function () {
    Schedule::destroy();
    Container::setInstance(null);
    Schema::dropIfExists('users');
});
