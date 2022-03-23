<?php

namespace Beebmx\KirbyDb\Tests\Feature;

use Beebmx\KirbyDb\DB;
use Beebmx\KirbyDb\Schema;
use Beebmx\KirbyDb\Tests\Fixtures\Models\Post;
use Beebmx\KirbyDb\Tests\Fixtures\Models\User;
use Beebmx\KirbyDb\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Hashing\BcryptHasher;

class ModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->kirby = $this->setDatabase();

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
    }

    /** @test */
    public function it_returns_all_the_model_resources()
    {
        $this->assertCount(3, User::all());
        $this->assertCount(3, User::get());
    }

    /** @test */
    public function it_can_create_a_resource()
    {
        User::create([
            'name' => 'Friend Doe',
            'email' => 'friend@doe.co',
            'password' => (new BcryptHasher)->make('password')
        ]);

        $this->assertCount(4, User::all());
    }

    /** @test */
    public function it_can_create_a_resource_as_object()
    {
        tap(new User, function($user) {
            $user->name = 'Friend Doe';
            $user->email = 'friend@doe.co';
            $user->password = (new BcryptHasher)->make('password');

            $user->save();
        });

        $this->assertCount(4, User::all());
    }

    /** @test */
    public function it_can_update_a_resource()
    {
        User::query()
            ->where('id', 1)
            ->update([
                'name' => 'Friend Doe',
                'email' => 'friend@doe.co',
            ]);

        $this->assertEquals('Friend Doe', User::find(1)->name);
    }

    /** @test */
    public function it_can_update_a_resource_as_object()
    {
        tap(User::find(1), function($user) {
            $user->name = 'Friend Doe';
            $user->email = 'friend@doe.co';

            $user->save();
        });

        $this->assertEquals('Friend Doe', User::find(1)->name);
    }

    /** @test */
    public function it_can_delete_a_resource()
    {
        User::where('id', 1)->delete();

        $this->assertCount(2, User::all());
    }

    /** @test */
    public function it_can_destroy_a_resource()
    {
        User::destroy([1, 2]);

        $this->assertCount(1, User::all());
    }

    /** @test */
    public function it_can_delete_a_resource_as_object()
    {
        tap(User::find(1), function($user) {
            $user->name = 'Friend Doe';
            $user->email = 'friend@doe.co';

            $user->delete();
        });

        $this->assertCount(2, User::all());
    }

    /** @test */
    public function it_can_use_softdeletes()
    {
        User::where('id', 1)->delete();

        $this->assertCount(2, User::all());
        $this->assertCount(3, DB::table('users')->get());
    }

    /** @test */
    public function it_can_load_hasMany_relation()
    {
        $user = User::with('posts')->find(1);

        $this->assertCount(2, $user->posts);
    }

    /** @test */
    public function it_can_load_belongsTo_relation()
    {
        $post = Post::with('user')->find(1);

        $this->assertEquals('John Doe', $post->user->name);
    }

    /** @test */
    public function it_can_create_a_resource_from_relationship()
    {
        $this->assertCount(1, Post::where('user_id', 2)->get());

        $user = User::find(2);

        $user->posts()->create([
            'title' => 'Proident deserunt dolore',
            'body' => 'Cupidatat culpa nisi mollit fugiat dolore officia officia.',
        ]);

        $this->assertCount(2, User::with('posts')->find(2)->posts);
    }

    /** @test */
    public function it_returns_resources_from_scope()
    {
        $this->assertCount(2, Post::news()->get());
        $this->assertCount(1, Post::info()->get());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (Schema::hasTable('users') || Schema::hasTable('posts')) {
            Schema::drop('users');
            Schema::drop('posts');
        }
    }
}
