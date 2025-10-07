<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'name' => $name,
            'description' => $this->faker->sentence(),
            'base_price' => $this->faker->randomFloat(2, 100, 2000),
            'slug' => Str::slug($name) . '-' . Str::random(6),
        ];
    }
}