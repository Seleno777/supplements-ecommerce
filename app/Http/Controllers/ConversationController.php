<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ConversationController extends Controller
{
    // 🚩 index() → mostrar lista de conversaciones
    public function index()
    {
        Log::info('=== INICIO ConversationController::index ===');
        Log::info('Usuario autenticado:', ['id' => Auth::id(), 'name' => Auth::user()->name ?? 'N/A']);

        try {
            $conversations = Conversation::where('user_one_id', Auth::id())
                ->orWhere('user_two_id', Auth::id())
                ->with([
                    'userOne',
                    'userTwo',
                    'messages' => function($query) {
                        $query->latest()->limit(1)->with('sender');
                    }
                ])
                ->get();

            $formattedConversations = $conversations->map(function ($conversation) {
                $otherUser = $conversation->user_one_id === Auth::id()
                    ? $conversation->userTwo
                    : $conversation->userOne;

                $lastMessage = $conversation->messages->first();

                return [
                    'id' => $conversation->id,
                    'other_user' => $otherUser ? $otherUser->only(['id', 'name', 'email']) : [
                        'id' => null,
                        'name' => 'Usuario desconocido',
                        'email' => ''
                    ],
                    'last_message' => $lastMessage ? [
                        'id' => $lastMessage->id,
                        'content' => $lastMessage->content,
                        'sender' => $lastMessage->sender ? $lastMessage->sender->only(['id', 'name', 'email']) : null,
                        'created_at' => $lastMessage->created_at->toISOString(),
                    ] : null,
                    'updated_at' => $conversation->updated_at,
                ];
            });

            Log::info('Conversaciones formateadas:', ['count' => $formattedConversations->count()]);

            return Inertia::render('Messages', [
                'conversations' => $formattedConversations,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ConversationController::index:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return Inertia::render('Messages', [
                'conversations' => [],
                'error' => 'Error al cargar las conversaciones.',
            ]);
        } finally {
            Log::info('=== FIN ConversationController::index ===');
        }
    }

    // 🚩 show() → abrir chat en la web → Inertia → betweenUsers permitido
    public function show(Request $request, $userId)
    {
        $authId = Auth::id();
        $otherUser = User::findOrFail($userId);

        // Aquí sí usamos betweenUsers (permitir crear conversación vacía)
        $conversation = Conversation::betweenUsers($authId, $userId);

        $conversation->load(['messages' => function($query) {
            $query->orderBy('created_at', 'asc');
        }, 'userOne', 'userTwo']);

        return Inertia::render('Chat', [
            'conversation' => [
                'id' => $conversation->id,
                'messages' => $conversation->messages->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'content' => $message->content,
                        'sender' => [
                            'id' => $message->sender_id,
                            'name' => $message->sender->name,
                            'email' => $message->sender->email,
                        ],
                        'created_at' => $message->created_at->toISOString(),
                    ];
                }),
            ],
            'otherUser' => [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'email' => $otherUser->email,
            ],
        ]);
    }

    // 🚩 getConversationData() → API → JSON → NO crear conversación
    public function getConversationData(Request $request, $userId)
{
    Log::info('=== INICIO ConversationController::getConversationData (API) ===');
    Log::info('Usuario autenticado:', ['auth_id' => Auth::id(), 'user_id' => $userId]);

    try {
        $authId = Auth::id();

        // 🚩 NO usar betweenUsers aquí → NO crear conversación en la API
        $conversation = Conversation::where(function ($query) use ($authId, $userId) {
            $query->where('user_one_id', $authId)->where('user_two_id', $userId);
        })->orWhere(function ($query) use ($authId, $userId) {
            $query->where('user_one_id', $userId)->where('user_two_id', $authId);
        })->first();

        if (!$conversation) {
            Log::warning('Conversación no encontrada entre usuarios.', [
                'auth_id' => $authId,
                'user_id' => $userId
            ]);
            return response()->json(['error' => 'Conversación no encontrada.'], 404);
        }

        // ✅ CARGAR MENSAJES CON SENDER
        $conversation->load(['messages' => function($query) {
            $query->orderBy('created_at', 'asc')->with('sender');
        }, 'userOne', 'userTwo']);

        $otherUser = $conversation->user_one_id === $authId
            ? $conversation->userTwo
            : $conversation->userOne;

        // ✅ ESTRUCTURA CORREGIDA - Mensajes dentro de conversation
        $responseData = [
            'conversation' => [
                'id' => $conversation->id,
                'user_one_id' => $conversation->user_one_id,
                'user_two_id' => $conversation->user_two_id,
                // ✅ AGREGAR MENSAJES AQUÍ
                'messages' => $conversation->messages->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'content' => $message->content,
                        'sender' => [
                            'id' => $message->sender_id,
                            'name' => $message->sender->name,
                            'email' => $message->sender->email ?? null,
                        ],
                        'created_at' => $message->created_at->toISOString(),
                    ];
                }),
            ],
            'other_user' => $otherUser ? $otherUser->only(['id', 'name', 'email']) : null,
        ];

        Log::info('✅ Respondiendo con conversación y mensajes:', [
            'conversation_id' => $conversation->id,
            'messages_count' => $conversation->messages->count()
        ]);
        
        return response()->json($responseData);

    } catch (\Exception $e) {
        Log::error('Error en ConversationController::getConversationData (API):', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json(['error' => 'Error al cargar los datos de la conversación.'], 500);
    } finally {
        Log::info('=== FIN ConversationController::getConversationData ===');
    }
}
}
