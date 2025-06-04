<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Juan Vendedor',
            'email' => 'juan@example.com',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'name' => 'Ana Compradora',
            'email' => 'ana@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
