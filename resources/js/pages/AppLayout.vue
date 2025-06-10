<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const userId = page.props.auth.user.id;

let echoChannel = null;

// 🚀 Estado global para mostrar "Nuevo mensaje"
const hasNewMessages = ref(false);

// 🚀 Guardamos en window para que ChatModal.vue lo pueda usar
window.hasNewMessages = hasNewMessages;

// 🚀 Función para limpiar badge - definida fuera para poder removerla
const clearNewMessagesHandler = () => {
    hasNewMessages.value = false;
    console.log('🟢 [AppLayout] Badge de Nuevo mensaje limpiado');
};

// 🚀 Montaje
onMounted(() => {
    if (!window.Echo) {
        console.warn('❌ Pusher/Echo no está disponible');
        return;
    }

    console.log(`✅ [AppLayout] Suscribiéndome a canal privado: user.${userId}`);

    echoChannel = window.Echo.private(`user.${userId}`);

    echoChannel.listen('.conversation.updated', (data) => {
        console.log('✅ [AppLayout] EVENTO conversation.updated recibido:', data);

        // 🚀 Emitimos evento global
        window.dispatchEvent(new CustomEvent('conversation-updated', { detail: data }));

        // 🚀 Si no estamos en la conversación activa → marcamos que hay mensaje nuevo
        if (!window.currentConversationId || window.currentConversationId !== data.conversation_id) {
            hasNewMessages.value = true;
            console.log('🟢 [AppLayout] Nuevo mensaje recibido fuera de la conversación activa');
        }
    });

    echoChannel.subscription
        .bind('pusher:subscription_succeeded', () => {
            console.log('✅ [AppLayout] Suscripción al canal user.' + userId + ' EXITOSA');
        })
        .bind('pusher:subscription_error', (status) => {
            console.error('❌ [AppLayout] ERROR al suscribirse al canal user.' + userId, status);
        });

    // 🚀 Escuchar evento global para limpiar badge
    window.addEventListener('clear-new-messages', clearNewMessagesHandler);
});

// 🚀 Desmontaje - CORREGIDO
onBeforeUnmount(() => {
    console.log('🧹 [AppLayout] Limpiando...');
    
    // Limpiar WebSocket - CORREGIDO
    if (echoChannel && typeof echoChannel.leave === 'function') {
        try {
            echoChannel.stopListening('.conversation.updated');
            echoChannel.leave();
            console.log(`✅ [AppLayout] Canal privado user.${userId} cerrado correctamente`);
        } catch (error) {
            console.warn('⚠️ [AppLayout] Error cerrando canal:', error);
        }
    }

    // Limpiar event listener - CORREGIDO
    window.removeEventListener('clear-new-messages', clearNewMessagesHandler);
    
    // Limpiar variable global
    if (window.hasNewMessages) {
        window.hasNewMessages = null;
    }
    
    // Reset variables
    echoChannel = null;
});
</script>

<template>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-100 p-4">
            <nav class="space-y-2">
                <a href="/" class="block py-2 px-4 rounded hover:bg-gray-200">Inicio</a>
                <a href="/cart" class="block py-2 px-4 rounded hover:bg-gray-200">Carrito</a>
                <a href="/my-products" class="block py-2 px-4 rounded hover:bg-gray-200">Mis productos</a>
                <a href="/orders" class="block py-2 px-4 rounded hover:bg-gray-200">Mis pedidos</a>
                <a href="/notifications" class="block py-2 px-4 rounded hover:bg-gray-200">Notificaciones</a>

                <!-- 🚀 Menú Chat con badge -->
                <a href="/conversations" class="flex items-center justify-between py-2 px-4 rounded hover:bg-gray-200">
                    <span>Chat</span>
                    <span v-if="hasNewMessages" class="ml-2 inline-block bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">
                        Nuevo
                    </span>
                </a>

                <a href="/products/create" class="block py-2 px-4 rounded hover:bg-gray-200">Crear producto</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 bg-white p-4 overflow-auto">
            <slot />
        </main>
    </div>
</template>