<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function login(User $user = null): User
    {
        $user = $user ?? User::factory()->create();

        $this->actingAs($user);

        return $user;
    }
}
