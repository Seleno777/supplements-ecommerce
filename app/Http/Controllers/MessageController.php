<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'content' => 'required|string',
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);

        if (!in_array(Auth::id(), [$conversation->user_one_id, $conversation->user_two_id])) {
            abort(403);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'content' => $request->content,
        ]);

        // Aquí podrías emitir un evento para WebSocket (ej: usando Laravel Echo)

        return $message->load('sender');
    }
}
