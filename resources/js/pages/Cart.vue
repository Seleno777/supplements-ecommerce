<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { CartItem } from '@/types';
import axios from 'axios';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{ cartItems: CartItem[] }>();
const cartItems = props.cartItems;

const loading = ref(false);

// -------------------- Notificación --------------------
const notification = ref<{ message: string; type: 'success' | 'error' | 'info' | null }>({
    message: '',
    type: null,
});
const showNotification = ref(false);

function notify(message: string, type: 'success' | 'error' | 'info' = 'success') {
    notification.value = { message, type };
    showNotification.value = true;
    setTimeout(() => (showNotification.value = false), 3000);
}

// -------------------- Confirmación personalizada --------------------
const confirmDialog = ref(false);
const confirmMessage = ref('');
const onConfirmAction = ref<() => void>(() => {});
const confirmCancelMessage = ref('');

function openConfirmDialog(message: string, onConfirm: () => void, cancelMessage = 'Acción cancelada') {
    confirmMessage.value = message;
    confirmCancelMessage.value = cancelMessage;
    onConfirmAction.value = onConfirm;
    confirmDialog.value = true;
}

function cancelConfirm() {
    notify(confirmCancelMessage.value, 'info');
    confirmDialog.value = false;
}

function proceedConfirm() {
    confirmDialog.value = false;
    onConfirmAction.value();
}

// -------------------- Acciones --------------------
const updateQuantity = async (item: CartItem, change: number) => {
    const newQty = item.quantity + change;
    if (newQty < 1 || newQty > item.product.stock) return;

    loading.value = true;
    try {
        await axios.post('/cart', {
            product_id: item.product_id,
            quantity: newQty,
        });
        item.quantity = newQty;
    } catch {
        notify('Error al actualizar cantidad.', 'error');
    } finally {
        loading.value = false;
    }
};

const removeItem = (id: number) => {
    openConfirmDialog('¿Eliminar este producto del carrito?', async () => {
        loading.value = true;
        try {
            await axios.delete(`/cart/${id}`);
            notify('Producto eliminado del carrito.', 'success');
            setTimeout(() => location.reload(), 1000);
        } catch {
            notify('Error al eliminar el producto.', 'error');
        } finally {
            loading.value = false;
        }
    }, 'Eliminación cancelada.');
};

const checkout = () => {
    openConfirmDialog('¿Deseas finalizar tu compra?', async () => {
        loading.value = true;
        try {
            await axios.post('/checkout');
            notify('Compra realizada con éxito 🎉', 'success');
            setTimeout(() => router.visit('/orders'), 1000);
        } catch (err: any) {
            notify(err.response?.data?.message || 'Ocurrió un error durante el checkout.', 'error');
        } finally {
            loading.value = false;
        }
    }, 'Compra cancelada.');
};
</script>

<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto">
            <h1 class="mb-6 text-3xl font-bold">🛒 Carrito de compras</h1>

            <div v-if="cartItems.length === 0" class="italic text-muted-foreground">Tu carrito está vacío.</div>

            <div v-else class="space-y-6">
                <div v-for="item in cartItems" :key="item.id" class="p-4 border rounded shadow-sm bg-card border-border">
                    <h2 class="text-lg font-semibold">{{ item.product.name }}</h2>
                    <p class="mb-2 text-sm text-muted-foreground">{{ item.product.description }}</p>

                    <div class="flex items-center gap-4">
                        <button @click="updateQuantity(item, -1)" :disabled="item.quantity <= 1 || loading">−</button>
                        <span class="font-semibold">{{ item.quantity }}</span>
                        <button @click="updateQuantity(item, 1)" :disabled="item.quantity >= item.product.stock || loading">+</button>
                        <span class="ml-auto">{{ (item.product.price * item.quantity).toFixed(2) }} USD</span>
                        <button @click="removeItem(item.product_id)" class="ml-4 text-red-600">🗑 Eliminar</button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button
                    @click="checkout"
                    :disabled="loading || cartItems.length === 0"
                    class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700 disabled:opacity-50"
                >
                    Finalizar compra 🛒
                </button>
            </div>
        </div>

        <!-- Confirmación personalizada -->
        <div v-if="confirmDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800 max-w-sm w-full text-center">
                <p class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">{{ confirmMessage }}</p>
                <div class="flex justify-center gap-4">
                    <button
                        @click="proceedConfirm"
                        class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700"
                    >
                        Sí
                    </button>
                    <button
                        @click="cancelConfirm"
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
