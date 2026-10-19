<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true), // Un nom de produit (ex: "Sac à dos noir")
            'category_id' => Category::factory(),  // Crée et lie automatiquement une catégorie !
            'price' => $this->faker->numberBetween(100, 5000),     // Prix entre 100 et 5000
            'salePrice' => $this->faker->numberBetween(50, 4000),  // Prix promo
            'stock' => $this->faker->numberBetween(0, 100),        // Quantité en stock
            'unit' => $this->faker->randomElement(['kg', 'pièce', 'litre']), // Unité aléatoire
            'origin' => $this->faker->country(),    // Un pays d'origine
            'description' => $this->faker->paragraph(), // Un faux texte de description
            'image' => $this->faker->imageUrl(640, 480, 'techno', true),
            'active' => $this->faker->boolean(80),   // 80% de chance d'être vrai (true)
            'featured' => $this->faker->boolean(20), // 20% de chance d'être mis en avant
        ];
    }
}
