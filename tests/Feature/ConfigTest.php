<?php

beforeEach(function () {
    $this->kirby = App(eloquent: false);
});

it('loads the configuration', function () {
    expect($this->kirby->option('beebmx.kirby-db.default'))->toEqual('sqlite');
});

it('has driver configuration', function () {
    expect($this->kirby->option('beebmx.kirby-db.drivers.sqlite'))
        ->not->toBeNull()
        ->and($this->kirby->option('beebmx.kirby-db.drivers.sqlite.database'))
        ->toEqual(':memory:');
});
