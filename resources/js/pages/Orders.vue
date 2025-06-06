<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { Order } from '@/types';

defineProps<{ orders: Order[] }>();
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto mt-10">
            <h1 class="mb-6 text-3xl font-bold">📦 Mis órdenes</h1>

            <div v-if="orders.length === 0" class="italic text-muted-foreground">
                Aún no has realizado ninguna orden.
            </div>

            <div v-else class="space-y-4">
                <div v-for="order in orders" :key="order.id" class="p-4 border rounded-xl bg-card">
                    <h2 class="mb-2 text-lg font-semibold">Orden #{{ order.id }}</h2>
                    <p class="mb-2 text-sm text-muted-foreground">Total: ${{ Number(order.total_price).toFixed(2) }}</p>
                    <ul class="ml-4 text-sm list-disc text-muted-foreground">
                        <li v-for="item in order.items" :key="item.id">
                            {{ item.quantity }} × {{ item.product?.name }} ({{ Number(item.price).toFixed(2) }} USD c/u)
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
