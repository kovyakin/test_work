<?php

namespace Tests\Feature;

use App\Enums\ResourceAbilityEnum;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResourcesPostTest extends TestCase
{

    use RefreshDatabase;


    public function test_unregistered_user(): void
    {
        $response = $this->post($this->uri('/resources'));

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

        $response = $this->post($this->uri('/resources'));

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
        ])->post($this->uri('/resources'));

        $response->assertStatus(401)
            ->assertJsonStructure([
                "message"
            ])->assertExactJson([
                "message" => "Unauthenticated."
            ]);
    }

    public function test_user_token_cant_post_resources(): void
    {
        $this->asUser();

        $token = $this->createToken(
            'resource.get',
            [ResourceAbilityEnum::RESOURCE_GET]
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post($this->uri('/resources'));

        $response->assertStatus(403)->assertJsonStructure([
            "message"
        ])->assertJson([
            "message" => "Invalid ability provided."
        ]);
    }

    public function test_user_autentificable_post_resources(): void
    {
        $this->asUser();

        $token = $this->createToken('resource.create', [ResourceAbilityEnum::RESOURCE_CREATE]);

        $name = Str::random(10);

        $type = Str::random(10);

        $description = Str::random(50);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/resources'),
            [
                'name' => $name,
                'type' => $type,
                'description' => $description,
            ]
        );

        $response->assertStatus(201)
            ->assertJsonMissingValidationErrors(['name', 'type', 'description'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'Resource created']);

        $this->assertDatabaseHas('resources', [
            'name' => $name,
            'type' => $type,
            'description' => $description,
        ]);
    }

    public function test_user_autentificable_post_resources_invalid_name_required(): void
    {
        $this->asUser();

        $token = $this->createToken('resource.create', [ResourceAbilityEnum::RESOURCE_CREATE]);

        $name = Str::random(10);

        $type = Str::random(10);

        $description = Str::random(50);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/resources'),
            [
                'type' => $type,
                'description' => $description,
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The name field is required.']);

        $this->assertDatabaseMissing('resources', [
            'name' => $name,
            'type' => $type,
            'description' => $description,
        ]);
    }

    public function test_user_autentificable_post_resources_invalid_name_min(): void
    {
        $this->asUser();

        $token = $this->createToken('resource.create', [ResourceAbilityEnum::RESOURCE_CREATE]);

        $name = Str::random(1);

        $type = Str::random(10);

        $description = Str::random(50);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/resources'),
            [
                'name' => $name,
                'type' => $type,
                'description' => $description,
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The name field must be at least 3 characters.']);

        $this->assertDatabaseMissing('resources', [
            'name' => $name,
            'type' => $type,
            'description' => $description,
        ]);
    }

    public function test_user_autentificable_post_resources_invalid_name_max(): void
    {
        $this->asUser();

        $token = $this->createToken('resource.create', [ResourceAbilityEnum::RESOURCE_CREATE]);

        $name = Str::random(101);

        $type = Str::random(10);

        $description = Str::random(50);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/resources'),
            [
                'name' => $name,
                'type' => $type,
                'description' => $description,
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The name field must not be greater than 100 characters.']);

        $this->assertDatabaseMissing('resources', [
            'name' => $name,
            'type' => $type,
            'description' => $description,
        ]);
    }

    public function test_user_autentificable_post_resources_invalid_name_unique(): void
    {
        $this->asUser();

        $token = $this->createToken('resource.create', [ResourceAbilityEnum::RESOURCE_CREATE]);

        $resource = Resource::factory()->create();

        $name = $resource->name;

        $type = Str::random(10);

        $description = Str::random(50);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/resources'),
            [
                'name' => $name,
                'type' => $type,
                'description' => $description,
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The name has already been taken.']);

        $this->assertDatabaseMissing('resources', [
            'name' => $name,
            'type' => $type,
            'description' => $description,
        ]);
    }

    public function test_user_autentificable_post_resources_invalid_type_required(): void
    {
        $this->asUser();

        $token = $this->createToken('resource.create', [ResourceAbilityEnum::RESOURCE_CREATE]);

        $name = Str::random(10);

        $description = Str::random(50);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/resources'),
            [
                'name' => $name,
                'description' => $description,
            ]
        );



        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The type field is required.']);

        $this->assertDatabaseMissing('resources', [
            'name' => $name,
            'description' => $description,
        ]);
    }

    public function test_user_autentificable_post_resources_invalid_type_min(): void
    {
        $this->asUser();

        $token = $this->createToken('resource.create', [ResourceAbilityEnum::RESOURCE_CREATE]);

        $name = Str::random(10);

        $type = Str::random(1);

        $description = Str::random(50);


        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/resources'),
            [
                'name' => $name,
                'type' => $type,
                'description' => $description,
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The type field must be at least 3 characters.']);

        $this->assertDatabaseMissing('resources', [
            'name' => $name,
            'type' => $type,
            'description' => $description,
        ]);
    }

    public function test_user_autentificable_post_resources_invalid_type_max(): void
    {
        $this->asUser();

        $token = $this->createToken('resource.create', [ResourceAbilityEnum::RESOURCE_CREATE]);

        $name = Str::random(10);

        $type = Str::random(51);

        $description = Str::random(50);


        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/resources'),
            [
                'name' => $name,
                'type' => $type,
                'description' => $description,
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The type field must not be greater than 50 characters.']);

        $this->assertDatabaseMissing('resources', [
            'name' => $name,
            'type' => $type,
            'description' => $description,
        ]);
    }

    public function test_user_autentificable_post_resources_invalid_description_min(): void
    {
        $this->asUser();

        $token = $this->createToken('resource.create', [ResourceAbilityEnum::RESOURCE_CREATE]);

        $name = Str::random(10);

        $type = Str::random(10);

        $description = Str::random(1);


        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/resources'),
            [
                'name' => $name,
                'type' => $type,
                'description' => $description,
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The description field must be at least 3 characters.']);

        $this->assertDatabaseMissing('resources', [
            'name' => $name,
            'type' => $type,
            'description' => $description,
        ]);
    }

    public function test_user_autentificable_post_resources_invalid_description_max(): void
    {
        $this->asUser();

        $token = $this->createToken('resource.create', [ResourceAbilityEnum::RESOURCE_CREATE]);

        $name = Str::random(10);

        $type = Str::random(10);

        $description = Str::random(256);


        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(
            $this->uri('/resources'),
            [
                'name' => $name,
                'type' => $type,
                'description' => $description,
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description'])
            ->assertJsonStructure([
                'message'
            ])->assertJson(['message' => 'The description field must not be greater than 255 characters.']);

        $this->assertDatabaseMissing('resources', [
            'name' => $name,
            'type' => $type,
            'description' => $description,
        ]);
    }
}
