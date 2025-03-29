<?php

namespace Tests\Feature;

use App\Enums\BookingAbilityEnum;
use App\Enums\ResourceAbilityEnum;
use App\Models\Bookings;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingDestroyTest extends TestCase
{

    use RefreshDatabase;


    public function test_unregistered_user(): void
    {
        $booking = Bookings::factory()->create();

        $response = $this->delete($this->uri('/bookings/'.$booking->id));

        $response->assertStatus(401)
            ->assertJsonStructure([
                "error"
            ])->assertExactJson([
                "error" => "Unauthorized"
            ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
        ]);
    }

    public function test_user_no_autentificable(): void
    {
        $this->asUser();

        $booking = Bookings::factory()->create();

        $response = $this->delete($this->uri('/bookings/'.$booking->id));

        $response->assertStatus(401)
            ->assertJsonStructure([
                "error"
            ])->assertExactJson([
                "error" => "Unauthorized"
            ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
        ]);
    }

    public function test_user_has_bag_token(): void
    {
        $this->asUser();

        $booking = Bookings::factory()->create();

        $token = Str::random(16);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->delete($this->uri('/bookings/'.$booking->id));

        $response->assertStatus(401)
            ->assertJsonStructure([
                "message"
            ])->assertExactJson([
                "message" => "Unauthenticated."
            ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
        ]);
    }

    public function test_user_token_cant_destroy_bookings(): void
    {
        $this->asUser();

        $booking = Bookings::factory()->create();

        $token = $this->createToken(
            'resource.create',
            [BookingAbilityEnum::BOOKING_CREATE]
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->delete($this->uri('/bookings/'.$booking->id));

        $response->assertStatus(403)->assertJsonStructure([
            "message"
        ])->assertJson([
            "message" => "Invalid ability provided."
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
        ]);
    }

    public function test_user_autentificable_destroy_bookings(): void
    {
        $this->asUser();

        $booking = Bookings::factory()->create();

        $token = $this->createToken('booking.create', [BookingAbilityEnum::BOOKING_DESTROY]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->delete($this->uri('/bookings/'.$booking->id));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'Booking deleted']);

        $this->assertDatabaseMissing('bookings', [
            'id' => $booking->id,
        ]);
    }

    public function test_user_autentificable_destroy_bookings_no_found(): void
    {
        $this->asUser();

        $booking = Bookings::factory()->create();

        $booking_id = 100;

        $token = $this->createToken('booking.create', [BookingAbilityEnum::BOOKING_DESTROY]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->delete($this->uri('/bookings/'.$booking_id));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'Booking not found']);
    }
}
