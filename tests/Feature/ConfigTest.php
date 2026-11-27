<?php

beforeEach(function () {
    $this->kirby = KirbyInstance();
});

it('loads the configuration', function () {
    expect($this->kirby->option('beebmx.db.default'))->toBeNull()
        ->and($this->kirby->option('beebmx.db.drivers'))->toHaveKey('sqlite');
});

it('has driver configuration', function () {
    expect($this->kirby->option('beebmx.db.drivers.sqlite'))
        ->not->toBeNull()
        ->and($this->kirby->option('beebmx.db.drivers.sqlite.database'))
        ->toEqual(':memory:');
});
