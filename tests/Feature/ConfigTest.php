<?php

namespace Beebmx\KirbyDb\Tests\Feature;

use Beebmx\KirbyDb\Tests\TestCase;
use Kirby\Cms\App;

class ConfigTest extends TestCase
{
    protected App $kirby;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kirby = $this->setDatabase($eloquent = false);
    }

    /** @test */
    public function it_loads_the_configuration()
    {
        $this->assertEquals('sqlite',
            kirby()->option('beebmx.kirby-db.default')
        );
    }

    /** @test */
    public function it_has_driver_configuration()
    {
        $this->assertNotNull(
            $this->kirby->option('beebmx.kirby-db.drivers.sqlite')
        );

        $this->assertEquals(
            ':memory:',
            $this->kirby->option('beebmx.kirby-db.drivers.sqlite.database')
        );
    }
}
