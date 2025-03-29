<?php

namespace Tests\Feature;

use App\Enums\ResourceAbilityEnum;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResourcesGetTest extends TestCase
{

    use RefreshDatabase;


    public function test_unregistered_user(): void
    {
        $response = $this->get($this->uri('/resources'));

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

        $response = $this->get($this->uri('/resources'));

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
        ])->get($this->uri('/resources'));

        $response->assertStatus(401)
            ->assertJsonStructure([
                "message"
            ])->assertExactJson([
                "message" => "Unauthenticated."
            ]);
    }

    public function test_user_token_cant_get_resources(): void
    {
        $this->asUser();

        $token = $this->createToken(
            'resource.create',
            [ResourceAbilityEnum::RESOURCE_CREATE]
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get($this->uri('/resources'));

        $response->assertStatus(403)->assertJsonStructure([
            "message"
        ])->assertJson([
            "message" => "Invalid ability provided."
        ]);
    }

    public function test_user_autentificable_get_resources(): void
    {
        $this->asUser();

        $resource = Resource::factory()->create();

        $token = $this->createToken('resource.create', [ResourceAbilityEnum::RESOURCE_GET]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get($this->uri('/resources'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])->assertJson( ['data'=>[$resource->toArray()]]);
    }
}
