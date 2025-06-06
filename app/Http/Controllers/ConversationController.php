<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConversationController extends Controller
{
    public function index()
    {
        $conversations = Conversation::with(['userOne', 'userTwo'])
            ->where('user_one_id', Auth::id())
            ->orWhere('user_two_id', Auth::id())
            ->get();

        return Inertia::render('Messages', [
            'conversations' => $conversations
        ]);
    }

    public function show($userId)
    {
        $authId = Auth::id();

        $conversation = Conversation::firstOrCreate(
            [
                'user_one_id' => min($authId, $userId),
                'user_two_id' => max($authId, $userId),
            ]
        );

        return response()->json($conversation->load('messages.sender'));
    }
}
