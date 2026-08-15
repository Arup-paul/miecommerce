<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catalog = [
            'Electronics' => [
                ['Wireless Headphones', 79.99, 15, 25],
                ['Bluetooth Speaker', 45.50, 15, 40],
                ['USB-C Charging Cable', 9.99, 15, 100],
            ],
            'Clothing' => [
                ['Cotton T-Shirt', 14.99, 5, 60],
                ['Denim Jacket', 59.99, 5, 20],
                ['Running Shoes', 89.99, 5, 30],
            ],
            'Home & Kitchen' => [
                ['Non-Stick Frying Pan', 24.99, 10, 35],
                ['Electric Kettle', 32.50, 10, 20],
                ['Ceramic Dinner Set', 65.00, 10, 15],
            ],
            'Books' => [
                ['The Art of Programming', 39.99, 0, 50],
                ['Modern Web Design', 29.99, 0, 45],
            ],
            'Sports & Outdoors' => [
                ['Yoga Mat', 19.99, 5, 40],
                ['Camping Tent (2-Person)', 129.99, 5, 12],
            ],
        ];

        foreach ($catalog as $categoryName => $products) {
            $category = Category::where('name', $categoryName)->first();

            if (! $category) {
                continue;
            }

            foreach ($products as [$name, $price, $vatRate, $stock]) {
                Product::firstOrCreate(
                    ['name' => $name],
                    [
                        'category_id' => $category->id,
                        'short_description' => "{$name} — a great addition to your {$categoryName} collection.",
                        'price' => $price,
                        'vat_rate' => $vatRate,
                        'stock_quantity' => $stock,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
