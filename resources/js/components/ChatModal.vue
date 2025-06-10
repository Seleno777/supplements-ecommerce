<script setup>
import { Button } from '@/components/ui/button';
import axios from 'axios';
import { ref, onMounted, nextTick, onBeforeUnmount } from 'vue';
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
  },
  show: {
    type: Boolean,
    default: false
  }
});

// Definir emit para cerrar el modal
const emit = defineEmits(['close']);

// Estado del componente
const messages = ref([]); // inicial vacío, luego cargamos ordenado
const newMessage = ref('');
const loading = ref(false);
const messagesContainer = ref(null);
const typing = ref(false);
const typingTimeout = ref(null);
const onlineStatus = ref(false);
const echoAvailable = ref(false);

// Variables para polling
const pollingInterval = ref(null);
const isPolling = ref(false);
const lastMessageId = ref(0);
const pollingError = ref(null);
const connectionStatus = ref('connecting'); // connecting, connected, error, disconnected

// Variable para el canal de Echo
let echoChannel = null;

// Inicializar mensajes ordenados y último ID
const initializeMessages = () => {
  if (props.conversation.messages.length > 0) {
    // Ordenar por fecha ascendente (del más antiguo al más reciente)
    messages.value = [...props.conversation.messages].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    lastMessageId.value = Math.max(...messages.value.map(m => m.id));
  }
};

