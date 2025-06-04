<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

class ConversationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $juan = User::where('email', 'juan@example.com')->first();
        $ana = User::where('email', 'ana@example.com')->first();

        $conversation = Conversation::create([
            'user_one_id' => $juan->id,
            'user_two_id' => $ana->id,
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $ana->id,
            'content' => 'Hola, ¿esta proteína es vegana?',
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $juan->id,
            'content' => 'Hola Ana, no, es de suero de leche.',
        ]);
    }
}
