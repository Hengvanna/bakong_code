<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Product::create([
            'name' => 'iPhone 17 Pro',
            'description' => '128GB, Blue Titanium',
            'price' => 200,
            'image' => 'https://i.ebayimg.com/images/g/EDEAAeSwLb9owuv8/s-l1600.webp'
        ]);
        Product::create([
            'name' => 'AirPods Pro 3',
            'description' => 'Wireless earphones with noise cancelling',
            'price' => 150,
            'image' => 'https://angkormeas.com/wp-content/uploads/2025/09/Apple-Airpods-Pro-3.jpg'
        ]);
    }
}
