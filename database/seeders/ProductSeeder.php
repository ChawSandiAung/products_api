<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory()
            ->count(10)
            ->create()
            ->each(function (Product $p) {
                Variant::factory()->count(rand(1, 4))->create([
                    'product_id' => $p->id,
                ]);
            });
    }
}