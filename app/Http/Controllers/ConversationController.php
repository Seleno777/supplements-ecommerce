<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index()
    {
        return Conversation::with(['userOne', 'userTwo'])
            ->where('user_one_id', Auth::id())
            ->orWhere('user_two_id', Auth::id())
            ->get();
    }

    public function show($userId)
    {
        $authId = Auth::id();

        $conversation = Conversation::firstOrCreate(
            [
                ['user_one_id', min($authId, $userId)],
                ['user_two_id', max($authId, $userId)],
            ]
        );

        return $conversation->load('messages.sender');
    }
}
