<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { Product } from '@/types';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

defineProps<{ products: Product[] }>();

const loading = ref(false);

// Notificación
const notification = ref<{ message: string; type: 'success' | 'error' | 'info' | null }>({
    message: '',
    type: null,
});
const showNotification = ref(false);

function notify(message: string, type: 'success' | 'error' | 'info' = 'success') {
    notification.value = { message, type };
    showNotification.value = true;
    setTimeout(() => {
        showNotification.value = false;
    }, 3000);
}

// Confirmación personalizada
const confirmDialog = ref(false);
const productIdToDelete = ref<number | null>(null);

const confirmDelete = (id: number) => {
    productIdToDelete.value = id;
    confirmDialog.value = true;
};

const cancelDelete = () => {
    confirmDialog.value = false;
    productIdToDelete.value = null;
    notify('Eliminación cancelada ❕', 'info');
};

const proceedDelete = async () => {
    if (!productIdToDelete.value) return;

    loading.value = true;
    confirmDialog.value = false;

    try {
        await axios.delete(`/products/${productIdToDelete.value}`);
        notify('Producto eliminado correctamente 🗑️', 'success');
        setTimeout(() => router.visit('/my-products'), 1000);
    } catch (err) {
        notify('Error al eliminar el producto ❌', 'error');
    } finally {
        loading.value = false;
        productIdToDelete.value = null;
    }
};
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto">
            <h1 class="mb-6 text-3xl font-bold">📦 Mis productos</h1>

            <div v-if="products.length === 0" class="italic text-muted-foreground">Aún no has publicado ningún producto.</div>

            <div v-else class="space-y-4">
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="flex flex-col justify-between p-4 border shadow-sm bg-card border-border rounded-xl"
                >
                    <div>
                        <h2 class="text-xl font-semibold">{{ product.name }}</h2>
                        <p class="text-sm text-muted-foreground">{{ product.description }}</p>
                        <p class="text-sm italic text-muted-foreground">Stock: {{ product.stock }}</p>
                    </div>

                    <div class="flex gap-2 mt-3">
                        <Link :href="`/products/${product.id}/edit`" class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
                            Editar
                        </Link>
                        <button
                            @click="confirmDelete(product.id)"
                            :disabled="loading"
                            class="px-3 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700 disabled:opacity-50"
                        >
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmación personalizada -->
        <div
            v-if="confirmDialog"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        >
            <div class="p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800 max-w-sm w-full text-center">
                <p class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                    ¿Estás seguro de eliminar este producto?
                </p>
                <div class="flex justify-center gap-4">
                    <button
                        @click="proceedDelete"
                        class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700"
                    >
                        Sí, eliminar
                    </button>
                    <button
                        @click="cancelDelete"
                        class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

        <!-- Notificación -->
        <div
            v-if="showNotification"
            :class="[
                'fixed top-6 right-6 px-5 py-3 rounded shadow-lg text-white font-semibold select-none z-50',
                notification.type === 'success' ? 'bg-green-500' :
                notification.type === 'error' ? 'bg-red-500' : 'bg-yellow-500'
            ]"
        >
            {{ notification.message }}
        </div>
    </AppLayout>
</template>
