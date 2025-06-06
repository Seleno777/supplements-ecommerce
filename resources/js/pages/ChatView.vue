<script setup lang="ts">
import echo from '@/echo';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { nextTick, onMounted, ref } from 'vue';

const props = defineProps<{
    conversation: {
        id: number;
        messages: {
            id: number;
            content: string;
            sender: { id: number; name: string };
            created_at: string;
        }[];
    };
}>();

const messages = ref([...props.conversation.messages]);
const newMessage = ref('');
const userId = usePage().props.auth.user.id;

const scrollRef = ref<HTMLElement | null>(null);

const sendMessage = async () => {
    if (!newMessage.value.trim()) return;

    await axios.post('/messages', {
        conversation_id: props.conversation.id,
        content: newMessage.value.trim(),
    });

    newMessage.value = '';
};

const scrollToBottom = () => {
    nextTick(() => {
        scrollRef.value?.scrollIntoView({ behavior: 'smooth' });
    });
};

onMounted(() => {
    echo.private(`chat.${props.conversation.id}`).listen('MessageSent', (event: any) => {
        messages.value.push(event.message);
        scrollToBottom();
    });

    scrollToBottom();
});
</script>

<template>
    <AppLayout>
        <div class="max-w-3xl p-6 mx-auto bg-white rounded shadow dark:bg-gray-800">
            <h1 class="mb-4 text-xl font-bold">💬 Conversación</h1>

            <div class="p-4 mb-4 space-y-3 overflow-y-auto bg-gray-100 border rounded h-96 dark:bg-gray-700">
                <div
                    v-for="msg in messages"
                    :key="msg.id"
                    :class="{
                        'text-right': msg.sender.id === userId,
                        'text-left': msg.sender.id !== userId,
                    }"
                >
                    <div :class="['inline-block rounded px-3 py-2', msg.sender.id === userId ? 'bg-blue-600 text-white' : 'bg-gray-300 text-black']">
                        <span class="block text-sm font-medium">{{ msg.sender.name }}</span>
                        <span>{{ msg.content }}</span>
                    </div>
                </div>
                <div ref="scrollRef" />
            </div>

            <form @submit.prevent="sendMessage" class="flex gap-2">
                <input
                    v-model="newMessage"
                    type="text"
                    placeholder="Escribe tu mensaje..."
                    class="flex-1 px-4 py-2 border rounded focus:outline-none"
                />
                <button type="submit" class="px-4 py-2 text-white rounded bg-primary hover:bg-primary/90">Enviar</button>
            </form>
        </div>
    </AppLayout>
</template>
