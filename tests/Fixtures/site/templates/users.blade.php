<ul>
@foreach (\Tests\Fixtures\Models\User::orderBy('name')->get() as $user)
    <li>{{ $user->name }}</li>
@endforeach
</ul>
<p>Total: {{ \Beebmx\KirbyDb\DB::table('users')->count() }}</p>
<p>{{ $title }}</p>
