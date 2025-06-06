<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { CartItem } from '@/types';
import { ref } from 'vue';
import axios from 'axios';

defineProps<{ cartItems: CartItem[] }>();

const loading = ref(false);

const updateQuantity = async (item: CartItem, change: number) => {
    const newQty = item.quantity + change;

    if (newQty < 1 || newQty > item.product.stock) return;

    loading.value = true;
    try {
        await axios.post('/cart', {
            product_id: item.product_id,
            quantity: newQty,
        });
        item.quantity = newQty; // Actualizamos localmente para evitar recarga
    } catch {
        alert('Error al actualizar cantidad.');
    } finally {
        loading.value = false;
    }
};

const removeItem = async (id: number) => {
    if (!confirm('¿Eliminar este producto del carrito?')) return;

    loading.value = true;
    try {
        await axios.delete(`/cart/${id}`);
        location.reload(); // o puedes emitir un evento para quitarlo sin recargar
    } catch {
        alert('Error al eliminar el producto.');
    } finally {
        loading.value = false;
    }
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
        </div>
    </AppLayout>
</template>
