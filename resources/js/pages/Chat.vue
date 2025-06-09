<script setup>
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';
import { ref, onMounted, nextTick, watch, onBeforeUnmount } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

// Obtener el usuario actual desde Inertia
const page = usePage();
const currentUser = page.props.auth.user;

// Definir props con validación adecuada
const props = defineProps({
    conversation: {
        type: Object,
        required: true,
        default: () => ({
            id: null,
            messages: []
        })
    },
    otherUser: {
        type: Object,
        required: true,
        default: () => ({
            id: null,
            name: 'Usuario',
            email: ''
        })
    },
    errors: {
        type: Object,
        default: () => ({})
    }
});

// Estado del componente
const messages = ref(props.conversation.messages || []);
const newMessage = ref('');
const loading = ref(false);
const messagesContainer = ref(null);
const typing = ref(false);
const typingTimeout = ref(null);
const onlineStatus = ref(false);
const echoAvailable = ref(false);

// 🚀 NUEVAS VARIABLES PARA POLLING
const pollingInterval = ref(null);
const isPolling = ref(false);
const lastMessageId = ref(0);
const pollingError = ref(null);
const connectionStatus = ref('connecting'); // connecting, connected, error

// Inicializar último mensaje ID
const initializeLastMessageId = () => {
    if (messages.value.length > 0) {
        lastMessageId.value = Math.max(...messages.value.map(m => m.id));
    }
    console.log('🆔 Último mensaje ID inicializado:', lastMessageId.value);
};

// 🔄 MÉTODOS DE POLLING
const startPolling = () => {
    if (isPolling.value) return;
    
    console.log('🔄 Iniciando polling para tiempo real...');
    isPolling.value = true;
    connectionStatus.value = 'connected';
    pollingError.value = null;
    
    // Polling cada 2 segundos
    pollingInterval.value = setInterval(() => {
        checkForNewMessages();
    }, 2000);
};

const stopPolling = () => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
        isPolling.value = false;
        connectionStatus.value = 'disconnected';
        console.log('⏹️ Polling detenido');
    }
};

const checkForNewMessages = async () => {
    if (!props.otherUser.id) return;

    try {
        const response = await axios.get(`/api/conversations/${props.otherUser.id}/poll`, {
            params: {
                last_message_id: lastMessageId.value
            },
            timeout: 5000 // 5 segundos timeout
        });

        if (response.data.has_new_messages && response.data.messages.length > 0) {
            console.log(`✅ ${response.data.messages.length} mensajes nuevos recibidos`);
            
            // Agregar mensajes nuevos
            response.data.messages.forEach(message => {
                addMessageIfNotExists(message);
            });
            
            // Actualizar último mensaje ID
            lastMessageId.value = response.data.last_message_id;
            
            // Scroll automático
            scrollToBottom();
            
            // Marcar como leídos si la ventana está activa
            if (document.hasFocus()) {
                markMessagesAsRead();
            }
        }

        // Reset error si había uno
        if (pollingError.value) {
            pollingError.value = null;
            connectionStatus.value = 'connected';
        }

    } catch (error) {
        console.error('❌ Error en polling:', error);
        
        // Manejar diferentes tipos de errores
        if (error.code === 'ECONNABORTED') {
            pollingError.value = 'Timeout de conexión';
        } else if (error.response?.status === 404) {
            console.log('💭 Conversación aún no existe, esperando...');
            pollingError.value = null; // No es realmente un error
        } else if (error.response?.status >= 500) {
            pollingError.value = 'Error del servidor';
            connectionStatus.value = 'error';
        } else {
            pollingError.value = 'Error de conexión';
            connectionStatus.value = 'error';
        }
    }
};

// Marcar mensajes como leídos
const markMessagesAsRead = async () => {
    if (!props.otherUser.id) return;

    try {
        await axios.patch(`/api/conversations/${props.otherUser.id}/read`);
        console.log('📖 Mensajes marcados como leídos');
    } catch (error) {
        console.error('Error marcando mensajes como leídos:', error);
    }
};

// 🎧 EVENTOS DE VISIBILIDAD DE LA VENTANA
const handleVisibilityChange = () => {
    if (document.hidden) {
        // Ventana oculta - detener polling para ahorrar recursos
        console.log('👁️ Ventana oculta, pausando polling...');
        stopPolling();
    } else {
        // Ventana visible - reanudar polling
        console.log('👁️ Ventana visible, reanudando polling...');
        startPolling();
        // Verificar mensajes inmediatamente
        setTimeout(() => checkForNewMessages(), 100);
    }
};

const handleWindowFocus = () => {
    if (messages.value.length > 0) {
        markMessagesAsRead();
    }
    
    // Asegurar que el polling esté activo
    if (!isPolling.value) {
        startPolling();
    }
};

