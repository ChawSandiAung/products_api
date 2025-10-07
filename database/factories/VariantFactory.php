<?php

namespace Database\Factories;

use App\Models\Variant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Variant> */
class VariantFactory extends Factory
{
    protected $model = Variant::class;

    public function definition(): array
    {
        $metals = ['gold', 'white_gold', 'platinum'];

        return [
            'carat' => $this->faker->randomElement([null, 14, 18, 22]),
            'metal_type' => $this->faker->randomElement($metals),
            'price' => $this->faker->randomFloat(2, 100, 2500),
            'stock' => $this->faker->numberBetween(0, 100),
            'sku' => strtoupper($this->faker->bothify('SKU-####-??')),
        ];
    }
}