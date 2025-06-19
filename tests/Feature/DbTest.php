<?php

use Beebmx\KirbyDb\DB;
use Beebmx\KirbyDb\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Hashing\BcryptHasher;

beforeEach(function () {
    $this->kirby = App();

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('password');
        $table->timestamps();
    });
});

it('can returns the total of records', function () {
    $users = DB::table('users')->get();

    expect($users)
        ->not->toBeNull()
        ->toHaveCount(0);
});

it('can insert a record', function () {
    expect(DB::table('users')->get())->toHaveCount(0);

    DB::table('users')->insert([
        'name' => 'John Doe',
        'email' => 'john@doe.co',
        'password' => (new BcryptHasher)->make('password'),
    ]);

    expect(DB::table('users')->get())
        ->toHaveCount(1);
});

it('can update a record', function () {
    DB::table('users')->insert([
        'name' => 'John Doe',
        'email' => 'john@doe.co',
        'password' => (new BcryptHasher)->make('password'),
    ]);
    $record = DB::table('users')->first();

    expect($record->name)
        ->toEqual('John Doe');

    DB::table('users')
        ->where('id', 1)
        ->update([
            'name' => 'Jane Doe',
            'email' => 'jane@doe.co',
        ]);

    $record = DB::table('users')->first();

    expect($record->name)
        ->toEqual('Jane Doe')
        ->and($record->email)
        ->toEqual('jane@doe.co');
});

it('can delete a record', function () {
    DB::table('users')->insert([
        'name' => 'John Doe',
        'email' => 'john@doe.co',
        'password' => (new BcryptHasher)->make('password'),
    ]);

    expect(DB::table('users')->get())
        ->toHaveCount(1);

    DB::table('users')
        ->where('id', 1)
        ->delete();

    expect(DB::table('users')->get())
        ->toHaveCount(0);
});

afterEach(function () {
    if (Schema::hasTable('users')) {
        Schema::drop('users');
    }
});
