<script setup lang="ts">
import { ref, watch, nextTick, onMounted } from 'vue';
import echo from '@/echo';
import axios from 'axios';

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
    userId: number;
    show: boolean;
}>();

const emit = defineEmits(['close']);

const messages = ref([...props.conversation?.messages ?? []]);
const newMessage = ref('');
const scrollRef = ref<HTMLElement | null>(null);

const sendMessage = async () => {
    if (!newMessage.value.trim()) return;

    try {
        const response = await axios.post('/messages', {
            conversation_id: props.conversation.id,
            content: newMessage.value.trim(),
        });

        // Esto muestra el mensaje enviado localmente de inmediato
        messages.value.push(response.data);

        scrollToBottom();
        newMessage.value = '';
    } catch (err) {
        alert('Error al enviar el mensaje.');
    }
};

const scrollToBottom = () => {
    nextTick(() => {
        scrollRef.value?.scrollIntoView({ behavior: 'smooth' });
    });
};

watch(() => props.show, (visible) => {
    if (visible) scrollToBottom();
});

onMounted(() => {
    echo.private(`chat.${props.conversation.id}`).listen('MessageSent', (event: any) => {
        messages.value.push(event.message);
        scrollToBottom();
    });

    scrollToBottom();
});
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="w-full max-w-xl p-4 bg-white rounded shadow-lg dark:bg-gray-800">
            <div class="flex justify-between mb-4">
                <h2 class="text-lg font-bold">💬 Chat</h2>
                <button @click="emit('close')" class="text-red-500 hover:underline">Cerrar</button>
            </div>

            <div class="p-2 mb-4 space-y-2 overflow-y-auto bg-gray-100 rounded h-80 dark:bg-gray-700">
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
    </div>
</template>
