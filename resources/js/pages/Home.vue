<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Product } from '@/types';
import axios from 'axios';
import { onMounted, ref } from 'vue';

const products = ref<Product[]>([]);

onMounted(async () => {
    const { data } = await axios.get('/api/products');
    products.value = data;
});
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl">
            <h1 class="font-gym text-primary mb-8 text-4xl tracking-wide">🏋️ Suplementos disponibles</h1>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="bg-card text-card-foreground border-border flex flex-col justify-between rounded-2xl border p-5 shadow-md transition-all hover:shadow-xl"
                >
                    <div>
                        <h2 class="mb-1 text-xl font-semibold">{{ product.name }}</h2>
                        <p class="text-muted-foreground text-sm">{{ product.description }}</p>
                    </div>

                    <div class="mt-4">
                        <p class="text-primary text-lg font-bold">{{ Number(product.price).toFixed(2) }} USD</p>
                        <p class="text-muted-foreground text-xs">Stock: {{ product.stock }}</p>
                        <p class="text-muted-foreground text-xs italic">Vendido por: {{ product.user?.name }}</p>
                    </div>

                    <Button class="mt-4 w-full">Agregar al carrito</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
