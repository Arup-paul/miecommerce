<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Electronics', 'Clothing', 'Home & Kitchen', 'Books', 'Sports & Outdoors'] as $name) {
            Category::firstOrCreate(['name' => $name], ['status' => 'active']);
        }
    }
}
