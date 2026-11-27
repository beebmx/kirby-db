<?php

namespace Tests\Fixtures\Observers;

use Tests\Fixtures\Models\User;

class UserObserver
{
    public static array $events = [];

    public function updated(User $user): void
    {
        static::$events[] = "updated {$user->name}";
    }

    public function deleted(User $user): void
    {
        static::$events[] = "deleted {$user->name}";
    }
}