// Configurar WebSockets (mantener como fallback)
const setupWebSockets = () => {
    if (!window.Echo) {
        console.warn('Pusher/Echo no está disponible, usando polling');
        echoAvailable.value = false;
        return null;
    }

    // Check if conversation has an ID
    if (!props.conversation?.id) {
        console.warn('No conversation ID available for WebSocket setup');
        echoAvailable.value = false;
        return null;
    }

    echoAvailable.value = true;
    
    try {
        const channel = window.Echo.private(`conversation.${props.conversation.id}`);

        channel
            .listen('.message.sent', (data) => {
                addMessageIfNotExists(data);
            })
            .listen('.conversation.updated', (data) => {
                if (data.conversation_id === props.conversation.id) {
                    addMessageIfNotExists(data.message);
                }
            })
            .listenForWhisper('typing', (data) => {
                handleTypingNotification(data);
            })
            .listenForWhisper('online', (data) => {
                onlineStatus.value = data.online;
            });

        return channel;
    } catch (error) {
        console.error('Error setting up WebSocket channel:', error);
        echoAvailable.value = false;
        return null;
    }
};


// Helper para agregar mensajes
const addMessageIfNotExists = (messageData) => {
    if (!messages.value.some(m => m.id === messageData.id)) {
        messages.value.push({
            id: messageData.id,
            content: messageData.content,
            sender: messageData.sender,
            created_at: messageData.created_at
        });
        
        // Actualizar último mensaje ID
        if (messageData.id > lastMessageId.value) {
            lastMessageId.value = messageData.id;
        }
        
        scrollToBottom();
    }
};

// Helper para notificaciones de escritura
const handleTypingNotification = (data) => {
    typing.value = data.typing;
    clearTimeout(typingTimeout.value);
    typingTimeout.value = setTimeout(() => {
        typing.value = false;
    }, 2000);
};

// Limpiar listeners al desmontar
let echoChannel = null;

onMounted(() => {
    console.log('🚀 Componente Chat montado');
    
    scrollToBottom();
    initializeLastMessageId();
    
    // Intentar WebSockets primero, luego polling
    echoChannel = setupWebSockets();
    
    if (echoAvailable.value) {
        notifyOnlineStatus(true);
        console.log('✅ WebSockets disponibles');
    } else {
        console.log('⚠️ WebSockets no disponibles, usando polling');
    }
    
    // Siempre iniciar polling como respaldo
    startPolling();
    
    // Event listeners para visibilidad
    document.addEventListener('visibilitychange', handleVisibilityChange);
    window.addEventListener('focus', handleWindowFocus);
    
    // Marcar mensajes como leídos al cargar
    if (messages.value.length > 0) {
        markMessagesAsRead();
    }
});

onBeforeUnmount(() => {
    console.log('🧹 Limpiando componente Chat...');
    
    stopPolling();
    
    if (echoChannel) {
        echoChannel.leave();
        notifyOnlineStatus(false);
    }
    
    if (typingTimeout.value) {
        clearTimeout(typingTimeout.value);
    }
    
    // Remover event listeners
    document.removeEventListener('visibilitychange', handleVisibilityChange);
    window.removeEventListener('focus', handleWindowFocus);
});

// Notificar estado en línea
const notifyOnlineStatus = (status) => {
    if (echoAvailable.value && window.Echo && props.conversation?.id) {
        try {
            window.Echo.private(`conversation.${props.conversation.id}`)
                .whisper('online', { online: status });
        } catch (error) {
            console.warn('Error sending online status:', error);
        }
    }
};
// Métodos del componente
const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const goBack = () => {
    router.visit('/conversations');
};

const formatTime = (timestamp) => {
    return new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const handleKeyPress = (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim() || loading.value) return;

    loading.value = true;
    const messageContent = newMessage.value.trim();
    
    try {
        const response = await axios.post('/api/messages', {
            conversation_id: props.conversation.id,
            content: messageContent,
        });

        newMessage.value = '';
        
        if (response.data.message) {
            addMessageIfNotExists(response.data.message);
        }
        
        console.log('📤 Mensaje enviado correctamente');
        
    } catch (error) {
        console.error('❌ Error sending message:', error);
        newMessage.value = messageContent;
        
        // Mostrar error al usuario
        alert('Error al enviar mensaje. Inténtalo de nuevo.');
    } finally {
        loading.value = false;
    }
};

// Función para manejar typing (si quieres mantenerla)
const handleTyping = () => {
    // Check if Echo is available and the conversation has an ID
    if (echoAvailable.value && window.Echo && props.conversation?.id) {
        try {
            window.Echo.private(`conversation.${props.conversation.id}`)
                .whisper('typing', { typing: true });
        } catch (error) {
            console.warn('Error sending typing notification:', error);
        }
    }
};

