<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import ChatModal from '@/components/ChatModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';

// ✅ Define props correctly
const props = defineProps<{ conversations: any[] }>();

const page = usePage<{ auth: { user: { id: number } } }>();
const userId = page.props.auth.user.id;

const showModal = ref(false);
const selectedConversation = ref<any>(null);

// ✅ Initialize reactive conversations
const conversations = ref<any[]>([]);

// ✅ Initialize conversations in onMounted
onMounted(() => {
    conversations.value = [...props.conversations];
    console.log('🔄 Conversaciones inicializadas:', conversations.value);
});

// ✅ Check if conversation has new messages
const isNewMessage = (conversationId: number) => {
    try {
        const hasNewMessages = JSON.parse(localStorage.getItem('hasNewMessages') || '[]');
        return hasNewMessages.includes(conversationId);
    } catch (error) {
        console.error('Error al leer localStorage:', error);
        return false;
    }
};

// ✅ Handle clear new messages event
const handleClearNewMessages = () => {
    console.log('🧹 Limpiando hasNewMessages localStorage');
    // Force component re-render by triggering reactivity
    conversations.value = [...conversations.value];
};

const openChat = async (otherUserId: number) => {
    console.log('🚀 Abriendo chat con usuario:', otherUserId);
    
    if (!otherUserId) {
        alert('ID de usuario no válido.');
        return;
    }

    try {
        console.log('🌐 Llamando a ruta API:', `/api/conversations/${otherUserId}`);
        
        // ✅ Add headers for better API communication
        const response = await axios.get(`/api/conversations/${otherUserId}`, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        });
        
        console.log('📡 Respuesta completa de la API:', response.data);
        console.log('📡 Status de respuesta:', response.status);
        
        // ✅ Additional debug logs
        console.log('🔍 Estructura conversation:', response.data.conversation);
        console.log('🔍 Propiedades de conversation:', Object.keys(response.data.conversation || {}));
        console.log('🔍 Estructura other_user:', response.data.other_user);
        console.log('🔍 Propiedades de other_user:', Object.keys(response.data.other_user || {}));

        // ✅ Check messages specifically
        if (response.data.conversation) {
            console.log('📨 Messages property:', response.data.conversation.messages);
            console.log('📨 Type of messages:', typeof response.data.conversation.messages);
            console.log('📨 All conversation keys:', Object.keys(response.data.conversation));
        }
        
        // ✅ Validate data structure
        if (!response.data.conversation) {
            console.error('❌ No se encontró conversación en la respuesta');
            alert('No se encontraron datos para esta conversación.');
            return;
        }

        if (!response.data.other_user) {
            console.error('❌ No se encontró other_user en la respuesta');
            alert('No se encontraron datos del usuario.');
            return;
        }

        // ✅ Check messages
        console.log('📨 Mensajes en la conversación:', response.data.conversation.messages);
        console.log('📊 Cantidad de mensajes:', response.data.conversation.messages?.length || 0);

        selectedConversation.value = response.data;
        showModal.value = true;
    } catch (error) {
        console.error('❌ Error al obtener la conversación:', error);
        
        // ✅ Better error handling
        if (error.response) {
            console.error('📡 Error response:', error.response.data);
            console.error('📡 Error status:', error.response.status);
            
            if (error.response.status === 404) {
                alert('Conversación no encontrada.');
            } else if (error.response.status === 500) {
                alert('Error interno del servidor. Por favor, intenta de nuevo.');
            } else {
                alert('Error al abrir la conversación.');
            }
        } else {
            alert('Error de conexión. Verifica tu conexión a internet.');
        }
    }
};

const closeChat = () => {
    console.log('🚪 Cerrando chat');
    showModal.value = false;
    selectedConversation.value = null;
};

// ✅ WebSocket configuration
let echoChannel: any = null;

const handleConversationUpdated = (data: any) => {
    console.log('✅ [Messages.vue] EVENTO conversation.updated recibido:', data);

    try {
        const index = conversations.value.findIndex(c => c.id === data.conversation_id);

        if (index !== -1) {
            // ✅ Create new array copy to maintain reactivity
            const updatedConversations = [...conversations.value];
            
            // ✅ Update last_message
            updatedConversations[index] = {
                ...updatedConversations[index],
                last_message: {
                    id: data.message.id,
                    content: data.message.content,
                    sender: data.message.sender,
                    created_at: data.message.created_at,
                }
            };

            // ✅ Move conversation to top
            const updatedConv = updatedConversations.splice(index, 1)[0];
            updatedConversations.unshift(updatedConv);
            
            // ✅ Assign new array
            conversations.value = updatedConversations;

            // ✅ Mark as "New"
            try {
                let hasNewMessages = JSON.parse(localStorage.getItem('hasNewMessages') || '[]');
                if (!hasNewMessages.includes(data.conversation_id)) {
                    hasNewMessages.push(data.conversation_id);
                    localStorage.setItem('hasNewMessages', JSON.stringify(hasNewMessages));
                }
            } catch (error) {
                console.error('Error al actualizar localStorage:', error);
            }
        } else {
            console.warn('La conversación no estaba en la lista, podría necesitar recargar.');
        }
    } catch (error) {
        console.error('Error al procesar conversación actualizada:', error);
    }
};

