<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Product } from '@/types';
import axios from 'axios';
import { onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const products = ref<Product[]>([]);
const loading = ref(false);

onMounted(async () => {
    const { data } = await axios.get('/products');
    products.value = data;
});

const addToCart = async (productId: number) => {
    try {
        await axios.post('/cart', {
            product_id: productId,
            quantity: 1,
        });
        alert('Producto agregado al carrito ✅');
    } catch (err: any) {
        if (err.response?.status === 409) {
            alert('Este producto ya está en el carrito.');
        } else {
            alert('Ocurrió un error al agregar al carrito.');
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
        <div class="mx-auto max-w-7xl">
            <h1 class="mb-8 text-4xl tracking-wide font-gym text-primary">🏋️ Suplementos disponibles</h1>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
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

                    <Button class="w-full mt-4" @click="startChat(product.user.id)"> 💬 Chatear con el vendedor </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
