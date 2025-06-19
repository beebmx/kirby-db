<?php

use Beebmx\KirbyDb\DB;
use Beebmx\KirbyDb\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Hashing\BcryptHasher;
use Tests\Fixtures\Models\Post;
use Tests\Fixtures\Models\User;

beforeEach(function () {
    $this->kirby = App();

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('password');
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id');
        $table->string('title');
        $table->text('body');
        $table->string('type')->nullable();
        $table->timestamps();
    });

    DB::table('users')->insert(['name' => 'John Doe', 'email' => 'john@doe.co', 'password' => (new BcryptHasher)->make('password')]);
    DB::table('users')->insert(['name' => 'Jane Doe', 'email' => 'jane@doe.co', 'password' => (new BcryptHasher)->make('password')]);
    DB::table('users')->insert(['name' => 'Other Doe', 'email' => 'other@doe.co', 'password' => (new BcryptHasher)->make('password')]);
    DB::table('posts')->insert(['user_id' => 1, 'title' => 'Culpa commodo duis', 'body' => 'Sit duis sunt eiusmod duis anim veniam officia eu.', 'type' => 'news']);
    DB::table('posts')->insert(['user_id' => 1, 'title' => 'Amet duis minim', 'body' => 'Non aute ea excepteur do ipsum aliquip aute velit sunt.', 'type' => 'info']);
    DB::table('posts')->insert(['user_id' => 2, 'title' => 'Officia in deserunt', 'body' => 'Sint ipsum reprehenderit commodo enim id nostrud.', 'type' => 'news']);
});

it('returns all the model resources', function () {
    expect(User::all())
        ->toHaveCount(3)
        ->and(User::get())
        ->toHaveCount(3);
});

it('can create a resource', function () {
    User::create([
        'name' => 'Friend Doe',
        'email' => 'friend@doe.co',
        'password' => (new BcryptHasher)->make('password'),
    ]);

    expect(User::all())->toHaveCount(4);
});

it('can create a resource as object', function () {
    tap(new User, function ($user) {
        $user->name = 'Friend Doe';
        $user->email = 'friend@doe.co';
        $user->password = (new BcryptHasher)->make('password');

        $user->save();
    });

    expect(User::all())
        ->toHaveCount(4);
});

it('can update a resource', function () {
    User::query()
        ->where('id', 1)
        ->update([
            'name' => 'Friend Doe',
            'email' => 'friend@doe.co',
        ]);

    expect(User::find(1)->name)
        ->toEqual('Friend Doe');
});

it('can update a resource as object', function () {
    tap(User::find(1), function ($user) {
        $user->name = 'Friend Doe';
        $user->email = 'friend@doe.co';

        $user->save();
    });

    expect(User::find(1)->name)
        ->toEqual('Friend Doe');
});

it('can delete a resource', function () {
    User::where('id', 1)->delete();

    expect(User::all())
        ->toHaveCount(2);
});

it('can destroy a resource', function () {
    User::destroy([1, 2]);

    expect(User::all())
        ->toHaveCount(1);
});

it('can delete a resource as object', function () {
    tap(User::find(1), function ($user) {
        $user->name = 'Friend Doe';
        $user->email = 'friend@doe.co';

        $user->delete();
    });

    expect(User::all())
        ->toHaveCount(2);
});

it('can use softdeletes', function () {
    User::where('id', 1)->delete();

    expect(User::all())
        ->toHaveCount(2)
        ->and(DB::table('users')->get())
        ->toHaveCount(3);
});

it('can load has many relation', function () {
    $user = User::with('posts')->find(1);

    expect($user->posts)
        ->toHaveCount(2);
});

it('can load belongs to relation', function () {
    $post = Post::with('user')->find(1);

    expect($post->user->name)
        ->toEqual('John Doe');
});

it('can create a resource from relationship', function () {
    expect(Post::where('user_id', 2)->get())->toHaveCount(1);

    $user = User::find(2);

    $user->posts()->create([
        'title' => 'Proident deserunt dolore',
        'body' => 'Cupidatat culpa nisi mollit fugiat dolore officia officia.',
    ]);

    expect(User::with('posts')->find(2)->posts)
        ->toHaveCount(2);
});

it('returns resources from scope', function () {
    expect(Post::news()->get())
        ->toHaveCount(2)
        ->and(Post::info()->get())
        ->toHaveCount(1);
});

afterEach(function () {
    if (Schema::hasTable('users') || Schema::hasTable('posts')) {
        Schema::drop('users');
        Schema::drop('posts');
    }
});
