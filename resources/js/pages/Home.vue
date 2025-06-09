<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Product } from '@/types';
import axios from 'axios';
import { onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Pill } from 'lucide-vue-next'; // Ícono que representa suplementos

const products = ref<Product[]>([]);
const loading = ref(false);

onMounted(async () => {
    const { data } = await axios.get('/products');
    products.value = data;
});

// Notificaciones simples
const notification = ref<{ message: string; type: 'success' | 'error' | null }>({ message: '', type: null });
const showNotification = ref(false);

function notify(message: string, type: 'success' | 'error' = 'success') {
  notification.value = { message, type };
  showNotification.value = true;
  setTimeout(() => {
    showNotification.value = false;
  }, 3000);
}

const addToCart = async (productId: number) => {
    try {
        await axios.post('/cart', {
            product_id: productId,
            quantity: 1,
        });
        notify('Producto agregado al carrito ✅', 'success');
    } catch (err: any) {
        if (err.response?.status === 409) {
            notify('Este producto ya está en el carrito.', 'error');
        } else {
            notify('Ocurrió un error al agregar al carrito.', 'error');
        }
    }
};

const startChat = (userId: number) => {
    if (!userId) return;
    router.visit(`/conversations/${userId}`);
};
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
            <h1 class="mb-10 flex items-center gap-4 text-5xl sm:text-6xl font-black uppercase tracking-wide text-primary">
                <Pill class="w-10 h-10 sm:w-12 sm:h-12 text-primary" />
                Suplementos disponibles
            </h1>

            <div
                class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 justify-center items-stretch"
            >
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="flex flex-col justify-between p-5 transition-all border shadow-md bg-card text-card-foreground border-border rounded-2xl hover:shadow-xl"
                >
                    <div>
                        <h2 class="mb-1 text-xl font-semibold">{{ product.name }}</h2>
                        <p class="text-sm text-muted-foreground">{{ product.description }}</p>
                    </div>

                    <div class="mt-4">
                        <p class="text-lg font-bold text-primary">{{ Number(product.price).toFixed(2) }} USD</p>
                        <p class="text-xs text-muted-foreground">Stock: {{ product.stock }}</p>
                        <p class="text-xs italic text-muted-foreground">Vendido por: {{ product.user?.name }}</p>
                    </div>

                    <Button class="w-full mt-4" @click="addToCart(product.id)">Agregar al carrito</Button>
                    <Button class="w-full mt-4" @click="startChat(product.user.id)">💬 Chatear con el vendedor</Button>
                </div>
            </div>
        </div>

        <!-- Notificación -->
        <div
          v-if="showNotification"
          :class="[
            'fixed top-6 right-6 px-5 py-3 rounded shadow-lg text-white font-semibold select-none',
            notification.type === 'success' ? 'bg-green-500' : 'bg-red-500'
          ]"
        >
          {{ notification.message }}
        </div>
    </AppLayout>
</template>
