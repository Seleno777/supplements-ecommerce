<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $juan = User::where('email', 'juan@example.com')->first();

        Product::create([
            'user_id' => $juan->id,
            'name' => 'Proteína Whey 2kg',
            'description' => 'Proteína de suero de leche para ganancia muscular.',
            'price' => 59.99,
            'stock' => 10,
            'category' => 'Proteína',
        ]);

        Product::create([
            'user_id' => $juan->id,
            'name' => 'Creatina Monohidratada 500g',
            'description' => 'Suplemento para fuerza y recuperación.',
            'price' => 29.99,
            'stock' => 5,
            'category' => 'Creatina',
        ]);
    }
}
