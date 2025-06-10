<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Product } from '@/types';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import { Pill } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import Notification from '@/components/ui/notification/Notification.vue';

// Datos reactivos
const products = ref<Product[]>([]);
const loading = ref(false);

// Obtener usuario autenticado desde props
const page = usePage();
const currentUserId = ref<number | null>(page.props.auth?.user?.id || null);

// Notificaciones
const notification = ref<{ message: string; type: 'success' | 'error' | null }>({ message: '', type: null });
const showNotification = ref(false);

function notify(message: string, type: 'success' | 'error' = 'success') {
    notification.value = { message, type };
    showNotification.value = true;
    setTimeout(() => {
        showNotification.value = false;
    }, 3000);
}

// Cargar productos, excluyendo los del usuario actual
onMounted(async () => {
    const { data } = await axios.get('/products');
    products.value = data.filter((product: Product) => product.user?.id !== currentUserId.value);
});

// Agregar al carrito
const addToCart = async (productId: number) => {
    try {
        await axios.post('/cart', {
            product_id: productId,
            quantity: 1,
        });
        notify('Producto agregado al carrito ✅', 'success');
    } catch (err: any) {
        notify(err.response?.data?.message || 'Ocurrió un error al agregar al carrito.', 'error');
    }
};

// Iniciar conversación con el vendedor
const startChat = (userId: number) => {
    if (!userId) return;
    router.visit(`/conversations/${userId}`);
};
</script>

<template>
    <AppLayout>
        <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <h1 class="flex items-center gap-4 mb-10 text-5xl font-black tracking-wide uppercase text-primary sm:text-6xl">
                <Pill class="w-10 h-10 text-primary sm:h-12 sm:w-12" />
                Suplementos disponibles
            </h1>

            <div class="grid items-stretch justify-center gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
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
        <Notification :message="notification.message" :type="notification.type" :show="showNotification" />
    </AppLayout>
</template>
