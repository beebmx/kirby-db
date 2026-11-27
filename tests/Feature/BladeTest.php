<?php

use Beebmx\KirbyDb\DB;
use Beebmx\KirbyDb\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\Fixtures\Models\User;

/*
|--------------------------------------------------------------------------
| kirby-blade integration
|--------------------------------------------------------------------------
|
| `beebmx/kirby-blade` is optional, so these tests are only registered
| when it's installed:
|
| composer require --dev beebmx/kirby-blade --no-update
| composer update
| composer test -- --group=blade
|
*/

if (! isBladeInstalled()) {
    return;
}

pest()->group('blade');

beforeEach(function () {
    resetDatabase();

    $this->kirby = KirbyPluginsInstance([
        'beebmx.kirby-blade' => ['views' => fixtures('cache/views')],
    ]);

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('password');
        $table->timestamps();
        $table->softDeletes();
    });

    DB::table('users')->insert(['name' => 'John Doe', 'email' => 'john@doe.co', 'password' => 'secret']);
    User::create(['name' => 'Jane Doe', 'email' => 'jane@doe.co', 'password' => 'secret']);
});

it('loads kirby-blade next to kirby-db', function () {
    expect($this->kirby->plugin('beebmx/kirby-blade'))->not->toBeNull()
        ->and($this->kirby->plugin('beebmx/db'))->not->toBeNull();
});

it('renders a Blade template with the Query Builder and Eloquent', function () {
    $html = $this->kirby->template('users')->render(['title' => '<b>Users</b>']);

    expect($html)
        ->toContain('<li>Jane Doe</li>')
        ->toContain('<li>John Doe</li>')
        ->toContain('Total: 2')
        // Blade escapes with Laravel's e() helper, so Kirby's e() helper must be disabled
        ->toContain('&lt;b&gt;Users&lt;/b&gt;');
});

it('keeps kirby-db working after rendering a Blade template', function () {
    $this->kirby->template('users')->render(['title' => 'Users']);

    User::create(['name' => 'Other Doe', 'email' => 'other@doe.co', 'password' => 'secret']);

    expect(DB::table('users')->count())->toBe(3)
        ->and(User::where('name', 'Other Doe')->exists())->toBeTrue();
});

afterEach(function () {
    Schema::dropIfExists('users');
});
