<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { Product } from '@/types';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

defineProps<{
    products: Product[];
}>();

const loading = ref(false);

const deleteProduct = async (id: number) => {
    if (!confirm('¿Estás seguro de eliminar este producto?')) return;

    loading.value = true;
    try {
        await axios.delete(`/products/${id}`);
        router.reload({ only: ['products'] });
    } catch (err) {
        alert('Error al eliminar el producto.');
    } finally {
        loading.value = false;
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
                        <button class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">Editar</button>
                        <button
                            @click="deleteProduct(product.id)"
                            :disabled="loading"
                            class="px-3 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700"
                        >
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
