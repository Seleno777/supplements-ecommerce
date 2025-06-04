<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CartItem;
use App\Models\User;
use App\Models\Product;

class CartItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ana = User::where('email', 'ana@example.com')->first();
        $product = Product::first();

        CartItem::create([
            'user_id' => $ana->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }
}
