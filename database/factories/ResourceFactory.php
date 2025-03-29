<?php

namespace Database\Factories;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceFactory extends Factory
{
    protected $model = Resource::class;

    public function definition()
    {
        return [
            'name' => $this->faker->colorName(),
            'type' => $this->faker->text(10),
            'description' => $this->faker->text(),

        ];
    }
}
