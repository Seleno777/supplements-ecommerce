<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { PackagePlus } from 'lucide-vue-next'; // ícono temático
import { ref } from 'vue';
import Notification from '@/components/ui/notification/Notification.vue';

const form = ref({
    name: '',
    description: '',
    category: '',
    price: 0,
    stock: 0,
});

const loading = ref(false);
const error = ref('');

// Notificación simple
const notification = ref<{ message: string; type: 'success' | 'error' | null }>({ message: '', type: null });
const showNotification = ref(false);

function notify(message: string, type: 'success' | 'error' = 'success') {
    notification.value = { message, type };
    showNotification.value = true;
    setTimeout(() => {
        showNotification.value = false;
    }, 3000);
}

const submit = async () => {
    loading.value = true;
    error.value = '';

    try {
        await axios.post('/products', form.value);
        notify('Producto creado exitosamente ✅', 'success');
        setTimeout(() => {
            router.visit('/my-products');
        }, 1000); // redirección con breve espera tras notificación
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Error inesperado';
        notify(error.value, 'error');
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <AppLayout>
        <div class="max-w-xl p-6 mx-auto mt-10 bg-white rounded shadow dark:bg-gray-800">
            <h1 class="flex items-center gap-2 mb-6 text-2xl font-bold text-primary">
                <PackagePlus class="w-6 h-6" />
                Crear nuevo producto
            </h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block mb-1 font-semibold">Nombre</label>
                    <input v-model="form.name" type="text" class="w-full p-2 border rounded" />
                </div>

                <div>
                    <label class="block mb-1 font-semibold">Descripción</label>
                    <textarea v-model="form.description" class="w-full p-2 border rounded"></textarea>
                </div>

                <div>
                    <label class="block mb-1 font-semibold">Categoría</label>
                    <input v-model="form.category" type="text" class="w-full p-2 border rounded" />
                </div>

                <div>
                    <label class="block mb-1 font-semibold">Precio (USD)</label>
                    <input v-model.number="form.price" type="number" class="w-full p-2 border rounded" />
                </div>

                <div>
                    <label class="block mb-1 font-semibold">Stock</label>
                    <input v-model.number="form.stock" type="number" class="w-full p-2 border rounded" />
                </div>

                <div v-if="error" class="font-semibold text-red-600">{{ error }}</div>

                <button type="submit" :disabled="loading" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50">
                    {{ loading ? 'Guardando...' : 'Guardar producto' }}
                </button>
            </form>
        </div>

        <!-- Notificación -->
        <Notification :message="notification.message" :type="notification.type" :show="showNotification" />
    </AppLayout>
</template>
