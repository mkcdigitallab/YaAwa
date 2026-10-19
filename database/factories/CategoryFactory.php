<?php

namespace Database\Factories;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(), // Un mot unique (ex: Électronique)
            'color' => $this->faker->safeHexColor(),   // Une couleur au format #hex
            'image' => $this->faker->imageUrl(640, 480, 'business', true), // URL d'une fausse image
        ];
    }
}
