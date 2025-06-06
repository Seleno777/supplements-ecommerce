<script setup lang="ts">
import ChatModal from '@/Components/ChatModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

defineProps<{ conversations: any[] }>();

const userId = usePage().props.auth.user.id;

const showModal = ref(false);
const selectedConversation = ref<any>(null);

const openChat = async (userToId: number) => {
    try {
        const response = await axios.get(`/conversations/${userToId}`);
        selectedConversation.value = response.data;
        showModal.value = true;
    } catch (error) {
        alert('Error al abrir la conversación.');
    }
};

const closeChat = () => {
    showModal.value = false;
    selectedConversation.value = null;
};
</script>

<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto mt-10">
            <h1 class="mb-6 text-3xl font-bold">💬 Conversaciones</h1>

            <div v-if="conversations.length === 0" class="italic text-muted-foreground">No tienes conversaciones aún.</div>

            <ul class="space-y-4">
                <li v-for="conv in conversations" :key="conv.id" class="p-4 border rounded shadow bg-card">
                    <p class="mb-1 font-medium">
                        Conversación con:
                        {{ conv.user_one_id === userId ? conv.user_two.name : conv.user_one.name }}
                    </p>
                    <button
                        @click="openChat(conv.user_one_id === userId ? conv.user_two.id : conv.user_one.id)"
                        class="text-sm text-blue-600 hover:underline"
                    >
                        Ver mensajes →
                    </button>
                </li>
            </ul>
        </div>

        <ChatModal v-if="showModal" :show="showModal" :conversation="selectedConversation" :user-id="userId" @close="closeChat" />
    </AppLayout>
</template>
