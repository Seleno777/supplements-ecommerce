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
  }
});

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

// Limpiar listeners al desmontar
let echoChannel = null;

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

// Scroll automático al final
const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
};

// Navegar atrás
const goBack = () => {
  router.visit('/conversations');
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
  <div class="flex flex-col h-full w-full">
    <header class="flex items-center p-4 border-b border-gray-200 dark:border-gray-700">
      <button
        class="mr-4 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
        @click="goBack"
        aria-label="Regresar"
      >
        ←
      </button>
      <h1 class="text-xl font-semibold">
        Chat con {{ props.otherUser.name }}
      </h1>
    </header>

    <main ref="messagesContainer" class="flex-grow overflow-auto p-4 space-y-2 bg-white dark:bg-gray-900 flex flex-col">
      <div
        v-for="message in messages"
        :key="message.id"
        class="flex"
        :class="message.sender.id === currentUser.id ? 'justify-end' : 'justify-start'"
      >
        <div
          :class="{
            'bg-blue-500 text-white rounded-lg p-2 max-w-xs': message.sender.id === currentUser.id,
            'bg-gray-300 dark:bg-gray-700 rounded-lg p-2 max-w-xs': message.sender.id !== currentUser.id
          }"
          class="inline-block"
        >
          <p class="whitespace-pre-wrap">{{ message.content }}</p>
          <span class="text-xs opacity-70 float-right">{{ formatTime(message.created_at) }}</span>
        </div>
      </div>

      <p v-if="typing" class="italic text-sm text-gray-500 dark:text-gray-400 text-center">
        {{ props.otherUser.name }} está escribiendo...
      </p>
    </main>

    <footer class="p-4 border-t border-gray-200 dark:border-gray-700 flex space-x-2">
      <textarea
        v-model="newMessage"
        @keypress="handleKeyPress"
        rows="1"
        placeholder="Escribe un mensaje..."
        class="flex-grow resize-none rounded-md border border-gray-300 dark:border-gray-600 px-3 py-2 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
      ></textarea>
      <Button
        :loading="loading"
        :disabled="loading || !newMessage.trim()"
        @click="sendMessage"
      >
        Enviar
      </Button>
    </footer>
  </div>
</template>

<style scoped>
/* Scroll smooth para contenedor de mensajes */
main {
  scroll-behavior: smooth;
}
</style>
