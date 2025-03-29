<?php

namespace Tests\Feature;

use App\Enums\BookingAbilityEnum;
use App\Enums\ResourceAbilityEnum;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingPostTest extends TestCase
{

    use RefreshDatabase;


    public function test_unregistered_user(): void
    {
        $response = $this->post($this->uri('/bookings'));

        $response->assertStatus(401)
            ->assertJsonStructure([
                "error"
            ])->assertExactJson([
                "error" => "Unauthorized"
            ]);
    }

    public function test_user_no_autentificable(): void
    {
        $this->asUser();

        $response = $this->post($this->uri('/bookings'));

        $response->assertStatus(401)
            ->assertJsonStructure([
                "error"
            ])->assertExactJson([
                "error" => "Unauthorized"
            ]);
    }

    public function test_user_has_bag_token(): void
    {
        $this->asUser();

        $token = Str::random(16);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post($this->uri('/bookings'));

        $response->assertStatus(401)
            ->assertJsonStructure([
                "message"
            ])->assertExactJson([
                "message" => "Unauthenticated."
            ]);
    }

    public function test_user_token_cant_post_bookings(): void
    {
        $this->asUser();

        $token = $this->createToken(
            'booking.get',
            [BookingAbilityEnum::BOOKING_SHOW]
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post($this->uri('/bookings'));

        $response->assertStatus(403)->assertJsonStructure([
            "message"
        ])->assertJson([
            "message" => "Invalid ability provided."
        ]);
    }

    public function test_user_autentificable_post_bookings(): void
    {
        $this->asUser();

        $token = $this->createToken('booking.create', [BookingAbilityEnum::BOOKING_CREATE]);

        $resource = Resource::factory()->create();

        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/bookings'),
            [
                'resource_id' => $resource->id,
                'user_id' => $user->id,
                'start_time' => now()->format('d-m-Y H:i:s'),
                'end_time' => now()->addHours(2)->format('d-m-Y H:i:s'),
            ]
        );

        $response->assertStatus(201)
            ->assertJsonMissingValidationErrors(['resource_id', 'user_id', 'start_time', 'end_time'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'Booking created']);

        $this->assertDatabaseHas('bookings', [
            'resource_id' => $resource->id,
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => now()->addHours(2),
        ]);
    }

    public function test_user_autentificable_post_bookings_user_id_required(): void
    {
        $this->asUser();

        $token = $this->createToken('booking.create', [BookingAbilityEnum::BOOKING_CREATE]);

        $resource = Resource::factory()->create();

        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/bookings'),
            [
                'resource_id' => $resource->id,
                'start_time' => now()->format('d-m-Y H:i:s'),
                'end_time' => now()->addHours(2)->format('d-m-Y H:i:s'),
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The user id field is required.']);

        $this->assertDatabaseMissing('bookings', [
            'resource_id' => $resource->id,
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => now()->addHours(2),
        ]);
    }

    public function test_user_autentificable_post_bookings_user_id_integer(): void
    {
        $this->asUser();

        $token = $this->createToken('booking.create', [BookingAbilityEnum::BOOKING_CREATE]);

        $resource = Resource::factory()->create();

        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/bookings'),
            [
                'resource_id' => $resource->id,
                'user_id' => Str::random(2),
                'start_time' => now()->format('d-m-Y H:i:s'),
                'end_time' => now()->addHours(2)->format('d-m-Y H:i:s'),
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The user id field must be an integer.']);

        $this->assertDatabaseMissing('bookings', [
            'resource_id' => $resource->id,
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => now()->addHours(2),
        ]);
    }

    public function test_user_autentificable_post_bookings_user_id_exists(): void
    {
        $this->asUser();

        $token = $this->createToken('booking.create', [BookingAbilityEnum::BOOKING_CREATE]);

        $resource = Resource::factory()->create();

        $user_id = 100;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/bookings'),
            [
                'resource_id' => $resource->id,
                'user_id' => $user_id,
                'start_time' => now()->format('d-m-Y H:i:s'),
                'end_time' => now()->addHours(2)->format('d-m-Y H:i:s'),
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The selected user id is invalid.']);

        $this->assertDatabaseMissing('bookings', [
            'resource_id' => $resource->id,
            'user_id' => $user_id,
            'start_time' => now(),
            'end_time' => now()->addHours(2),
        ]);
    }

    public function test_user_autentificable_post_bookings_resource_id_required(): void
    {
        $this->asUser();

        $token = $this->createToken('booking.create', [BookingAbilityEnum::BOOKING_CREATE]);

        $resource = Resource::factory()->create();

        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/bookings'),
            [
                'user_id' => $user->id,
                'start_time' => now()->format('d-m-Y H:i:s'),
                'end_time' => now()->addHours(2)->format('d-m-Y H:i:s'),
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['resource_id'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The resource id field is required.']);

        $this->assertDatabaseMissing('bookings', [
            'resource_id' => $resource->id,
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => now()->addHours(2),
        ]);
    }

    public function test_user_autentificable_post_bookings_resource_id_integer(): void
    {
        $this->asUser();

        $token = $this->createToken('booking.create', [BookingAbilityEnum::BOOKING_CREATE]);

        $resource = Resource::factory()->create();

        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/bookings'),
            [
                'resource_id' => Str::random(2),
                'user_id' => $user->id,
                'start_time' => now()->format('d-m-Y H:i:s'),
                'end_time' => now()->addHours(2)->format('d-m-Y H:i:s'),
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['resource_id'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The resource id field must be an integer.']);

        $this->assertDatabaseMissing('bookings', [
            'resource_id' => $resource->id,
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => now()->addHours(2),
        ]);
    }

    public function test_user_autentificable_post_bookings_resource_id_exist(): void
    {
        $this->asUser();

        $token = $this->createToken('booking.create', [BookingAbilityEnum::BOOKING_CREATE]);

        $resource = Resource::factory()->create();

        $user = User::factory()->create();

        $resource_id = 100;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/bookings'),
            [
                'resource_id' => $resource_id,
                'user_id' => $user->id,
                'start_time' => now()->format('d-m-Y H:i:s'),
                'end_time' => now()->addHours(2)->format('d-m-Y H:i:s'),
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['resource_id'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The selected resource id is invalid.']);

        $this->assertDatabaseMissing('bookings', [
            'resource_id' =>$resource_id,
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_user_autentificable_post_bookings_start_time_required(): void
    {
        $this->asUser();

        $token = $this->createToken('booking.create', [BookingAbilityEnum::BOOKING_CREATE]);

        $resource = Resource::factory()->create();

        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/bookings'),
            [
                'resource_id' => $resource->id,
                'user_id' => $user->id,
                'end_time' => now()->addHours(2)->format('d-m-Y H:i:s'),
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_time'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The start time field is required.']);

        $this->assertDatabaseMissing('bookings', [
            'resource_id' =>$resource->id,
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_user_autentificable_post_bookings_start_time_format(): void
    {
        $this->asUser();

        $token = $this->createToken('booking.create', [BookingAbilityEnum::BOOKING_CREATE]);

        $resource = Resource::factory()->create();

        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/bookings'),
            [
                'resource_id' => $resource->id,
                'user_id' => $user->id,
                'start_time' => now()->format('Y-m-d H:i:s'),
                'end_time' => now()->addHours(2)->format('d-m-Y H:i:s'),
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_time'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The start time field must match the format d-m-Y H:i:s.']);

        $this->assertDatabaseMissing('bookings', [
            'resource_id' =>$resource->id,
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_user_autentificable_post_bookings_end_time_required(): void
    {
        $this->asUser();

        $token = $this->createToken('booking.create', [BookingAbilityEnum::BOOKING_CREATE]);

        $resource = Resource::factory()->create();

        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/bookings'),
            [
                'resource_id' => $resource->id,
                'user_id' => $user->id,
                'start_time' => now()->format('d-m-Y H:i:s'),
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_time'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The end time field is required.']);

        $this->assertDatabaseMissing('bookings', [
            'resource_id' =>$resource->id,
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_user_autentificable_post_bookings_end_time_format(): void
    {
        $this->asUser();

        $token = $this->createToken('booking.create', [BookingAbilityEnum::BOOKING_CREATE]);

        $resource = Resource::factory()->create();

        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/bookings'),
            [
                'resource_id' => $resource->id,
                'user_id' => $user->id,
                'start_time' => now()->format('d-m-Y H:i:s'),
                'end_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_time'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The end time field must match the format d-m-Y H:i:s.']);

        $this->assertDatabaseMissing('bookings', [
            'resource_id' =>$resource->id,
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_user_autentificable_post_bookings_end_time_incorrect(): void
    {
        $this->asUser();

        $token = $this->createToken('booking.create', [BookingAbilityEnum::BOOKING_CREATE]);

        $resource = Resource::factory()->create();

        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/bookings'),
            [
                'resource_id' => $resource->id,
                'user_id' => $user->id,
                'start_time' => now()->format('d-m-Y H:i:s'),
                'end_time' => now()->subHours(2)->format('d-m-Y H:i:s'),
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_time'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'Data end incorrect']);

        $this->assertDatabaseMissing('bookings', [
            'resource_id' =>$resource->id,
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => now()->subHours(2)->format('Y-m-d H:i:s'),
        ]);
    }
}
