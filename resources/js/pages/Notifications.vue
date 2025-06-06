<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { Notification } from '@/types';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

defineProps<{ notifications: Notification[] }>();

const loading = ref<number | null>(null);

const markAsRead = async (id: number) => {
    loading.value = id;
    try {
        await axios.patch(`/notifications/${id}/read`);
        router.reload({ only: ['notifications'] }); // Evita recargar todo
    } catch {
        alert('Error al marcar como leída');
    } finally {
        loading.value = null;
    }
};
</script>

<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto">
            <h1 class="mb-6 text-3xl font-bold">🔔 Notificaciones</h1>

            <div v-if="notifications.length === 0" class="italic text-muted-foreground">No tienes notificaciones.</div>

            <ul class="space-y-4" v-else>
                <li v-for="n in notifications" :key="n.id" class="p-4 border rounded shadow-sm border-border bg-card">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm">{{ n.message }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ new Date(n.created_at).toLocaleString() }}
                            </p>
                        </div>
                        <button
                            v-if="!n.read_at"
                            @click="markAsRead(n.id)"
                            :disabled="loading === n.id"
                            class="ml-4 text-sm text-blue-600 hover:underline disabled:opacity-50"
                        >
                            Marcar como leída
                        </button>
                        <span v-else class="ml-4 text-sm text-muted-foreground">✅ Leída</span>
                    </div>
                </li>
            </ul>
        </div>
    </AppLayout>
</template>
