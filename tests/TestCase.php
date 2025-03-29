<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected User|null $user = null;

    protected function uri($param = null): string
    {
        return '/api/v1' . $param;
    }

    protected function asUser(): void
    {
        $this->user = User::factory()->create();
    }

    protected function createToken(string $name, array $ability): string
    {
        return $this->user->createToken($name, $ability)->plainTextToken;
    }
}
