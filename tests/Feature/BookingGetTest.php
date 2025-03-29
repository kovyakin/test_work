<?php

namespace Tests\Feature;

use App\Enums\BookingAbilityEnum;
use App\Enums\ResourceAbilityEnum;
use App\Models\Bookings;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingGetTest extends TestCase
{

    use RefreshDatabase;


    public function test_unregistered_user(): void
    {
        $resource = Resource::factory()->create();

        $response = $this->get($this->uri('/resources/' . $resource->id . '/bookings'));

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

        $resource = Resource::factory()->create();

        $response = $this->get($this->uri('/resources/' . $resource->id . '/bookings'));

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

        $resource = Resource::factory()->create();

        $token = Str::random(16);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get($this->uri('/resources/' . $resource->id . '/bookings'));

        $response->assertStatus(401)
            ->assertJsonStructure([
                "message"
            ])->assertExactJson([
                "message" => "Unauthenticated."
            ]);
    }

    public function test_user_token_cant_get_bookings(): void
    {
        $this->asUser();

        $resource = Resource::factory()->create();

        $token = $this->createToken(
            'resource.create',
            [ResourceAbilityEnum::RESOURCE_CREATE]
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get($this->uri('/resources/' . $resource->id . '/bookings'));

        $response->assertStatus(403)->assertJsonStructure([
            "message"
        ])->assertJson([
            "message" => "Invalid ability provided."
        ]);
    }

    public function test_user_autentificable_get_bookings(): void
    {
        $this->asUser();

        $booking = Bookings::factory()->create();

        $token = $this->createToken('booking.create', [BookingAbilityEnum::BOOKING_SHOW]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get($this->uri('/resources/' . $booking->resources->id . '/bookings'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])->assertJson(['data' => [$booking->withoutRelations()->toArray()]]);
    }
    public function test_user_autentificable_get_bookings_but_no_data(): void
    {
        $this->asUser();

        $token = $this->createToken('booking.create', [BookingAbilityEnum::BOOKING_SHOW]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get($this->uri('/resources/' .random_int(1,100) . '/bookings'));

        $response->assertStatus(404)
            ->assertJsonStructure([
                'message'
            ]);
    }
}
