<?php

use Beebmx\KirbyDb\DB;
use Beebmx\KirbyDb\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Tests\Fixtures\Models\User;

function paginationKirby(array $query = []): void
{
    KirbyInstance(request: ['query' => $query, 'url' => 'https://example.com/users']);

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('password');
        $table->timestamps();
        $table->softDeletes();
    });

    foreach (range(1, 5) as $number) {
        DB::table('users')->insert(['name' => "User {$number}", 'email' => "user{$number}@doe.co", 'password' => 'secret']);
    }
}

it('paginates the Query Builder', function () {
    paginationKirby();

    $users = DB::table('users')->paginate(2);

    expect($users)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($users->total())->toBe(5)
        ->and($users->currentPage())->toBe(1)
        ->and($users->lastPage())->toBe(3)
        ->and(collect($users->items())->pluck('name')->all())->toBe(['User 1', 'User 2']);
});

it('paginates the models', function () {
    paginationKirby(['page' => '2']);

    $users = User::orderBy('id')->paginate(2);

    expect($users->currentPage())->toBe(2)
        ->and($users->pluck('name')->all())->toBe(['User 3', 'User 4']);
});

it('resolves the current page from the Kirby request', function () {
    paginationKirby(['page' => '3']);

    $users = DB::table('users')->paginate(2);

    expect($users->currentPage())->toBe(3)
        ->and(collect($users->items())->pluck('name')->all())->toBe(['User 5']);
});

it('uses the first page when the page is not valid', function (string $page) {
    paginationKirby(['page' => $page]);

    expect(DB::table('users')->paginate(2)->currentPage())->toBe(1);
})->with(['0', '-1', 'abc', '1.5']);

it('uses a custom page name', function () {
    paginationKirby(['p' => '2']);

    expect(DB::table('users')->paginate(2, pageName: 'p')->currentPage())->toBe(2);
});

it('resolves the urls from the Kirby request', function () {
    paginationKirby(['page' => '2', 'sort' => 'name']);

    $users = DB::table('users')->paginate(2)->withQueryString();

    expect($users->nextPageUrl())->toBe('https://example.com/users?sort=name&page=3')
        ->and($users->previousPageUrl())->toBe('https://example.com/users?sort=name&page=1');
});

it('simple paginates the Query Builder', function () {
    paginationKirby(['page' => '2']);

    $users = DB::table('users')->simplePaginate(2);

    expect($users)->toBeInstanceOf(Paginator::class)
        ->and($users->currentPage())->toBe(2)
        ->and($users->hasMorePages())->toBeTrue();
});

it('cursor paginates with the cursor from the Kirby request', function () {
    paginationKirby();

    $first = DB::table('users')->orderBy('id')->cursorPaginate(2);
    $cursor = $first->nextCursor()->encode();

    paginationKirby(['cursor' => $cursor]);

    $second = DB::table('users')->orderBy('id')->cursorPaginate(2);

    expect($first)->toBeInstanceOf(CursorPaginator::class)
        ->and(collect($second->items())->pluck('name')->all())->toBe(['User 3', 'User 4']);
});

afterEach(function () {
    Schema::dropIfExists('users');
});
