<?php

namespace Beebmx\KirbyDb\Tests\Feature;

use Beebmx\KirbyDb\DB;
use Beebmx\KirbyDb\Schema;
use Beebmx\KirbyDb\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Hashing\BcryptHasher;
use Kirby\Cms\App;

class DbTest extends TestCase
{
    protected App $kirby;

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
        });
    }

    /** @test */
    public function it_can_returns_the_total_of_records()
    {
        $users = DB::table('users')->get();

        $this->assertNotNull($users);
        $this->assertCount(0, $users);
    }

    /** @test */
    public function it_can_insert_a_record()
    {
        $this->assertCount(0, DB::table('users')->get());

        DB::table('users')->insert([
            'name' => 'John Doe',
            'email' => 'john@doe.co',
            'password' => (new BcryptHasher)->make('password'),
        ]);

        $this->assertCount(1, DB::table('users')->get());
    }

    /** @test */
    public function it_can_update_a_record()
    {
        DB::table('users')->insert([
            'name' => 'John Doe',
            'email' => 'john@doe.co',
            'password' => (new BcryptHasher)->make('password'),
        ]);
        $record = DB::table('users')->first();
        $this->assertEquals('John Doe', $record->name);

        DB::table('users')
            ->where('id', 1)
            ->update([
            'name' => 'Jane Doe',
            'email' => 'jane@doe.co',
        ]);

        $record = DB::table('users')->first();
        $this->assertEquals('Jane Doe', $record->name);
        $this->assertEquals('jane@doe.co', $record->email);
    }


    /** @test */
    public function it_can_delete_a_record()
    {
        DB::table('users')->insert([
            'name' => 'John Doe',
            'email' => 'john@doe.co',
            'password' => (new BcryptHasher)->make('password'),
        ]);
        $this->assertCount(1, DB::table('users')->get());

        DB::table('users')
            ->where('id', 1)
            ->delete();
        $this->assertCount(0, DB::table('users')->get());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (Schema::hasTable('users')) {
            Schema::drop('users');
        }
    }
}
