<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    /**
     * Almacenar un nuevo mensaje
     */
    public function store(Request $request)
    {
        Log::info('=== INICIO MessageController::store ===');
        Log::info('Request data:', $request->all());
        Log::info('Usuario autenticado:', ['id' => Auth::id(), 'name' => Auth::user()->name ?? 'N/A']);

        try {
            // Validar los datos de entrada
            $validated = $request->validate([
                'conversation_id' => 'required|integer|exists:conversations,id',
                'content' => 'required|string|max:2000',
            ]);

            Log::info('Datos validados:', $validated);

            // Verificar que la conversación existe
            $conversation = Conversation::findOrFail($validated['conversation_id']);
            Log::info('Conversación encontrada:', ['id' => $conversation->id]);

            // Verificar que el usuario autenticado participa en la conversación
            $authUserId = Auth::id();
            if (!$conversation->hasUser($authUserId)) {
                Log::warning('Usuario no autorizado para esta conversación', [
                    'user_id' => $authUserId,
                    'conversation_id' => $conversation->id
                ]);
                return response()->json(['error' => 'No autorizado para esta conversación'], 403);
            }

            // Crear el mensaje
            $message = Message::create([
                'conversation_id' => $validated['conversation_id'],
                'sender_id' => $authUserId,
                'content' => $validated['content'],
            ]);

            Log::info('Mensaje creado:', ['id' => $message->id]);

            // Cargar la relación sender
            $message->load('sender');

            // Actualizar timestamp de la conversación
            $conversation->touch();

            // Formatear el mensaje para la respuesta
            $formattedMessage = [
                'id' => $message->id,
                'content' => $message->content,
                'sender' => [
                    'id' => $message->sender->id,
                    'name' => $message->sender->name,
                    'email' => $message->sender->email,
                ],
                'created_at' => $message->created_at->toISOString(),
            ];

            // Broadcast del mensaje (si está configurado)
            try {
                broadcast(new MessageSent($formattedMessage, $conversation->id))->toOthers();
                Log::info('Mensaje broadcasted correctamente');
            } catch (\Exception $e) {
                Log::warning('Error al hacer broadcast del mensaje:', [
                    'error' => $e->getMessage(),
                    'message' => 'El mensaje se guardó pero no se pudo broadcast'
                ]);
                // No fallar toda la operación por un error de broadcast
            }

            // Broadcast evento de conversación actualizada
            try {
                // Obtener el otro usuario de la conversación
                $otherUserId = $conversation->user_one_id === $authUserId 
                    ? $conversation->user_two_id 
                    : $conversation->user_one_id;

                // Broadcast a ambos usuarios
                broadcast(new \App\Events\ConversationUpdated($conversation->id, $formattedMessage, $otherUserId));
                Log::info('Evento ConversationUpdated broadcasted');
            } catch (\Exception $e) {
                Log::warning('Error al broadcast ConversationUpdated:', [
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('✅ Mensaje enviado exitosamente');

            return response()->json([
                'success' => true,
                'message' => $formattedMessage,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación:', [
                'errors' => $e->errors(),
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Datos de entrada inválidos',
                'details' => $e->errors()
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Conversación no encontrada:', [
                'conversation_id' => $request->conversation_id ?? 'N/A',
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Conversación no encontrada'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error inesperado en MessageController::store:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Error interno del servidor al enviar mensaje'
            ], 500);

        } finally {
            Log::info('=== FIN MessageController::store ===');
        }
    }
    public function pollNewMessages(Request $request, $userId)
{
    Log::info('=== INICIO MessageController::pollNewMessages ===');
    Log::info('Polling para mensajes entre usuarios:', [
        'auth_id' => Auth::id(), 
        'other_user_id' => $userId,
        'last_message_id' => $request->get('last_message_id')
    ]);

    try {
        $authId = Auth::id();
        
        // Buscar la conversación
        $conversation = Conversation::where(function ($query) use ($authId, $userId) {
            $query->where('user_one_id', $authId)->where('user_two_id', $userId);
        })->orWhere(function ($query) use ($authId, $userId) {
            $query->where('user_one_id', $userId)->where('user_two_id', $authId);
        })->first();

        if (!$conversation) {
            return response()->json([
                'has_new_messages' => false,
                'messages' => []
            ]);
        }

        // ID del último mensaje que conoce el cliente
        $lastMessageId = $request->get('last_message_id', 0);

        // Buscar mensajes más recientes
        $newMessages = $conversation->messages()
            ->where('id', '>', $lastMessageId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        $formattedMessages = $newMessages->map(function ($message) {
            return [
                'id' => $message->id,
                'content' => $message->content,
                'sender' => [
                    'id' => $message->sender->id,
                    'name' => $message->sender->name,
                    'email' => $message->sender->email,
                ],
                'created_at' => $message->created_at->toISOString(),
            ];
        });

        Log::info('Mensajes nuevos encontrados:', ['count' => $newMessages->count()]);

        return response()->json([
            'has_new_messages' => $newMessages->count() > 0,
            'messages' => $formattedMessages,
            'last_message_id' => $newMessages->last()?->id ?? $lastMessageId
        ]);

    } catch (\Exception $e) {
        Log::error('Error en polling de mensajes:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return response()->json([
            'has_new_messages' => false,
            'messages' => [],
            'error' => 'Error al verificar mensajes nuevos'
        ], 500);
    } finally {
        Log::info('=== FIN MessageController::pollNewMessages ===');
    }
}

/**
 * Server-Sent Events para tiempo real (alternativa más eficiente)
 */
public function streamMessages(Request $request, $userId)
{
    Log::info('=== INICIO MessageController::streamMessages (SSE) ===');
    
    try {
        $authId = Auth::id();
        
        // Verificar que la conversación existe
        $conversation = Conversation::where(function ($query) use ($authId, $userId) {
            $query->where('user_one_id', $authId)->where('user_two_id', $userId);
        })->orWhere(function ($query) use ($authId, $userId) {
            $query->where('user_one_id', $userId)->where('user_two_id', $authId);
        })->first();

        if (!$conversation) {
            return response('Conversación no encontrada', 404);
        }

        // Configurar headers para SSE
        return response()->stream(function () use ($conversation) {
            
            // Headers SSE
            echo "data: " . json_encode(['type' => 'connected', 'conversation_id' => $conversation->id]) . "\n\n";
            ob_flush();
            flush();

            $lastMessageId = 0;
            
            // Loop infinito para mantener la conexión
            while (true) {
                // Verificar nuevos mensajes cada 2 segundos
                $newMessages = $conversation->messages()
                    ->where('id', '>', $lastMessageId)
                    ->with('sender')
                    ->orderBy('created_at', 'asc')
                    ->get();

                if ($newMessages->count() > 0) {
                    foreach ($newMessages as $message) {
                        $formattedMessage = [
                            'type' => 'new_message',
                            'message' => [
                                'id' => $message->id,
                                'content' => $message->content,
                                'sender' => [
                                    'id' => $message->sender->id,
                                    'name' => $message->sender->name,
                                    'email' => $message->sender->email,
                                ],
                                'created_at' => $message->created_at->toISOString(),
                            ]
                        ];

                        echo "data: " . json_encode($formattedMessage) . "\n\n";
                        ob_flush();
                        flush();
                        
                        $lastMessageId = $message->id;
                    }
                }

                // Pausa de 2 segundos
                sleep(2);

                // Verificar si la conexión sigue activa
                if (connection_aborted()) {
                    Log::info('Conexión SSE cerrada por el cliente');
                    break;
                }
            }
            
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Para Nginx
        ]);

    } catch (\Exception $e) {
        Log::error('Error en SSE stream:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return response('Error en el stream', 500);
    }
}

/**
 * Marcar mensajes como leídos
 */
public function markAsRead(Request $request, $userId)
{
    Log::info('=== INICIO MessageController::markAsRead ===');
    
    try {
        $authId = Auth::id();
        
        $conversation = Conversation::where(function ($query) use ($authId, $userId) {
            $query->where('user_one_id', $authId)->where('user_two_id', $userId);
        })->orWhere(function ($query) use ($authId, $userId) {
            $query->where('user_one_id', $userId)->where('user_two_id', $authId);
        })->first();

        if (!$conversation) {
            return response()->json(['error' => 'Conversación no encontrada'], 404);
        }

        // Marcar como leídos todos los mensajes que NO son del usuario actual
        $updatedCount = $conversation->messages()
            ->where('sender_id', '!=', $authId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        Log::info('Mensajes marcados como leídos:', ['count' => $updatedCount]);

        return response()->json([
            'success' => true,
            'marked_as_read' => $updatedCount
        ]);

    } catch (\Exception $e) {
        Log::error('Error al marcar mensajes como leídos:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return response()->json(['error' => 'Error al marcar mensajes como leídos'], 500);
    } finally {
        Log::info('=== FIN MessageController::markAsRead ===');
    }
}

    /**
     * Obtener mensajes de una conversación específica
     */
    public function index(Request $request, $conversationId)
    {
        try {
            $conversation = Conversation::findOrFail($conversationId);
            
            // Verificar autorización
            if (!$conversation->hasUser(Auth::id())) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $messages = $conversation->messages()
                ->with('sender')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'content' => $message->content,
                        'sender' => [
                            'id' => $message->sender->id,
                            'name' => $message->sender->name,
                            'email' => $message->sender->email,
                        ],
                        'created_at' => $message->created_at->toISOString(),
                    ];
                });

            return response()->json([
                'messages' => $messages
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener mensajes:', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Error al cargar mensajes'], 500);
        }
    }
}