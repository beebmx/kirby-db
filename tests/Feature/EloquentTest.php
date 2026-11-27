<?php

use Beebmx\KirbyDb\DatabaseManager;
use Beebmx\KirbyDb\DB;
use Beebmx\KirbyDb\Model;
use Beebmx\KirbyDb\Schema;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Schema\Blueprint;
use Tests\Fixtures\Models\User;
use Tests\Fixtures\Observers\UserObserver;

beforeEach(function () {
    $this->kirby = KirbyInstance();

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('password');
        $table->timestamps();
        $table->softDeletes();
    });
});

it('creates the database connections once', function () {
    DB::table('users')->count();
    $manager = DatabaseManager::instance();

    User::count();

    expect(DatabaseManager::instance())->toBe($manager)
        ->and(DB::getCapsuleInstance())->toBe($manager->getCapsule())
        ->and(Schema::getCapsuleInstance())->toBe($manager->getCapsule())
        ->and(Model::getCapsuleInstance())->toBe($manager->getCapsule());
});

it('uses the shared instance that was set for the models', function () {
    $manager = new DatabaseManager(['sqlite' => ['driver' => 'sqlite', 'database' => ':memory:']]);
    $manager->getCapsule()->schema()->create('notes', fn (Blueprint $table) => $table->id());

    resetDatabase();
    DB::select('select 1');
    DatabaseManager::setInstance($manager);

    $note = new class extends Model
    {
        protected $table = 'notes';
    };

    expect($note::count())->toBe(0)
        ->and(DB::getCapsuleInstance())->toBe($manager->getCapsule());
});

it('works with the models from the first query', function () {
    User::create(['name' => 'John Doe', 'email' => 'john@doe.co', 'password' => 'secret']);

    expect(User::count())->toBe(1);
});

it('boots Eloquent with the Query Builder', function () {
    DB::table('users')->insert(['name' => 'John Doe', 'email' => 'john@doe.co', 'password' => 'secret']);

    $model = new class extends EloquentModel
    {
        protected $table = 'users';
    };

    expect($model::count())->toBe(1);
});

it('ignores the removed eloquent option', function () {
    $kirby = KirbyInstance(options: ['eloquent' => false]);

    DB::statement('create table notes (id integer primary key, body text)');

    $note = new class extends EloquentModel
    {
        protected $table = 'notes';

        public $timestamps = false;

        protected $guarded = [];
    };

    $note::create(['body' => 'saved']);

    expect($kirby->option('beebmx.db.eloquent'))->toBeFalse()
        ->and($note::count())->toBe(1);
});

it('fires the model events', function () {
    $events = [];

    User::creating(function (User $user) use (&$events) {
        $events[] = "creating {$user->name}";
    });

    User::created(function (User $user) use (&$events) {
        $events[] = "created {$user->name}";
    });

    User::create(['name' => 'John Doe', 'email' => 'john@doe.co', 'password' => 'secret']);

    expect($events)->toBe(['creating John Doe', 'created John Doe']);
});

it('fires the model events registered before the first query', function () {
    resetDatabase();

    $fired = false;

    User::saving(function () use (&$fired) {
        $fired = true;
    });

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('password');
        $table->timestamps();
        $table->softDeletes();
    });

    User::create(['name' => 'John Doe', 'email' => 'john@doe.co', 'password' => 'secret']);

    expect($fired)->toBeTrue();
});

it('can cancel a model event', function () {
    User::creating(fn () => false);

    $user = new User(['name' => 'John Doe', 'email' => 'john@doe.co', 'password' => 'secret']);

    expect($user->save())->toBeFalse()
        ->and(User::count())->toBe(0);
});

it('calls the model observers', function () {
    UserObserver::$events = [];

    User::observe(UserObserver::class);

    $user = User::create(['name' => 'John Doe', 'email' => 'john@doe.co', 'password' => 'secret']);
    $user->update(['name' => 'Jane Doe']);
    $user->delete();

    expect(UserObserver::$events)->toBe(['updated Jane Doe', 'deleted Jane Doe']);
});

afterEach(function () {
    User::flushEventListeners();
    Schema::dropIfExists('users');
});
