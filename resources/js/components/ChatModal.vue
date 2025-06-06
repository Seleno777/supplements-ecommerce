<script setup lang="ts">
import echo from '@/echo';
import axios from 'axios';
import { computed, nextTick, ref, watch } from 'vue';

let channel: any = null;

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

// Computed: siempre actualizado con props
const baseMessages = computed(() => props.conversation?.messages ?? []);
const extraMessages = ref<any[]>([]);

const allMessages = computed(() => [...baseMessages.value, ...extraMessages.value]);

const newMessage = ref('');
const scrollRef = ref<HTMLElement | null>(null);

const sendMessage = async () => {
    if (!newMessage.value.trim()) return;

    try {
        const response = await axios.post('/messages', {
            conversation_id: props.conversation.id,
            content: newMessage.value.trim(),
        });

        extraMessages.value.push(response.data); // solo nuevos, no duplicamos los props
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

watch(
    () => props.conversation.id,
    (id) => {
        if (!id) return;

        if (channel) {
            echo.leave(`chat.${id}`);
            channel = null;
        }

        channel = echo.private(`chat.${id}`);
        channel.listen('MessageSent', (event: any) => {
            extraMessages.value.push(event.message);
            scrollToBottom();
        });

        scrollToBottom();
    },
    { immediate: true },
);

watch(
    () => props.show,
    (visible) => {
        if (!visible && props.conversation?.id && channel) {
            echo.leave(`chat.${props.conversation.id}`);
            channel = null;
        }
    },
);
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
                    v-for="msg in allMessages"
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
