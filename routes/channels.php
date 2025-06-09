<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use App\Models\Conversation;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// ✅ Canal de usuario privado (para lista de conversaciones)
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// ✅ Canal de conversación privada (para chat específico)
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    // Verificar que el usuario pertenece a esta conversación
    $conversation = Conversation::find($conversationId);
    
    if (!$conversation) {
        return false;
    }
    
    return $conversation->user_one_id === $user->id || $conversation->user_two_id === $user->id;
});

// ✅ Canal de presencia (opcional - para mostrar quién está online)
Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    
    if (!$conversation) {
        return false;
    }
    
    if ($conversation->user_one_id === $user->id || $conversation->user_two_id === $user->id) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
    
    return false;
});