<?php

use Beebmx\KirbyDb\Schema;
use Illuminate\Database\Schema\Blueprint;

beforeEach(function () {
    $this->kirby = App(eloquent: false);

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('password');
        $table->timestamps();
    });
});

it('can create a table', function () {
    expect(Schema::hasTable('users'))
        ->toBeTrue();
});

it('can delete a table', function () {
    expect(Schema::hasTable('users'))
        ->toBeTrue();

    Schema::drop('users');

    expect(Schema::hasTable('users'))
        ->toBeFalse();
});

afterEach(function () {
    if (Schema::hasTable('users')) {
        Schema::drop('users');
    }
});
