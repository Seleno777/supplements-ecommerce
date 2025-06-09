<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Message, User, Conversation } from '@/types';
import axios from 'axios';
import { ref, onMounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';

interface Props {
    conversation: Conversation;
    messages: Message[];
    otherUser: User;
    currentUserId: number
}

const props = defineProps<Props>();

const messages = ref<Message[]>(props.messages || []);
const newMessage = ref('');
const loading = ref(false);
const messagesContainer = ref<HTMLElement>();

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

onMounted(() => {
    scrollToBottom();
    
    // Set up real-time messaging if using broadcasting
    if (window.Echo) {
        window.Echo.private(`conversation.${props.conversation.id}`)
            .listen('message.sent', (e: any) => {
                messages.value.push(e.message);
                scrollToBottom();
            });
    }
});

const sendMessage = async () => {
    if (!newMessage.value.trim() || loading.value) return;

    loading.value = true;
    
    try {
        const response = await axios.post('/messages', {
            conversation_id: props.conversation.id,
            content: newMessage.value.trim(),
        });

        // Add message to local state if not using real-time updates
        if (!window.Echo) {
            messages.value.push(response.data);
            scrollToBottom();
        }

        newMessage.value = '';
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Error al enviar el mensaje');
    } finally {
        loading.value = false;
    }
};

const goBack = () => {
    router.visit('/conversations');
};

const handleKeyPress = (event: KeyboardEvent) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
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
                    </div>
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
                    :class="message.sender_id === currentUserId ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg"
                        :class="message.sender_id === currentUserId 
                            ? 'bg-primary text-primary-foreground' 
                            : 'bg-muted text-muted-foreground'"
                    >
                        <p class="text-sm">{{ message.content }}</p>
                        <p class="text-xs mt-1 opacity-70">
                            {{ new Date(message.created_at).toLocaleTimeString() }}
                        </p>
                    </div>
                </div>

                <div v-if="messages.length === 0" class="text-center text-muted-foreground py-8">
                    No hay mensajes aún. ¡Inicia la conversación!
                </div>
            </div>

            <!-- Message Input -->
            <div class="p-4 border-t border-border bg-card">
                <div class="flex space-x-2">
                    <textarea
                        v-model="newMessage"
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