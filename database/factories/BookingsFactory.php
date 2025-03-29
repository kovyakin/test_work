<?php

namespace Database\Factories;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bookings>
 */
class BookingsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $resource = Resource::factory()->create();

        $user = User::factory()->create();

        return [
            'resource_id' => $resource->id,
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => now()->addHours(2),
        ];
    }
}
