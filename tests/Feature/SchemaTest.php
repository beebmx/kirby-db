<?php

namespace Beebmx\KirbyDb\Tests\Feature;

use Beebmx\KirbyDb\Schema;
use Beebmx\KirbyDb\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Kirby\Cms\App;

class SchemaTest extends TestCase
{
    protected App $kirby;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kirby = $this->setDatabase($eloquent = false);

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_can_create_a_table()
    {
        $this->assertTrue(Schema::hasTable('users'));
    }

    /** @test */
    public function it_can_delete_a_table()
    {
        $this->assertTrue(Schema::hasTable('users'));

        Schema::drop('users');
        $this->assertFalse(Schema::hasTable('users'));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (Schema::hasTable('users')) {
            Schema::drop('users');
        }
    }
}