// Métodos de polling
const startPolling = () => {
  if (isPolling.value) return;

  console.log('🔄 Iniciando polling para tiempo real...');
  isPolling.value = true;
  connectionStatus.value = 'connected';
  pollingError.value = null;

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
      timeout: 5000
    });

    if (response.data.has_new_messages && response.data.messages.length > 0) {
      console.log(`✅ ${response.data.messages.length} mensajes nuevos recibidos`);

      response.data.messages.forEach(message => {
        addMessageIfNotExists(message);
      });

      lastMessageId.value = response.data.last_message_id;

      scrollToBottom();

      if (document.hasFocus()) {
        markMessagesAsRead();
      }
    }

    if (pollingError.value) {
      pollingError.value = null;
      connectionStatus.value = 'connected';
    }

  } catch (error) {
    console.error('❌ Error en polling:', error);

    if (error.code === 'ECONNABORTED') {
      pollingError.value = 'Timeout de conexión';
    } else if (error.response?.status === 404) {
      console.log('💭 Conversación aún no existe, esperando...');
      pollingError.value = null;
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

// Eventos de visibilidad y foco
const handleVisibilityChange = () => {
  if (document.hidden) {
    console.log('👁️ Ventana oculta, pausando polling...');
    stopPolling();
  } else {
    console.log('👁️ Ventana visible, reanudando polling...');
    startPolling();
    setTimeout(() => checkForNewMessages(), 100);
  }
};

const handleWindowFocus = () => {
  if (messages.value.length > 0) {
    markMessagesAsRead();
  }

  if (!isPolling.value) {
    startPolling();
  }
};

// Configurar WebSockets (fallback si está disponible)
const setupWebSockets = () => {
  if (!window.Echo) {
    console.warn('Pusher/Echo no está disponible, usando polling');
    echoAvailable.value = false;
    return null;
  }

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

// Helper para agregar mensajes ordenadamente y evitar duplicados
const addMessageIfNotExists = (messageData) => {
  if (!messages.value.some(m => m.id === messageData.id)) {
    messages.value.push({
      id: messageData.id,
      content: messageData.content,
      sender: messageData.sender,
      created_at: messageData.created_at
    });

    // Ordenar mensajes después de agregar nuevo para mantener el orden correcto
    messages.value.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

    if (messageData.id > lastMessageId.value) {
      lastMessageId.value = messageData.id;
    }

    scrollToBottom();
  }
};

// Notificaciones de escritura
const handleTypingNotification = (data) => {
  typing.value = data.typing;
  clearTimeout(typingTimeout.value);
  typingTimeout.value = setTimeout(() => {
    typing.value = false;
  }, 2000);
};

// Función para cerrar el modal
const closeModal = () => {
  emit('close');
};

// Función para manejar clicks en el backdrop
const handleBackdropClick = (event) => {
  if (event.target === event.currentTarget) {
    closeModal();
  }
};

// Función para manejar la tecla Escape
const handleEscapeKey = (event) => {
  if (event.key === 'Escape') {
    closeModal();
  }
};

onMounted(() => {
  console.log('🚀 Componente Chat montado');

  initializeMessages();
  scrollToBottom();

  echoChannel = setupWebSockets();

  if (echoAvailable.value) {
    notifyOnlineStatus(true);
    console.log('✅ WebSockets disponibles');
  } else {
    console.log('⚠️ WebSockets no disponibles, usando polling');
  }

  startPolling();

  document.addEventListener('visibilitychange', handleVisibilityChange);
  window.addEventListener('focus', handleWindowFocus);
  document.addEventListener('keydown', handleEscapeKey);

  if (messages.value.length > 0) {
    markMessagesAsRead();
  }
});

onBeforeUnmount(() => {
  console.log('🧹 Limpiando componente Chat...');

  stopPolling();

  // Corregir la limpieza del canal de Echo
  if (echoChannel && typeof echoChannel.leave === 'function') {
    try {
      echoChannel.leave();
      notifyOnlineStatus(false);
    } catch (error) {
      console.warn('Error al abandonar el canal de Echo:', error);
    }
  }

  if (typingTimeout.value) {
    clearTimeout(typingTimeout.value);
  }

  document.removeEventListener('visibilitychange', handleVisibilityChange);
  window.removeEventListener('focus', handleWindowFocus);
  document.removeEventListener('keydown', handleEscapeKey);
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

// Scroll automático al final
const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
};

// Formatear hora del mensaje
const formatTime = (timestamp) => {
  return new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

// Enviar mensaje con tecla Enter
const handleKeyPress = (event) => {
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault();
    sendMessage();
  }
};

// Enviar mensaje al servidor
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
      scrollToBottom();
    }

  } catch (error) {
    console.error('Error enviando mensaje:', error);
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <!-- Modal backdrop -->
  <div
    v-if="show"
    class="fixed inset-0 z-50 overflow-y-auto"
    @click="handleBackdropClick"
  >
    <!-- Backdrop oscuro -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
    
    <!-- Contenedor del modal -->
    <div class="flex min-h-screen items-center justify-center p-4">
      <!-- Modal content -->
      <div 
        class="relative w-full max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all"
        @click.stop
      >
        <!-- Contenedor del chat -->
        <div class="flex flex-col h-[80vh] max-h-[600px]">
          
          <!-- Header del chat -->
          <header class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-t-lg">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                {{ props.otherUser.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ props.otherUser.name }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  <span v-if="onlineStatus" class="text-green-500">● En línea</span>
                  <span v-else class="text-gray-400">● Desconectado</span>
                </p>
              </div>
            </div>
            
            <!-- Botón de cerrar -->
            <button
              @click="closeModal"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
              aria-label="Cerrar chat"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </header>

          <!-- Área de mensajes con altura fija para permitir scroll -->
          <main 
            ref="messagesContainer" 
            class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50 dark:bg-gray-900"
          >
            <!-- Mensaje cuando no hay conversación -->
            <div v-if="messages.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">
              <div class="mb-4">
                <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
              </div>
              <p class="text-lg">No hay mensajes aún</p>
              <p class="text-sm">¡Inicia la conversación!</p>
            </div>

            <!-- Lista de mensajes -->
            <div
              v-for="message in messages"
              :key="message.id"
              class="flex"
              :class="message.sender.id === currentUser.id ? 'justify-end' : 'justify-start'"
            >
              <div
                :class="{
                  'bg-blue-500 text-white': message.sender.id === currentUser.id,
                  'bg-white dark:bg-gray-700 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600': message.sender.id !== currentUser.id
                }"
                class="max-w-xs lg:max-w-md px-4 py-2 rounded-2xl shadow-sm"
              >
                <p class="text-sm whitespace-pre-wrap">{{ message.content }}</p>
                <div class="flex justify-end mt-1">
                  <span 
                    :class="message.sender.id === currentUser.id ? 'text-blue-100' : 'text-gray-500 dark:text-gray-400'"
                    class="text-xs"
                  >
                    {{ formatTime(message.created_at) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Indicador de escritura -->
            <div v-if="typing" class="flex justify-start">
              <div class="bg-gray-200 dark:bg-gray-700 px-4 py-2 rounded-2xl max-w-xs">
                <div class="flex items-center space-x-1">
                  <div class="flex space-x-1">
                    <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                  </div>
                  <span class="text-xs text-gray-500 ml-2">{{ props.otherUser.name }} está escribiendo...</span>
                </div>
              </div>
            </div>
          </main>

          <!-- Pie del chat con input -->
          <footer class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-b-lg">
            <div class="flex items-end space-x-3">
              <div class="flex-1">
                <textarea
                  v-model="newMessage"
                  @keypress="handleKeyPress"
                  rows="1"
                  placeholder="Escribe un mensaje..."
                  class="w-full resize-none rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent max-h-32"
                  style="min-height: 40px;"
                ></textarea>
              </div>
              <Button
                :disabled="loading || !newMessage.trim()"
                @click="sendMessage"
                class="px-6 py-2 bg-blue-500 hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-lg transition-colors"
              >
                <span v-if="loading">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Enviando...
                </span>
                <span v-else>Enviar</span>
              </Button>
            </div>
          </footer>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Scroll smooth para contenedor de mensajes */
main {
  scroll-behavior: smooth;
}

/* Personalizar scrollbar */
main::-webkit-scrollbar {
  width: 6px;
}

main::-webkit-scrollbar-track {
  background: transparent;
}

main::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 3px;
}

main::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}

/* Animación para los puntos de escritura */
@keyframes bounce {
  0%, 80%, 100% {
    transform: scale(0);
  }
  40% {
    transform: scale(1);
  }
}

.animate-bounce {
  animation: bounce 1.4s infinite ease-in-out both;
}
</style>