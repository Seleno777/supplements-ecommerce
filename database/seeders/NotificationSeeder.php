<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $juan = User::where('email', 'juan@example.com')->first();

        Notification::create([
            'user_id' => $juan->id,
            'message' => 'Tu producto "Creatina Monohidratada" está agotado.',
            'type' => 'warning',
        ]);
    }
}