// Función para reconectar manualmente
const reconnect = () => {
    console.log('🔄 Reconectando...');
    stopPolling();
    setTimeout(() => {
        startPolling();
    }, 1000);
};
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-4xl h-screen flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-border bg-card">
                <div class="flex items-center space-x-3">
                    <Button variant="ghost" size="sm" @click="goBack">
                        ← Volver
                    </Button>
                    <div>
                        <h1 class="text-xl font-semibold">{{ otherUser.name }}</h1>
                        <p class="text-sm text-muted-foreground">{{ otherUser.email }}</p>
                        <p v-if="typing" class="text-xs text-green-500">Escribiendo...</p>
                    </div>
                </div>
                
                <!-- 🚀 NUEVO: Indicador de estado de conexión -->
                <div class="flex items-center space-x-2">
                    <div v-if="connectionStatus === 'connected'" class="flex items-center">
                        <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                        <span class="text-xs text-muted-foreground">Conectado</span>
                    </div>
                    <div v-else-if="connectionStatus === 'connecting'" class="flex items-center">
                        <span class="w-2 h-2 rounded-full bg-yellow-500 mr-2 animate-pulse"></span>
                        <span class="text-xs text-muted-foreground">Conectando...</span>
                    </div>
                    <div v-else-if="connectionStatus === 'error'" class="flex items-center">
                        <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span>
                        <span class="text-xs text-muted-foreground">Error</span>
                        <Button variant="ghost" size="sm" @click="reconnect" class="ml-2">
                            🔄
                        </Button>
                    </div>
                    <div v-else class="flex items-center">
                        <span class="w-2 h-2 rounded-full bg-gray-500 mr-2"></span>
                        <span class="text-xs text-muted-foreground">Desconectado</span>
                    </div>
                </div>
            </div>

            <!-- 🚀 NUEVO: Barra de estado de polling -->
            <div v-if="pollingError" class="bg-yellow-50 border-b border-yellow-200 px-4 py-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-yellow-800">⚠️ {{ pollingError }}</span>
                    <Button variant="ghost" size="sm" @click="reconnect" class="text-yellow-800">
                        Reconectar
                    </Button>
                </div>
            </div>

            <!-- Messages Container -->
            <div 
                ref="messagesContainer"
                class="flex-1 overflow-y-auto p-4 space-y-3 bg-background"
            >
                <div
                    v-for="message in messages"
                    :key="message.id"
                    class="flex"
                    :class="message.sender.id === currentUser.id ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg"
                        :class="message.sender.id === currentUser.id 
                            ? 'bg-primary text-primary-foreground' 
                            : 'bg-muted text-muted-foreground'"
                    >
                        <div v-if="message.sender.id !== currentUser.id" class="text-xs font-semibold mb-1">
                            {{ message.sender.name }}
                        </div>
                        <p class="text-sm">{{ message.content }}</p>
                        <p class="text-xs mt-1 opacity-70 text-right">
                            {{ formatTime(message.created_at) }}
                        </p>
                    </div>
                </div>

                <div v-if="messages.length === 0" class="text-center text-muted-foreground py-8">
                    No hay mensajes aún. ¡Inicia la conversación!
                </div>
            </div>

            <!-- Message Input -->
            <div class="p-4 border-t border-border bg-card">
                <!-- 🚀 NUEVO: Indicador de modo de tiempo real -->
                <div class="mb-2 text-sm flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span v-if="echoAvailable" class="text-green-600">
                            ✅ Tiempo real: WebSockets
                        </span>
                        <span v-else class="text-blue-600">
                            🔄 Tiempo real: Polling ({{ isPolling ? 'activo' : 'pausado' }})
                        </span>
                    </div>
                    <div class="text-xs text-muted-foreground">
                        Mensajes: {{ messages.length }} | Último ID: {{ lastMessageId }}
                    </div>
                </div>
                
                <div class="flex space-x-2">
                    <textarea
                        v-model="newMessage"
                        @keydown="handleTyping"
                        @keypress="handleKeyPress"
                        placeholder="Escribe tu mensaje..."
                        rows="2"
                        class="flex-1 min-h-0 resize-none rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                        :disabled="loading"
                    ></textarea>
                    <Button 
                        @click="sendMessage" 
                        :disabled="!newMessage.trim() || loading"
                        class="px-6"
                    >
                        {{ loading ? 'Enviando...' : 'Enviar' }}
                    </Button>
                </div>
                <p class="text-xs text-muted-foreground mt-1">
                    Presiona Enter para enviar, Shift+Enter para nueva línea
                </p>
            </div>
        </div>
    </AppLayout>
</template>