// ✅ Improved WebSocket cleanup function
const cleanupWebSocket = () => {
    if (echoChannel) {
        try {
            console.log('🔌 Desconectando WebSocket...');
            
            // ✅ Stop listening to events first
            if (typeof echoChannel.stopListening === 'function') {
                echoChannel.stopListening('.conversation.updated');
            }
            
            // ✅ Try different methods to disconnect
            if (typeof echoChannel.unsubscribe === 'function') {
                echoChannel.unsubscribe();
            } else if (typeof echoChannel.disconnect === 'function') {
                echoChannel.disconnect();
            } else if (typeof echoChannel.leaveChannel === 'function') {
                echoChannel.leaveChannel();
            } else {
                // ✅ Fallback: access Pusher directly
                const channelName = `private-user.${userId}`;
                if ((window as any).Echo?.connector?.pusher) {
                    (window as any).Echo.connector.pusher.unsubscribe(channelName);
                }
            }
            
            console.log('✅ WebSocket desconectado correctamente');
        } catch (error) {
            console.warn('⚠️ Error al desconectar WebSocket (no crítico):', error);
        } finally {
            echoChannel = null;
        }
    }
};

onMounted(() => {
    // ✅ Add listener for cleanup event
    window.addEventListener('clear-new-messages', handleClearNewMessages);

    // ✅ Check if Echo is available
    if (!(window as any).Echo) {
        console.warn('❌ Pusher/Echo no está disponible');
        return;
    }

    try {
        // ✅ Subscribe to user channel
        console.log('🔌 Intentando conectar al canal:', `user.${userId}`);
        echoChannel = (window as any).Echo.private(`user.${userId}`);

        // ✅ Verify channel was created correctly
        if (!echoChannel) {
            console.error('❌ No se pudo crear el canal de Echo');
            return;
        }

        echoChannel.listen('.conversation.updated', (data: any) => {
            handleConversationUpdated(data);
        });

        console.log('✅ WebSocket conectado correctamente al canal:', `user.${userId}`);
        
        // ✅ Additional debug
        console.log('🔍 Echo channel object:', echoChannel);
        console.log('🔍 Echo methods available:', Object.getOwnPropertyNames(Object.getPrototypeOf(echoChannel)));

    } catch (error) {
        console.error('❌ Error al conectar WebSocket:', error);
    }
});

onBeforeUnmount(() => {
    // ✅ Clean up listener
    window.removeEventListener('clear-new-messages', handleClearNewMessages);
    
    // ✅ Clean up WebSocket
    cleanupWebSocket();
});
</script>

<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto mt-10">
            <h1 class="mb-6 text-3xl font-bold">💬 Conversaciones</h1>

            <div v-if="conversations.length === 0" class="italic text-muted-foreground">
                No tienes conversaciones aún.
            </div>

            <ul class="space-y-4">
                <li 
                    v-for="conv in conversations" 
                    :key="`conv-${conv.id}`" 
                    class="p-4 border rounded shadow bg-card relative"
                >
                    <p class="mb-1 font-medium">
                        Conversación con: {{ conv.other_user?.name || 'Usuario desconocido' }}
                    </p>
                    <p class="text-sm text-muted-foreground mb-2">
                        Último mensaje: {{ conv.last_message?.content || 'Sin mensajes' }}
                    </p>

                    <!-- ✅ Show "New" if applicable -->
                    <span 
                        v-if="isNewMessage(conv.id)" 
                        class="absolute top-2 right-2 text-xs bg-red-500 text-white px-2 py-1 rounded-full"
                    >
                        Nuevo
                    </span>

                    <button
                        @click="openChat(conv.other_user?.id)"
                        class="text-sm text-blue-600 hover:underline disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!conv.other_user?.id"
                    >
                        Ver mensajes →
                    </button>
                </li>
            </ul>
        </div>

        <!-- ✅ Render modal only if it has valid data -->
        <ChatModal
            v-if="showModal && selectedConversation?.conversation && selectedConversation?.other_user"
            :show="showModal"
            :conversation="selectedConversation.conversation"
            :other-user="selectedConversation.other_user"
            :user-id="userId"
            @close="closeChat"
        />
    </AppLayout>
</template>